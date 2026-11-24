<?php

namespace App\Helpers;

use App\Models\CommonText;

class CommonTextHelper
{
    /**
     * Simple boolean determining whether values have been loaded
     * from the database or not
     * 
     * @var bool
     */
    protected static $LOADED    = false;

    /**
     * Local storage array for loaded values
     * 
     * @var array
     */
    protected static $VALUES    = [];

    /**
     * Get the common text representation of the provided field, in the
     * currently loaded language
     * 
     * @param string $field
     * @return string
     */
    public static function getValue(string $field): string
    {
        if (!self::$LOADED) {
            self::load();
        }

        return isset(self::$VALUES[$field]) ? self::$VALUES[$field] : '';
    }

    /**
     * Loads all common text values into the local storage array
     * 
     * @return void
     */
    protected static function load(): void
    {
        self::$VALUES = CommonText::select('field', 'value')
            ->get()
            ->pluck('value', 'field')
            ->toArray();
    }
}