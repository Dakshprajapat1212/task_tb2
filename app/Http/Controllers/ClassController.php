<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Faculty;

class ClassController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL CLASSES
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        // fetch all classes with faculty and subject
        $classes = ClassModel::with(['subjects'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Classes fetched successfully',
            'data' => $classes
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE NEW CLASS
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20'
        ]);

        $class = ClassModel::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully',
            'data' => $class
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE CLASS
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $class = ClassModel::with(['subjects'])->find($id);

        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found'
            ], 404);
        }

        if (auth()->user()->role_id == 1) {
            $enrollment = Enrollment::where('user_id', auth()->id())
                ->where('class_id', $class->id)
                ->where('status', 'approved')
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not enrolled in this class'
                ], 403);
            }
        }

        if (auth()->user()->role_id == 2) {
            $faculty = Faculty::where('user_id', auth()->id())->first();

            if (!$faculty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty profile not found'
                ], 404);
            }

            if (!$class->subjects()->wherePivot('faculty_id', $faculty->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can access only your own classes'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Class fetched successfully',
            'data' => $class
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE CLASS
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $class = ClassModel::find($id);

        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:20'
        ]);

        $class->update([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully',
            'data' => $class
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE CLASS
    |--------------------------------------------------------------------------
    */
   public function destroy($id)
{
    $class = ClassModel::find($id);

    if (!$class) {
        return response()->json([
            'success' => false,
            'message' => 'Class not found'
        ], 404);
    }

    $class->delete();

    return response()->json([
        'success' => true,
        'message' => 'Class deleted successfully'
    ], 200);
}

public function myClasses()
{
    $classes = Enrollment::where(
        'user_id',
        auth()->id()
    )
    ->where(
        'status',
        'approved'
    )
    ->with([
        'class.subjects'
    ])
    ->get()
    ->pluck('class');

    // Append teacher name for each subject's pivot data
    foreach ($classes as $class) {
        if ($class && $class->subjects) {
            foreach ($class->subjects as $subject) {
                if ($subject->pivot && $subject->pivot->faculty_id) {
                    $faculty = \App\Models\Faculty::with('user')->find($subject->pivot->faculty_id);
                    $subject->pivot->faculty_name = $faculty && $faculty->user ? $faculty->user->name : 'Expert Faculty';
                } else {
                    $subject->pivot->faculty_name = 'Expert Faculty';
                }
            }
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'My classes fetched successfully',
        'data' => $classes
    ], 200);
}
public function facultyClasses()
{
    $faculty = \App\Models\Faculty::where(
        'user_id',
        auth()->id()
    )->first();

    if (!$faculty) {

        return response()->json([
            'success' => false,
            'message' => 'Faculty profile not found'
        ], 404);
    }

    $classes = ClassModel::forFaculty($faculty->id)
    ->with([
        'subjects'
    ])
    ->get();

    return response()->json([
        'success' => true,
        'message' => 'Faculty classes fetched successfully',
        'data' => $classes
    ], 200);
}

    public function assignSubject(Request $request, $classId)
    {
        $class = ClassModel::find($classId);

        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found'
            ], 404);
        }

        $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_link' => 'nullable|string|max:100',
            'class_date' => 'nullable|date',
            'start_time' => 'nullable',
            'end_time'   => 'nullable',
        ]);

        $class->subjects()->attach($request->subject_id, [
            'faculty_id' => $request->faculty_id,
            'class_link' => $request->class_link,
            'class_date' => $request->class_date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject assigned successfully'
        ], 200);
    }
}
