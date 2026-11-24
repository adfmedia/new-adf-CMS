<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class Delete extends RequestHandler
{
    /**
     * Boolean determining if the current model class
     * has the SoftDeletes trait applied
     * 
     * @var bool
     */
    protected $softDeletes  = false;

    /**
     * Handle the request & delete the item
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(): JsonResponse
    {
        $ItemsToDelete = $this->getItems();
        if ($ItemsToDelete->isEmpty()) {
            return response()->json(['success' => true]);
        }

        $this->softDeletes = $this->classHasSoftDeletes();

        $fields = $this->getConfigProperty('fields');
        $DeletedItems = new Collection();
        $deleted_ids = [];
        try {
            foreach ($ItemsToDelete as $ItemToDelete) {
                $DeletedItem = $this->deleteItem($ItemToDelete, $fields);
                $DeletedItems->push($DeletedItem);
                $deleted_ids[data_get($DeletedItem, $this->getClassIDField())] = 1;

                $this->logChange($DeletedItem, 'Deleted');
            }
        } catch (Exception $e) {
            Log::debug('Delete error:');
            Log::debug($e);

            $deleted_count = sizeof($deleted_ids);
            $error_suffix = ', but ' . $deleted_count . ' ' . ($deleted_count == 1 ? 'has' : 'have') . ' deleted.';
            if ($deleted_count == 0) {
                $error_suffix = '';
            }
            return response()->json([
                'success'   => false,
                'error'     => $this->getSingularConfigProperty('title') . ' failed to delete' . $error_suffix,
                'excp'      => $e->getMessage()
            ]);
        }

        session()->flash('success', sizeof($deleted_ids) . ' ' . strtolower($this->getSingularConfigProperty('title')) . '(s) deleted.');

        return response()->json([
            'success'   => true,
            'message'   => sizeof($deleted_ids) . ' ' . strtolower($this->getSingularConfigProperty('title')) . '(s) deleted.'
        ]);
    }

    /**
     * Return a collection containing the items to
     * delete
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getItems(): Collection
    {
        $item_class = $this->getConfigProperty('class');
        $Qry = $item_class::query()->withoutGlobalScopes();
        $this->CrudInterface->applyLanguageCondition($Qry);
        return $Qry->whereIn($this->getClassIDField(), request()->input('delete_items'))->get();
    }

    /**
     * Perform the item deletion, and any afterDelete
     * hooks on attached fields
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @param array $fields
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function deleteItem(Model $Item, array $fields): Model
    {
        if ($this->configClassHasMethod('beforeDeleted')) {
            $this->Config->getMethod('beforeDeleted')->invoke(null, $Item);
        }

        if ($this->softDeletes) {
            $Item->delete();
        }

        try {
            foreach ($fields as $field_name => $field_details) {
                $Field = new CrudField($this->CrudInterface, $field_details['type']);
                $Field->afterDeleted($Item, $field_name, $field_details);
            }
        } catch (Exception $e) {
            if ($this->softDeletes) {
                $Item->restore();
            }
            throw $e;
        }

        if (!$this->softDeletes) {
            $Item->delete();
        }

        if ($this->configClassHasMethod('afterDeleted')) {
            $this->Config->getMethod('afterDeleted')->invoke(null, $Item);
        }

        return $Item;
    }
}
