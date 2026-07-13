<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Student;

class EnrollmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL ENROLLMENTS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $enrollments = Enrollment::with([

            'user',

            'class.subjects'

        ])->get();

        return response()->json([

            'success' => true,

            'message' => 'Enrollments fetched successfully',

            'data' => $enrollments

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ENROLLMENT REQUEST
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
  

        /*
        |--------------------------------------------------------------------------
        | ONLY STUDENTS CAN ENROLL 
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role_id != 1) {

            return response()->json([

                'success' => false,

                'message' => 'Only students can enroll'

            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

      $request->validate([
    'class_ids' => 'required_without:class_id|array|min:1',
    'class_ids.*' => 'distinct|exists:classes,id',

    'class_id' => 'required_without:class_ids|exists:classes,id',

    'dob' => 'required|date',
    'address' => 'required|string',
    
    'fullName' => 'nullable|string',
    'email' => 'nullable|email',
    'mobile' => 'nullable|string',
    'gender' => 'nullable|string',
    'photo' => 'nullable|string',
    'school' => 'nullable|string',
    'board' => 'nullable|string',
    'course' => 'nullable|string',
    'batchMode' => 'nullable|string',
    'fatherName' => 'nullable|string',
    'fatherOccupation' => 'nullable|string',
    'motherName' => 'nullable|string',
    'parentMobile' => 'nullable|string',
    'marksheet' => 'nullable|string',
]);
        $isSingleClassRequest = !$request->has('class_ids');

        $classIds = $request->has('class_ids')

            ? $request->class_ids

            : [$request->class_id];

        /*
        |--------------------------------------------------------------------------
        | CREATE ENROLLMENT REQUESTS
        |--------------------------------------------------------------------------
        */

        $created = [];

        $skipped = [];

        foreach ($classIds as $classId) {

            $alreadyExists = Enrollment::where(

                'user_id',

                auth()->id()

            )->where(

                'class_id',

                $classId

            )->whereIn(

                'status',

                ['pending', 'approved']

            )->first();

            if ($alreadyExists) {

                if ($isSingleClassRequest) {

                    return response()->json([

                        'success' => false,

                        'message' => 'You are already enrolled in this class'

                    ], 400);
                }

                $skipped[] = $alreadyExists;

                continue;
            }

            $created[] = Enrollment::create([

                'user_id' => auth()->id(),

                'class_id' => $classId,

                'dob' => $request->dob,

                'address' => $request->address,

                'status' => 'pending',
                
                'full_name' => $request->fullName,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'gender' => $request->gender,
                'photo' => $request->photo,
                'school' => $request->school,
                'board' => $request->board,
                'course' => $request->course,
                'batch_mode' => $request->batchMode,
                'father_name' => $request->fatherName,
                'father_occupation' => $request->fatherOccupation,
                'mother_name' => $request->motherName,
                'parent_mobile' => $request->parentMobile,
                'marksheet' => $request->marksheet,

            ]);
        }

        if (count($created) == 0) {

            return response()->json([

                'success' => false,

                'message' => 'All selected classes are already enrolled or pending'

            ], 400);
        }

        return response()->json([

            'success' => true,

            'message' => 'Enrollment requests submitted successfully',

            'created_count' => count($created),

            'skipped_count' => count($skipped),

            'created' => $created,

            'skipped' => $skipped

        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE ENROLLMENT
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $enrollment = Enrollment::with([

            'user',

            'class.subjects'

        ])->find($id);

        if (!$enrollment) {

            return response()->json([

                'success' => false,

                'message' => 'Enrollment not found'

            ], 404);
        }

        if (

            auth()->user()->role_id != 3 &&

            (

                auth()->user()->role_id != 1 ||

                $enrollment->user_id != auth()->id()

            )

        ) {

            return response()->json([

                'success' => false,

                'message' => 'Unauthorized access'

            ], 403);
        }

        return response()->json([

            'success' => true,

            'message' => 'Enrollment fetched successfully',

            'data' => $enrollment

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | GET MY ENROLLMENTS
    |--------------------------------------------------------------------------
    */
    public function myEnrollments()
    {
      
        $enrollments = Enrollment::with([

            'user',

            'class.subjects'

        ])->where(

            'user_id',

            auth()->id()

        )->get();

        return response()->json([

            'success' => true,

            'message' => 'My enrollments fetched successfully',

            'data' => $enrollments

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ENROLLMENT STATUS
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | FIND ENROLLMENT
        |--------------------------------------------------------------------------
        */

        $enrollment = Enrollment::find($id);

        if (!$enrollment) {

            return response()->json([

                'success' => false,

                'message' => 'Enrollment not found'

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'status' => 'required|in:approved,rejected'
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS
        |--------------------------------------------------------------------------
        */

        $enrollment->update([

            'status' => $request->status
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE STUDENT RECORD
        |--------------------------------------------------------------------------
        */

        if ($request->status == 'approved') {

            $student = Student::where(

                'user_id',

                $enrollment->user_id

            )->first();

            if (!$student) {

                Student::create([

                    'user_id' => $enrollment->user_id,

                    'dob' => $enrollment->dob,

                    'address' => $enrollment->address,

                    'full_name' => $enrollment->full_name,
                    'email' => $enrollment->email,
                    'mobile' => $enrollment->mobile,
                    'gender' => $enrollment->gender,
                    'photo' => $enrollment->photo,
                    'school' => $enrollment->school,
                    'board' => $enrollment->board,
                    'course' => $enrollment->course,
                    'batch_mode' => $enrollment->batch_mode,
                    'father_name' => $enrollment->father_name,
                    'father_occupation' => $enrollment->father_occupation,
                    'mother_name' => $enrollment->mother_name,
                    'parent_mobile' => $enrollment->parent_mobile,
                    'marksheet' => $enrollment->marksheet,
                ]);
            }
        }

        return response()->json([

            'success' => true,

            'message' => 'Enrollment updated successfully',

            'data' => $enrollment

        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ENROLLMENT
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        /*
        |--------------------------------------------------------------------------
        | FIND ENROLLMENT
        |--------------------------------------------------------------------------
        */

        $enrollment = Enrollment::find($id);

        if (!$enrollment) {

            return response()->json([

                'success' => false,

                'message' => 'Enrollment not found'

            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE ENROLLMENT
        |--------------------------------------------------------------------------
        */

        $enrollment->delete();

        return response()->json([

            'success' => true,

            'message' => 'Enrollment deleted successfully'

        ], 200);
    }
}
