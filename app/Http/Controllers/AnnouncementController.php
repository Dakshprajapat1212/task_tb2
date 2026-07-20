<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = \App\Models\Announcement::orderBy('created_at', 'desc')->get();
        return response()->json($announcements);
    }
}
