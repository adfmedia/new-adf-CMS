<?php

namespace Modules\CaseStudies\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\CaseStudies\Config\CaseStudyCategoriesConfig;
use Modules\CaseStudies\Config\CaseStudiesConfig;

class CaseStudiesController extends AdmController
{
    public function case_studies($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            CaseStudiesConfig::class
        );
    }

    public function case_study_categories($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            CaseStudyCategoriesConfig::class
        );
    }
}