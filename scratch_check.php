<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Setting::where('group', 'general')->get() as $s) {
    echo $s->key . ' : ' . $s->type . "\n";
}
