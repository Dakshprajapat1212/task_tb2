<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Validation\Rule;

class FacultyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL FACULTIES
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $faculties = Faculty::with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Faculties fetched successfully',
            'data' => $faculties
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE NEW FACULTY
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('faculties', 'user_id'),
                function ($attribute, $value, $fail) {
                    $user = User::find($value);

                    if ($user && $user->role_id != 2) {
                        $fail('Selected user must have faculty role.');
                    }
                },
            ],
            'date_of_joining' => 'required|date',
            'qualification' => 'required|string|max:100',
            'bio' => 'nullable|string',
        ]);

        $faculty = Faculty::create([
            'user_id' => $request->user_id,
            'date_of_joining' => $request->date_of_joining,
            'qualification' => $request->qualification,
            'bio' => $request->bio,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Faculty created successfully',
            'data' => $faculty
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE FACULTY
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $faculty = Faculty::with('user')->find($id);

        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Faculty fetched successfully',
            'data' => $faculty
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE FACULTY
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $faculty = Faculty::find($id);

        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found'
            ], 404);
        }

        $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('faculties', 'user_id')->ignore($faculty->id),
                function ($attribute, $value, $fail) {
                    $user = User::find($value);

                    if ($user && $user->role_id != 2) {
                        $fail('Selected user must have faculty role.');
                    }
                },
            ],
            'date_of_joining' => 'required|date',
            'qualification' => 'required|string|max:100',
            'bio' => 'nullable|string',
        ]);

        $faculty->update([
            'user_id' => $request->user_id,
            'date_of_joining' => $request->date_of_joining,
            'qualification' => $request->qualification,
            'bio' => $request->bio,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Faculty updated successfully',
            'data' => $faculty
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE FACULTY
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $faculty = Faculty::find($id);

        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found'
            ], 404);
        }

        $faculty->delete();

        return response()->json([
            'success' => true,
            'message' => 'Faculty deleted successfully'
        ], 200);
    }
}
