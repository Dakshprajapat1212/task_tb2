<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doubt;
use App\Models\Faculty;
use App\Models\ClassModel;
use App\Models\Chapter;
use App\Services\DoubtService;
use App\Http\Requests\SearchDoubtRequest;
use App\Http\Requests\SubmitDoubtRequest;
use App\Http\Requests\AnswerDoubtRequest;
use App\Http\Resources\DoubtSearchResultResource;
use App\Http\Resources\DoubtResource;

class DoubtController extends Controller
{
    protected $doubtService;

    public function __construct(DoubtService $doubtService)
    {
        $this->doubtService = $doubtService;
    }

    // ==========================================
    // STUDENT ROUTES
    // ==========================================

    public function search(SearchDoubtRequest $request)
    {
        // 1. Authorization check
        $chapter = Chapter::findOrFail($request->chapter_id);
        if (!\App\Models\Enrollment::where('user_id', auth()->id())->where('class_id', $chapter->class_id)->where('status', 'approved')->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized class access'], 403);
        }

        // 2. Perform Intelligent Search
        $results = $this->doubtService->searchIntelligent($request->chapter_id, $request->query('query', $request->input('query')));

        return response()->json([
            'success' => true,
            'results' => DoubtSearchResultResource::collection($results)
        ], 200);
    }

    public function store(SubmitDoubtRequest $request)
    {
        $chapter = Chapter::findOrFail($request->chapter_id);
        if (!\App\Models\Enrollment::where('user_id', auth()->id())->where('class_id', $chapter->class_id)->where('status', 'approved')->exists()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized class access'], 403);
        }

        $doubt = $this->doubtService->submitDoubt(
            auth()->id(),
            $chapter->class_id,
            $chapter->subject_id,
            $chapter->id,
            $request->question
        );

        return response()->json([
            'success' => true,
            'message' => 'Your doubt has been submitted successfully.',
            'data' => new DoubtResource($doubt)
        ], 201);
    }

    // ==========================================
    // FACULTY ROUTES
    // ==========================================

    public function facultyIndex(Request $request)
    {
        $faculty = Faculty::where('user_id', auth()->id())->first();
        if (!$faculty) {
            return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
        }

        $classIds = ClassModel::forFaculty($faculty->id)->pluck('id');
        $query = Doubt::whereIn('class_id', $classIds)->where('status', 'pending')->with(['user', 'class', 'subject', 'chapter', 'answeredBy']);

        return response()->json([
            'success' => true,
            'message' => 'Pending doubts fetched successfully',
            'data' => DoubtResource::collection($query->latest()->get())
        ], 200);
    }

    public function facultyAnswer(AnswerDoubtRequest $request, $id)
    {
        $faculty = Faculty::where('user_id', auth()->id())->first();
        if (!$faculty) {
            return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
        }

        $classIds = ClassModel::forFaculty($faculty->id)->pluck('id');
        $doubt = Doubt::where('id', $id)->whereIn('class_id', $classIds)->first();

        if (!$doubt) {
            return response()->json(['success' => false, 'message' => 'Doubt not found or unauthorized'], 404);
        }

        $doubt = $this->doubtService->answerDoubt($doubt->id, auth()->id(), $request->answer, $request->explanation);

        return response()->json([
            'success' => true,
            'message' => 'Doubt answered successfully',
            'data' => new DoubtResource($doubt)
        ], 200);
    }
}
