# Laravel Payment Gateway SDK for BAS Platform

This SDK provides API-only payment integration with the BAS Super App platform for Laravel applications. It allows merchants to accept payments through BAS wallet and various payment methods supported by the BAS platform without requiring a Mini App implementation.

## Features

- ✅ Payment Initiation - Start payment transactions
- ✅ Transaction Status - Check payment status
- ✅ Refund Processing - Process refunds
- ✅ Token Authentication - Automatic bearer token management
- ✅ Signature Verification - Secure API communication with AES-256-CBC encryption
- ✅ API-Only - No UI, routes, or controllers (pure backend SDK)

## Installation

1. **Require the package via Composer:**

   ```bash
   composer require basgate/laravel-payment-sdk
   ```

2. **The service provider will be automatically registered via Laravel's package auto-discovery.**

## Requirements

- **PHP**: 8.1, 8.2, or 8.3
- **Laravel**: 10.x, 11.x, or 12.x
- **PHP Extensions**: OpenSSL, JSON

This package uses modern PHP features including typed properties, union types, and return type declarations.

## Configuration

1. **Add your BAS credentials to your `.env` file:**

   ```env
   BAS_BASE_URL=https://api.basgate.com
   BAS_CLIENT_ID=your_client_id
   BAS_CLIENT_SECRET=your_client_secret
   BAS_APP_ID=your_app_id
   BAS_MERCHANT_KEY=your_merchant_key
   BAS_ENVIRONMENT=production  # or staging
   ```

2. **Environment Variable Descriptions:**

   - **`BAS_BASE_URL`**: The base URL for the BAS API platform
     - Staging: `https://staging-api.basgate.com`
     - Production: `https://api.basgate.com`
   - **`BAS_CLIENT_ID`**: Your Client ID provided by BAS
   - **`BAS_CLIENT_SECRET`**: Your Client Secret (keep this secure)
   - **`BAS_APP_ID`**: Your App ID provided by BAS
   - **`BAS_MERCHANT_KEY`**: Merchant Key for signature generation
   - **`BAS_IV`**: (Optional) 16-byte Initialization Vector - uses BAS default if not set
   - **`BAS_ENVIRONMENT`**: Set to `staging` or `production`

   **Security Notes:**
   - Never hardcode credentials in your code
   - Keep your merchant key secure and never commit it to version control
   - The IV uses a default constant provided by BAS - only override if instructed by BAS support
   - Keep `BAS_CLIENT_SECRET` and `BAS_MERCHANT_KEY` secure
   - Do not commit credentials to version control

## Usage

### Using the Facade

```php
use Bas\LaravelPayment\Facades\BasPaymentFacade as BasPayment;

// Initiate a payment transaction
$orderId = 'ORDER_' . time();
$amount = 100.00;
$currency = 'YER';

$payment = BasPayment::initiateTransaction($orderId, $amount, $currency);

if ($payment['status'] === 1 && $payment['code'] === '1111') {
    $trxToken = $payment['body']['trxToken'];
    // Store trxToken for later reference
}
```

### Check Transaction Status

```php
use Bas\LaravelPayment\Facades\BasPaymentFacade as BasPayment;

$orderId = 'ORDER_123';
$status = BasPayment::checkTransactionStatus($orderId);

if ($status['body']['transaction_status'] === 'SUCCESS') {
    // Payment successful - update order status
} elseif ($status['body']['transaction_status'] === 'FAILED') {
    // Payment failed - handle accordingly
}
```

### Process Refund

```php
use Bas\LaravelPayment\Facades\BasPaymentFacade as BasPayment;

$trxToken = 'your_transaction_token';
$reason = 'Customer requested refund';

$refund = BasPayment::refund($trxToken, $reason);

if ($refund['status'] === 1) {
    // Refund processed successfully
}
```

### Send Notification

```php
use Bas\LaravelPayment\Facades\BasPaymentFacade as BasPayment;

$orderId = 'ORDER_123';
$message = 'Your payment has been received successfully';

```

## API Methods

### `initiateTransaction($orderId, $amount, $currency)`

Initiates a new payment transaction.

**Parameters:**
- `$orderId` (string): Unique order identifier
- `$amount` (float): Payment amount
- `$currency` (string): Currency code (e.g., 'YER', 'USD')

**Returns:** Array with transaction details including `trxToken`

### `checkTransactionStatus($orderId)`

Checks the status of a payment transaction.

**Parameters:**
- `$orderId` (string): Order identifier

**Returns:** Array with transaction status

### `refund($trxToken, $reason = 'test')`

Processes a refund for a completed transaction.

**Parameters:**
- `$trxToken` (string): Transaction token from initiate response
- `$reason` (string): Reason for refund (optional)

**Returns:** Array with refund status

## Response Format

All API responses follow this structure:

```php
[
    'status' => 1,  // 1 = success, 0 = failure
    'code' => '1111',  // Response code
    'head' => [
        'responseCode' => 'string',
        'responseMessage' => 'string',
        'signature' => 'string',  // For secured endpoints
    ],
    'body' => [
        // Response data
    ]
]
```

## Complete Example

```php
<?php

namespace App\Http\Controllers;

use Bas\LaravelPayment\Facades\BasPaymentFacade as BasPayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function initiatePayment(Request $request)
    {
        try {
            $orderId = 'ORDER_' . time();
            $amount = $request->input('amount');
            
            // Initiate transaction
            $payment = BasPayment::initiateTransaction($orderId, $amount, 'YER');
            
            if ($payment['status'] === 1 && $payment['code'] === '1111') {
                // Store payment details in database
                // Redirect customer to payment page or return trxToken
                
                return response()->json([
                    'success' => true,
                    'orderId' => $orderId,
                    'trxToken' => $payment['body']['trxToken']
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function checkStatus($orderId)
    {
        try {
            $status = BasPayment::checkTransactionStatus($orderId);
            
            if ($status['body']['transaction_status'] === 'SUCCESS') {
                // Update order as paid in database
                // Send confirmation to customer via your preferred method
            }
            
            return response()->json($status);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

## Security

This SDK uses AES-256-CBC encryption with HMAC-SHA256 for signature generation and verification. All API requests are authenticated using bearer tokens that are automatically managed by the SDK.

## Conflict Prevention

This SDK is designed to coexist with the BAS Mini App SDK (`basgate/laravel-sdk`). Both packages can be installed in the same Laravel application without conflicts:

- **Different Package Name**: `basgate/laravel-payment-sdk` vs `basgate/laravel-sdk`
- **Different Namespace**: `Bas\LaravelPayment\` vs `Bas\LaravelSdk\`
- **Different Facade**: `BasPayment` vs `BAS`
- **Different Config**: `bas-payment` vs `bas`

## Requirements

- PHP 7.4 or higher
- Laravel 8.x, 9.x, 10.x, or 11.x

## Updating

To update to the latest version:

```bash
composer update basgate/laravel-payment-sdk
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes and version history.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security vulnerabilities, please review our [Security Policy](SECURITY.md).

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- Contact BAS technical support
- Refer to the official BAS API documentation
