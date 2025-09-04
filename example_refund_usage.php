<?php

require_once 'vendor/autoload.php';

use PayPal\Checkout\Factory\PaymentProviderFactory;
use PayPal\Checkout\Refunds\RefundRequest;
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

echo "=== Multi-Provider Refund SDK Demo ===\n\n";

function testRefund($providerName, $provider, $paymentId, $refundAmount = null, $currency = 'USD') {
    echo "--- Testing {$providerName} Refund ---\n";
    
    try {
        // Create refund request
        $refundRequest = new RefundRequest();
        
        // Set refund amount (optional - full refund if not specified)
        if ($refundAmount) {
            $amount = new Amount($currency, (string) $refundAmount);
            $refundRequest->setAmount($amount);
        }
        
        // Set additional refund details
        $refundRequest->setInvoiceId('refund-invoice-' . time())
                     ->setNoteToPayer('Refund processed as requested')
                     ->setReason('requested_by_customer'); // Stripe uses 'duplicate', 'fraudulent', 'requested_by_customer'
        
        echo "1. Processing refund...\n";
        echo "   Payment ID: {$paymentId}\n";
        if ($refundAmount) {
            echo "   Refund Amount: {$refundAmount} {$currency}\n";
        } else {
            echo "   Refund Amount: Full refund\n";
        }
        echo "   Invoice ID: " . $refundRequest->getInvoiceId() . "\n";
        echo "   Reason: " . $refundRequest->getReason() . "\n";
        
        // Execute refund
        $response = $provider->refundPayment($paymentId, $refundRequest);
        $responseData = json_decode($response->getBody(), true);
        
        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
            echo "✓ Refund processed successfully\n";
            echo "   Status Code: " . $response->getStatusCode() . "\n";
            
            if ($providerName === 'PayPal') {
                echo "   Refund ID: " . ($responseData['id'] ?? 'N/A') . "\n";
                echo "   Status: " . ($responseData['status'] ?? 'N/A') . "\n";
                echo "   Amount: " . ($responseData['amount']['value'] ?? 'N/A') . " " . 
                     ($responseData['amount']['currency_code'] ?? 'N/A') . "\n";
            } else {
                echo "   Refund ID: " . ($responseData['id'] ?? 'N/A') . "\n";
                echo "   Status: " . ($responseData['status'] ?? 'N/A') . "\n";
                echo "   Amount: " . (($responseData['amount'] ?? 0) / 100) . " " . 
                     strtoupper($responseData['currency'] ?? 'USD') . "\n";
            }
        } else {
            echo "✗ Failed to process refund\n";
            echo "   Status Code: " . $response->getStatusCode() . "\n";
            echo "   Response: " . $response->getBody() . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Refund Error: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    echo "\n";
}

// Example usage with actual payment IDs
// Note: Replace these with actual captured payment/capture IDs

// PayPal Refund Example
echo "=== PayPal Refund Examples ===\n\n";
try {
    $paypalProvider = PaymentProviderFactory::create('paypal', $environment, $config['paypal']);
    
    // Example 1: Full refund of a PayPal capture
    $paypalCaptureId = '2GG279541U471931P'; // Replace with actual capture ID from a completed payment
    echo "Example 1: Full PayPal Refund\n";
    testRefund('PayPal', $paypalProvider, $paypalCaptureId);
    
    // Example 2: Partial refund
    echo "Example 2: Partial PayPal Refund\n";
    testRefund('PayPal', $paypalProvider, $paypalCaptureId, 10.00, 'USD');
    
} catch (Exception $e) {
    echo "✗ PayPal Provider Error: " . $e->getMessage() . "\n\n";
}

// Stripe Refund Example
echo "=== Stripe Refund Examples ===\n\n";
try {
    $stripeProvider = PaymentProviderFactory::create('stripe', $environment, $config['stripe']);
    
    // Example 1: Full refund of a Stripe payment
    $stripePaymentIntentId = 'pi_1234567890abcdef'; // Replace with actual payment intent ID
    echo "Example 1: Full Stripe Refund\n";
    testRefund('Stripe', $stripeProvider, $stripePaymentIntentId);
    
    // Example 2: Partial refund
    echo "Example 2: Partial Stripe Refund\n";
    testRefund('Stripe', $stripeProvider, $stripePaymentIntentId, 15.50, 'USD');
    
} catch (Exception $e) {
    echo "✗ Stripe Provider Error: " . $e->getMessage() . "\n\n";
}

echo "=== Advanced Refund Usage ===\n\n";

// Advanced example showing how to create refund requests manually
echo "Example: Manual Refund Request Creation\n";

// Create a detailed refund request
$advancedRefund = new RefundRequest('USD', 25.00);
$advancedRefund->setInvoiceId('ADV-REFUND-' . date('Y-m-d-H-i-s'))
               ->setNoteToPayer('Advanced refund example with custom details')
               ->setReason('requested_by_customer');

// You can also set amount separately if needed
$customAmount = new Amount('EUR', '20.50');
$advancedRefund->setAmount($customAmount);

echo "Advanced Refund Request Details:\n";
echo "- Amount: " . $advancedRefund->getAmount()->getValue() . " " . 
     $advancedRefund->getAmount()->getCurrencyCode() . "\n";
echo "- Invoice ID: " . $advancedRefund->getInvoiceId() . "\n";
echo "- Note to Payer: " . $advancedRefund->getNoteToPayer() . "\n";
echo "- Reason: " . $advancedRefund->getReason() . "\n";
echo "- JSON Representation: " . $advancedRefund->toJson() . "\n\n";

echo "=== Demo Complete ===\n";
echo "\nNotes:\n";
echo "- Replace payment IDs with actual captured payment/capture IDs\n";
echo "- PayPal refunds require capture IDs, not order IDs\n";
echo "- Stripe refunds use payment intent IDs\n";
echo "- Full refunds don't require an amount parameter\n";
echo "- Partial refunds must specify the amount to refund\n";
echo "- Always test with sandbox/test credentials first\n";
echo "- Check payment gateway documentation for specific requirements\n";