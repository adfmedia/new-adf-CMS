<?php

namespace App\Models\Traits;

use Exception;

trait HasCompositePrimaryKey
{
    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws Exception
     */
    protected function setKeysForSaveQuery($query): \Illuminate\Database\Eloquent\Builder
    {
        foreach ($this->getKeyName() as $key) {
            if ($this->$key)
                $query->where($key, '=', $this->$key);
            else
                throw new Exception(__METHOD__ . 'Missing part of the primary key: ' . $key);
        }

        return $query;
    }

    /**
     * Get the 'primary' key name for the owner model
     * 
     * @return mixed
     */
    public function getOwnerKeyName(): mixed
    {
        $to_skip = ['language_id', 'version'];
        foreach ($this->getKeyName() as $key) {
            if (!in_array($key, $to_skip)) { return $key; }
        }
        return $this->getKeyName();
    }
}
