<?php namespace GeneaLabs\LaravelMixpanel\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RecordStripeEvent extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            //
        ];
    }

    public function process()
    {
        $data = $this->json()->all();

        if (! $data || ! array_key_exists('data', $data)) {
            return;
        }

        $transaction = $data['data']['object'];
        $originalValues = array_key_exists('previous_attributes', $data['data'])
            ? $data['data']['previous_attributes']
            : [];
        $stripeCustomerId = $this->findStripeCustomerId($transaction);
        $authModel = config('auth.providers.users.model') ?? config('auth.model');
        $user = app($authModel)->where('stripe_id', $stripeCustomerId)->first();

        if (! $user) {
            return;
        }

        app('mixpanel')->identify($user->id);

        if ($transaction['object'] === 'charge' && ! count($originalValues)) {
            $this->recordCharge($transaction, $user);
        }

        if ($transaction['object'] === 'subscription') {
            $this->recordSubscription($transaction, $user, $originalValues);
        }
    }

    /**
     * @param          $transaction
     * @param          $user
     */
    private function recordCharge($transaction, $user)
    {
        if ($transaction['paid'] && $transaction['captured'] && ! $transaction['refunded']) {
            app('mixpanel')->people->trackCharge($user->id, ($transaction['amount'] / 100));
            app('mixpanel')->track('Payment', [
                'Status' => 'Successful',
                'Amount' => ($transaction['amount'] / 100),
            ]);
        }

        if ($transaction['paid'] && $transaction['captured'] && $transaction['refunded']) {
            app('mixpanel')->people->trackCharge($user->id, 0 - ($transaction['amount'] / 100));
            app('mixpanel')->track('Payment', [
                'Status' => 'Refunded',
                'Amount' => ($transaction['amount'] / 100),
            ]);
        }

        if (! $transaction['paid'] && $transaction['captured'] && ! $transaction['refunded']) {
            app('mixpanel')->track('Payment', [
                'Status' => 'Failed',
                'Amount' => ($transaction['amount'] / 100),
            ]);
        }

        if ($transaction['paid'] && ! $transaction['captured'] && ! $transaction['refunded']) {
            app('mixpanel')->track('Payment', [
                'Status' => 'Authorized',
                'Amount' => ($transaction['amount'] / 100),
            ]);
        }
    }

    /**
     * @todo refactor all these if statements
     *
     * @param          $transaction
     * @param          $user
     * @param array    $originalValues
     */
    private function recordSubscription($transaction, $user, array $originalValues = [])
    {
        $planStatus = array_key_exists('status', $transaction) ? $transaction['status'] : null;
        $planName = isset($transaction['plan']['name']) ? $transaction['plan']['name'] : null;
        $planStart = array_key_exists('start', $transaction) ? $transaction['start'] : null;
        $planAmount = isset($transaction['plan']['amount']) ? $transaction['plan']['amount'] : null;
        $oldPlanName = isset($originalValues['plan']['name']) ? $originalValues['plan']['name'] : null;
        $oldPlanAmount = isset($originalValues['plan']['amount']) ? $originalValues['plan']['amount'] : null;

        if ($planStatus === 'canceled') {
            app('mixpanel')->people->set($user->id, [
                'Subscription' => 'None',
                'Churned' => Carbon::now('UTC')->format('Y-m-d\Th:i:s'),
                'Plan When Churned' => $planName,
                'Paid Lifetime' => Carbon::createFromTimestampUTC($planStart)->diffInDays(Carbon::now('UTC')) . ' days'
            ]);
            app('mixpanel')->track('Subscription', ['Status' => 'Canceled', 'Upgraded' => false]);
            app('mixpanel')->track('Churn! :-(');
        }

        if (count($originalValues)) {
            if ($planAmount && $oldPlanAmount) {
                if ($planAmount < $oldPlanAmount) {
                    app('mixpanel')->people->set($user->id, [
                        'Subscription' => $planName,
                        'Churned' => Carbon::now('UTC')->format('Y-m-d\Th:i:s'),
                        'Plan When Churned' => $oldPlanName,
                    ]);
                    app('mixpanel')->track('Subscription', [
                        'Upgraded' => false,
                        'FromPlan' => $oldPlanName,
                        'ToPlan' => $planName,
                    ]);
                    app('mixpanel')->track('Churn! :-(');
                }

                if ($planAmount > $oldPlanAmount) {
                    app('mixpanel')->people->set($user->id, [
                        'Subscription' => $planName,
                    ]);
                    app('mixpanel')->track('Subscription', [
                        'Upgraded' => true,
                        'FromPlan' => $oldPlanName,
                        'ToPlan' => $planName,
                    ]);
                    app('mixpanel')->track('Unchurn! :-)');
                }
            } else {
                if ($planStatus === 'trialing' && ! $oldPlanName) {
                    app('mixpanel')->people->set($user->id, [
                        'Subscription' => $planName,
                    ]);
                    app('mixpanel')->track('Subscription', [
                        'Upgraded' => true,
                        'FromPlan' => 'Trial',
                        'ToPlan' => $planName,
                    ]);
                    app('mixpanel')->track('Unchurn! :-)');
                }
            }
        } else {
            if ($planStatus === 'active') {
                app('mixpanel')->people->set($user->id, [
                    'Subscription' => $planName,
                ]);
                app('mixpanel')->track('Subscription', ['Status' => 'Created']);
            }

            if ($planStatus === 'trialing') {
                app('mixpanel')->people->set($user->id, [
                    'Subscription' => 'Trial',
                ]);
                app('mixpanel')->track('Subscription', ['Status' => 'Trial']);
            }
        }
    }

    /**
     * @param $transaction
     *
     * @return mixed
     */
    private function findStripeCustomerId($transaction)
    {
        if (array_key_exists('customer', $transaction)) {
            return $transaction['customer'];
        }

        if (array_key_exists('object', $transaction) && $transaction['object'] === 'customer') {
            return $transaction['id'];
        }

        if (array_key_exists('subscriptions', $transaction)
            && array_key_exists('data', $transaction['subscriptions'])
            && array_key_exists(0, $transaction['subscriptions']['data'])
            && array_key_exists('customer', $transaction['subscriptions']['data'][0])
        ) {
            return $transaction['subscriptions']['data'][0]['customer'];
        }
    }
}
