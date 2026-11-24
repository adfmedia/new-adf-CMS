<?php

namespace Adm\Library\Database;

/**
 * Column helper class added after the Doctrine
 * DBAL package was removed as part of Laravel v11
 */
class Column
{
    /**
     * Construct a new column instance
     * 
     * @param string            $name               The DB column name
     * @param string            $type_name          The DB column type name
     * @param string            $type               The DB column type
     * @param ?string           $collation          The DB column's collation
     * @param bool              $nullable           Whether the column is nullable or not
     * @param null|string|int   $default            The column's default value
     * @param bool              $auto_increment     Whether the column is auto-incrementing or not
     * @param ?string           $comment            The column's comment
     * @param ?string           $generation         The column's generation type
     */
    public function __construct(
        public string $name,
        public string $type_name,
        public string $type,
        public ?string $collation,
        public bool $nullable,
        public null|string|int $default,
        public bool $auto_increment,
        public ?string $comment,
        public ?string $generation
    ){}

    /**
     * Returns the column's nullable attribute
     * 
     * @return bool
     */
    public function getNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Returns the column's default value
     * 
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }
}