<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret')
            );
        } catch (\UnexpectedValueException | SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid webhook'], 400);
        }

        $session = $event->data->object;

        if ($event->type === 'checkout.session.completed') {

            $payment = Payment::where(
                'stripe_session_id',
                $session->id
            )->first();

            if (
                $payment &&
                $session->payment_status === 'paid' &&
                $session->currency === $payment->currency &&
                $session->amount_total === (int) round($payment->amount * 1000)
            ) {
                Payment::whereKey($payment->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'paid',
                        'stripe_payment_intent_id' => $session->payment_intent,
                        'paid_at' => now(),
                    ]);
            }
        }

        if ($event->type === 'checkout.session.expired') {
            Payment::where('stripe_session_id', $session->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                ]);
        }

        return response()->json(['received' => true]);
    }
}
