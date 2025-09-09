# Multi-Provider Payment SDK (PayPal & Stripe)

![Tests](https://github.com/phpjuice/paypal-checkout-sdk/workflows/Tests/badge.svg?branch=main)
[![Latest Stable Version](http://poser.pugx.org/phpjuice/paypal-checkout-sdk/v)](https://packagist.org/packages/phpjuice/paypal-checkout-sdk)
[![Maintainability](https://api.codeclimate.com/v1/badges/e600bc7ccce319ffe7c7/maintainability)](https://codeclimate.com/github/phpjuice/paypal-checkout-sdk/maintainability)
[![Total Downloads](http://poser.pugx.org/phpjuice/paypal-checkout-sdk/downloads)](https://packagist.org/packages/phpjuice/paypal-checkout-sdk)
[![License](http://poser.pugx.org/phpjuice/paypal-checkout-sdk/license)](https://packagist.org/packages/phpjuice/paypal-checkout-sdk)

This package is an enhanced multi-provider payment SDK that supports both PayPal and Stripe payment processing through a unified interface. It provides a simple, fluent API to create, capture, and refund payments with both sandbox and production environments supported.

**Features:**
- ✅ Unified interface for PayPal and Stripe payments
- ✅ Complete refund functionality for both providers
- ✅ Order creation and capture
- ✅ Sandbox and production environment support
- ✅ Comprehensive error handling
- ✅ Full working examples included

## Installation

PayPal Checkout SDK Package requires PHP 7.4 or higher.

> **INFO:** If you are using an older version of php this package may not function correctly.

The supported way of installing PayPal Checkout SDK package is via Composer.

```bash
composer require phpjuice/paypal-checkout-sdk
```

## Quick Start Examples

This SDK includes comprehensive working examples to get you started quickly:

### Available Example Files

- **`example_usage.php`** - Basic usage examples for both PayPal and Stripe
- **`corrected_test.php`** - Corrected PayPal implementation example  
- **`working_example_with_refunds.php`** - Complete example with refund functionality
- **`example_refund_usage.php`** - Dedicated refund examples and advanced usage

### Running the Examples

1. Replace credential placeholders with your actual API keys in any example file
2. Run: `php example_usage.php` (or any other example file)
3. Follow the output instructions for completing payments

## Setup

### Credentials Setup

#### PayPal Credentials
Get client ID and client secret from [PayPal Developer Console](https://developer.paypal.com/developer/applications):
- Create a new REST API app
- Copy Client ID and Client Secret

#### Stripe Credentials  
Get API keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys):
- Copy Secret Key (starts with `sk_`)
- Copy Publishable Key (starts with `pk_`)

### Basic Provider Setup

```php
use PayPal\Checkout\Factory\PaymentProviderFactory;

// PayPal Provider
$paypalProvider = PaymentProviderFactory::create('paypal', 'sandbox', [
    'client_id' => 'your_paypal_client_id',
    'client_secret' => 'your_paypal_client_secret'
]);

// Stripe Provider  
$stripeProvider = PaymentProviderFactory::create('stripe', 'sandbox', [
    'secret_key' => 'sk_test_your_stripe_secret_key',
    'publishable_key' => 'pk_test_your_stripe_publishable_key'
]);
```

## Usage

### Creating Orders (Unified Interface)

The same code works for both PayPal and Stripe providers:

```php
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;
use PayPal\Checkout\Orders\Amount;

// Create order (same for both providers)
$order = new Order();
$order->setIntent('CAPTURE');

// Create purchase unit
$purchaseUnit = new PurchaseUnit();
$purchaseUnit->setAmount(new Amount('100.00', 'USD'));
$purchaseUnit->setReferenceId('order-' . time());

$order->addPurchaseUnit($purchaseUnit);

// Create payment with either provider
$response = $paymentProvider->createOrder($order);
$responseData = json_decode($response->getBody(), true);

echo "Payment ID: " . $responseData['id'];
```

### PayPal-Specific Usage

```php
// PayPal returns approval URL for customer
foreach ($responseData['links'] as $link) {
    if ($link['rel'] === 'approve') {
        echo "Customer Approval URL: " . $link['href'];
        break;
    }
}
```

### Stripe-Specific Usage  

```php
// Stripe returns client_secret for frontend integration
echo "Client Secret: " . $responseData['client_secret'];
```

### Refund Processing

Both providers support full and partial refunds through a unified interface:

```php
use PayPal\Checkout\Refunds\RefundRequest;

// Create refund request
$refundRequest = new RefundRequest('25.00', 'USD'); // Partial refund
$refundRequest->setInvoiceId('refund-' . time())
             ->setNoteToPayer('Refund processed as requested')
             ->setReason('requested_by_customer');

// Process refund (works with both PayPal capture IDs and Stripe payment intent IDs)
$refundResponse = $paymentProvider->refundPayment($paymentId, $refundRequest);
```

### Order Details

Retrieve order/payment details:

```php
// Get order details (works for both providers)
$detailsResponse = $paymentProvider->showOrder($orderId);
$details = json_decode($detailsResponse->getBody(), true);
```

## Example Files Overview

### `example_usage.php`
Complete basic usage examples demonstrating:
- PayPal and Stripe payment creation
- Order details retrieval  
- Dynamic provider selection
- Error handling patterns

### `corrected_test.php`
Focused PayPal example showing:
- Proper PayPal order structure
- JSON output for debugging
- Comprehensive error handling
- Working PayPal implementation

### `working_example_with_refunds.php`
Comprehensive example featuring:
- Multi-provider payment creation (PayPal & Stripe)
- Complete refund functionality for both providers
- Refund request creation and processing
- Summary of all implemented features

### `example_refund_usage.php`
Dedicated refund examples including:
- Full and partial refund processing
- Advanced refund request configuration
- Multi-provider refund testing
- Production-ready refund patterns

### Getting Started
1. Choose the example file that best matches your needs
2. Replace credential placeholders with your actual API keys
3. Run: `php filename.php`
4. Follow console output for next steps

## Changelog

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING.md](./CONTRIBUTING.md) for details and a todo list.

## Security

If you discover any security related issues, please email author instead of using the issue tracker.

## Credits

- [PayPal Developer Documentation](https://developer.paypal.com/docs/)
- [Stripe API Documentation](https://stripe.com/docs/api)
- [Original PayPal SDK](https://github.com/phpjuice/paypal-checkout-sdk)

## License

Please see the [LICENSE](https://github.com/phpjuice/paypal-checkout-sdk/blob/main/LICENSE) file for more information.
