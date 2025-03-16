# SagaPay PHP SDK

SagaPay (https://sagapay.net) is the world's first free, non-custodial blockchain payment gateway service provider, enabling businesses to seamlessly integrate cryptocurrency payments without holding customer funds. With enterprise-grade security and zero transaction fees, SagaPay empowers merchants to accept crypto payments across multiple blockchains while maintaining full control of their digital assets.

## Installation

Install the SagaPay PHP SDK via Composer:

```bash
composer require sagapay/sdk-php
```

## Quick Start

```php
<?php
// Initialize the SagaPay client
require_once 'vendor/autoload.php';

use SagaPay\SDK\Client;

$client = new Client('your-api-key', 'your-api-secret');

// Create a deposit address
try {
    $deposit = $client->createDeposit([
        'networkType' => 'BEP20',
        'contractAddress' => '0', // Use '0' for native coins
        'amount' => '1.5',
        'ipnUrl' => 'https://yourwebsite.com/webhook.php',
        'udf' => 'order-123',
        'type' => 'TEMPORARY'
    ]);
    
    echo "Deposit address created: " . $deposit['address'];
} catch (\SagaPay\SDK\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Features

- Deposit address generation
- Withdrawal processing
- Transaction status checking
- Wallet balance fetching
- Multi-chain support (ERC20, BEP20, TRC20, POLYGON, SOLANA)
- Webhook notifications (IPN)
- Custom UDF field support
- Zero transaction fees
- Non-custodial architecture

## API Reference

### Create Deposit

```php
$deposit = $client->createDeposit([
    'networkType' => 'BEP20',          // Required: ERC20, BEP20, TRC20, POLYGON, SOLANA
    'contractAddress' => '0',          // Required: Contract address or '0' for native coins
    'amount' => '1.5',                 // Required: Expected deposit amount
    'ipnUrl' => 'https://example.com/webhook.php',  // Required: URL for notifications
    'udf' => 'order-123',              // Optional: User-defined field
    'type' => 'TEMPORARY'              // Optional: TEMPORARY or PERMANENT
]);
```

### Create Withdrawal

```php
$withdrawal = $client->createWithdrawal([
    'networkType' => 'ERC20',          // Required: ERC20, BEP20, TRC20, POLYGON, SOLANA
    'contractAddress' => '0xdAC17F...', // Required: Contract address or '0' for native coins
    'address' => '0x742d35C...',       // Required: Destination wallet address
    'amount' => '10.5',                // Required: Withdrawal amount
    'ipnUrl' => 'https://example.com/webhook.php',  // Required: URL for notifications
    'udf' => 'withdrawal-456'          // Optional: User-defined field
]);
```

### Check Transaction Status

```php
$status = $client->checkTransactionStatus('0x742d35C...', 'deposit');
```

### Fetch Wallet Balance

```php
$balance = $client->fetchWalletBalance(
    '0x742d35C...',  // Address
    'ERC20',         // Network type
    '0xdAC17F...'    // Contract address (use '0' for native currency)
);
```

## Handling Webhooks (IPN)

SagaPay sends webhook notifications to your specified `ipnUrl` when transaction statuses change. Use the WebhookHandler to process these notifications:

```php
<?php
// webhook.php
require_once 'vendor/autoload.php';

use SagaPay\SDK\Client;
use SagaPay\SDK\WebhookHandler;

$client = new Client('your-api-key', 'your-api-secret');
$webhookHandler = new WebhookHandler($client);

try {
    // Process and validate the webhook
    $webhookData = $webhookHandler->processWebhook(getallheaders(), file_get_contents('php://input'));
    
    // Handle the validated webhook data
    $transactionId = $webhookData['id'];
    $type = $webhookData['type']; // 'deposit' or 'withdrawal'
    $status = $webhookData['status']; // 'PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'CANCELLED'
    $address = $webhookData['address'];
    $amount = $webhookData['amount'];
    $udf = $webhookData['udf'] ?? null; // Your custom reference field
    
    // Update your database or trigger actions based on status
    if ($status === 'COMPLETED') {
        // Process successful payment
        // e.g., updateOrderStatus($udf, 'paid');
    }
    
    // Send success response
    $webhookHandler->sendSuccessResponse();
    
} catch (\SagaPay\SDK\Exception $e) {
    // Log the error and send error response
    error_log("Webhook error: " . $e->getMessage());
    $webhookHandler->sendErrorResponse($e->getMessage(), $e->getCode());
}
```

## Webhook Payload Format

When SagaPay sends a webhook to your endpoint, it will include the following payload:

```json
{
  "id": "transaction-uuid",
  "type": "deposit|withdrawal",
  "status": "PENDING|PROCESSING|COMPLETED|FAILED|CANCELLED",
  "address": "0x123abc...",
  "networkType": "ERC20|BEP20|TRC20|POLYGON|SOLANA",
  "amount": "10.5",
  "udf": "your-optional-user-defined-field",
  "txHash": "0xabc123...",
  "timestamp": "2025-03-16T14:30:00Z"
}
```

## Error Handling

All methods in the SagaPay SDK can throw a `SagaPay\SDK\Exception`. This exception contains:

- Message: Error description
- Code: Error code
- HTTP code: HTTP status code (when applicable)

```php
try {
    $client->createDeposit($params);
} catch (\SagaPay\SDK\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "HTTP Status: " . $e->getHttpCode() . "\n";
}
```

## License

This SDK is released under the MIT License.

## Support

For questions or support, please contact support@sagapay.net or visit [https://sagapay.net](https://sagapay.net).