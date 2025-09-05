# Laravel MailerLite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/theihasan/laravel-mailerlite.svg?style=flat-square)](https://packagist.org/packages/theihasan/laravel-mailerlite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/theihasan/laravel-mailerlite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/theihasan/laravel-mailerlite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/theihasan/laravel-mailerlite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/theihasan/laravel-mailerlite/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/theihasan/laravel-mailerlite.svg?style=flat-square)](https://packagist.org/packages/theihasan/laravel-mailerlite)

A comprehensive, fluent Laravel wrapper for the official MailerLite PHP SDK. Provides strongly-typed DTOs, expressive builders that read like English, robust services with granular exception handling, contracts for testability, and comprehensive test coverage.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Subscribers](#subscribers)
- [Groups](#groups)
- [Fields](#fields)
- [Campaigns](#campaigns)
- [Segments](#segments)
- [Automations](#automations)
- [Webhooks](#webhooks)
- [Testing](#testing)

## Installation

```bash
composer require theihasan/laravel-mailerlite
```

The package will auto-register via Laravel's package discovery.

### Publish Config

```bash
php artisan vendor:publish --provider="Ihasan\LaravelMailerlite\LaravelMailerliteServiceProvider" --tag=config
```

## Configuration

Add your MailerLite API key to `.env`:

```bash
MAILERLITE_API_KEY=your_api_key_here
MAILERLITE_API_URL=https://connect.mailerlite.com/api/
MAILERLITE_TIMEOUT=30
```

**Configuration file** (`config/mailerlite.php`):
```php
return [
    'key' => env('MAILERLITE_API_KEY'),
    'url' => env('MAILERLITE_API_URL', 'https://connect.mailerlite.com/api/'),
    'timeout' => env('MAILERLITE_TIMEOUT', 30),
];
```

## Subscribers

### How to Get All Subscribers

```php
use Ihasan\LaravelMailerlite\Facades\MailerLite;

// Get all subscribers (with pagination)
$subscribers = MailerLite::subscribers()->all();

// Get subscribers with filters
$subscribers = MailerLite::subscribers()->list([
    'filter[status]' => 'active',
    'limit' => 50,
    'page' => 1
]);

// Response structure
/*
{
    "data": [
        {
            "id": "12345",
            "email": "user@example.com",
            "name": "John Doe",
            "status": "active",
            "created_at": "2024-01-15T10:30:00Z",
            "fields": [...],
            "groups": [...]
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 25,
        "total": 150
    },
    "links": {
        "first": "...",
        "last": "...",
        "next": "..."
    }
}
*/
```

### How to Create a Subscriber

```php
// Basic subscriber creation
$subscriber = MailerLite::subscribers()
    ->email('user@example.com')
    ->named('John Doe')
    ->subscribe();

// Advanced subscriber creation with custom fields and groups
$subscriber = MailerLite::subscribers()
    ->email('jane@example.com')
    ->named('Jane Smith')
    ->withField('company', 'Acme Corp')
    ->withField('phone', '+1234567890')
    ->withFields([
        'age' => 30,
        'city' => 'New York'
    ])
    ->toGroup('mailerlite group id')
    ->toGroups(['mailerlite group id', 'mailerlite group id'])
    ->active()
    ->withAutoresponders()
    ->subscribe();

// Create with resubscribe option
$subscriber = MailerLite::subscribers()
    ->email('existing@example.com')
    ->named('Existing User')
    ->resubscribeIfExists()
    ->subscribe();

// Create imported subscriber (bypasses double opt-in)
$subscriber = MailerLite::subscribers()
    ->email('import@example.com')
    ->named('Imported User')
    ->imported()
    ->withoutAutoresponders()
    ->subscribe();
```

### How to Fetch a Subscriber

```php
// Find subscriber by email
$subscriber = MailerLite::subscribers()
    ->email('user@example.com')
    ->find();

if ($subscriber) {
    echo "Found: " . $subscriber['name'];
} else {
    echo "Subscriber not found";
}

// Find subscriber by ID
$subscriber = MailerLite::subscribers()->findById('12345');

```

### How to Update a Subscriber

```php
// Update subscriber by finding with email first
$updated = MailerLite::subscribers()
    ->email('user@example.com')
    ->named('Updated Name')
    ->withField('company', 'New Company')
    ->withField('role', 'Manager')
    ->active()
    ->update();

// Update subscriber by ID directly (if you already have the MailerLite ID)
$updated = MailerLite::subscribers()
    ->named('Direct Update')
    ->withField('last_updated', now()->toDateString())
    ->updateById('12345');
```

### How to Delete a Subscriber

```php
// Delete subscriber by finding with email first
$deleted = MailerLite::subscribers()
    ->email('user@example.com')
    ->delete(); // Returns true if deleted, false if not found
```

### How to Get Total Subscriber Count

```php
// Get total from the meta information when listing
$result = MailerLite::subscribers()->list(['limit' => 1]);
$total = $result['meta']['total'] ?? 0;

// Get active subscribers count
$result = MailerLite::subscribers()->list([
    'filter[status]' => 'active',
    'limit' => 1
]);
$activeCount = $result['meta']['total'] ?? 0;

// Get count by status
$unsubscribed = MailerLite::subscribers()->list([
    'filter[status]' => 'unsubscribed',
    'limit' => 1
])['meta']['total'] ?? 0;

$bounced = MailerLite::subscribers()->list([
    'filter[status]' => 'bounced', 
    'limit' => 1
])['meta']['total'] ?? 0;
```

### How to Unsubscribe/Resubscribe a Subscriber

```php
// Unsubscribe subscriber
$result = MailerLite::subscribers()
    ->email('user@example.com')
    ->unsubscribe();

// Resubscribe subscriber
$result = MailerLite::subscribers()
    ->email('user@example.com')
    ->resubscribe();

// Alternative: Update status directly
$result = MailerLite::subscribers()
    ->email('user@example.com')
    ->unsubscribed() // Sets status to 'unsubscribed'
    ->update();

$result = MailerLite::subscribers()
    ->email('user@example.com')
    ->active() // Sets status to 'active'
    ->update();
```

### How to Manage Subscriber Groups

```php
// Add existing subscriber to a group
$result = MailerLite::subscribers()
    ->email('user@example.com')
    ->addToGroup('group-id-123');

// Remove subscriber from a group
$result = MailerLite::subscribers()
    ->email('user@example.com')
    ->removeFromGroup('group-id-123');

// Add to multiple groups during creation
//https://dashboard.mailerlite.com/subscribers?rules=W1t7Im9wZXJhdG9yIjoiaW5fYW55IiwiY29uZGl0aW9uIjoiZ3JvdXBzIiwiYXJncyI6WyJncm91cHMiLFsiMTY0NjM3OTY3MjkyMzAyNjQxIl1dfV1d&group=164637967292302641
//This is your group id: &group=164637967292302641
$subscriber = MailerLite::subscribers()
    ->email('new@example.com')
    ->named('New User')
    ->toGroups(['group id', 'group id', 'group id'])
    ->subscribe();
```

### How to Import Subscribers in Bulk

```php
// Import multiple subscribers to a group
$subscribers = [
    [
        'email' => 'user1@example.com',
        'name' => 'User One',
        'fields' => ['company' => 'Company A']
    ],
    [
        'email' => 'user2@example.com', 
        'name' => 'User Two',
        'fields' => ['company' => 'Company B']
    ]
];

$import = MailerLite::subscribers()->importToGroup(
    'group-id-123',
    $subscribers,
    [
        'resubscribe' => true,
        'autoresponders' => false
    ]
);

// Using batch builder pattern
$import = MailerLite::subscribers()
    ->email('batch1@example.com')
    ->named('Batch User 1')
    ->withField('source', 'import')
    ->addToBatch()
    ->email('batch2@example.com') 
    ->named('Batch User 2')
    ->withField('source', 'import')
    ->addToBatch()
    ->importBatchToGroup('group-id-123', ['resubscribe' => false]);

// Get current batch before importing
$currentBatch = MailerLite::subscribers()->getBatch();

// Clear batch
MailerLite::subscribers()->clearBatch();
```

## Groups

### How to Get All Groups

```php
// Get all groups
$groups = MailerLite::groups()->all();

// Get groups with filters
$groups = MailerLite::groups()->list([
    'limit' => 50,
    'page' => 1
]);
```

### How to Create a Group

```php
// Basic group creation
$group = MailerLite::groups()
    ->name('Newsletter Subscribers')
    ->create();

// Advanced group creation
$group = MailerLite::groups()
    ->name('VIP Customers')
    ->withDescription('High-value customers with premium access')
    ->create();
```

### How to Fetch a Group

```php
// Find group by ID
$group = MailerLite::groups()->find('group-id-123');

// Find group by name
$group = MailerLite::groups()->findByName('Newsletter Subscribers');
```

### How to Update a Group

```php
$updated = MailerLite::groups()
    ->name('Updated Group Name')
    ->withDescription('Updated description')
    ->update('group-id-123');
```

### How to Delete a Group

```php
$deleted = MailerLite::groups()->delete('group-id-123');
```

### How to Manage Group Subscribers

```php
// Get subscribers in a group
$subscribers = MailerLite::groups()->getSubscribers('group-id-123', [
    'limit' => 50,
    'page' => 1
]);

// Add subscribers to group
$result = MailerLite::groups()->addSubscribers('group-id-123', [
    'subscriber-id-1',
    'subscriber-id-2'
]);

// Remove subscribers from group  
$result = MailerLite::groups()->removeSubscribers('group-id-123', [
    'subscriber-id-1'
]);
```

## Fields

### How to Get All Fields

```php
// Get all custom fields
$fields = MailerLite::fields()->all();

// Get fields with pagination
$fields = MailerLite::fields()->list([
    'limit' => 25,
    'page' => 1
]);
```

### How to Create a Field

```php
// Text field
$field = MailerLite::fields()
    ->name('company')
    ->asText()
    ->withTitle('Company Name')
    ->create();

// Number field
$field = MailerLite::fields()
    ->name('age')
    ->asNumber()
    ->withTitle('Age')
    ->required()
    ->create();

// Date field
$field = MailerLite::fields()
    ->name('birthday')
    ->asDate()
    ->withTitle('Date of Birth')
    ->create();

// Boolean field
$field = MailerLite::fields()
    ->name('newsletter_opt_in')
    ->asBoolean()
    ->withTitle('Newsletter Subscription')
    ->withDefault(true)
    ->create();

// Select field with options
$field = MailerLite::fields()
    ->name('plan')
    ->asSelect(['basic', 'pro', 'enterprise'])
    ->withTitle('Subscription Plan')
    ->create();
```

### How to Fetch a Field

```php
// Find field by ID
$field = MailerLite::fields()->find('field-id-123');

// Find field by name
$field = MailerLite::fields()->findByName('company');
```

### How to Update a Field

```php
$updated = MailerLite::fields()
    ->withTitle('Updated Company Name')
    ->update('field-id-123');
```

### How to Delete a Field

```php
$deleted = MailerLite::fields()->delete('field-id-123');
```

### How to Get Field Usage Stats

```php
$usage = MailerLite::fields()->getUsage('field-id-123');

// Response includes:
/*
{
    "subscribers_count": 150,
    "filled_count": 120,
    "empty_count": 30
}
*/
```

## Campaigns

### How to Get All Campaigns

```php
// Get all campaigns
$campaigns = MailerLite::campaigns()->all();

// Get campaigns with filters
$campaigns = MailerLite::campaigns()->list([
    'filter[status]' => 'sent',
    'limit' => 25
]);
```

### How to Create a Campaign

```php
// Create draft campaign
$campaign = MailerLite::campaigns()
    ->subject('Weekly Newsletter')
    ->from('Newsletter Team', 'newsletter@company.com')
    ->html('<h1>Newsletter</h1><p>Content here...</p>')
    ->plain('Newsletter - Content here...')
    ->toGroups(['newsletter-subscribers', 'customers'])
    ->create();

// Create and send immediately
$campaign = MailerLite::campaigns()
    ->subject('Breaking News')
    ->from('News Team', 'news@company.com')
    ->html('<h1>Important Update</h1>')
    ->toGroup('subscribers')
    ->send();

// Create and schedule
$campaign = MailerLite::campaigns()
    ->subject('Weekend Sale')
    ->from('Sales Team', 'sales@company.com')
    ->html('<h1>50% Off Everything!</h1>')
    ->toSegment('active-customers')
    ->scheduleAt(now()->addDays(2))
    ->schedule();
```

### How to Fetch a Campaign

```php
$campaign = MailerLite::campaigns()->find('campaign-id-123');
```

### How to Update a Campaign

```php
$updated = MailerLite::campaigns()
    ->subject('Updated Subject')
    ->html('<h1>Updated content</h1>')
    ->update('campaign-id-123');
```

### How to Delete a Campaign

```php
$deleted = MailerLite::campaigns()->delete('campaign-id-123');
```

### How to Manage Campaign Operations

```php
// Send existing campaign
MailerLite::campaigns()->sendById('campaign-id-123');

// Schedule existing campaign
MailerLite::campaigns()->scheduleById('campaign-id-123', now()->addHour());

// Cancel scheduled campaign
MailerLite::campaigns()->cancel('campaign-id-123');

// Get campaign statistics
$stats = MailerLite::campaigns()->stats('campaign-id-123');

// Get campaign subscribers
$subscribers = MailerLite::campaigns()->subscribers('campaign-id-123', [
    'limit' => 100,
    'filter[status]' => 'sent'
]);
```

## Segments

### How to Get All Segments

```php
// Get all segments
$segments = MailerLite::segments()->all();

// Get segments with filters
$segments = MailerLite::segments()->list([
    'filter[status]' => 'active',
    'limit' => 25
]);
```

### How to Create a Segment

```php
// Field-based segment
$segment = MailerLite::segments()
    ->name('Active Premium Users')
    ->whereField('plan', 'equals', 'premium')
    ->andWhereField('last_login', 'after', '2024-01-01')
    ->create();

// Email activity segment
$segment = MailerLite::segments()
    ->name('Highly Engaged')
    ->whoOpened(null, 30) // Opened any email in last 30 days
    ->andWhoClicked(null, 30) // And clicked in last 30 days
    ->create();

// Group-based segment
$segment = MailerLite::segments()
    ->name('Newsletter VIPs')
    ->inGroup('newsletter-subscribers')
    ->andWhereField('engagement_score', 'greater_than', 80)
    ->create();

// Date-based segment
$segment = MailerLite::segments()
    ->name('Recent Subscribers')
    ->createdAfter('2024-01-01')
    ->subscribedAfter('2024-01-01')
    ->create();
```

### How to Fetch a Segment

```php
$segment = MailerLite::segments()->find('segment-id-123');
```

### How to Update a Segment

```php
$updated = MailerLite::segments()
    ->name('Updated Segment Name')
    ->whereField('status', 'equals', 'active')
    ->update('segment-id-123');
```

### How to Delete a Segment

```php
$deleted = MailerLite::segments()->delete('segment-id-123');
```

### How to Manage Segment Operations

```php
// Get segment subscribers
$subscribers = MailerLite::segments()->getSubscribers('segment-id-123', [
    'limit' => 100
]);

// Refresh segment (recalculate matches)
MailerLite::segments()->refresh('segment-id-123');

// Get segment statistics
$stats = MailerLite::segments()->getStats('segment-id-123');

// Activate/deactivate segment
MailerLite::segments()->activate('segment-id-123');
MailerLite::segments()->deactivate('segment-id-123');
```

## Automations

### How to Get All Automations

```php
// Get all automations
$automations = MailerLite::automations()->all();

// Get automations with filters
$automations = MailerLite::automations()->list([
    'filter[status]' => 'active',
    'limit' => 25
]);
```

### How to Create an Automation

```php
// Welcome series automation
$automation = MailerLite::automations()
    ->create('Welcome Series')
    ->description('Welcome new subscribers')
    ->whenSubscriberJoinsGroup('newsletter')
    ->sendEmail('welcome-template')
    ->delayDays(3)
    ->sendEmail('getting-started-template')
    ->delayWeeks(1)
    ->sendEmail('tips-template')
    ->start();

// Conditional automation
$automation = MailerLite::automations()
    ->create('Smart Follow-up')
    ->whenSubscriberUpdatesField('purchase_status')
    ->delayHours(2)
    ->ifField('country', 'equals', 'US')
    ->sendEmail('us-specific-template')
    ->addTag('us-customer')
    ->start();

// Birthday automation
$automation = MailerLite::automations()
    ->create('Birthday Campaign')
    ->whenDateReached('birthday', 0)
    ->sendEmail('birthday-template')
    ->addTag('birthday-sent')
    ->updateField('last_birthday_email', now()->toDateString())
    ->start();
```

### How to Fetch an Automation

```php
$automation = MailerLite::automations()->find('automation-id-123');
```

### How to Update an Automation

```php
$updated = MailerLite::automations()
    ->description('Updated automation description')
    ->update('automation-id-123');
```

### How to Delete an Automation

```php
$deleted = MailerLite::automations()->delete('automation-id-123');
```

### How to Manage Automation Operations

```php
// Control automation state
MailerLite::automations()->startById('automation-id-123');
MailerLite::automations()->stopById('automation-id-123');
MailerLite::automations()->pauseById('automation-id-123');
MailerLite::automations()->resumeById('automation-id-123');

// Get automation analytics
$stats = MailerLite::automations()->stats('automation-id-123');
$activity = MailerLite::automations()->activity('automation-id-123');
$subscribers = MailerLite::automations()->subscribers('automation-id-123');
```

## Webhooks

### How to Get All Webhooks

```php
// Get all webhooks
$webhooks = MailerLite::webhooks()->all();

// Get webhooks with filters
$webhooks = MailerLite::webhooks()->list([
    'limit' => 25
]);
```

### How to Create a Webhook

```php
// Basic webhook
$webhook = MailerLite::webhooks()
    ->onSubscriberCreated('https://app.example.com/webhooks/subscriber-created')
    ->create();

// Advanced webhook with security
$webhook = MailerLite::webhooks()
    ->on('campaign.opened')
    ->url('https://app.example.com/webhooks/campaign-opened')
    ->named('Campaign Open Tracking')
    ->withSecret('webhook-secret-key')
    ->withHeaders([
        'Authorization' => 'Bearer your-token',
        'X-App-ID' => 'your-app-id'
    ])
    ->timeout(60)
    ->verifySSL(true)
    ->create();
```

### How to Fetch a Webhook

```php
$webhook = MailerLite::webhooks()->find('webhook-id-123');

// Find by URL
$webhook = MailerLite::webhooks()->findByUrl('https://app.example.com/webhook');
```

### How to Update a Webhook

```php
$updated = MailerLite::webhooks()
    ->url('https://app.example.com/new-webhook-url')
    ->timeout(120)
    ->update('webhook-id-123');
```

### How to Delete a Webhook

```php
$deleted = MailerLite::webhooks()->delete('webhook-id-123');
```

### How to Manage Webhook Operations

```php
// Test webhook
$testResult = MailerLite::webhooks()->test('webhook-id-123');

// Enable/disable webhook
MailerLite::webhooks()->enable('webhook-id-123');
MailerLite::webhooks()->disable('webhook-id-123');

// Get webhook logs
$logs = MailerLite::webhooks()->logs('webhook-id-123', [
    'limit' => 100,
    'filter[status]' => 'success'
]);

// Get webhook stats
$stats = MailerLite::webhooks()->stats('webhook-id-123');
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/pest tests/Unit/DTOs/SubscriberDTOTest.php

# Run with coverage
composer test-coverage

# Run static analysis
composer analyse
```

### Example Test

```php
use Ihasan\LaravelMailerlite\DTOs\SubscriberDTO;
use Ihasan\LaravelMailerlite\Facades\MailerLite;

test('subscriber can be created with fluent API', function () {
    $subscriber = MailerLite::subscribers()
        ->email('test@example.com')
        ->named('Test User')
        ->withField('company', 'Test Corp')
        ->subscribe();
        
    expect($subscriber['email'])->toBe('test@example.com');
    expect($subscriber['name'])->toBe('Test User');
});
```

## Error Handling

The package provides granular exception handling:

```php
use Ihasan\LaravelMailerlite\Exceptions\{
    MailerLiteAuthenticationException,
    SubscriberCreateException,
    SubscriberNotFoundException,
    CampaignSendException
};

try {
    // Your MailerLite operations here
} catch (MailerLiteAuthenticationException $e) {
    // Handle authentication issues
} catch (SubscriberCreateException $e) {
    // Handle subscriber creation failures
} catch (SubscriberNotFoundException $e) {
    // Handle missing subscribers
} catch (\InvalidArgumentException $e) {
    // Handle validation errors
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Abul Hassan](https://github.com/theihasan) - Package Author
- Built on [MailerLite PHP SDK](https://github.com/mailerlite/mailerlite-php)
- Powered by [Spatie Laravel Package Tools](https://github.com/spatie/laravel-package-tools)