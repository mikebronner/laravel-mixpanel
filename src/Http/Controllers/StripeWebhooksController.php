<?php namespace GeneaLabs\LaravelMixpanel\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;

class StripeWebhooksController extends Controller
{
    /**
     * @param LaravelMixpanel $mixPanel
     */
    public function postTransaction(LaravelMixpanel $mixPanel)
    {
        $data = Input::json()->all();

        if (! $data || ! array_key_exists('data', $data)) {
            throw new Exception('Missing "data" parameter in Stripe webhook POST request: ' . $data);
        }

        $transaction = $data['data']['object'];
        $originalValues = (array_key_exists('previous_attributes', $data['data']) ? $data['data']['previous_attributes'] : []);
        $stripeCustomerId = $this->findStripeCustomerId($transaction);
        $user = App::make(config('auth.model'))->where('stripe_id', $stripeCustomerId)->first();

        if (! $user) {
            return;
        }

        $mixPanel->identify($user->id);

        if ($transaction['object'] === 'charge' && ! count($originalValues)) {
            $this->recordCharge($mixPanel, $transaction, $user);
        }

        if ($transaction['object'] === 'subscription') {
            $this->recordSubscription($mixPanel, $transaction, $user, $originalValues);
        }
    }

    /**
     * @param LaravelMixpanel $mixPanel
     * @param          $transaction
     * @param          $user
     */
    private function recordCharge(LaravelMixpanel $mixPanel, $transaction, $user)
    {
        if ($transaction['paid'] && $transaction['captured'] && ! $transaction['refunded']) {
            $mixPanel->people->trackCharge($user->id, ($transaction['amount'] / 100));
            $mixPanel->track('Payment', [
                'Status' => 'Successful',
                'Amount' => ($transaction['amount'] / 100),
            ]);
        }

        if ($transaction['paid'] && $transaction['captured'] && $transaction['refunded']) {
            $mixPanel->people->trackCharge($user->id, 0 - ($transaction['amount'] / 100));
            $mixPanel->track('Payment', [
                'Status' => 'Refunded',
                'Amount' => ($transaction['amount'] / 100),
            ]);
        }

        if (! $transaction['paid'] && $transaction['captured'] && ! $transaction['refunded']) {
            $mixPanel->track('Payment', [
                'Status' => 'Failed',
                'Amount' => ($transaction['amount'] / 100),
            ]);
        }

        if ($transaction['paid'] && ! $transaction['captured'] && ! $transaction['refunded']) {
            $mixPanel->track('Payment', [
                'Status' => 'Authorized',
                'Amount' => ($transaction['amount'] / 100),
            ]);
        }
    }

    /**
     * @todo refactor all these if statements
     *
     * @param LaravelMixpanel $mixPanel
     * @param          $transaction
     * @param          $user
     * @param array    $originalValues
     */
    private function recordSubscription(LaravelMixpanel $mixPanel, $transaction, $user, array $originalValues = [])
    {
        $planStatus = array_key_exists('status', $transaction) ? $transaction['status'] : null;
        $planName = isset($transaction['plan']['name']) ? $transaction['plan']['name'] : null;
        $planStart = array_key_exists('start', $transaction) ? $transaction['start'] : null;
        $planAmount = isset($transaction['plan']['amount']) ? $transaction['plan']['amount'] : null;
        $oldPlanName = isset($originalValues['plan']['name']) ? $originalValues['plan']['name'] : null;
        $oldPlanAmount = isset($originalValues['plan']['amount']) ? $originalValues['plan']['amount'] : null;

        if ($planStatus === 'canceled') {
            $mixPanel->people->set($user->id, [
                'Subscription' => 'None',
                'Churned' => Carbon::now('UTC')->format('Y-m-d\Th:i:s'),
                'Plan When Churned' => $planName,
                'Paid Lifetime' => Carbon::createFromTimestampUTC($planStart)->diffInDays(Carbon::now('UTC')) . ' days'
            ]);
            $mixPanel->track('Subscription', ['Status' => 'Canceled', 'Upgraded' => false]);
            $mixPanel->track('Churn! :-(');
        }

        if (count($originalValues)) {
            if ($planAmount && $oldPlanAmount) {
                if ($planAmount < $oldPlanAmount) {
                    $mixPanel->people->set($user->id, [
                        'Subscription' => $planName,
                        'Churned' => Carbon::now('UTC')->format('Y-m-d\Th:i:s'),
                        'Plan When Churned' => $oldPlanName,
                    ]);
                    $mixPanel->track('Subscription', [
                        'Upgraded' => false,
                        'FromPlan' => $oldPlanName,
                        'ToPlan' => $planName,
                    ]);
                    $mixPanel->track('Churn! :-(');
                }

                if ($planAmount > $oldPlanAmount) {
                    $mixPanel->people->set($user->id, [
                        'Subscription' => $planName,
                    ]);
                    $mixPanel->track('Subscription', [
                        'Upgraded' => true,
                        'FromPlan' => $oldPlanName,
                        'ToPlan' => $planName,
                    ]);
                    $mixPanel->track('Unchurn! :-)');
                }
            } else {
                if ($planStatus === 'trialing' && ! $oldPlanName) {
                    $mixPanel->people->set($user->id, [
                        'Subscription' => $planName,
                    ]);
                    $mixPanel->track('Subscription', [
                        'Upgraded' => true,
                        'FromPlan' => 'Trial',
                        'ToPlan' => $planName,
                    ]);
                    $mixPanel->track('Unchurn! :-)');
                }
            }
        } else {
            if ($planStatus === 'active') {
                $mixPanel->people->set($user->id, [
                    'Subscription' => $planName,
                ]);
                $mixPanel->track('Subscription', ['Status' => 'Created']);
            }

            if ($planStatus === 'trialing') {
                $mixPanel->people->set($user->id, [
                    'Subscription' => 'Trial',
                ]);
                $mixPanel->track('Subscription', ['Status' => 'Trial']);
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
