<?php

namespace App\Helpers;

/**
 * ExportSanitizer
 * 
 * Utility class for sanitizing data before PDF export.
 * Ensures all text is valid UTF-8 to prevent encoding errors.
 */
class ExportSanitizer
{
    /**
     * Fields that commonly contain user input and need sanitization
     */
    protected static array $textFields = [
        'description',
        'issue_description',
        'feedback',
        'notes',
        'return_notes',
        'borrow_notes',
        'reason',
        'reject_reason',
        'name',
        'position',
    ];

    /**
     * Sanitize a string to ensure valid UTF-8 encoding
     *
     * @param string|null $text
     * @return string|null
     */
    public static function sanitizeString(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        // Remove invalid UTF-8 sequences
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // Strip any remaining invalid characters
        $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
        
        // Remove control characters except newlines and tabs
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        
        return $text;
    }

    /**
     * Sanitize a model instance's text fields
     *
     * @param object $model
     * @return object
     */
    public static function sanitizeModel(object $model): object
    {
        foreach (self::$textFields as $field) {
            if (isset($model->{$field}) && is_string($model->{$field})) {
                $model->{$field} = self::sanitizeString($model->{$field});
            }
        }

        // Recursively sanitize loaded relations
        foreach ($model->getRelations() as $relationName => $relation) {
            if ($relation === null) {
                continue;
            }

            if ($relation instanceof \Illuminate\Database\Eloquent\Collection) {
                foreach ($relation as $item) {
                    self::sanitizeModel($item);
                }
            } elseif (is_object($relation) && method_exists($relation, 'getRelations')) {
                self::sanitizeModel($relation);
            }
        }

        return $model;
    }

    /**
     * Sanitize a collection of models
     *
     * @param \Illuminate\Support\Collection $collection
     * @return \Illuminate\Support\Collection
     */
    public static function sanitizeCollection(\Illuminate\Support\Collection $collection): \Illuminate\Support\Collection
    {
        return $collection->map(function ($item) {
            if (is_object($item) && method_exists($item, 'getRelations')) {
                return self::sanitizeModel($item);
            }
            return $item;
        });
    }

    /**
     * Sanitize an array recursively
     *
     * @param array $data
     * @return array
     */
    public static function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = self::sanitizeString($value);
            } elseif (is_array($value)) {
                $data[$key] = self::sanitizeArray($value);
            } elseif ($value instanceof \Illuminate\Support\Collection) {
                $data[$key] = self::sanitizeCollection($value);
            } elseif (is_object($value) && method_exists($value, 'getRelations')) {
                $data[$key] = self::sanitizeModel($value);
            }
        }

        return $data;
    }
}
