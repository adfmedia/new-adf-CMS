<?php

namespace Adm\Crud\Handler;

use Illuminate\Database\Eloquent\Model;

class TranslateStore extends Store
{
    /**
     * The 'primary' ID of the item being translated
     * 
     * @var ?int
     */
    protected $item_id  = null;

    /**
     * Construct a new instance of the request handler
     * and set the item's ID to the internal property
     * 
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->item_id = $id;
    }

    /**
     * {@inheritdoc}
     */
    protected function assignPrimaryKeyValues(Model $Item): void
    {
        // If auto-incrementing, we don't need to set anything
        if ($Item->getIncrementing()) { return; }

        foreach ((array)$Item->getKeyName() as $pk_part) {
            switch ($pk_part) {
                case 'language_id': data_set($Item, 'language_id', $this->CrudInterface->getLanguageId()); break;
                case 'version': data_set($Item, 'version', 1); break;
                default: data_set($Item, $pk_part, $this->item_id); break;
            }
        }
    }
}