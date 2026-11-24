<?php

namespace Modules\Events\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\Events\Config\EventCategoriesConfig;
use Modules\Events\Config\EventsConfig;

class EventsController extends AdmController
{
    public function events($method, $id)
    {
        return $this->Crud->boot($method, $id, EventsConfig::class);
    }

    public function event_categories($method, $id)
    {
        return $this->Crud->boot($method, $id, EventCategoriesConfig::class);
    }
}