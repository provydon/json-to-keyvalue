<?php

use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Text;

if (! function_exists('json_to_keyvalue_fields')) {
    function json_to_keyvalue_fields($jsonData, $fieldName, $config = [])
    {
        $fields = [];
        $items = is_string($jsonData) ? json_decode($jsonData, true) : $jsonData;

        if (! is_array($items) || empty($items)) {
            return [Text::make($fieldName)
                ->resolveUsing(fn () => "No {$fieldName} available")
                ->onlyOnDetail()
                ->readonly()];
        }

        $isSingleObject = ! isset($items[0]) && array_keys($items) !== range(0, count($items) - 1);
        if ($isSingleObject) {
            $items = [$items];
        }

        $excludeSuffixes = $config['exclude_suffixes'] ?? ['_error'];
        $excludePrefixes = $config['exclude_prefixes'] ?? [];
        $flattenNested   = $config['flatten_nested'] ?? true;
        $nestedSeparator = $config['nested_separator'] ?? ' → ';

        foreach ($items as $index => $item) {
            $keyValueData = [];

            $flattenedItem = $flattenNested
                ? array_flatten_with_keys($item, '', $nestedSeparator)
                : $item;

            foreach ($flattenedItem as $key => $value) {
                if (isset($config['skip']) && in_array($key, $config['skip'])) continue;

                $shouldExclude = false;

                foreach ($excludeSuffixes as $suffix) {
                    if (str_ends_with($key, $suffix)) {
                        $shouldExclude = true;
                        break;
                    }
                }

                if (! $shouldExclude) {
                    foreach ($excludePrefixes as $prefix) {
                        if (str_starts_with($key, $prefix)) {
                            $shouldExclude = true;
                            break;
                        }
                    }
                }

                if ($shouldExclude) continue;

                $label = $config['labels'][$key] ?? str_replace('_', ' ', ucwords($key, '_'));

                if (isset($config['lookups'][$key])) {
                    $lookup = $config['lookups'][$key];
                    $model  = $lookup['model']::where($lookup['field'], $value)->first();
                    $keyValueData[$label] = $model
                        ? $model->{$lookup['display']}
                        : ($item[$lookup['fallback'] ?? $key] ?? 'Unknown');
                } elseif (isset($config['formatters'][$key])) {
                    $keyValueData[$label] = $config['formatters'][$key]($value, $item);
                } else {
                    $keyValueData[$label] = is_array($value)
                        ? json_encode($value)
                        : ($value ?? 'N/A');
                }
            }

            $itemLabel = $config['item_label'] ?? $fieldName;
            $label = $isSingleObject ? $itemLabel : "{$itemLabel} #".($index + 1);

            $fields[] = KeyValue::make($label)
                ->resolveUsing(fn () => $keyValueData)
                ->onlyOnDetail()
                ->readonly();
        }

        return $fields;
    }
}

if (! function_exists('array_flatten_with_keys')) {
    function array_flatten_with_keys($array, $prefix = '', $separator = '.')
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix.$separator.$key;

            if (is_array($value) && ! empty($value) && array_keys($value) !== range(0, count($value) - 1)) {
                $result = array_merge($result, array_flatten_with_keys($value, $newKey, $separator));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
