<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\RecordingController;

$request = Request::create('/api/admin/recordings', 'POST', [
    'course' => 'Class 8',
    'topic' => 'Algebra',
    'title' => 'percentages',
    'videoUrl' => 'https://www.tasktutorials.com/video.mp4',
    'timestamps' => [
        ['title' => 'Intro', 'time' => '00:00:00'],
        ['title' => 'Solving', 'time' => '00:05:00']
    ]
]);

$controller = new RecordingController();
$response = $controller->adminStore($request);

echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT);
