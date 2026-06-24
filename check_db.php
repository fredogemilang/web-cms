<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo "CONTACT LEVELS:\n";
print_r(Plugins\Events\Models\ContactLevel::all()->toArray());
echo "CONTACT DIVISIONS:\n";
print_r(Plugins\Events\Models\ContactDivision::all()->toArray());
