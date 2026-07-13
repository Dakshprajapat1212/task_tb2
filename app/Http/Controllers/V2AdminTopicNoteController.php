<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TopicNote;
use App\Models\ClassModel;
use App\Models\Faculty;
use App\Models\Chapter;

class V2AdminTopicNoteController extends Controller
{
    public function index()
    {
        if (auth()->user()->role_id == 3) {
            $notes = TopicNote::with(['class', 'subject'])->get();
        } elseif (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
            $class_ids = ClassModel::forFaculty($faculty->id)->pluck('id');
            $notes = TopicNote::whereIn('class_id', $class_ids)->with(['class', 'subject'])->get();
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Topic Notes fetched successfully',
            'data' => \App\Http\Resources\TopicNoteResource::collection($notes)
        ], 200);
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role_id, [2, 3])) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'chapter_id' => 'required|exists:chapters,id',
            'title' => 'required',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx',
            'file_url' => 'nullable|url'
        ]);

        if (!$request->hasFile('file') && !$request->filled('file_url')) {
            return response()->json(['success' => false, 'message' => 'Either file or file_url is required'], 422);
        }

        // Validate Chapter belongs to Class
        $chapter = Chapter::find($request->chapter_id);
        if ($chapter->class_id != $request->class_id) {
            return response()->json(['success' => false, 'message' => 'Chapter does not belong to the selected class'], 422);
        }

        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return response()->json(['success' => false, 'message' => 'Faculty profile not found'], 404);
            $class = ClassModel::forFaculty($faculty->id)->where('id', $request->class_id)->first();
            if (!$class) return response()->json(['success' => false, 'message' => 'Unauthorized class access'], 403);
        }

        $filePath = $request->hasFile('file') ? $request->file('file')->store('notes', 'public') : $request->file_url;

        $note = TopicNote::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'chapter_id' => $request->chapter_id,
            'topic' => $request->title,
            'file_url' => $filePath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Topic Note uploaded successfully',
            'data' => new \App\Http\Resources\TopicNoteResource($note)
        ], 201);
    }

    public function show($id)
    {
        $note = TopicNote::with(['class', 'subject'])->find($id);
        if (!$note) return response()->json(['success' => false, 'message' => 'Topic Note not found'], 404);
        if (!$this->canAccessNote($note)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        return response()->json([
            'success' => true,
            'message' => 'Topic Note fetched successfully',
            'data' => new \App\Http\Resources\TopicNoteResource($note)
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $note = TopicNote::find($id);
        if (!$note) return response()->json(['success' => false, 'message' => 'Topic Note not found'], 404);
        if (!$this->canAccessNote($note)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'chapter_id' => 'required|exists:chapters,id',
            'title' => 'required',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx',
            'file_url' => 'nullable|url'
        ]);

        $chapter = Chapter::find($request->chapter_id);
        if ($chapter->class_id != $request->class_id) {
            return response()->json(['success' => false, 'message' => 'Chapter does not belong to the selected class'], 422);
        }

        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            $newClass = ClassModel::forFaculty($faculty->id)->where('id', $request->class_id)->first();
            if (!$newClass) return response()->json(['success' => false, 'message' => 'Unauthorized class assignment'], 403);
        }

        $filePath = $topicNote->file_url;
        if ($request->hasFile('file') || $request->filled('file_url')) {
            // Delete old file if it was stored locally
            if ($topicNote->file_url && !filter_var($topicNote->file_url, FILTER_VALIDATE_URL)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($topicNote->file_url);
            }
            
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('notes', 'public');
            } else {
                $filePath = $request->file_url;
            }
        }

        $topicNote->update([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'chapter_id' => $request->chapter_id,
            'topic' => $request->title,
            'file_url' => $filePath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Topic Note updated successfully',
            'data' => new \App\Http\Resources\TopicNoteResource($note)
        ], 200);
    }

    public function destroy($id)
    {
        $note = TopicNote::find($id);
        if (!$note) return response()->json(['success' => false, 'message' => 'Topic Note not found'], 404);
        if (!$this->canAccessNote($note)) return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);

        if ($topicNote->file_url && !filter_var($topicNote->file_url, FILTER_VALIDATE_URL)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($topicNote->file_url);
        }

        $topicNote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Topic Note deleted successfully'
        ], 200);
    }

    private function canAccessNote(Note $note)
    {
        if (auth()->user()->role_id == 3) return true;
        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();
            if (!$faculty) return false;
            return ClassModel::forFaculty($faculty->id)->where('id', $topicNote->class_id)->exists();
        }
        return false;
    }
}
