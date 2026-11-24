<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Model;

class Price extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $formatted_val = $this->formatValue(data_get($Item, $field), $details);
        data_set($Item, $field, $formatted_val);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForExport(Model $Item, string $field, array $details): void
    {
        $formatted_val = $this->formatValue(data_get($Item, $field), $details);
        data_set($Item, $field, $formatted_val);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForRead(Model $Item, string $field, array $details): void
    {
        $formatted_val = $this->formatValue(data_get($Item, $field), $details);
        data_set($Item, $field, $formatted_val);
    }

    /**
     * Format the stored numeric value into a consistent
     * format
     * 
     * @param mixed $number
     * @param array $details
     * @return string
     */
    protected function formatValue(mixed $number, array $details): string
    {
        // If there's no stored value (i.e. null), don't format it
        if (!strlen($number)) { return $number; }

        $dps = 2;
        $symbol = "£";

        if (isset($details['price_format']) && isset($details['price_format']['decimals'])) {
            $dps = (int)$details['price_format']['decimals'];
        }
        if (isset($details['price_format']) && isset($details['price_format']['currency_symbol'])) {
            $symbol = (int)$details['price_format']['currency_symbol'];
        }

        return $symbol . number_format($number, $dps);
    }
}