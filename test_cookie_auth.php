<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 1. Simulate login
$loginRequest = Illuminate\Http\Request::create('/api/login', 'POST', [
    'email' => 'admin@gmail.com', // wait, do we know the admin email? Let's check DB
    'password' => 'password' // or whatever it is.
]);
$loginResponse = $kernel->handle($loginRequest);
$cookies = $loginResponse->headers->getCookies();
$sessionCookie = null;
foreach($cookies as $c) {
    if ($c->getName() === 'laravel_session') {
        $sessionCookie = $c->getValue();
    }
}
echo "Session Cookie: " . ($sessionCookie ? "FOUND" : "NOT FOUND") . "\n";
