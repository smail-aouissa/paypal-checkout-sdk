# Multi-Provider Payment SDK Usage Guide

This package provides a unified interface for processing payments through multiple providers (PayPal and Stripe) with a consistent API.

## Installation

```bash
composer require phpjuice/payment-checkout-sdk
```

## Quick Start

### 1. Basic Setup

```php
<?php

require_once 'vendor/autoload.php';

use PayPal\Checkout\Factory\PaymentProviderFactory;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;
use PayPal\Checkout\Orders\Amount;
```

### 2. PayPal Integration

#### PayPal Configuration

```php
// PayPal Sandbox
$paypalProvider = PaymentProviderFactory::create('paypal', 'sandbox', [
    'client_id' => 'your_paypal_client_id',
    'client_secret' => 'your_paypal_client_secret'
]);

// PayPal Production
$paypalProvider = PaymentProviderFactory::create('paypal', 'production', [
    'client_id' => 'your_paypal_client_id',
    'client_secret' => 'your_paypal_client_secret'
]);
```

#### Creating a PayPal Order

```php
// Create order object
$order = new Order();
$order->setIntent('CAPTURE'); // or 'AUTHORIZE'

// Create purchase unit
$purchaseUnit = new PurchaseUnit();
$purchaseUnit->setAmount(new Amount('USD', '100.00'));
$purchaseUnit->setReferenceId('order-123');

// Add purchase unit to order
$order->addPurchaseUnit($purchaseUnit);

// Create order via PayPal
$response = $paypalProvider->createOrder($order);
$responseBody = json_decode($response->getBody(), true);

echo "PayPal Order ID: " . $responseBody['id'];
echo "Approval URL: " . $responseBody['links'][1]['href'];
```

#### PayPal Order Operations

```php
$orderId = 'paypal_order_id_here';

// Show order details
$response = $paypalProvider->showOrder($orderId);
$orderDetails = json_decode($response->getBody(), true);

// Capture payment (for CAPTURE intent)
$response = $paypalProvider->captureOrder($orderId);
$captureResult = json_decode($response->getBody(), true);

// Authorize payment (for AUTHORIZE intent)
$response = $paypalProvider->authorizeOrder($orderId);
$authResult = json_decode($response->getBody(), true);
```

### 3. Stripe Integration

#### Stripe Configuration

```php
// Stripe Sandbox (Test Mode)
$stripeProvider = PaymentProviderFactory::create('stripe', 'sandbox', [
    'secret_key' => 'sk_test_your_stripe_secret_key',
    'publishable_key' => 'pk_test_your_stripe_publishable_key' // optional
]);

// Stripe Production
$stripeProvider = PaymentProviderFactory::create('stripe', 'production', [
    'secret_key' => 'sk_live_your_stripe_secret_key',
    'publishable_key' => 'pk_live_your_stripe_publishable_key' // optional
]);
```

#### Creating a Stripe Payment Intent

```php
// Create order object (same structure as PayPal)
$order = new Order();
$order->setIntent('CAPTURE'); // or 'AUTHORIZE'

// Create purchase unit
$purchaseUnit = new PurchaseUnit();
$purchaseUnit->setAmount(new Amount('USD', '100.00'));
$purchaseUnit->setReferenceId('order-123');

// Add purchase unit to order
$order->addPurchaseUnit($purchaseUnit);

// Create payment intent via Stripe
$response = $stripeProvider->createOrder($order);
$responseBody = json_decode($response->getBody(), true);

echo "Stripe Payment Intent ID: " . $responseBody['id'];
echo "Client Secret: " . $responseBody['client_secret'];
```

#### Stripe Payment Operations

```php
$paymentIntentId = 'stripe_payment_intent_id_here';

// Show payment intent details
$response = $stripeProvider->showOrder($paymentIntentId);
$paymentDetails = json_decode($response->getBody(), true);

// Capture payment (for manual capture)
$response = $stripeProvider->captureOrder($paymentIntentId);
$captureResult = json_decode($response->getBody(), true);

// Authorize is handled automatically by Stripe
$response = $stripeProvider->authorizeOrder($paymentIntentId);
```

## Advanced Usage Examples

### 1. Dynamic Provider Selection

```php
function createPaymentProvider($provider, $environment, $config) {
    switch ($provider) {
        case 'paypal':
            return PaymentProviderFactory::create('paypal', $environment, [
                'client_id' => $config['paypal_client_id'],
                'client_secret' => $config['paypal_client_secret']
            ]);
        
        case 'stripe':
            return PaymentProviderFactory::create('stripe', $environment, [
                'secret_key' => $config['stripe_secret_key'],
                'publishable_key' => $config['stripe_publishable_key']
            ]);
        
        default:
            throw new InvalidArgumentException("Unsupported provider: {$provider}");
    }
}

// Usage
$config = [
    'paypal_client_id' => 'your_paypal_client_id',
    'paypal_client_secret' => 'your_paypal_client_secret',
    'stripe_secret_key' => 'your_stripe_secret_key',
    'stripe_publishable_key' => 'your_stripe_publishable_key'
];

$paypalProvider = createPaymentProvider('paypal', 'sandbox', $config);
$stripeProvider = createPaymentProvider('stripe', 'sandbox', $config);
```

### 2. Error Handling

```php
try {
    $provider = PaymentProviderFactory::create('paypal', 'sandbox', [
        'client_id' => 'invalid_client_id',
        'client_secret' => 'invalid_client_secret'
    ]);
    
    $response = $provider->createOrder($order);
    
    if ($response->getStatusCode() !== 200) {
        throw new Exception("Payment creation failed with status: " . $response->getStatusCode());
    }
    
    $result = json_decode($response->getBody(), true);
    
} catch (InvalidArgumentException $e) {
    echo "Configuration error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Payment error: " . $e->getMessage();
}
```

### 3. Environment Configuration with .env

Create a `.env` file:

```env
# PayPal Configuration
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret

# Stripe Configuration  
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key
STRIPE_PUBLISHABLE_KEY=pk_test_your_stripe_publishable_key

# Environment (sandbox/production)
PAYMENT_ENVIRONMENT=sandbox
```

PHP implementation:

```php
// Load environment variables (using vlucas/phpdotenv or similar)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$environment = $_ENV['PAYMENT_ENVIRONMENT'];

// Create PayPal provider
$paypalProvider = PaymentProviderFactory::create('paypal', $environment, [
    'client_id' => $_ENV['PAYPAL_CLIENT_ID'],
    'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET']
]);

// Create Stripe provider
$stripeProvider = PaymentProviderFactory::create('stripe', $environment, [
    'secret_key' => $_ENV['STRIPE_SECRET_KEY'],
    'publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY']
]);
```

## Laravel Integration

### Laravel Service Provider

Create a service provider to bind the payment provider:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PayPal\Checkout\Factory\PaymentProviderFactory;
use PayPal\Checkout\Contracts\PaymentProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PaymentProvider::class, function ($app) {
            $driver = config('payment.driver', 'paypal');
            $environment = config('payment.environment', 'sandbox');
            
            $config = config("payment.providers.{$driver}", []);
            
            return PaymentProviderFactory::create($driver, $environment, $config);
        });
    }
}
```

Add configuration file `config/payment.php`:

```php
<?php

return [
    'driver' => env('PAYMENT_DRIVER', 'paypal'),
    'environment' => env('PAYMENT_ENVIRONMENT', 'sandbox'),
    
    'providers' => [
        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        ],
        
        'stripe' => [
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        ],
    ],
];
```

### Laravel Controller Example

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPal\Checkout\Contracts\PaymentProvider;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;
use PayPal\Checkout\Orders\Amount;

class PaymentController extends Controller
{
    protected $paymentProvider;
    
    public function __construct(PaymentProvider $paymentProvider)
    {
        $this->paymentProvider = $paymentProvider;
    }
    
    public function createPayment(Request $request)
    {
        try {
            $order = new Order();
            $order->setIntent('CAPTURE');
            
            $purchaseUnit = new PurchaseUnit();
            $purchaseUnit->setAmount(new Amount($request->currency, $request->amount));
            
            $order->addPurchaseUnit($purchaseUnit);
            
            $response = $this->paymentProvider->createOrder($order);
            $responseData = json_decode($response->getBody(), true);
            
            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function capturePayment(Request $request)
    {
        try {
            $orderId = $request->order_id;
            $response = $this->paymentProvider->captureOrder($orderId);
            $responseData = json_decode($response->getBody(), true);
            
            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

## Payment Flow Comparison

| Operation | PayPal | Stripe |
|-----------|--------|--------|
| Create Payment | `createOrder()` | `createOrder()` (PaymentIntent) |
| Show Payment | `showOrder()` | `showOrder()` |
| Capture | `captureOrder()` | `captureOrder()` |
| Authorize | `authorizeOrder()` | `authorizeOrder()` (returns show) |

## Key Differences

### PayPal
- Uses Order-based API
- Requires redirect for customer approval
- Intent: `CAPTURE` or `AUTHORIZE`
- Returns approval URLs for customer interaction

### Stripe
- Uses PaymentIntent-based API
- Can be completed without redirect (with payment methods)
- Capture method: `automatic` or `manual`
- Returns client_secret for frontend integration

## Available Methods

Both PayPal and Stripe adapters implement the same `PaymentProvider` interface:

- `createOrder(Order $order): ResponseInterface` - Create a new payment order
- `showOrder(string $orderId): ResponseInterface` - Get order details
- `captureOrder(string $orderId): ResponseInterface` - Capture/complete the payment
- `authorizeOrder(string $orderId): ResponseInterface` - Authorize the payment (hold funds)

## Testing

Run the test suite:

```bash
composer test
```

## Requirements

- PHP 7.4+ or 8.0+
- ext-json extension
- Valid PayPal and/or Stripe API credentials

## Error Handling

Both providers will throw `InvalidArgumentException` for:
- Missing required credentials
- Invalid environment configuration
- Unsupported payment driver

HTTP errors are returned as PSR-7 ResponseInterface objects with appropriate status codes.

## Architecture

The package uses:

- **Factory Pattern**: `PaymentProviderFactory` creates the appropriate provider
- **Adapter Pattern**: `PayPalAdapter` and `StripeAdapter` provide unified interfaces
- **Environment Classes**: Handle different API endpoints and credentials
- **Contracts**: Ensure consistent interfaces across providers