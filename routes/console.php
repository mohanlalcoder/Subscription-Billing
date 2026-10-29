<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//Invoice genrate
Schedule::command('billing:process-due-cycles')
    ->everyMinute()
    ->withoutOverlapping();

//Expired Date Update
Schedule::command('subscriptions:expire')->everyMinute();