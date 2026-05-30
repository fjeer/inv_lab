<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
echo "User role_id: " . $user->role_id . "\n";
echo "Type of roleRelation: " . get_class($user->roleRelation) . "\n";
echo "Role name: " . $user->roleRelation->name . "\n";
