<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command("app:update")
    ->environments(["production", "testing"])
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(Storage::disk("logs")->path("schedule.log"))
;

Schedule::command("bank:download")
    ->environments(["local", "production", "testing"])
    ->hourly()
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(Storage::disk("logs")->path("schedule.log"))
;

// Schedule::command("bank:decrypt")
    // ->environments(["local", "production", "testing"])
    // ->hourly()
    // ->everyMinute()
    // ->withoutOverlapping()
    // ->appendOutputTo(Storage::disk("logs")->path("schedule.log"))
// ;