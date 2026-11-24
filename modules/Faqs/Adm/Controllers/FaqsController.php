<?php

namespace Modules\Faqs\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\Faqs\Config\FaqCategoriesConfig;
use Modules\Faqs\Config\FaqsConfig;

class FaqsController extends AdmController
{
    public function faqs($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            FaqsConfig::class
        );
    }

    public function faq_categories($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            FaqCategoriesConfig::class
        );
    }
}