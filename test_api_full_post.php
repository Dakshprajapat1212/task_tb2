<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/api/admin/recordings', 'POST', [
    'course' => 'Class 8',
    'subject' => 'Mathematics',
    'topic' => 'Algebra',
    'title' => 'percentages',
    'videoUrl' => 'https://www.tasktutorials.com/video.mp4'
]);
$request->headers->set('Authorization', 'Bearer 4|S8TDLNZFztKHEI9BO6EUtrOf7vyDyeT42TgFzlx3b4ee55d5');
$response = $kernel->handle($request);
echo $response->getContent();
