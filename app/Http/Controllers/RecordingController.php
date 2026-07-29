<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recording;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\ClassModel;

class RecordingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (FULL CRUD)
    |--------------------------------------------------------------------------
    */

    // GET /admin/recordings
    public function adminIndex(Request $request)
    {
        $query = Recording::with(['class', 'subject', 'chapter']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('chapter_id')) {
            $query->where('chapter_id', $request->chapter_id);
        }

        $recordings = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'All recordings fetched (admin)',
            'data' => $recordings
        ], 200);
    }

    // GET /admin/recordings/{id}
    public function adminShow($id)
    {
        $recording = Recording::with('class')->find($id);

        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Recording fetched (admin)',
            'data' => $recording
        ], 200);
    }

    // POST /admin/recordings
    public function adminStore(Request $request)
    {
        if ($request->has('course')) {
            $className = str_replace('Class', 'Grade', $request->course);
            $classModel = ClassModel::where('name', $className)->first();
            
            if ($classModel) {
                $request->merge(['class_id' => $classModel->id]);
            }
        }
        
        if ($request->has('subject')) {
            $subjectModel = \App\Models\Subject::where('name', $request->subject)->first();
            if ($subjectModel) {
                $request->merge(['subject_id' => $subjectModel->id]);
            }
        }

        if ($request->has('chapter')) {
            $chapterModel = \App\Models\Chapter::where('title', $request->chapter)->first();
            if ($chapterModel) {
                $request->merge(['chapter_id' => $chapterModel->id]);
            }
        }
        
        if ($request->has('topic') && $request->has('title')) {
            $request->merge(['topic' => substr($request->topic . ' - ' . $request->title, 0, 100)]);
        } else if ($request->has('title')) {
            $request->merge(['topic' => substr($request->title, 0, 100)]);
        }
        
        if ($request->has('videoUrl')) {
            $request->merge(['video_link' => $request->videoUrl]);
        }
        
        if ($request->has('timestamps')) {
            $request->merge(['video_timestamps' => $request->timestamps]);
        }
        
        if (!$request->has('duration')) {
            $request->merge(['duration' => 0]);
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'topic' => 'required|string|max:100',
            'duration' => 'required|integer|min:0',
            'video_link' => 'required|string',
            'video_timestamps' => 'nullable|array',
            'subject_id' => 'nullable|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id'
        ]);

        $recording = Recording::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'chapter_id' => $request->chapter_id,
            'topic' => $request->topic,
            'duration' => $request->duration,
            'video_link' => $request->video_link,
            'video_timestamps' => $request->video_timestamps,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recording created (admin)',
            'data' => $recording
        ], 201);
    }

    // PUT /admin/recordings/{id}
    public function adminUpdate(Request $request, $id)
    {
        $recording = Recording::find($id);

        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        if ($request->has('course')) {
            $className = str_replace('Class', 'Grade', $request->course);
            $classModel = ClassModel::where('name', $className)->first();
            
            if ($classModel) {
                $request->merge(['class_id' => $classModel->id]);
            }
        }
        
        if ($request->has('subject')) {
            $subjectModel = \App\Models\Subject::where('name', $request->subject)->first();
            if ($subjectModel) {
                $request->merge(['subject_id' => $subjectModel->id]);
            }
        }

        if ($request->has('chapter')) {
            $chapterModel = \App\Models\Chapter::where('title', $request->chapter)->first();
            if ($chapterModel) {
                $request->merge(['chapter_id' => $chapterModel->id]);
            }
        }
        
        if ($request->has('topic') && $request->has('title')) {
            $request->merge(['topic' => substr($request->topic . ' - ' . $request->title, 0, 100)]);
        } else if ($request->has('title')) {
            $request->merge(['topic' => substr($request->title, 0, 100)]);
        }
        
        if ($request->has('videoUrl')) {
            $request->merge(['video_link' => $request->videoUrl]);
        }
        
        if ($request->has('timestamps')) {
            $request->merge(['video_timestamps' => $request->timestamps]);
        }
        
        if (!$request->has('duration')) {
            $request->merge(['duration' => 0]);
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'topic' => 'required|string|max:100',
            'duration' => 'required|integer|min:0',
            'video_link' => 'required|string',
            'video_timestamps' => 'nullable|array',
            'subject_id' => 'nullable|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id'
        ]);

        $recording->update([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'chapter_id' => $request->chapter_id,
            'topic' => $request->topic,
            'duration' => $request->duration,
            'video_link' => $request->video_link,
            'video_timestamps' => $request->video_timestamps,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recording updated (admin)',
            'data' => $recording
        ], 200);
    }

    // DELETE /admin/recordings/{id}
    public function adminDestroy($id)
    {
        $recording = Recording::find($id);

        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        $recording->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recording deleted (admin)'
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | FACULTY ROUTES (ONLY OWN CLASS)
    |--------------------------------------------------------------------------
    */

    // GET /faculty/classes/{class_id}/recordings
    public function facultyClassRecordings($class_id)
    {
        $this->ensureFacultyOwnsClass($class_id);

        $recordings = Recording::where('class_id', $class_id)
            ->with('class')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Recordings fetched (faculty class)',
            'data' => $recordings
        ], 200);
    }

    // POST /faculty/classes/{class_id}/recordings
    public function facultyStoreInClass(Request $request, $class_id)
    {
        $this->ensureFacultyOwnsClass($class_id);

        if ($request->has('subject')) {
            $subjectModel = \App\Models\Subject::where('name', $request->subject)->first();
            if ($subjectModel) {
                $request->merge(['subject_id' => $subjectModel->id]);
            }
        }

        if ($request->has('chapter')) {
            $chapterModel = \App\Models\Chapter::where('title', $request->chapter)->first();
            if ($chapterModel) {
                $request->merge(['chapter_id' => $chapterModel->id]);
            }
        }

        if ($request->has('topic') && $request->has('title')) {
            $request->merge(['topic' => substr($request->topic . ' - ' . $request->title, 0, 100)]);
        } else if ($request->has('title')) {
            $request->merge(['topic' => substr($request->title, 0, 100)]);
        }
        
        if ($request->has('videoUrl')) {
            $request->merge(['video_link' => $request->videoUrl]);
        }
        
        if ($request->has('timestamps')) {
            $request->merge(['video_timestamps' => $request->timestamps]);
        }
        
        if (!$request->has('duration')) {
            $request->merge(['duration' => 0]);
        }

        $request->validate([
            'topic' => 'required|string|max:100',
            'duration' => 'required|integer|min:0',
            'video_link' => 'required|string',
            'video_timestamps' => 'nullable|array',
            'subject_id' => 'nullable|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id'
        ]);

        $recording = Recording::create([
            'class_id' => $class_id,
            'subject_id' => $request->subject_id,
            'chapter_id' => $request->chapter_id,
            'topic' => $request->topic,
            'duration' => $request->duration,
            'video_link' => $request->video_link,
            'video_timestamps' => $request->video_timestamps,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recording created (faculty)',
            'data' => $recording
        ], 201);
    }

    // GET /faculty/recordings/{id}
    public function facultyShow($id)
    {
        $recording = Recording::with('class')->find($id);

        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        $this->ensureFacultyOwnsClass($recording->class_id);

        return response()->json([
            'success' => true,
            'message' => 'Recording fetched (faculty)',
            'data' => $recording
        ], 200);
    }

    // PUT /faculty/recordings/{id}
    public function facultyUpdate(Request $request, $id)
    {
        $recording = Recording::find($id);

        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        $this->ensureFacultyOwnsClass($recording->class_id);

        if ($request->has('subject')) {
            $subjectModel = \App\Models\Subject::where('name', $request->subject)->first();
            if ($subjectModel) {
                $request->merge(['subject_id' => $subjectModel->id]);
            }
        }

        if ($request->has('chapter')) {
            $chapterModel = \App\Models\Chapter::where('title', $request->chapter)->first();
            if ($chapterModel) {
                $request->merge(['chapter_id' => $chapterModel->id]);
            }
        }

        if ($request->has('topic') && $request->has('title')) {
            $request->merge(['topic' => substr($request->topic . ' - ' . $request->title, 0, 100)]);
        } else if ($request->has('title')) {
            $request->merge(['topic' => substr($request->title, 0, 100)]);
        }
        
        if ($request->has('videoUrl')) {
            $request->merge(['video_link' => $request->videoUrl]);
        }
        
        if ($request->has('timestamps')) {
            $request->merge(['video_timestamps' => $request->timestamps]);
        }
        
        if (!$request->has('duration')) {
            $request->merge(['duration' => 0]);
        }

        $request->validate([
            'topic' => 'required|string|max:100',
            'duration' => 'required|integer|min:0',
            'video_link' => 'required|string',
            'video_timestamps' => 'nullable|array',
            'subject_id' => 'nullable|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id'
        ]);

        $recording->update([
            'subject_id' => $request->subject_id,
            'chapter_id' => $request->chapter_id,
            'topic' => $request->topic,
            'duration' => $request->duration,
            'video_link' => $request->video_link,
            'video_timestamps' => $request->video_timestamps,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recording updated (faculty)',
            'data' => $recording
        ], 200);
    }

    // DELETE /faculty/recordings/{id}
    public function facultyDestroy($id)
    {
        $recording = Recording::find($id);

        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        $this->ensureFacultyOwnsClass($recording->class_id);

        $recording->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recording deleted (faculty)'
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT ROUTES (ONLY ENROLLED CLASS)
    |--------------------------------------------------------------------------
    */

    // GET /classes/{class_id}/recordings
    public function studentClassRecordings(Request $request, $class_id)
    {
        $this->ensureStudentEnrolledInClass($class_id);

        $query = Recording::where('class_id', $class_id);

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $recordings = $query->with(['class', 'subject', 'chapter'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Recordings fetched (student class)',
            'data' => $recordings
        ], 200);
    }

    // GET /recordings/{id} (student view single recording, only if enrolled)
    public function studentShow($id)
    {
        $recording = Recording::with('class')->find($id);

        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        $this->ensureStudentEnrolledInClass($recording->class_id);

        return response()->json([
            'success' => true,
            'message' => 'Recording fetched (student)',
            'data' => $recording
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function ensureFacultyOwnsClass($class_id)
    {
        $faculty = Faculty::where('user_id', auth()->id())->first();

        if (!$faculty) {
            return abort(response()->json([
                'success' => false,
                'message' => 'Faculty profile not found'
            ], 403));
        }

        $class = ClassModel::forFaculty($faculty->id)->where('id', $class_id)
            ->first();

        if (!$class) {
            return abort(response()->json([
                'success' => false,
                'message' => 'You can access only your own class recordings'
            ], 403));
        }
    }

    private function ensureStudentEnrolledInClass($class_id)
    {
        $enrollment = Enrollment::where('user_id', auth()->id())
            ->where('class_id', $class_id)
            ->where('status', 'approved')
            ->first();

        if (!$enrollment) {
            return abort(response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this class'
            ], 403));
        }
    }
}
