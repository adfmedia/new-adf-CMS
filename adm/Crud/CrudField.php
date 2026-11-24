<?php

namespace Adm\Crud;

use Illuminate\Support\Str;
use InvalidArgumentException;

class CrudField
{
    /**
     * Instance of the CrudInterface for use in field processing
     * 
     * @var \Adm\Crud\CrudInterface
     */
    protected $CrudInterface;

    /**
     * The field class matching the field type
     * 
     * @var \Adm\Crud\Field
     */
    protected $Field;

    /**
     * Aliasing for any field types that are also
     * reserved PHP words
     * 
     * @var array
     */
    private $type_aliases  = [
        'string'    => 'string_text',
        'readonly'  => 'readonly_field',
    ];

    /**
     * Construct a new field instance, setting the CRUD interface
     * and field type
     * 
     * @param CrudInterface $CrudInterface
     * @param string $field_type
     */
    public function __construct(CrudInterface $CrudInterface, string $field_type)
    {
        $this->CrudInterface = $CrudInterface;
        $this->loadField($field_type);
    }

    /**
     * Magic call method used to relay function calls
     * to the underlying field type class instance
     * 
     * @param string $name      The method being called
     * @param array $arguments  Array containing the arguments to pass to the method
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this, $name)) {
            return call_user_func([$this, $name], $arguments);
        }
        elseif (method_exists($this->Field, $name)) {
            return call_user_func([$this->Field, $name], ...$arguments);
        }

        return null;
    }

    /**
     * Loads the underlying field type class instance
     * 
     * @param string $field_type
     * @return void
     * @throws InvalidArgumentException
     */
    protected function loadField(string $field_type): void
    {
        if (isset($this->type_aliases[$field_type])) {
            $field_type = $this->type_aliases[$field_type];
        }

        $field_class = '\Adm\Crud\Field\\' . Str::studly($field_type);
        if (!class_exists($field_class)) {
            throw new InvalidArgumentException('Field type ' . $field_type . ' has no matching class (' . $field_class . ').');
        }

        $this->Field = new $field_class($this->CrudInterface);
    }
}