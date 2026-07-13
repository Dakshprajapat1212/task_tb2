<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL STUDENTS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $students = Student::with('user')->get();

        return response()->json([

            'success' => true,

            'message' => 'Students fetched successfully',

            'data' => $students

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE STUDENT
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

            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('students', 'user_id'),
                function ($attribute, $value, $fail) {
                    $user = User::find($value);

                    if ($user && $user->role_id != 1) {
                        $fail('Selected user must have student role.');
                    }
                },
            ],

            'dob' => 'required|date',

            'address' => 'required'
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE STUDENT
        |--------------------------------------------------------------------------
        */

        $student = Student::create([

            'user_id' => $request->user_id,

            'dob' => $request->dob,

            'address' => $request->address
        ]);

        return response()->json([

            'success' => true,

            'message' => 'Student created successfully',

            'data' => $student

        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE STUDENT
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $student = Student::with('user')

            ->find($id);

        if (!$student) {

            return response()->json([

                'success' => false,

                'message' => 'Student not found'

            ], 404);
        }

        return response()->json([

            'success' => true,

            'message' => 'Student fetched successfully',

            'data' => $student

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STUDENT
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {

            return response()->json([

                'success' => false,

                'message' => 'Student not found'

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'dob' => 'required|date',

            'address' => 'required'
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE STUDENT
        |--------------------------------------------------------------------------
        */

        $student->update([

            'dob' => $request->dob,

            'address' => $request->address
        ]);

        return response()->json([

            'success' => true,

            'message' => 'Student updated successfully',

            'data' => $student

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE STUDENT
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $student = Student::find($id);

        if (!$student) {

            return response()->json([

                'success' => false,

                'message' => 'Student not found'

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE STUDENT
        |--------------------------------------------------------------------------
        */

        $student->delete();

        return response()->json([

            'success' => true,

            'message' => 'Student deleted successfully'

        ], 200);
    }
}
