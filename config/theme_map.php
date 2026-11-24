<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Button mapping
    |--------------------------------------------------------------------------
    |
    | This option defines the options available to theme mapped button 
    | fields, where each key is the storage value & associated value 
    | array contains the displayed label and mapped class
    |
    */

    'buttons'       => [
        'primary'       => ['label' => 'Blue', 'class' => 'btn--primary'],
        'secondary'     => ['label' => 'Pink', 'class' => 'btn--secondary'],
        'red'           => ['label' => 'Red', 'class' => 'btn--red'],
        'green'         => ['label' => 'Green', 'class' => 'btn--green'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Background mapping
    |--------------------------------------------------------------------------
    |
    | This option defines the options available to theme mapped background 
    | fields, where each key is the storage value & associated value 
    | array contains the displayed label and mapped class
    |
    */

    'backgrounds'   => [
        'primary'       => ['label' => 'Blue', 'class' => 'bg-primary'],
        'secondary'     => ['label' => 'Pink', 'class' => 'bg-secondary'],
        'grey'          => ['label' => 'Grey', 'class' => 'bg-gray-100']
    ],
];