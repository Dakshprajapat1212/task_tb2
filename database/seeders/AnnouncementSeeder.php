<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Announcement::create([
            'title' => 'Campus Maintenance Notice',
            'type' => 'notice',
            'created_at' => now()->subHours(1),
        ]);

        \App\Models\Announcement::create([
            'title' => 'Advanced Calculus Notes Added',
            'type' => 'notes',
            'created_at' => now()->subHours(4),
        ]);

        \App\Models\Announcement::create([
            'title' => 'Midterm Practice Quiz Live',
            'type' => 'quiz',
            'created_at' => now()->subHours(12),
        ]);

        \App\Models\Announcement::create([
            'title' => 'Math Olympiad Results Declared',
            'type' => 'results',
            'created_at' => now()->subDays(1),
        ]);

        \App\Models\Announcement::create([
            'title' => 'Extended Library Hours',
            'type' => 'notice',
            'created_at' => now()->subDays(2),
        ]);
    }
}
