<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Homework;
use App\Models\Enrollment;
use App\Models\ClassModel;
use App\Models\Faculty;

class HomeworkController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL HOMEWORKS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN GETS ALL HOMEWORKS
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role_id == 3) {

            $homeworks = Homework::with('class')->get();

            return response()->json([

                'success' => true,

                'message' => 'All homeworks fetched successfully',

                'data' => $homeworks

            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | FACULTY GETS ONLY THEIR CLASS HOMEWORKS
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role_id == 2) {

            /*
            |--------------------------------------------------------------------------
            | GET FACULTY RECORD
            |--------------------------------------------------------------------------
            */

            $faculty = Faculty::where('user_id', auth()->id())

                ->first();

            /*
            |--------------------------------------------------------------------------
            | GET FACULTY CLASSES
            |--------------------------------------------------------------------------
            */

            $class_ids = ClassModel::forFaculty($faculty->id)

                ->pluck('id');

            /*
            |--------------------------------------------------------------------------
            | GET HOMEWORKS OF FACULTY CLASSES
            |--------------------------------------------------------------------------
            */

            $homeworks = Homework::whereIn('class_id', $class_ids)

                ->with('class')

                ->get();

            return response()->json([

                'success' => true,

                'message' => 'Faculty homeworks fetched successfully',

                'data' => $homeworks

            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENT GETS ONLY ENROLLED CLASS HOMEWORKS
        |--------------------------------------------------------------------------
        */

        $class_ids = Enrollment::where('user_id', auth()->id())

            ->where('status', 'approved')

            ->pluck('class_id');

        $homeworks = Homework::whereIn('class_id', $class_ids)

            ->with('class')

            ->get();

        return response()->json([

            'success' => true,

            'message' => 'Student homeworks fetched successfully',

            'data' => $homeworks

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE HOMEWORK
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'class_id' => 'required|exists:classes,id',

            'topic' => 'required',

            'status' => 'required'
        ]);

        /*
        |--------------------------------------------------------------------------
        | ONLY ADMIN AND FACULTY CAN CREATE HOMEWORK
        |--------------------------------------------------------------------------
        */

        if (!in_array(auth()->user()->role_id, [2, 3])) {

            return response()->json([

                'success' => false,

                'message' => 'Unauthorized access'

            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | FACULTY CAN ONLY CREATE FOR OWN CLASS
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role_id == 2) {

            /*
            |--------------------------------------------------------------------------
            | GET FACULTY RECORD
            |--------------------------------------------------------------------------
            */

            $faculty = Faculty::where('user_id', auth()->id())

                ->first();

            /*
            |--------------------------------------------------------------------------
            | CHECK CLASS OWNERSHIP
            |--------------------------------------------------------------------------
            */

            $class = ClassModel::forFaculty($faculty->id)->where('id', $request->class_id)

                ->first();

            if (!$class) {

                return response()->json([

                    'success' => false,

                    'message' => 'You can only create homework for your own classes'

                ], 403);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE HOMEWORK
        |--------------------------------------------------------------------------
        */

        $homework = Homework::create([

            'class_id' => $request->class_id,

            'topic' => $request->topic,

            'description' => $request->description,

            'due_date' => $request->due_date,

            'status' => $request->status
        ]);

        return response()->json([

            'success' => true,

            'message' => 'Homework created successfully',

            'data' => $homework

        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE HOMEWORK
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $homework = Homework::with('class')->find($id);

        if (!$homework) {

            return response()->json([

                'success' => false,

                'message' => 'Homework not found'

            ], 404);
        }

        return response()->json([

            'success' => true,

            'message' => 'Homework fetched successfully',

            'data' => $homework

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE HOMEWORK
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $homework = Homework::find($id);

        if (!$homework) {

            return response()->json([

                'success' => false,

                'message' => 'Homework not found'

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | ONLY ADMIN AND FACULTY CAN UPDATE HOMEWORK
        |--------------------------------------------------------------------------
        */

        if (!in_array(auth()->user()->role_id, [2, 3])) {

            return response()->json([

                'success' => false,

                'message' => 'Unauthorized access'

            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | FACULTY CAN ONLY UPDATE OWN CLASS HOMEWORK
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role_id == 2) {

            /*
            |--------------------------------------------------------------------------
            | GET FACULTY RECORD
            |--------------------------------------------------------------------------
            */

            $faculty = Faculty::where('user_id', auth()->id())

                ->first();

            /*
            |--------------------------------------------------------------------------
            | CHECK CLASS OWNERSHIP
            |--------------------------------------------------------------------------
            */

            $class = ClassModel::forFaculty($faculty->id)->where('id', $homework->class_id)

                ->first();

            if (!$class) {

                return response()->json([

                    'success' => false,

                    'message' => 'You can only update your own class homework'

                ], 403);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE HOMEWORK
        |--------------------------------------------------------------------------
        */

        $homework->update([

            'topic' => $request->topic,

            'description' => $request->description,

            'due_date' => $request->due_date,

            'status' => $request->status
        ]);

        return response()->json([

            'success' => true,

            'message' => 'Homework updated successfully',

            'data' => $homework

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE HOMEWORK
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $homework = Homework::find($id);

        if (!$homework) {

            return response()->json([

                'success' => false,

                'message' => 'Homework not found'

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | ONLY ADMIN AND FACULTY CAN DELETE HOMEWORK
        |--------------------------------------------------------------------------
        */

        if (!in_array(auth()->user()->role_id, [2, 3])) {

            return response()->json([

                'success' => false,

                'message' => 'Unauthorized access'

            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | FACULTY CAN ONLY DELETE OWN CLASS HOMEWORK
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role_id == 2) {

            /*
            |--------------------------------------------------------------------------
            | GET FACULTY RECORD
            |--------------------------------------------------------------------------
            */

            $faculty = Faculty::where('user_id', auth()->id())

                ->first();

            /*
            |--------------------------------------------------------------------------
            | CHECK CLASS OWNERSHIP
            |--------------------------------------------------------------------------
            */

            $class = ClassModel::forFaculty($faculty->id)->where('id', $homework->class_id)

                ->first();

            if (!$class) {

                return response()->json([

                    'success' => false,

                    'message' => 'You can only delete your own class homework'

                ], 403);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE HOMEWORK
        |--------------------------------------------------------------------------
        */

        $homework->delete();

        return response()->json([

            'success' => true,

            'message' => 'Homework deleted successfully'

        ], 200);
    }
}