<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

class Helper
{
    private static array $eloquentModelsDirs;

    public static function setEloquentModelsDirs(array|string $eloquentModelsDirs): void
    {
        static::$eloquentModelsDirs = (array) $eloquentModelsDirs;
    }

    public static function getEloquentModelsDirs(): array
    {
        return static::$eloquentModelsDirs;
    }

    public static function getAcfValuesWithNames(array $array): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $field = get_field_object($key);

            // Handle group
            if (is_array($value) && str_starts_with(array_key_first($value), 'field_')) {
                $value = static::getAcfValuesWithNames($value);

                $newArray[$field['name']] = $value;

                continue;
            }

            // Handle repeater
            if (is_array($value) && ! str_starts_with(array_key_first($value), 'field_')) {
                $newValue = [];
                foreach ($value as $_value) {
                    $newValue[] = static::getAcfValuesWithNames($_value);
                }

                $newArray[$field['name']] = $newValue;

                continue;
            }

            $newArray[$field['name']] = $value;
        }

        return $newArray;
    }

    public static function convertNamesToFieldKeys(array $array): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $newValue = [];

                foreach ($value as $_key => $_value) {
                    if (is_array($_value)) {
                        $newValue[] = static::convertNamesToFieldKeys($_value);

                        continue;
                    }

                    $_field = acf_get_field($_key);

                    if (empty($_field)) {
                        continue;
                    }

                    $newValue[$_field['key']] = $_value;
                }

                $newArray[] = $newValue;

                continue;
            }

            if (is_numeric($key)) {
                $newArray[$key] = $value;

                continue;
            }

            $field = acf_get_field($key);
            $newArray[$field['key']] = $value;
        }

        return $newArray;
    }
}
