# JSON to KeyValue for Laravel Nova

Convert JSON data or arrays into beautiful, read-only Laravel Nova KeyValue fields with zero hassle.

## Features

- 🔄 Handles JSON strings or arrays
- 🎯 Automatically flattens nested objects
- 🎨 Pretty key formatting (snake_case → Title Case)
- 🚫 Smart filtering (exclude by prefix/suffix)
- 📊 Multiple items displayed as separate fields
- 🔍 Database lookups for foreign keys
- ✨ Custom formatters for any field
- 🔒 Read-only, detail-only display

## Installation

```bash
composer require provydon/json-to-keyvalue
```

## Usage

```php
public function fields(Request $request)
{
    return [
        json_to_keyvalue_fields($this->metadata, 'Metadata'),
    ];
}
```

The function is automatically available globally after installation.

### Basic Example

```php
json_to_keyvalue_fields($jsonData, 'Field Name')
```

## Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| `skip` | `[]` | Array of keys to skip completely |
| `exclude_suffixes` | `['_error']` | Exclude keys ending with these suffixes |
| `exclude_prefixes` | `[]` | Exclude keys starting with these prefixes |
| `flatten_nested` | `true` | Flatten nested arrays |
| `nested_separator` | `' → '` | Separator for nested keys |
| `item_label` | Field name | Label for items in arrays |
| `labels` | `[]` | Custom labels for specific keys |
| `formatters` | `[]` | Custom formatters: `fn($value, $item) => ...` |
| `lookups` | `[]` | Database lookups for foreign keys |


### With Configuration

```php
json_to_keyvalue_fields($data, 'User Details', [
    'skip' => ['password', 'token'],
    'exclude_suffixes' => ['_error', '_internal'],
    'exclude_prefixes' => ['temp_'],
    'flatten_nested' => true,
    'nested_separator' => ' → ',
    'item_label' => 'User',
    
    'labels' => [
        'first_name' => 'First Name',
        'dob' => 'Date of Birth',
    ],
    
    'formatters' => [
        'created_at' => fn($value) => date('M d, Y', strtotime($value)),
        'amount' => fn($value) => '$' . number_format($value, 2),
    ],
    
    'lookups' => [
        'user_id' => [
            'model' => \App\Models\User::class,
            'field' => 'id',
            'display' => 'name',
            'fallback' => 'user_id',
        ],
    ],
])
```

## Helper Function

The package also provides `array_flatten_with_keys()` for general use:

```php
$flat = array_flatten_with_keys([
    'user' => [
        'name' => 'John',
        'address' => ['city' => 'Lagos']
    ]
], '', ' → ');

// Result: ['user → name' => 'John', 'user → address → city' => 'Lagos']
```

## License

MIT


