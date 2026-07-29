<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\ClassModel;
use App\Models\Faculty;

class V2AdminChapterController extends Controller
{
    public function index(Request $request)
    {
        $query = Chapter::with(['class', 'subject']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if (auth()->user()->role_id == 3) {
            // Admin can see all filtered chapters
            $chapters = $query->get();
        } elseif (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
            $class_ids = ClassModel::forFaculty($faculty->id)->pluck('id');
            $chapters = $query->whereIn('class_id', $class_ids)->get();
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chapters fetched successfully',
            'data' => \App\Http\Resources\ChapterResource::collection($chapters)
        ], 200);
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role_id, [2, 3])) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer',
            'status' => 'nullable|in:active,inactive'
        ]);

        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
            $class = ClassModel::forFaculty($faculty->id)->where('id', $request->class_id)->first();
            if (!$class) return response()->json(['success' => false, 'message' => 'Unauthorized class access'], 403);
        }

        $topic = Chapter::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'display_order' => $request->display_order ?? 0,
            'status' => $request->status ?? 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chapter created successfully',
            'data' => new \App\Http\Resources\ChapterResource($topic)
        ], 201);
    }

    public function show($id)
    {
        $topic = Chapter::with(['class', 'subject'])->find($id);
        if (!$topic) return response()->json(['success' => false, 'message' => 'Chapter not found'], 404);
        if (!$this->canAccessChapter($topic)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        return response()->json([
            'success' => true,
            'message' => 'Chapter fetched successfully',
            'data' => new \App\Http\Resources\ChapterResource($topic)
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $topic = Chapter::find($id);
        if (!$topic) return response()->json(['success' => false, 'message' => 'Chapter not found'], 404);
        if (!$this->canAccessChapter($topic)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer',
            'status' => 'nullable|in:active,inactive'
        ]);

        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            $newClass = ClassModel::forFaculty($faculty->id)->where('id', $request->class_id)->first();
            if (!$newClass) return response()->json(['success' => false, 'message' => 'Unauthorized class assignment'], 403);
        }

        $topic->update([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'display_order' => $request->display_order ?? $topic->display_order,
            'status' => $request->status ?? $topic->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chapter updated successfully',
            'data' => new \App\Http\Resources\ChapterResource($topic)
        ], 200);
    }

    public function destroy($id)
    {
        $topic = Chapter::find($id);
        if (!$topic) return response()->json(['success' => false, 'message' => 'Chapter not found'], 404);
        if (!$this->canAccessChapter($topic)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $topic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chapter deleted successfully'
        ], 200);
    }

    private function canAccessChapter(Chapter $chapter)
    {
        if (auth()->user()->role_id == 3) return true;
        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return false;
            return ClassModel::forFaculty($faculty->id)->where('id', $chapter->class_id)->exists();
        }
        return false; // Student flow is in LibraryController
    }
}
