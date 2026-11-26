<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    public function checkout(int $id)
    {
        $payment = DB::transaction(function () use ($id) {

            $appointment = Appointments::with('doctor_appointment_type')
                ->where('patient_id', Auth::id())
                ->lockForUpdate()
                ->findOrFail($id);

            abort_unless(
                in_array($appointment->status, ['pending', 'confirmed']),
                403
            );

            abort_if(
                $appointment->payments()->where('status', 'paid')->exists(),
                409,
                'Appointment already paid.'
            );

            return $appointment->payments()->firstOrCreate(
                ['status' => 'pending'],
                [
                    'amount' => $appointment->doctor_appointment_type->price,
                    'currency' => 'jod',
                ]
            );
        });

        $stripe = new StripeClient(
            config('services.stripe.secret')
        );

        $session = $stripe->checkout->sessions->create([
            'allowed_payment_method_types' => ['card'],

            'line_items' => [[
                'price_data' => [
                    'currency' => $payment->currency,
                    'product_data' => [
                        'name' => 'TAQWEEM Dental Appointment',
                    ],
                    'unit_amount' => (int) round($payment->amount * 1000),
                ],
                'quantity' => 1,
            ]],

            'mode' => 'payment',

            'success_url' => route('patient.payment.success')
                . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => route('patient.workspace'),

            'metadata' => [
                'payment_id' => (string) $payment->id,
            ],

        ], [
            'idempotency_key' => 'taqweem-payment-' . $payment->id,
        ]);

        $payment->update([
            'stripe_session_id' => $session->id,
        ]);

        return redirect()->away($session->url);
    }

    public function success()
    {
        request()->validate([
            'session_id' => 'required|string',
        ]);

        $payment = Payment::where(
            'stripe_session_id',
            request('session_id')
        )->whereHas('appointment', function ($query) {
            $query->where('patient_id', Auth::id());
        })->firstOrFail();

        $stripe = new StripeClient(
            config('services.stripe.secret')
        );

        $session = $stripe->checkout->sessions->retrieve(
            $payment->stripe_session_id
        );

        if (
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

            return redirect()
                ->route('patient.workspace')
                ->with('success', 'Payment completed successfully!');
        }

        return redirect()
            ->route('patient.workspace')
            ->with('error', 'Payment was not completed.');
    }
}
