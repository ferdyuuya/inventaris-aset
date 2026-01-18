<?php

namespace App\Helpers;

class BadgeColorHelper
{
    /**
     * Flux UI official badge colors from documentation
     * https://fluxui.dev/components/badge#colors
     */
    private static array $colors = [
        'red',
        'orange',
        'amber',
        'yellow',
        'lime',
        'green',
        'emerald',
        'teal',
        'cyan',
        'sky',
        'blue',
        'indigo',
        'violet',
        'purple',
        'fuchsia',
        'pink',
        'rose',
    ];

    /**
     * Get a consistent color for a category based on its ID
     * Uses Flux UI official badge colors
     * 
     * @param mixed $category Category object or ID
     * @return string Color name for Flux UI badge
     */
    public static function getCategoryColor($category): string
    {
        if (!$category) {
            return 'zinc';
        }

        // Get identifier - prefer ID, fall back to name hash
        $identifier = is_object($category) ? ($category->id ?? crc32($category->name ?? '')) : $category;
        
        // Use modulo to get consistent color index
        $index = $identifier % count(self::$colors);
        
        return self::$colors[$index];
    }

    /**
     * Get all available colors
     */
    public static function getColors(): array
    {
        return self::$colors;
    }
}
