<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Refuse to run a test that rebuilds the database (RefreshDatabase or
     * DatabaseMigrations) unless it points at a test database, so the
     * test suite can never wipe a site's real content.
     *
     * A test database is one whose name ends in "_test" or "_testing",
     * or an in-memory SQLite database.
     */
    protected function setUpTraits()
    {
        $uses = class_uses_recursive(static::class);

        if (isset($uses[RefreshDatabase::class]) || isset($uses[DatabaseMigrations::class])) {
            $connection = config('database.default');
            $database = (string) config('database.connections.' . $connection . '.database');

            $is_test_db = $database === ':memory:'
                || preg_match('/_(test|testing)$/', $database);

            if (!$is_test_db) {
                throw new RuntimeException(
                    static::class . ' rebuilds the database, but the tests are pointed at "' . $database . '". '
                    . 'Set DB_DATABASE to a database ending in _test in phpunit.xml first.'
                );
            }
        }

        return parent::setUpTraits();
    }
}
