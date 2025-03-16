<?php
/**
 * SagaPay PHP SDK - Webhook Handler Example
 * 
 * This file should be accessible via the URL you provided in the ipnUrl parameter
 * when creating deposits or withdrawals.
 */

// Include Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use SagaPay\SDK\Client;
use SagaPay\SDK\WebhookHandler;
use SagaPay\SDK\Exception;

// Initialize the SagaPay client with your API credentials
$client = new Client('your-api-key', 'your-api-secret');

// Create webhook handler
$webhookHandler = new WebhookHandler($client);

try {
    // Get the request headers
    $headers = getallheaders();
    
    // Get the raw request body
    $body = file_get_contents('php://input');
    
    // Process and validate the webhook
    $webhookData = $webhookHandler->processWebhook($headers, $body);
    
    // Log the webhook for debugging (optional)
    error_log('SagaPay Webhook received: ' . json_encode($webhookData));
    
    // Extract important fields
    $transactionId = $webhookData['id'];
    $type = $webhookData['type']; // 'deposit' or 'withdrawal'
    $status = $webhookData['status']; // 'PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'CANCELLED'
    $address = $webhookData['address'];
    $amount = $webhookData['amount'];
    $udf = $webhookData['udf'] ?? null; // Your custom reference field
    $txHash = $webhookData['txHash'] ?? null;
    
    // Handle different transaction statuses
    switch ($status) {
        case 'COMPLETED':
            // Payment successful, update your database and trigger necessary actions
            // For example, update order status in your system
            if ($type === 'deposit') {
                // Handle successful deposit
                // updateOrderStatus($udf, 'paid');
                error_log("Deposit {$transactionId} completed: {$amount} received at {$address}");
            } else {
                // Handle successful withdrawal
                // updateWithdrawalStatus($udf, 'completed');
                error_log("Withdrawal {$transactionId} completed: {$amount} sent to {$address}");
            }
            break;
            
        case 'FAILED':
            // Handle failed transaction
            // updateTransactionStatus($udf, 'failed');
            error_log("Transaction {$transactionId} failed: {$type} for {$amount}");
            break;
            
        case 'PENDING':
        case 'PROCESSING':
            // Handle pending or processing transaction
            // updateTransactionStatus($udf, strtolower($status));
            error_log("Transaction {$transactionId} is {$status}: {$type} for {$amount}");
            break;
            
        case 'CANCELLED':
            // Handle cancelled transaction
            // updateTransactionStatus($udf, 'cancelled');
            error_log("Transaction {$transactionId} cancelled: {$type} for {$amount}");
            break;
    }
    
    // Send success response
    $webhookHandler->sendSuccessResponse();
    
} catch (Exception $e) {
    // Log the error
    error_log("SagaPay Webhook Error: {$e->getMessage()} (Code: {$e->getCode()})");
    
    // Send error response (still returns HTTP 200 to prevent retries)
    $webhookHandler->sendErrorResponse($e->getMessage(), $e->getCode());
}