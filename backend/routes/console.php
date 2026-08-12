<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('qms:backup-database')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('qms:notify-overdue-reports')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('qms:export-yesterday')->dailyAt('23:30')->withoutOverlapping();
Schedule::command('queue:prune-batches --hours=48')->dailyAt('03:00')->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
