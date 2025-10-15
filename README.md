# JSON to KeyValue for Laravel Nova

A Laravel package that transforms nested JSON/arrays into flat, readable key-value pairs for display in Nova's KeyValue field.

## Why You Need This

### The Problem

Not everyone understands JSON — especially regular admin users in your Nova Admin Panel. 
Nova's built-in KeyValue field doesn't work with nested objects or JSON arrays; it simply displays them as raw JSON strings with index numbers as keys, or sometimes doesn’t display them at all.

**Without this package:**

![Before - Raw JSON](https://github.com/provydon/json-to-keyvalue/blob/main/screenshots/before.jpg)
![Before - Nova KeyValue](https://github.com/provydon/json-to-keyvalue/blob/main/screenshots/before2.jpg)

### The Solution

This package transforms your JSON data into flat, readable key-value pairs before displaying them using Laravel Nova's existing KeyValue fields.

**With this package:**

![After - KeyValue Display](https://github.com/provydon/json-to-keyvalue/blob/main/screenshots/after.jpg)


## Features

- 🔄 Handles JSON strings or arrays
- 🔍 Intelligently handles database relationships, resolving foreign keys by fetching and displaying the related record's name or any column you specify.
- 🎯 Automatically flattens nested objects
- 🎨 Pretty key formatting (snake_case → Title Case)
- 🚫 Smart filtering (exclude by prefix/suffix)
- 📊 Multiple items displayed as separate fields
- ✨ Custom formatters for any field
- 🔒 Read-only, detail-only display
- 🎨 Fluent API for clean, readable code
- 🧰 Built-in formatters (currency, dates, phone, etc.)
- ⚙️ Publishable config for global defaults
- 🎭 Blade component for non-Nova use
- 🎁 Auto-flatten single-item arrays (no more "#1" suffixes!)
- 🔢 Customizable or skippable array indices
- 📦 Conditional flattening based on array size
- 🗂️ Simplified nested array iteration

## What's New in v1.1.0

### Auto-Flatten Single-Item Arrays
No more seeing "Item #1" when you only have one item! Use `flattenSingleArrays()` to automatically extract single items:

```php
JsonToKeyvalue::make($data, 'User')
    ->flattenSingleArrays(true)
    ->toFields();
```

### Simplified Nested Array Iteration
Transform complex nested structures with one simple method:

```php
// Before
collect($this->data)->flatMap(function ($value, $key) {
    $label = ucwords(str_replace('_', ' ', $key));
    $data = is_array($value) && isset($value[0]) ? $value[0] : $value;
    return JsonToKeyvalue::make($data, $label)->flattenNested(true)->toFields();
})->toArray()

// After
JsonToKeyvalue::fromNestedArray($this->data)
```

### Customizable Array Indices
Skip indices entirely or customize their format:

```php
// Skip completely
->skipArrayIndices(true)

// Use parentheses
->arrayIndexFormat(' (%d)')

// Use brackets
->arrayIndexFormat(' [%d]')
```

### More Control
Conditionally flatten arrays and control when to process large datasets:

```php
->maxArraySize(10)  // Only process arrays with ≤10 items
```

## Requirements

- PHP 8.1+
- Laravel Nova (not included - must be installed separately)

## Installation

```bash
composer require provydon/json-to-keyvalue
```

## Usage

### Quick Start

The simplest usage:

```php
use Provydon\JsonToKeyvalue\JsonToKeyvalue;

public function fields(Request $request)
{
    return [
        JsonToKeyvalue::make($this->metadata, 'Metadata')
            ->toFields()
    ];
}
```

That's it! Your nested JSON is now flattened and displayed as readable key-value pairs.

### Adding Options

You can chain methods to customize the output:

```php
use Provydon\JsonToKeyvalue\JsonToKeyvalue;

public function fields(Request $request)
{
    return JsonToKeyvalue::make($this->metadata, 'Metadata')
        ->skip(['password'])
        ->excludeSuffixes(['_error'])
        ->formatters([
            'amount' => fn($value) => '₦' . number_format($value, 2)
        ])
        ->toFields();
}
```

**Why use the class?**
- ✨ Clean, readable, chainable methods
- 🎯 Type hints and IDE autocomplete
- 🔧 Easier to test and maintain
- 📦 Can use `toArray()` for non-Nova contexts

### Helper Function (Legacy)

The global helper function is still available but less recommended:

```php
public function fields(Request $request)
{
    return [
        json_to_keyvalue_fields($this->metadata, 'Metadata', [
            'skip' => ['password'],
            'exclude_suffixes' => ['_error']
        ])
    ];
}
```

### Blade Component (Non-Nova)

For displaying key-value pairs outside of Nova:

```blade
<x-keyvalue-display 
    :data="$jsonData" 
    label="User Details" 
    :config="['skip' => ['password']]" 
/>
```

## API Methods

### Available Methods

| Method | Parameters | Description |
|--------|------------|-------------|
| `make($data, $label)` | data, label | Create new instance |
| `fromNestedArray($data, $formatter)` | array, ?callable | Static method to iterate nested arrays |
| `skip($keys)` | array | Skip specific keys |
| `excludeSuffixes($suffixes)` | array | Exclude keys by suffix |
| `excludePrefixes($prefixes)` | array | Exclude keys by prefix |
| `flattenNested($bool)` | boolean | Enable/disable flattening |
| `nestedSeparator($sep)` | string | Set separator for nested keys |
| `flattenSingleArrays($bool)` | boolean | Auto-extract single-item arrays |
| `skipArrayIndices($bool)` | boolean | Skip adding array indices to labels |
| `arrayIndexFormat($format)` | string | Customize array index format (sprintf) |
| `maxArraySize($size)` | ?int | Only flatten arrays below this size |
| `itemLabel($label)` | string | Label for array items |
| `labels($labels)` | array | Custom field labels |
| `formatters($formatters)` | array | Custom formatters |
| `lookups($lookups)` | array | Database lookups |
| `config($config)` | array | Merge config array |
| `toFields()` | - | Return Nova fields |
| `toArray()` | - | Return plain arrays |

### Examples

**Skip specific keys**
```php
JsonToKeyvalue::make($data, 'Payment Info')
    ->skip(['cvv', 'password', 'secret_key'])
    ->toFields();
```

**Exclude by suffix/prefix**
```php
JsonToKeyvalue::make($data, 'Response')
    ->excludeSuffixes(['_error', '_debug', '_internal'])
    ->excludePrefixes(['temp_', 'cache_'])
    ->toFields();
```

**Custom labels**
```php
JsonToKeyvalue::make($data, 'User')
    ->labels([
        'dob' => 'Date of Birth',
        'phone_number' => 'Phone',
        'created_at' => 'Member Since'
    ])
    ->toFields();
```

**Custom formatters**
```php
JsonToKeyvalue::make($data, 'Transaction')
    ->formatters([
        'amount' => fn($value) => '₦' . number_format($value, 2),
        'created_at' => fn($value) => \Carbon\Carbon::parse($value)->format('M d, Y'),
        'status' => fn($value) => strtoupper($value)
    ])
    ->toFields();
```

**Database lookups**
```php
JsonToKeyvalue::make($data, 'Order')
    ->lookups([
        'user_id' => [
            'model' => \App\Models\User::class,
            'field' => 'id',
            'display' => 'name',
            'fallback' => 'user_id'
        ],
        'product_id' => [
            'model' => \App\Models\Product::class,
            'field' => 'id',
            'display' => 'title'
        ]
    ])
    ->toFields();
```

**Nested array handling**
```php
JsonToKeyvalue::make($data, 'Config')
    ->flattenNested(true)
    ->nestedSeparator(' > ')
    ->toFields();
```

**Multiple items**
```php
$orders = [
    ['id' => 1, 'total' => 5000],
    ['id' => 2, 'total' => 3000]
];

JsonToKeyvalue::make($orders, 'Order')
    ->itemLabel('Order')
    ->toFields();
```

**Auto-flatten single-item arrays**
```php
// If your data has single-item arrays like [['name' => 'John']]
// This will extract the item without showing "Item #1"
JsonToKeyvalue::make($data, 'User')
    ->flattenSingleArrays(true)
    ->toFields();
```

**Skip array indices**
```php
// Removes "#1", "#2" suffixes from labels
JsonToKeyvalue::make($items, 'Items')
    ->skipArrayIndices(true)
    ->toFields();
```

**Custom array index format**
```php
// Customize how array indices are displayed
JsonToKeyvalue::make($items, 'Item')
    ->arrayIndexFormat(' (%d)')  // Item (1), Item (2)
    ->toFields();

// Or use brackets
JsonToKeyvalue::make($items, 'Item')
    ->arrayIndexFormat(' [%d]')  // Item [1], Item [2]
    ->toFields();
```

**Conditional flattening by size**
```php
// Only process arrays with 10 or fewer items
JsonToKeyvalue::make($data, 'Large Dataset')
    ->maxArraySize(10)
    ->toFields();
```

**Nested array iteration**
```php
// The old way
Panel::make('Details', $this->data
    ? collect($this->data)->flatMap(function ($value, $key) {
        $label = ucwords(str_replace('_', ' ', $key));
        $data = is_array($value) && isset($value[0]) ? $value[0] : $value;
        return JsonToKeyvalue::make($data, $label)->flattenNested(true)->toFields();
    })->toArray()
    : []
),

// The new way ✨
Panel::make('Details', 
    $this->data ? JsonToKeyvalue::fromNestedArray($this->data) : []
),

// With custom label formatter
Panel::make('Details',
    $this->data 
        ? JsonToKeyvalue::fromNestedArray($this->data, fn($key) => strtoupper($key))
        : []
),
```

**Complete example**

```php
use Provydon\JsonToKeyvalue\JsonToKeyvalue;
use Provydon\JsonToKeyvalue\Formatters;

JsonToKeyvalue::make($data, 'Payment Details')
    ->skip(['cvv', 'secret_key'])
    ->excludeSuffixes(['_error', '_debug'])
    ->flattenNested(true)
    ->nestedSeparator(' → ')
    ->labels([
        'transaction_ref' => 'Reference',
        'created_at' => 'Date'
    ])
    ->formatters([
        'amount' => Formatters::currency('₦', 2),
        'created_at' => Formatters::datetime('M d, Y g:i A')
    ])
    ->lookups([
        'user_id' => [
            'model' => \App\Models\User::class,
            'field' => 'id',
            'display' => 'email',
            'fallback' => 'user_id'
        ]
    ])
    ->toFields();
```

**Using `toArray()` for non-Nova contexts**

```php
$data = JsonToKeyvalue::make($payment, 'Payment')
    ->skip(['internal_id'])
    ->formatters(['amount' => Formatters::currency('$')])
    ->toArray();
```

## Built-in Formatters

The package includes ready-to-use formatters:

```php
use Provydon\JsonToKeyvalue\JsonToKeyvalue;
use Provydon\JsonToKeyvalue\Formatters;

JsonToKeyvalue::make($data, 'Transaction')
    ->formatters([
        'amount' => Formatters::currency('₦', 2),
        'created_at' => Formatters::date('M d, Y'),
        'updated_at' => Formatters::datetime('M d, Y g:i A'),
        'is_active' => Formatters::boolean('Active', 'Inactive'),
        'status' => Formatters::uppercase(),
        'name' => Formatters::titleCase(),
        'phone' => Formatters::phone('+234'),
        'description' => Formatters::truncate(100),
        'discount' => Formatters::percentage(2),
        'file_size' => Formatters::fileSize(),
        'metadata' => Formatters::json(pretty: true),
        'type' => Formatters::enumLabel([
            'pending' => 'Pending Payment',
            'completed' => 'Completed'
        ])
    ])
    ->toFields();
```

### Available Formatters

- `currency($symbol, $decimals)` - Format numbers as currency
- `date($format)` - Format dates
- `datetime($format)` - Format date and time
- `boolean($trueLabel, $falseLabel)` - Convert boolean to text
- `uppercase()` - Convert to uppercase
- `lowercase()` - Convert to lowercase
- `titleCase()` - Convert to title case
- `phone($countryCode)` - Format phone numbers
- `truncate($length, $ending)` - Truncate long text
- `percentage($decimals)` - Format as percentage
- `fileSize()` - Convert bytes to human-readable size
- `json($pretty)` - Format as JSON
- `enumLabel($labels)` - Map enum values to labels

## Global Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=json-to-keyvalue-config
```

Set global defaults in `config/json-to-keyvalue.php`:

```php
return [
    'exclude_suffixes' => ['_error', '_debug'],
    'exclude_prefixes' => ['temp_'],
    'flatten_nested' => true,
    'nested_separator' => ' → ',
    'flatten_single_arrays' => false,
    'skip_array_indices' => false,
    'array_index_format' => ' #%d',
    'max_array_size' => null,
    'skip' => [],
    'labels' => [],
    'formatters' => [],
    'lookups' => [],
];
```

## Blade Component

Publish the views:

```bash
php artisan vendor:publish --tag=json-to-keyvalue-views
```

Use in Blade templates:

```blade
<x-keyvalue-display 
    :data="$user->metadata" 
    label="User Metadata" 
    :config="[
        'skip' => ['password'],
        'formatters' => [
            'created_at' => fn($v) => $v->format('M d, Y')
        ]
    ]" 
/>
```

## Advanced Usage

### Chaining Multiple Configurations

```php
use Provydon\JsonToKeyvalue\JsonToKeyvalue;
use Provydon\JsonToKeyvalue\Formatters;

JsonToKeyvalue::make($metadata, 'User Metadata')
    ->skip(['password', 'token', 'api_key'])
    ->excludeSuffixes(['_error', '_internal', '_debug'])
    ->excludePrefixes(['temp_', 'cache_'])
    ->flattenNested(true)
    ->nestedSeparator(' → ')
    ->itemLabel('Metadata')
    ->labels([
        'first_name' => 'First Name',
        'last_name' => 'Last Name'
    ])
    ->formatters([
        'created_at' => Formatters::datetime(),
        'amount' => Formatters::currency('₦'),
        'is_active' => Formatters::boolean()
    ])
    ->toFields();
```

### Using Config Array

For complex configurations, you can pass an array:

```php
JsonToKeyvalue::make($data, 'Details')
    ->config([
        'skip' => ['password'],
        'exclude_suffixes' => ['_error'],
        'formatters' => [
            'amount' => Formatters::currency('$')
        ]
    ])
    ->toFields();
```

### Helper Functions (Advanced)

The package provides `array_flatten_with_keys()` for general use:

```php
$flat = array_flatten_with_keys([
    'user' => [
        'name' => 'John',
        'address' => ['city' => 'Lagos']
    ]
], '', ' → ');

// Result: ['user → name' => 'John', 'user → address → city' => 'Lagos']
```

## Testing

Run the tests:

```bash
composer test
```

Or:

```bash
./vendor/bin/phpunit
```

## Code Formatting

This package uses Laravel Pint for code formatting:

**Format code:**
```bash
composer format
```

**Check formatting without fixing:**
```bash
composer format:test
```

Or run Pint directly:
```bash
./vendor/bin/pint
```

## Local Development

To test this package locally in another Laravel project before publishing to Packagist:

Add to your project's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../json-to-keyvalue"
    }
],
"require": {
    "provydon/json-to-keyvalue": "*"
}
```

Then run:

```bash
composer update provydon/json-to-keyvalue
```

## Publishing to Packagist

1. Push your code to GitHub
2. Go to [packagist.org](https://packagist.org) and sign in
3. Click "Submit" and paste your GitHub repository URL
4. Packagist will auto-update on each GitHub push (configure webhook for automation)

Users can then install via:

```bash
composer require provydon/json-to-keyvalue
```

## License

MIT


