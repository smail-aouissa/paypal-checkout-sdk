<?php

require_once 'vendor/autoload.php';

use PayPal\Checkout\Factory\PaymentProviderFactory;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;
use PayPal\Checkout\Orders\Amount;

// Configuration - Replace with your actual credentials
$config = [
    'paypal' => [
        'client_id' => 'your_paypal_client_id',
        'client_secret' => 'your_paypal_client_secret',
    ],
    'stripe' => [
        'secret_key' => 'sk_test_your_stripe_secret_key',
        'publishable_key' => 'pk_test_your_stripe_publishable_key',
    ]
];

$environment = 'sandbox'; // or 'production'

echo "=== Multi-Provider Payment SDK Test ===\n\n";

function createTestOrder($amount = '50.00', $currency = 'USD', $intent = 'CAPTURE') {
    $order = new Order();
    $order->setIntent($intent);
    
    $purchaseUnit = new PurchaseUnit();
    $purchaseUnit->setAmount(new Amount($currency, $amount));
    $purchaseUnit->setReferenceId('test-order-' . time());
    
    $order->addPurchaseUnit($purchaseUnit);
    
    return $order;
}

function testProvider($providerName, $provider, $order) {
    echo "--- Testing {$providerName} Provider ---\n";
    
    try {
        // Test 1: Create Order/Payment
        echo "1. Creating payment...\n";
        $response = $provider->createOrder($order);
        $responseData = json_decode($response->getBody(), true);
        
        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
            echo "✓ Payment created successfully\n";
            echo "   Status Code: " . $response->getStatusCode() . "\n";
            
            if ($providerName === 'PayPal') {
                echo "   Order ID: " . $responseData['id'] . "\n";
                echo "   Status: " . $responseData['status'] . "\n";
                
                // Find approval URL
                foreach ($responseData['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        echo "   Approval URL: " . $link['href'] . "\n";
                        break;
                    }
                }
            } else {
                echo "   Payment Intent ID: " . $responseData['id'] . "\n";
                echo "   Status: " . $responseData['status'] . "\n";
                echo "   Client Secret: " . $responseData['client_secret'] . "\n";
                echo "   Amount: " . $responseData['amount'] . " " . strtoupper($responseData['currency']) . "\n";
            }
            
            $orderId = $responseData['id'];
            
            // Test 2: Show Order/Payment Details
            echo "\n2. Retrieving payment details...\n";
            $showResponse = $provider->showOrder($orderId);
            $showData = json_decode($showResponse->getBody(), true);
            
            if ($showResponse->getStatusCode() === 200) {
                echo "✓ Payment details retrieved successfully\n";
                echo "   ID: " . $showData['id'] . "\n";
                echo "   Status: " . $showData['status'] . "\n";
            } else {
                echo "✗ Failed to retrieve payment details\n";
                echo "   Status Code: " . $showResponse->getStatusCode() . "\n";
            }
            
            // Test 3: Authorize Order (if supported)
            echo "\n3. Authorizing payment...\n";
            try {
                $authResponse = $provider->authorizeOrder($orderId);
                $authData = json_decode($authResponse->getBody(), true);
                
                if ($authResponse->getStatusCode() === 200 || $authResponse->getStatusCode() === 201) {
                    echo "✓ Payment authorized successfully\n";
                } else {
                    echo "ℹ Authorization response: " . $authResponse->getStatusCode() . "\n";
                }
            } catch (Exception $e) {
                echo "ℹ Authorization not applicable or failed: " . $e->getMessage() . "\n";
            }
            
            // Test 4: Capture Order (Note: This would actually charge the payment in production)
            echo "\n4. Capture test (simulation - not actually capturing)...\n";
            echo "ℹ In production, this would capture/charge the payment\n";
            echo "ℹ Skipping actual capture to avoid charges\n";
            
            /*
            // Uncomment to actually test capture (will charge real money in production!)
            try {
                $captureResponse = $provider->captureOrder($orderId);
                $captureData = json_decode($captureResponse->getBody(), true);
                
                if ($captureResponse->getStatusCode() === 200 || $captureResponse->getStatusCode() === 201) {
                    echo "✓ Payment captured successfully\n";
                } else {
                    echo "✗ Failed to capture payment\n";
                    echo "   Status Code: " . $captureResponse->getStatusCode() . "\n";
                }
            } catch (Exception $e) {
                echo "✗ Capture failed: " . $e->getMessage() . "\n";
            }
            */
            
        } else {
            echo "✗ Failed to create payment\n";
            echo "   Status Code: " . $response->getStatusCode() . "\n";
            echo "   Response: " . $response->getBody() . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    echo "\n";
}

// Test PayPal Provider
try {
    echo "Creating PayPal provider...\n";
    $paypalProvider = PaymentProviderFactory::create('paypal', $environment, $config['paypal']);
    $order = createTestOrder('25.00', 'USD', 'CAPTURE');
    testProvider('PayPal', $paypalProvider, $order);
} catch (Exception $e) {
    echo "✗ PayPal Provider Error: " . $e->getMessage() . "\n\n";
}

// Test Stripe Provider
try {
    echo "Creating Stripe provider...\n";
    $stripeProvider = PaymentProviderFactory::create('stripe', $environment, $config['stripe']);
    $order = createTestOrder('25.00', 'USD', 'CAPTURE');
    testProvider('Stripe', $stripeProvider, $order);
} catch (Exception $e) {
    echo "✗ Stripe Provider Error: " . $e->getMessage() . "\n\n";
}

// Configuration Test
echo "--- Configuration Test ---\n";
echo "Testing provider factory with different configurations...\n\n";

$testConfigs = [
    'empty_paypal' => ['paypal', 'sandbox', []],
    'empty_stripe' => ['stripe', 'sandbox', []],
    'invalid_driver' => ['invalid', 'sandbox', []],
];

foreach ($testConfigs as $testName => $testConfig) {
    echo "Testing {$testName}:\n";
    try {
        $provider = PaymentProviderFactory::create($testConfig[0], $testConfig[1], $testConfig[2]);
        echo "✓ Configuration accepted (unexpected)\n";
    } catch (Exception $e) {
        echo "✓ Expected error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "=== Test Complete ===\n";
echo "\nNotes:\n";
echo "- Make sure to replace the configuration with your actual credentials\n";
echo "- This test uses sandbox/test mode to avoid real charges\n";
echo "- PayPal orders require customer approval via redirect URL\n";
echo "- Stripe payments can be completed programmatically\n";
echo "- Capture operations are commented out to prevent real charges\n";