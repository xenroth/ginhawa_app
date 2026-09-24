<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('ginhawa:status', function () { $this->info('GINHAWA mainframe online.'); })->purpose('Check the Ginhawa application status');