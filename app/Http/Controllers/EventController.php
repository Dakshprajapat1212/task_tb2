<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = \App\Models\Event::orderBy('event_date', 'asc')->get();
        return response()->json($events);
    }

    public function featured()
    {
        $event = \App\Models\Event::where('is_event_of_the_week', true)->first();
        return response()->json($event);
    }
}
