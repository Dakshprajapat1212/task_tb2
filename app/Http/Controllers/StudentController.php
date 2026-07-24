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

    /*
    |--------------------------------------------------------------------------
    | GET DYNAMIC ACHIEVEMENTS CATALOG
    |--------------------------------------------------------------------------
    */
    public function getAchievements()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $unlockedBadges = \App\Models\StudentBadge::where('student_id', $student->id)
            ->get()
            ->keyBy('badge_id');

        $userNotesCount = \App\Models\StudentNoteProgress::where('student_id', $student->id)->count();
        $userSubmissionsCount = \App\Models\SubmitHomework::where('student_id', $student->id)->where('status', 'approved')->count();
        $userQuizAttempts = \App\Models\QuizAttempt::where('student_id', $student->id)->get();
        $userQuizCount = $userQuizAttempts->count();
        $userQuizAvg = $userQuizCount > 0 ? round($userQuizAttempts->avg('score_percentage'), 1) : 0;
        $userStreak = $student->streak_days;

        $userClassIds = \App\Models\Enrollment::where('user_id', $user->id)->where('status', 'approved')->pluck('class_id');
        $totalRecordings = \App\Models\Recording::whereIn('class_id', $userClassIds)->count();
        $attendedCount = \App\Models\LiveAttendance::where('student_id', $student->id)->whereIn('class_id', $userClassIds)->whereNotNull('completed_at')->count();
        $attendancePct = $totalRecordings > 0 ? min(100, round(($attendedCount / $totalRecordings) * 100)) : 95;

        $activity = [
            [
                'id' => 'consistency-king',
                'title' => 'Consistency King',
                'requirement' => 'Maintain a 5-day streak',
                'desc' => 'Studied consistently for 5 consecutive days.',
                'progress' => min(100, round(($userStreak / 5) * 100)),
                'unlocked' => $unlockedBadges->has('consistency-king'),
                'unlocked_at' => $unlockedBadges->get('consistency-king')?->unlocked_at,
                'icon' => '👑',
                'tag' => $userStreak . ' / 5 Days',
                'color' => '#FFA500',
                'tips' => 'Log in and complete at least one study activity every day.'
            ],
            [
                'id' => 'notes-master',
                'title' => 'Notes Master',
                'requirement' => 'Read at least 1 study note',
                'desc' => 'Actively explored library study materials.',
                'progress' => min(100, round(($userNotesCount / 1) * 100)),
                'unlocked' => $unlockedBadges->has('notes-master'),
                'unlocked_at' => $unlockedBadges->get('notes-master')?->unlocked_at,
                'icon' => '📖',
                'tag' => $userNotesCount . ' Notes Read',
                'color' => '#8b5cf6',
                'tips' => 'Visit the Library and click Mark as Complete on notes.'
            ],
            [
                'id' => 'attendance-hero',
                'title' => 'Attendance Hero',
                'requirement' => 'Maintain 85%+ lecture attendance',
                'desc' => 'Remained active in real-time interactive lectures.',
                'progress' => min(100, $attendancePct),
                'unlocked' => $unlockedBadges->has('attendance-hero'),
                'unlocked_at' => $unlockedBadges->get('attendance-hero')?->unlocked_at,
                'icon' => '🔥',
                'tag' => $attendancePct . '% Attendance',
                'color' => '#10b981',
                'tips' => 'Join live classes regularly and watch for at least 30 seconds.'
            ],
            [
                'id' => 'qa-contributor',
                'title' => 'Q&A Contributor',
                'requirement' => 'Post 10 accepted answers',
                'desc' => 'Help peer students by solving doubts.',
                'progress' => 0,
                'unlocked' => false,
                'unlocked_at' => null,
                'icon' => '💬',
                'tag' => '0 / 10 Completed',
                'color' => '#06b6d4',
                'tips' => 'Answer questions in class Q&A forums.'
            ],
            [
                'id' => 'night-owl',
                'title' => 'Night Owl',
                'requirement' => 'Study between 10PM and 2AM',
                'desc' => 'Log late night study hours.',
                'progress' => 0,
                'unlocked' => false,
                'unlocked_at' => null,
                'icon' => '🦉',
                'tag' => '0 / 10 Hours',
                'color' => '#3b82f6',
                'tips' => 'Study notes or watch lectures late at night.'
            ]
        ];

        $academic = [
            [
                'id' => 'quiz-genius',
                'title' => 'Quiz Genius',
                'requirement' => 'Maintain 88%+ average score in quizzes',
                'desc' => 'Achieved high accuracy on weekly quiz assessments.',
                'progress' => min(100, round(($userQuizAvg / 88) * 100)),
                'unlocked' => $unlockedBadges->has('quiz-genius'),
                'unlocked_at' => $unlockedBadges->get('quiz-genius')?->unlocked_at,
                'icon' => '🎯',
                'tag' => $userQuizAvg . '% Avg Score',
                'color' => '#ef4444',
                'tips' => 'Review notes before attempting quizzes.'
            ],
            [
                'id' => 'react-guru',
                'title' => 'React Guru',
                'requirement' => 'Score 100% in React challenge',
                'desc' => 'Scored flawless accuracy in React assessment.',
                'progress' => 0,
                'unlocked' => false,
                'unlocked_at' => null,
                'icon' => '⚛️',
                'tag' => 'Challenge Lock',
                'color' => '#00d8ff',
                'tips' => 'Master components and state management.'
            ],
            [
                'id' => 'logic-master',
                'title' => 'Logic Master',
                'requirement' => 'Pass 5 approved assignments',
                'desc' => 'Submitted 5 approved homework assignments.',
                'progress' => min(100, round(($userSubmissionsCount / 5) * 100)),
                'unlocked' => $userSubmissionsCount >= 5,
                'unlocked_at' => null,
                'icon' => '📐',
                'tag' => $userSubmissionsCount . ' / 5 Approved',
                'color' => '#3b82f6',
                'tips' => 'Submit homework assignments on time.'
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Achievements fetched successfully',
            'data' => [
                'activity' => $activity,
                'academic' => $academic
            ]
        ], 200);
    }
}
