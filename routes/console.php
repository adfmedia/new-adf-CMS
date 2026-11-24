<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work --max-jobs=5')->everyMinute();
Schedule::command('site_search:index')->dailyAt('03:00');
Schedule::command('gdpr:form_log_clean')->dailyAt('03:30');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
