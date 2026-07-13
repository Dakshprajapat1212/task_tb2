<?php

$file = 'app/Http/Controllers/LibraryController.php';
$content = file_get_contents($file);

// Replace imports
$content = str_replace('use App\Models\Quiz;', 'use App\Models\QuizQuestion;', $content);
$content = str_replace('use App\Http\Resources\QuizResource;', '', $content);

// We will overwrite the methods from quiz() down to result() manually.
