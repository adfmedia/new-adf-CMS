<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Image Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default image processing driver that will be
    | used when manipulating or converting images. This driver is always
    | utilized unless another driver is explicitly specified instead.
    |
    | Supported: "gd", "imagick"
    |
    */

    'default' => env('IMAGE_DRIVER', 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Editable images
    |--------------------------------------------------------------------------
    |
    | Set ENV flag to true to make images (JPG & PNG) editable (rotateable)
    | within the file manager
    |
    */

    'editable'  => env('FILE_MANAGER_EDIT_ENABLED', false)

];
