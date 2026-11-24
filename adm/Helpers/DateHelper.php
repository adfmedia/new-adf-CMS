<?php

namespace Adm\Helpers;

use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

class DateHelper
{
    /**
     * Add database where condition to the provided query based on the date format
     * of the provided search term.
     * For example, if searching for "March 2021", apply a condition of: 
     *  where (year = 2021 AND month = 03)
     * If searching for 3rd April 2022, apply a condition of:
     *  where date(field) = date(2022-04-03)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $table
     * @param string $field
     * @param string $search_term
     * @return void
     */
    public static function multiFormatDateQuery(Builder $query, string $table, string $field, string $search_term): void
    {
        $db_field = $table . '.' . $field;
        if ($search_date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $search_term)) {
            $query->orWhere($db_field, $search_date->format('Y-m-d H:i:s'));
        }
        elseif ($search_date = DateTimeImmutable::createFromFormat('Y-m-d H:i', $search_term)) {
            $query->orWhere($db_field, 'LIKE', $search_date->format('Y-m-d H:i') . '%');
        } 
        elseif ($search_date = DateTimeImmutable::createFromFormat('Y-m-d H', $search_term)) {
            $query->orWhere($db_field, 'LIKE', $search_date->format('Y-m-d H') . '%');
        }
        elseif ($search_date = DateTimeImmutable::createFromFormat('Y-m-d', $search_term)) {
            $query->orWhereDate($db_field, $search_date->format('Y-m-d'));
        }
        elseif ($search_date = DateTimeImmutable::createFromFormat('jS F Y', $search_term)) {
            $query->orWhereDate($db_field, $search_date->format('Y-m-d'));
        }
        elseif ($search_date = DateTimeImmutable::createFromFormat('Y-m', $search_term)) {
            $query->orWhere(function (Builder $subquery) use ($db_field, $search_date) {
                $subquery->whereYear($db_field, $search_date->format('Y'))
                    ->whereMonth($db_field, $search_date->format('m'));
            });
        }
        elseif ($search_date = DateTimeImmutable::createFromFormat('F Y', $search_term)) {
            $query->orWhere(function (Builder $subquery) use ($db_field, $search_date) {
                $subquery->whereYear($db_field, $search_date->format('Y'))
                    ->whereMonth($db_field, $search_date->format('m'));
            });
        }
        elseif ($search_date = DateTimeImmutable::createFromFormat('Y', $search_term)) {
            $query->orWhereYear($db_field, $search_date->format('Y'));
        }
        elseif ($search_date = DateTimeImmutable::createFromFormat('ga', $search_term)) {
            $query->orWhere($db_field, 'LIKE', '%' . $search_date->format('H') . ':%');
        }
    }
}