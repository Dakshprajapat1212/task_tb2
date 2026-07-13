<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\ClassModel;
use App\Models\Faculty;

class V2AdminChapterController extends Controller
{
    public function index()
    {
        if (auth()->user()->role_id == 3) {
            $chapters = Chapter::with(['class', 'subject'])->get();
        } elseif (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
            $class_ids = ClassModel::forFaculty($faculty->id)->pluck('id');
            $chapters = Chapter::whereIn('class_id', $class_ids)->with(['class', 'subject'])->get();
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

        $chapter->update([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'display_order' => $request->display_order ?? $chapter->display_order,
            'status' => $request->status ?? $chapter->status
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

        $chapter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chapter deleted successfully'
        ], 200);
    }

    private function canAccessChapter(Topic $topic)
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
