<?php

namespace Adm\Library\Database;

use Illuminate\Support\Facades\Schema;

/**
 * Table helper class added after the Doctrine
 * DBAL package was removed as part of Laravel v11
 */
class TableHelper
{
    /**
     * Return a Column class representation of the provided
     * DB column in the provided DB table
     * 
     * @param string $table_name    The database table to check
     * @param string $column_name   The database column to retrieve details for
     * @return ?\Adm\Library\Database\Column
     */
    public static function getTableColumn(string $table_name, string $column_name): ?Column
    {
        $columns = Schema::getColumns($table_name);
        foreach ($columns as $column) {
            if ($column['name'] == $column_name) {
                return new Column(
                    $column['name'],
                    $column['type_name'],
                    $column['type'],
                    $column['collation'],
                    $column['nullable'],
                    $column['default'],
                    $column['auto_increment'],
                    $column['comment'],
                    $column['generation'],
                );
            }
        }

        return null;
    }
}