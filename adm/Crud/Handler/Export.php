<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Adm\Library\XLSXWriter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Request handler used to process CMS listing exports
 */
class Export extends RequestHandler
{
    /**
     * The columns to display in listings
     * 
     * @var array
     */
    protected $columns  = [];

    /**
     * Handle the listings for the current resource
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function handle(): BinaryFileResponse
    {
        $this->validateSetColumns();
        return $this->buildExport($this->getItems());
    }

    /**
     * Validate the provided column set, or load the default column 
     * configuration
     * 
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateSetColumns(): void
    {
        if (!$this->Config->hasProperty('listing_columns')) {
            throw new InvalidArgumentException('Listing columns not defined.');
        }

        $fields = $this->getConfigProperty('fields');
        $cols = $this->getConfigProperty('export_columns');
        if (!$cols) {
            $cols = $this->getConfigProperty('listing_columns');
        }
        foreach ($cols as $col) {
            if (!isset($fields[$col])) {
                throw new InvalidArgumentException('Fieldset missing column ' . $col);
            }

            $this->columns[$col] = $fields[$col];
        }
    }

    /**
     * Retrieve the items to display in exported data
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getItems(): Collection
    {
        if (request()->query('search') && $this->configClassHasMethod('customListingSearch')) {
            return $this->Config->getMethod('customListingSearch')->invoke(
                null,
                request()->query('search'),
                $this->CrudInterface->getLanguageId(),
                0,
                $this->CrudInterface->getItemSortField(),
                $this->CrudInterface->getItemSortDirection(),
                true
            );
        }

        $item_class = $this->getConfigProperty('class');
        $Qry = $item_class::query()->withoutGlobalScopes();
        $this->CrudInterface->applyLanguageCondition($Qry);

        $this->filterItemsQuery($Qry, $item_class, $this->columns);
        $this->sortItemsQuery($Qry, $this->columns);

        return $Qry->get()->filter(function (Model $Item) {
            return empty(data_get($Item, 'deleted_at'));
        });
    }

    /**
     * Return the export as a file download
     * 
     * @param \Illuminate\Database\Eloquent\Collection $Items
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function buildExport(Collection $Items): BinaryFileResponse
    {
        // Before exporting, clear any files in the tmp folder
        $tmp_dir = storage_path('tmp-export/');
        if (!is_dir($tmp_dir)) {
            mkdir($tmp_dir, 0755);
        }

        $exports = glob(storage_path('tmp-export/*'));
        if (!empty($exports)) {
            foreach ($exports as $path) {
                if (!is_dir($path)) {
                    unlink($path);
                }
            }
        }

        $cols = $this->columns;
        $type = $this->getConfigProperty('export_type', config('app.cms_exports.default_type'));

        $file_name_parts = array_filter([
            $this->getConfigProperty('title') . ' export',
            request()->query('search'),
            date("Y-m-d-h-i-s")
        ]);

        switch ($type) {
            case 'xlsx':
                $fname = implode(' - ', $file_name_parts) . '.xlsx';
                $tmp_path = storage_path('tmp-export/' . $fname);
                $this->buildXLSX($cols, $Items, $tmp_path);

                return response()->download($tmp_path, $fname, [
                    'Content-Description'       => 'File Transfer',
                    'Content-Transfer-Encoding' => 'binary',
                    'Content-Type'              => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition'       => 'attachment; filename="' . $fname . '"',
                    'Pragma'                    => 'public',
                    'Expires'                   => 0,
                    'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
                    'X-Download-Options'        => 'noopen',
                    'X-Content-Type-Options'    => 'nosniff',
                    'X-Suggested-Filename'      => $fname
                ]);
            default:
                $fname = implode(' - ', $file_name_parts) . '.csv';
                $tmp_path = storage_path('tmp-export/' . $fname);
                $this->buildCSV($cols, $Items, $tmp_path);

                return response()->download($tmp_path, $fname, [
                    'Content-type'  => 'text/csv; charset=utf-8'
                ]);
        }
    }

    /**
     * Build the CSV for the current resource, saving the contents 
     * to the provided file name
     * 
     * @param array $columns
     * @param \Illuminate\Database\Eloquent\Collection $Items
     * @param string $file_name
     * @return void
     */
    protected function buildCSV(array $columns, Collection $Items, string $file_name): void
    {
        $fp = fopen($file_name, 'w');
        $labels = [];
        foreach ($columns as $column) {
            $labels[] = $column['label'];
        }
        fputcsv($fp, $labels);

        foreach ($Items as $Item) {
            $row = [];
            foreach ($columns as $field => $column) {
                $Field = new CrudField($this->CrudInterface, $column['type']);
                $Field->prepareForExport($Item, $field, $column);

                $row[] = data_get($Item, $field);
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
    }

    /**
     * Build the XLSX for the current resource, saving the contents
     * to the provided file name
     * 
     * @param array $columns
     * @param \Illuminate\Database\Eloquent\Collection $Items
     * @param string $file_name
     * @return void
     */
    protected function buildXLSX(array $columns, Collection $Items, string $fname): void
    {
        $Writer = new XLSXWriter();
        $labels = [];
        foreach ($columns as $column) {
            $labels[] = $column['label'];
        }
        $Writer->writeSheetRow('Sheet1', $labels);

        foreach ($Items as $Item) {
            $row = [];
            foreach ($columns as $field => $column) {
                $Field = new CrudField($this->CrudInterface, $column['type']);
                $Field->prepareForExport($Item, $field, $column);

                $row[] = data_get($Item, $field);
            }
            $Writer->writeSheetRow('Sheet1', $row);
        }

        $Writer->writeToFile($fname);
    }
}
