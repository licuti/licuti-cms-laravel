<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\User::where('is_admin', 1)->limit(10)->get(['id', 'name', 'email']) as $u) {
    echo $u->id . ' | ' . $u->name . ' | ' . $u->email . "\n";
}
echo "--- languages ---\n";
foreach (App\Models\Language::orderBy('id')->get(['id', 'code', 'name', 'is_default', 'is_active']) as $l) {
    echo $l->id . ' | ' . $l->code . ' | ' . $l->name . ' | default=' . var_export($l->is_default, true) . ' active=' . var_export($l->is_active, true) . "\n";
}
