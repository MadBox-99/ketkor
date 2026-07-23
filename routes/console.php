<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('maintenance:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();
