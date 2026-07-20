<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Event::create([
            'title' => 'National Coding Hackathon',
            'subtitle' => 'Build AI Solutions',
            'event_date' => now()->addDays(3),
            'registered_count' => 350,
            'prize_pool' => '₹15000',
            'is_event_of_the_week' => true,
        ]);

        \App\Models\Event::create([
            'title' => 'Guest Lecture: Space',
            'subtitle' => 'With Dr. Sarah Jenkins',
            'event_date' => now()->addDays(8),
            'registered_count' => 150,
            'prize_pool' => null,
            'is_event_of_the_week' => false,
        ]);

        \App\Models\Event::create([
            'title' => 'Debate Championship',
            'subtitle' => 'Topic: Future of AI',
            'event_date' => now()->addDays(14),
            'registered_count' => 210,
            'prize_pool' => null,
            'is_event_of_the_week' => false,
        ]);
    }
}
