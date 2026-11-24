<?php

namespace App\Models\Traits;

use App\Library\CachingQueryBuilder;

trait CachesQueries
{
    /**
     * If a model uses this trait, ensure that the CachingQueryBuilder 
     * is used instead of the 'base' Query Builder.
     *
     * @return CachingQueryBuilder
     */
    protected function newBaseQueryBuilder(): CachingQueryBuilder
    {
        $conn = $this->getConnection();
        $grammar = $conn->getQueryGrammar();
        return new CachingQueryBuilder($conn, $grammar, $conn->getPostProcessor());
    }
}