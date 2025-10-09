<?php

namespace Provydon\JsonToKeyvalue;

class JsonToKeyvalue
{
    protected $data;

    protected $fieldName;

    protected $config = [];

    public static function make($data, string $fieldName): self
    {
        $instance = new self();
        $instance->data = $data;
        $instance->fieldName = $fieldName;

        return $instance;
    }

    public function skip(array $keys): self
    {
        $this->config['skip'] = $keys;

        return $this;
    }

    public function excludeSuffixes(array $suffixes): self
    {
        $this->config['exclude_suffixes'] = $suffixes;

        return $this;
    }

    public function excludePrefixes(array $prefixes): self
    {
        $this->config['exclude_prefixes'] = $prefixes;

        return $this;
    }

    public function flattenNested(bool $flatten = true): self
    {
        $this->config['flatten_nested'] = $flatten;

        return $this;
    }

    public function nestedSeparator(string $separator): self
    {
        $this->config['nested_separator'] = $separator;

        return $this;
    }

    public function itemLabel(string $label): self
    {
        $this->config['item_label'] = $label;

        return $this;
    }

    public function labels(array $labels): self
    {
        $this->config['labels'] = $labels;

        return $this;
    }

    public function formatters(array $formatters): self
    {
        $this->config['formatters'] = $formatters;

        return $this;
    }

    public function lookups(array $lookups): self
    {
        $this->config['lookups'] = $lookups;

        return $this;
    }

    public function config(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function toFields(): array
    {
        return json_to_keyvalue_fields($this->data, $this->fieldName, $this->config);
    }

    public function toArray(): array
    {
        $items = is_string($this->data) ? json_decode($this->data, true) : $this->data;

        if (! is_array($items) || empty($items)) {
            return [];
        }

        $isSingleObject = ! isset($items[0]) && array_keys($items) !== range(0, count($items) - 1);
        if ($isSingleObject) {
            $items = [$items];
        }

        $excludeSuffixes = $this->config['exclude_suffixes'] ?? ['_error'];
        $excludePrefixes = $this->config['exclude_prefixes'] ?? [];
        $flattenNested = $this->config['flatten_nested'] ?? true;
        $nestedSeparator = $this->config['nested_separator'] ?? ' → ';

        $results = [];

        foreach ($items as $index => $item) {
            $keyValueData = [];
            $flattenedItem = $flattenNested ? array_flatten_with_keys($item, '', $nestedSeparator) : $item;

            foreach ($flattenedItem as $key => $value) {
                if (isset($this->config['skip']) && in_array($key, $this->config['skip'])) {
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

                if (isset($this->config['labels'][$key])) {
                    $label = $this->config['labels'][$key];
                } else {
                    $parts = explode($nestedSeparator, $key);
                    $parts = array_map(fn ($part) => str_replace('_', ' ', ucwords($part, '_')), $parts);
                    $label = implode($nestedSeparator, $parts);
                }

                if (isset($this->config['lookups'][$key])) {
                    $lookup = $this->config['lookups'][$key];
                    $model = $lookup['model']::where($lookup['field'], $value)->first();
                    $keyValueData[$label] = $model ? $model->{$lookup['display']} : ($item[$lookup['fallback'] ?? $key] ?? 'Unknown');
                } elseif (isset($this->config['formatters'][$key])) {
                    $keyValueData[$label] = $this->config['formatters'][$key]($value, $item);
                } else {
                    $keyValueData[$label] = is_array($value) ? json_encode($value) : ($value ?? 'N/A');
                }
            }

            $results[] = $keyValueData;
        }

        return $results;
    }
}
