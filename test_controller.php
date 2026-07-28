<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

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

$controller = new \App\Http\Controllers\RecordingController();
try {
    $response = $controller->adminStore($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Failed: \n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
