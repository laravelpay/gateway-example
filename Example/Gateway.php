<?php

namespace App\Gateways\Example;

use Illuminate\Http\Request;
use LaraPay\Framework\Interfaces\GatewayFoundation;

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
     * @var array
     */
    protected array $currencies = [
        'USD',
        'EUR'
    ];

    public function config(): array
    {
        return [
            'public_key' => [
                'label' => 'Public Key',
                'description' => 'Stripe Public Key',
                'type' => 'text',
                'rules' => ['required', 'numeric'],
            ],
        ];
    }

    public function pay($payment)
    {
        dd('Processing Payment using stripe', $payment);
    }

    public function callback(Request $request)
    {

    }
}
