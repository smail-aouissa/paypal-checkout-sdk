<?php

require_once 'vendor/autoload.php';

use PayPal\Checkout\Factory\PaymentProviderFactory;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;
use PayPal\Checkout\Orders\Amount;

// Configuration - REPLACE WITH YOUR ACTUAL CREDENTIALS
$credentials = [
    'paypal_client_id' => 'your_paypal_client_id',
    'paypal_client_secret' => 'your_paypal_client_secret',
    'stripe_secret_key' => 'sk_test_your_stripe_secret_key',
    'stripe_publishable_key' => 'pk_test_your_stripe_publishable_key',
];

$environment = 'sandbox'; // Use 'production' for live payments

echo "Multi-Provider Payment SDK - Basic Usage Example\n";
echo "================================================\n\n";

// Example 1: PayPal Payment
echo "Example 1: Creating a PayPal Payment\n";
echo "------------------------------------\n";

try {
    // Create PayPal provider
    $paypalProvider = PaymentProviderFactory::create('paypal', $environment, [
        'client_id' => $credentials['paypal_client_id'],
        'client_secret' => $credentials['paypal_client_secret']
    ]);
    
    // Create order
    $order = new Order();
    $order->setIntent('CAPTURE');
    
    // Add purchase unit
    $purchaseUnit = new PurchaseUnit();
    $purchaseUnit->setAmount(new Amount('USD', '100.00'));
    $purchaseUnit->setReferenceId('order-' . uniqid());
    
    $order->addPurchaseUnit($purchaseUnit);
    
    // Create payment
    echo "Creating PayPal order for $100.00 USD...\n";
    $response = $paypalProvider->createOrder($order);
    $responseData = json_decode($response->getBody(), true);
    
    if ($response->getStatusCode() === 201) {
        echo "✓ PayPal order created successfully!\n";
        echo "  Order ID: " . $responseData['id'] . "\n";
        echo "  Status: " . $responseData['status'] . "\n";
        
        // Find approval URL for customer
        foreach ($responseData['links'] as $link) {
            if ($link['rel'] === 'approve') {
                echo "  Customer Approval URL: " . $link['href'] . "\n";
                break;
            }
        }
        
        // Get order details
        $orderId = $responseData['id'];
        $detailsResponse = $paypalProvider->showOrder($orderId);
        $detailsData = json_decode($detailsResponse->getBody(), true);
        
        echo "  Order Details Retrieved: " . $detailsData['status'] . "\n";
        
    } else {
        echo "✗ PayPal order creation failed\n";
        echo "  Status: " . $response->getStatusCode() . "\n";
        echo "  Response: " . $response->getBody() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ PayPal Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 2: Stripe Payment
echo "Example 2: Creating a Stripe Payment\n";
echo "------------------------------------\n";

try {
    // Create Stripe provider
    $stripeProvider = PaymentProviderFactory::create('stripe', $environment, [
        'secret_key' => $credentials['stripe_secret_key'],
        'publishable_key' => $credentials['stripe_publishable_key']
    ]);
    
    // Create order (same structure as PayPal!)
    $order = new Order();
    $order->setIntent('CAPTURE');
    
    // Add purchase unit
    $purchaseUnit = new PurchaseUnit();
    $purchaseUnit->setAmount(new Amount('USD', '100.00'));
    $purchaseUnit->setReferenceId('order-' . uniqid());
    
    $order->addPurchaseUnit($purchaseUnit);
    
    // Create payment intent
    echo "Creating Stripe payment intent for $100.00 USD...\n";
    $response = $stripeProvider->createOrder($order);
    $responseData = json_decode($response->getBody(), true);
    
    if ($response->getStatusCode() === 200) {
        echo "✓ Stripe payment intent created successfully!\n";
        echo "  Payment Intent ID: " . $responseData['id'] . "\n";
        echo "  Status: " . $responseData['status'] . "\n";
        echo "  Amount: $" . ($responseData['amount'] / 100) . " " . strtoupper($responseData['currency']) . "\n";
        echo "  Client Secret: " . $responseData['client_secret'] . "\n";
        
        // Get payment intent details
        $paymentIntentId = $responseData['id'];
        $detailsResponse = $stripeProvider->showOrder($paymentIntentId);
        $detailsData = json_decode($detailsResponse->getBody(), true);
        
        echo "  Payment Details Retrieved: " . $detailsData['status'] . "\n";
        
    } else {
        echo "✗ Stripe payment intent creation failed\n";
        echo "  Status: " . $response->getStatusCode() . "\n";
        echo "  Response: " . $response->getBody() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Stripe Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 3: Dynamic Provider Selection
echo "Example 3: Dynamic Provider Selection\n";
echo "-------------------------------------\n";

function createPayment($provider, $amount, $currency = 'USD') {
    global $credentials, $environment;
    
    echo "Creating payment using {$provider} for {$amount} {$currency}...\n";
    
    try {
        // Select configuration based on provider
        $config = [];
        switch ($provider) {
            case 'paypal':
                $config = [
                    'client_id' => $credentials['paypal_client_id'],
                    'client_secret' => $credentials['paypal_client_secret']
                ];
                break;
            case 'stripe':
                $config = [
                    'secret_key' => $credentials['stripe_secret_key'],
                    'publishable_key' => $credentials['stripe_publishable_key']
                ];
                break;
            default:
                throw new Exception("Unsupported provider: {$provider}");
        }
        
        // Create provider
        $paymentProvider = PaymentProviderFactory::create($provider, $environment, $config);
        
        // Create order (same for both providers!)
        $order = new Order();
        $order->setIntent('CAPTURE');
        
        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setAmount(new Amount($currency, $amount));
        $purchaseUnit->setReferenceId('dynamic-order-' . uniqid());
        
        $order->addPurchaseUnit($purchaseUnit);
        
        // Create payment
        $response = $paymentProvider->createOrder($order);
        $responseData = json_decode($response->getBody(), true);
        
        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
            echo "✓ {$provider} payment created: " . $responseData['id'] . "\n";
            return $responseData['id'];
        } else {
            echo "✗ {$provider} payment failed: " . $response->getStatusCode() . "\n";
            return null;
        }
        
    } catch (Exception $e) {
        echo "✗ {$provider} error: " . $e->getMessage() . "\n";
        return null;
    }
}

// Test both providers with the same function
$paypalOrderId = createPayment('paypal', '25.50');
$stripePaymentId = createPayment('stripe', '25.50');

echo "\n";

// Example 4: Error Handling
echo "Example 4: Error Handling\n";
echo "-------------------------\n";

echo "Testing with invalid credentials...\n";

try {
    $invalidProvider = PaymentProviderFactory::create('paypal', 'sandbox', [
        'client_id' => 'invalid_id',
        'client_secret' => 'invalid_secret'
    ]);
    echo "✓ Provider created (will fail on API call)\n";
    
    $order = new Order();
    $order->setIntent('CAPTURE');
    $purchaseUnit = new PurchaseUnit();
    $purchaseUnit->setAmount(new Amount('USD', '10.00'));
    $order->addPurchaseUnit($purchaseUnit);
    
    $response = $invalidProvider->createOrder($order);
    echo "Unexpected success\n";
    
} catch (Exception $e) {
    echo "✓ Expected error caught: " . $e->getMessage() . "\n";
}

echo "\nTesting with missing credentials...\n";

try {
    $missingCredsProvider = PaymentProviderFactory::create('stripe', 'sandbox', []);
    echo "✗ Should have failed\n";
} catch (Exception $e) {
    echo "✓ Expected validation error: " . $e->getMessage() . "\n";
}

echo "\n=== Usage Example Complete ===\n\n";

echo "Next Steps:\n";
echo "1. Replace the credential placeholders with your actual API keys\n";
echo "2. Run: php example_usage.php\n";
echo "3. For PayPal: Use the approval URL to complete customer authentication\n";
echo "4. For Stripe: Use the client_secret in your frontend to complete payment\n";
echo "5. Check the MULTI_PROVIDER_GUIDE.md for detailed integration instructions\n";