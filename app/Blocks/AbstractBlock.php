<?php

namespace App\Blocks;

abstract class AbstractBlock 
{
    /**
     * The block's title
     * 
     * @var string
     */
    public static $title        = 'BLOCK NEEDS TITLE';

    /**
     * The block's description
     * 
     * @var string
     */
    public static $description  = 'BLOCK NEEDS DESCRIPTION';

    /**
     * The block's preview image
     * 
     * @var bool|array
     */
    public static $preview      = false;

    /**
     * The block's configurable fields
     * 
     * @var array
     */
    public static $fields       = [
        
    ];

    /**
     * Get the block's render settings
     * 
     * @return array
     */
    public function getRenderSettings(): array
    {
        return [
            'class'         => static::class,
            'title'         => static::$title,
            'description'   => static::$description,
            'fields'        => static::$fields,
        ];
    }
}