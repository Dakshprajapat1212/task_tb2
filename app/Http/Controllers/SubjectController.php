<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL SUBJECTS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $subjects = Subject::all();

        return response()->json([
            'success' => true,
            'message' => 'Subjects fetched successfully',
            'data' => $subjects
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE NEW SUBJECT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $subject = Subject::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully',
            'data' => $subject
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE SUBJECT
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subject fetched successfully',
            'data' => $subject
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE SUBJECT
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $subject->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully',
            'data' => $subject
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE SUBJECT
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully'
        ], 200);
    }
}
