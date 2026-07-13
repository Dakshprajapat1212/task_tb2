<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasRole;
use Illuminate\Validation\Rule;

class MasRoleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL MAS ROLES
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $masRoles = MasRole::with('users')->get();

        return response()->json([
            'success' => true,
            'message' => 'Mas roles fetched successfully',
            'data' => $masRoles
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE NEW MAS ROLE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:mas_roles,name',
        ]);

        $masRole = MasRole::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mas role created successfully',
            'data' => $masRole
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SINGLE MAS ROLE
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $masRole = MasRole::with('users')->find($id);

        if (!$masRole) {
            return response()->json([
                'success' => false,
                'message' => 'Mas role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mas role fetched successfully',
            'data' => $masRole
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE MAS ROLE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $masRole = MasRole::find($id);

        if (!$masRole) {
            return response()->json([
                'success' => false,
                'message' => 'Mas role not found'
            ], 404);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mas_roles', 'name')->ignore($masRole->id),
            ],
        ]);

        $masRole->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mas role updated successfully',
            'data' => $masRole
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE MAS ROLE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $masRole = MasRole::find($id);

        if (!$masRole) {
            return response()->json([
                'success' => false,
                'message' => 'Mas role not found'
            ], 404);
        }

        $masRole->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mas role deleted successfully'
        ], 200);
    }
}
