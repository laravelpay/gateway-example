<?php

namespace App\Gateways\Example;

use LaraPay\Framework\Interfaces\GatewayFoundation;
use Illuminate\Support\Facades\Http;
use LaraPay\Framework\Payment;
use Illuminate\Http\Request;
use Exception;

class Gateway extends GatewayFoundation
{
    /**
     * Define the gateway identifier. This identifier should be unique. For example,
     * if the gateway name is "PayPal Express", the gateway identifier should be "paypal-express".
     *
     * @var string
     */
    protected string $identifier = 'example_gateway';

    /**
     * Define the gateway version.
     *
     * @var string
     */
    protected string $version = '1.0.0';

    /**
     * Define the currencies supported by this gateway
     *
     * (Optional)
     *
     * @var array
     */
    protected array $currencies = [
        'USD',
        'EUR'
    ];

    /**
     * Define the gateway config. Here you might
     * specify api keys, tokens and more that your gateway needs
     *
     * These values can later be accessed using $payment->gateway->config('key', 'default')
     *
     * @var array
     */
    public function config(): array
    {
        return [
            'secret_key' => [
                'label' => 'Secret Key',
                'description' => 'Example Secret Key',
                'type' => 'text',
                'rules' => ['required', 'string'], // Laravel validation rules
            ],
            // add more fields as needed
        ];
    }

    /**
     * This is the main function that handles the payment process
     *
     * In this method, you may call some API to generate a payment 
     * and redirect the user there
     *
     * @var void
     */
    public function pay($payment)
    {
        // For example, you might call a specific API to create the payment
        $checkout = Http::withToken($payment->gateway->config('secret_key'))
            ->post('https://example.app/api/v1/payment/create', [
                    // the $payment->total() returns the total value of the payment
                   'amount' => $payment->total(),
                   'currency' => $payment->currency,

                    // Retrieve the webhook URL using $payment->webhookUrl()
                    // this url listens for webhooks in the callback() method
                   'webhook_url' => $payment->webhookUrl(),
                    // $payment->successUrl() returns the url the user is redirected to after success
                   'success_url' => $payment->successUrl(),
                    // $payment->cancelUrl() returns the url the user is redirected to after they cancel the purchase
                   'cancel_url' => $payment->cancelUrl(),
            ]);

        if($checkout->failed()) {
            throw new Exception('Failed to create the payment using the API');
        }

        return redirect()->away($checkout['checkout_url']);
    }

    /**
     * This function listens for any API calls from the payment server
     *
     * If you receive missing, incorrect or bad data, throw an Exception
     *
     * @var void
     */
    public function callback(Request $request)
    {
        // Since we manually set the webhook URL earlier using $payment->webhookUrl()
        // The payment id was automatically injected into the URL, so we can retrieve it
        // easily and locate it using the Payment model
        $payment = Payment::find($request->get('payment_id'));

        // We check if the payment status is completed
        if($request->get('status') === 'COMPLETED') {
            // Lets check if the payment amount is the expected amount
            if($request->get('amount') == $payment->total()) {
                // After all the checks are complete, call the $payment->completed() method
                // In this method, we can optionally pass the transaction ID
                $payment->completed(
                    $request->get('transaction_id')
                );
            } else {
                throw new Exception('Unexpected payment total');
            }
        } else {
            throw new Exception('Unexpected Status Code');
        }
    }
}
