<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('plans:timeout-stale')->everyFiveMinutes();
