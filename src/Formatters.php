<?php

namespace Provydon\JsonToKeyvalue;

class Formatters
{
    public static function currency(string $symbol = '₦', int $decimals = 2): callable
    {
        return fn ($value) => $symbol.number_format((float) $value, $decimals);
    }

    public static function date(string $format = 'M d, Y'): callable
    {
        return fn ($value) => $value ? date($format, strtotime($value)) : 'N/A';
    }

    public static function datetime(string $format = 'M d, Y g:i A'): callable
    {
        return fn ($value) => $value ? date($format, strtotime($value)) : 'N/A';
    }

    public static function boolean(string $trueLabel = 'Yes', string $falseLabel = 'No'): callable
    {
        return fn ($value) => $value ? $trueLabel : $falseLabel;
    }

    public static function uppercase(): callable
    {
        return fn ($value) => strtoupper($value);
    }

    public static function lowercase(): callable
    {
        return fn ($value) => strtolower($value);
    }

    public static function titleCase(): callable
    {
        return fn ($value) => ucwords(strtolower($value));
    }

    public static function phone(string $countryCode = ''): callable
    {
        return fn ($value) => $countryCode.preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $value);
    }

    public static function truncate(int $length = 50, string $ending = '...'): callable
    {
        return fn ($value) => strlen($value) > $length ? substr($value, 0, $length).$ending : $value;
    }

    public static function percentage(int $decimals = 2): callable
    {
        return fn ($value) => number_format((float) $value, $decimals).'%';
    }

    public static function fileSize(): callable
    {
        return function ($bytes) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);

            return round($bytes, 2).' '.$units[$pow];
        };
    }

    public static function json(bool $pretty = true): callable
    {
        return fn ($value) => json_encode($value, $pretty ? JSON_PRETTY_PRINT : 0);
    }

    public static function badge(array $colors = []): callable
    {
        return function ($value) use ($colors) {
            $color = $colors[$value] ?? 'gray';

            return "<span class='badge badge-{$color}'>{$value}</span>";
        };
    }

    public static function url(string $text = 'View'): callable
    {
        return fn ($value) => "<a href='{$value}' target='_blank'>{$text}</a>";
    }

    public static function email(): callable
    {
        return fn ($value) => "<a href='mailto:{$value}'>{$value}</a>";
    }

    public static function enumLabel(array $labels): callable
    {
        return fn ($value) => $labels[$value] ?? ucwords(str_replace('_', ' ', $value));
    }
}
