<?php namespace GeneaLabs\MixPanel\HTTP\Controllers;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use GeneaLabs\MixPanel\MixPanel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;

class StripeWebhooksController extends Controller
{
    /**
     * @param MixPanel $mixPanel
     */
    public function postTransaction(MixPanel $mixPanel)
    {
        $data = Input::json()->all();

        if (! $data || ! array_key_exists('data', $data)) {
            return;
        }

        $transaction = $data['data']['object'];
        $originalValues = (array_key_exists('previous_attributes', $data['data']) ? $data['data']['previous_attributes'] : []);
        $stripeCustomerId = array_key_exists('customer', $transaction) ? $transaction['customer'] : isset($transaction['subscriptions']['data'][0]['customer']) ?: null;
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
     * @param MixPanel $mixPanel
     * @param          $transaction
     * @param          $user
     */
    private function recordCharge(MixPanel $mixPanel, $transaction, $user)
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
     * @param MixPanel $mixPanel
     * @param          $transaction
     * @param          $user
     * @param array    $originalValues
     */
    private function recordSubscription(MixPanel $mixPanel, $transaction, $user, array $originalValues = [])
    {
        if ($transaction['status'] === 'active' && ! count($originalValues)) {
            $mixPanel->track('Subscription', ['Status' => 'Created']);
            $mixPanel->people->set($user->id, [
                'Subscription' => $transaction['plan']['name'],
            ]);
        }

        if ($transaction['status'] === 'canceled') {
            $mixPanel->track('Subscription', ['Status' => 'Canceled', 'Upgraded' => false]);
            $mixPanel->track('Churn! :-(');
            $mixPanel->people->set($user->id, [
                'Subscription' => 'None',
                'Churned' => Carbon::now('UTC')->format('Y-m-d\Th:i:s'),
                'Plan When Churned' => $transaction['plan']['name'],
                'Paid Lifetime' => Carbon::createFromTimestampUTC($transaction['start'])->diffInDays(Carbon::now('UTC')) . ' days'
            ]);
        }

        if (count($originalValues)) {
            if (array_key_exists('plan', $originalValues)
                && array_key_exists('amount', $originalValues['plan'])
            ) {
                if ($transaction['plan']['amount'] < $originalValues['plan']['amount']) {
                    $mixPanel->people->set($user->id, [
                        'Subscription' => $transaction['plan']['name'],
                        'Churned' => Carbon::now('UTC')->format('Y-m-d\Th:i:s'),
                        'Plan When Churned' => $originalValues['plan']['name'],
                    ]);
                    $mixPanel->track('Subscription', [
                        'Upgraded' => false,
                        'FromPlan' => $originalValues['plan']['name'],
                        'ToPlan' => $transaction['plan']['name'],
                    ]);
                    $mixPanel->track('Churn! :-(');
                }

                if ($transaction['plan']['amount'] > $originalValues['plan']['amount']) {
                    $mixPanel->people->set($user->id, [
                        'Subscription' => $transaction['plan']['name'],
                    ]);
                    $mixPanel->track('Subscription', [
                        'Upgraded' => true,
                        'FromPlan' => $originalValues['plan']['name'],
                        'ToPlan' => $transaction['plan']['name'],
                    ]);
                    $mixPanel->track('Unchurn! :-)');
                }
            } else {
                if ($transaction['status'] === 'trialing' && ! array_key_exists('plan', $originalValues)) {
                    $mixPanel->people->set($user->id, [
                        'Subscription' => $transaction['plan']['name'],
                    ]);
                    $mixPanel->track('Subscription', [
                        'Upgraded' => true,
                        'FromPlan' => $originalValues['plan']['name'],
                        'ToPlan' => $transaction['plan']['name'],
                    ]);
                    $mixPanel->track('Unchurn! :-)');
                }
            }
        } else {
            if ($transaction['status'] === 'trialing') {
                $mixPanel->track('Subscription', ['Status' => 'Trial']);
                $mixPanel->people->set($user->id, [
                    'Subscription' => 'Trial',
                ]);
            }
        }
    }
}
