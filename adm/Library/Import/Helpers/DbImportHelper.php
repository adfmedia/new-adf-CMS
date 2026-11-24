<?php

namespace Adm\Library\Import\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Helper class for common functionality used during
 * database imports
 */
class DbImportHelper 
{
    /**
     * Get the next incremental ID field value for a table
     * 
     * @param string $table_name    The table to get an ID for
     * @param string $id_field      The ID field column to retrieve a value for
     * @return int
     */
    public function getNextDbRecordId(string $table_name, string $id_field): int
    {
        $LastRow = DB::table($table_name)
            ->orderBy($id_field, 'desc')
            ->first();
        
        return $LastRow ? ($LastRow->{$id_field} + 1) : 1;
    }
}