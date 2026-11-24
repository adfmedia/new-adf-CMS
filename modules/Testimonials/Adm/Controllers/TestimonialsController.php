<?php

namespace Modules\Testimonials\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\Testimonials\Config\TestimonialsConfig;

class TestimonialsController extends AdmController
{
    public function testimonials($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            TestimonialsConfig::class
        );
    }
}