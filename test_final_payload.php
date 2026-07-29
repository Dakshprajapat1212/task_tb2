<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 1. Simulate login to get cookie
$loginRequest = Illuminate\Http\Request::create('/api/login', 'POST', [
    'email' => 'admin@tasktutorials.com',
    'password' => 'password'
]);
$loginResponse = $kernel->handle($loginRequest);
$cookies = $loginResponse->headers->getCookies();
$sessionCookie = null;
foreach($cookies as $c) {
    if ($c->getName() === 'laravel_session') {
        $sessionCookie = $c->getValue();
    }
}

// 2. Test payload
$payload = [
  "course" => "Class 10",
  "subject" => "Mathematics",
  "chapter" => "Chapter 2", 
  "topic" => "Trigonometry",
  "title" => "hvjdclksx;",
  "videoUrl" => "https://www.tasktutorials.com/videos/VIII PCMB 21.07.2026 Algebraic identity and Percentage.mp4",
  "timestamps" => [
    [
      "title" => "abc",
      "time" => "00:06:50"
    ],
    [
      "title" => "def",
      "time" => "00:24:10"
    ]
  ]
];

$request = Illuminate\Http\Request::create('/api/admin/recordings', 'POST', $payload);
if ($sessionCookie) {
    $request->cookies->set('laravel_session', $sessionCookie);
}

$response = $kernel->handle($request);
echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Content: " . $response->getContent() . "\n";
