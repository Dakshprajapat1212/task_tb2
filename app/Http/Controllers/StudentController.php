<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /*
    |--------------------------------------------------------------------------
    | STUDENT-FACING: GET CURRENT LOGGED-IN PROFILE
    |--------------------------------------------------------------------------
    */
    public function getProfile(Request $request)
    {
        $userId = Auth::id();
        $student = Student::with('user')->where('user_id', $userId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student profile fetched successfully',
            'data' => $student
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT-FACING: UPDATE PROFILE
    |--------------------------------------------------------------------------
    */
    public function updateProfile(Request $request)
    {
        $userId = Auth::id();
        $student = Student::where('user_id', $userId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone_no' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string',
            'school' => 'nullable|string|max:255',
            'board' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'parent_mobile' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Update users table
            $user = User::find($userId);
            $user->update([
                'name' => $request->name,
                'phone_no' => $request->phone_no,
            ]);

            // Update students table
            $student->update([
                'dob' => $request->dob,
                'gender' => $request->gender,
                'address' => $request->address,
                'school' => $request->school,
                'board' => $request->board,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'parent_mobile' => $request->parent_mobile,
            ]);

            DB::commit();

            // Load fresh relation
            $student->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $student
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT-FACING: UPLOAD AVATAR
    |--------------------------------------------------------------------------
    */
    public function uploadAvatar(Request $request)
    {
        $userId = Auth::id();
        $student = Student::where('user_id', $userId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.'
            ], 404);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('avatars'), $filename);

                $photoPath = '/avatars/' . $filename;
                $student->update([
                    'photo' => $photoPath
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Avatar uploaded successfully',
                    'photo_url' => $photoPath
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No image file uploaded'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Avatar upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET STUDENT BADGES
    |--------------------------------------------------------------------------
    */
    public function getBadges()
    {
        $student = Student::where('user_id', auth()->id())->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        $badges = \App\Models\StudentBadge::where('student_id', $student->id)
            ->latest('unlocked_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Student badges fetched successfully',
            'data' => $badges
        ], 200);
    }
}
