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

        $globalConfig = function_exists('config') ? config('json-to-keyvalue', []) : [];

        $flattenSingleArrays = $config['flatten_single_arrays'] ?? $globalConfig['flatten_single_arrays'] ?? false;
        $maxArraySize = $config['max_array_size'] ?? $globalConfig['max_array_size'] ?? null;

        if ($flattenSingleArrays && isset($items[0]) && count($items) === 1) {
            $items = $items[0];
        }

        if ($maxArraySize !== null && isset($items[0]) && count($items) > $maxArraySize) {
            return [Text::make($fieldName)
                ->resolveUsing(fn () => 'Array too large to display ('.count($items).' items)')
                ->onlyOnDetail()
                ->readonly()];
        }

        $isSingleObject = ! isset($items[0]) && array_keys($items) !== range(0, count($items) - 1);
        if ($isSingleObject) {
            $items = [$items];
        }

        $excludeSuffixes = $config['exclude_suffixes'] ?? $globalConfig['exclude_suffixes'] ?? ['_error'];
        $excludePrefixes = $config['exclude_prefixes'] ?? $globalConfig['exclude_prefixes'] ?? [];
        $flattenNested = $config['flatten_nested'] ?? $globalConfig['flatten_nested'] ?? true;
        $nestedSeparator = $config['nested_separator'] ?? $globalConfig['nested_separator'] ?? ' → ';
        $skipArrayIndices = $config['skip_array_indices'] ?? $globalConfig['skip_array_indices'] ?? false;
        $arrayIndexFormat = $config['array_index_format'] ?? $globalConfig['array_index_format'] ?? ' #%d';

        foreach ($items as $index => $item) {
            $keyValueData = [];

            $flattenedItem = $flattenNested
                ? array_flatten_with_keys($item, '', $nestedSeparator)
                : $item;

            foreach ($flattenedItem as $key => $value) {
                if (isset($config['skip']) && in_array($key, $config['skip'])) {
                    continue;
                }

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

                if ($shouldExclude) {
                    continue;
                }

                if (isset($config['labels'][$key])) {
                    $label = $config['labels'][$key];
                } else {
                    $parts = explode($nestedSeparator, $key);
                    $parts = array_map(fn ($part) => str_replace('_', ' ', ucwords($part, '_')), $parts);
                    $label = implode($nestedSeparator, $parts);
                }

                if (isset($config['lookups'][$key])) {
                    $lookup = $config['lookups'][$key];
                    $model = $lookup['model']::where($lookup['field'], $value)->first();
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

            if ($skipArrayIndices || $isSingleObject) {
                $label = $itemLabel;
            } else {
                $label = $itemLabel.sprintf($arrayIndexFormat, $index + 1);
            }

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
