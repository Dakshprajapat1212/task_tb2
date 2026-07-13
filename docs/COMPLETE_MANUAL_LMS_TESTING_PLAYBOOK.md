# Complete Manual LMS Testing Playbook

Project: `task_tutorials_backend`

Purpose: This document is a complete manual Postman testing guide, learning handbook, and interview revision document for the Laravel LMS backend.

Use this document from top to bottom. Treat it like a real QA test plan and a learning path.

---

# Section 1: Project Overview

## What This LMS Is

`task_tutorials_backend` is a Laravel backend API for a Learning Management System.

It supports three main users:

- **Student**: registers, requests enrollment, accesses learning material after approval, submits homework.
- **Faculty**: manages class content, notes, recordings, homework, and reviews submissions for own classes.
- **Admin**: manages the complete system, approves enrollments, creates users, classes, subjects, notes, recordings, and homework.

The project is backend-only. You test it using Postman by sending HTTP requests to API endpoints.

## Why It Exists

The LMS solves a common coaching/tutorial platform problem:

- Students should not automatically access all classes.
- Students should request enrollment.
- Admin should approve or reject access.
- Faculty should only manage their own classes.
- Students should access only approved class content.
- Homework should be assigned, submitted, and reviewed.

## Main Business Flow

```text
Student registers
Student logs in
Student requests enrollment
Admin approves enrollment
Student profile is created
Student accesses class material
Faculty creates homework
Student submits homework
Faculty reviews homework
```

## Student Workflow

```text
Register
Login
Request enrollment
Wait for admin approval
View approved classes
View notes
View recordings
View homework
Submit homework
View submission status
Logout
```

## Faculty Workflow

```text
Login
View own classes
Create notes for own class
Update notes for own class
Delete notes for own class
Create homework for own class
Update homework for own class
Delete homework for own class
Create recordings for own class
View student submissions
Approve or reject submissions
Logout
```

## Admin Workflow

```text
Login
Create users
Create faculty profiles
Create subjects
Create classes
Manage notes
Manage recordings
Manage homework
View enrollment requests
Approve or reject enrollments
Manage students
Manage faculties
Manage all LMS records
Logout
```

---

# Section 2: Database Understanding

## Visual Relationship Map

```text
MasRole
|
+-- Users
    |
    +-- Student
    |
    +-- Faculty

Faculty
|
+-- Subjects
|
+-- Classes

Subject
|
+-- Classes
|
+-- Notes

Class
|
+-- Enrollments
|
+-- Notes
|
+-- Recordings
|
+-- AssignHomework

AssignHomework
|
+-- SubmitHomework

Student
|
+-- SubmitHomework

User
|
+-- Enrollments
```

## `mas_roles`

Purpose: Stores user roles.

Important columns:

- `id`
- `name`
- `created_at`
- `updated_at`

Expected role records:

```text
1 student
2 faculty
3 admin
```

Business meaning:

- Role controls what APIs a user can access.
- Middleware checks `role_id`.

## `users`

Purpose: Stores login accounts.

Important columns:

- `id`
- `role_id`
- `name`
- `email`
- `password`
- `phone_no`
- `created_at`
- `updated_at`

Relationships:

- User belongs to `mas_roles`.
- User may have one `student`.
- User may have one `faculty`.
- User may have many `enrollments`.

Business meaning:

- Authentication happens through this table.
- Password is stored hashed.
- `role_id` decides broad access level.

## `students`

Purpose: Stores approved student profile details.

Important columns:

- `id`
- `user_id`
- `dob`
- `address`
- `created_at`
- `updated_at`

Business meaning:

- A registered student user does not automatically have LMS access.
- Student access starts after admin approves enrollment and a student profile exists.

## `faculties`

Purpose: Stores faculty profile details.

Important columns:

- `id`
- `user_id`
- `date_of_joining`
- `qualification`
- `bio`
- `created_at`
- `updated_at`

Business meaning:

- Faculty user account and faculty profile are separate.
- A faculty can manage only classes where `classes.faculty_id` equals their faculty profile ID.

## `subjects`

Purpose: Stores subjects taught by faculties.

Important columns:

- `id`
- `faculty_id`
- `name`
- `created_at`
- `updated_at`

Business meaning:

- A subject belongs to a faculty.
- Classes are created under subjects.

## `classes`

Purpose: Stores class/session details.

Important columns:

- `id`
- `faculty_id`
- `subject_id`
- `name`
- `class_link`
- `class_date`
- `start_time`
- `end_time`
- `created_at`
- `updated_at`

Business meaning:

- A class belongs to one faculty and one subject.
- Notes, recordings, homework, and enrollments are attached to classes.

## `enrollments`

Purpose: Stores student enrollment requests.

Important columns:

- `id`
- `user_id`
- `class_id`
- `dob`
- `address`
- `status`
- `created_at`
- `updated_at`

Allowed statuses:

- `pending`
- `approved`
- `rejected`

Business meaning:

- Student requests access to a class.
- Admin approves or rejects.
- Approved enrollment gives access to class resources.

## `notes`

Purpose: Stores class notes or file URLs.

Important columns:

- `id`
- `class_id`
- `subject_id`
- `topic`
- `file_url`
- `created_at`
- `updated_at`

Business meaning:

- Admin can manage all notes.
- Faculty can manage notes for own classes.
- Student can view notes only for approved enrolled classes.

## `recordings`

Purpose: Stores class recording links.

Important columns:

- `id`
- `class_id`
- `topic`
- `duration`
- `video_link`
- `created_at`
- `updated_at`

Business meaning:

- Admin can manage all recordings.
- Faculty can manage own class recordings.
- Student can view enrolled class recordings.

## `assign_homeworks`

Purpose: Stores homework assigned to classes.

Important columns:

- `id`
- `class_id`
- `topic`
- `description`
- `due_date`
- `status`
- `created_at`
- `updated_at`

Business meaning:

- Homework belongs to a class.
- Faculty can create homework only for own classes.
- Student can view homework only for approved enrolled classes.

## `submit_homeworks`

Purpose: Stores student homework submissions and review status.

Important columns:

- `id`
- `assign_homework_id`
- `student_id`
- `file`
- `status`
- `remarks`
- `created_at`
- `updated_at`

Allowed review statuses:

- `pending`
- `approved`
- `rejected`

Business meaning:

- A student submits homework.
- Duplicate submission for the same homework is blocked.
- Faculty/admin reviews submission.

---

# Section 3: Authentication Testing

## Postman Setup

Base URL:

```text
http://127.0.0.1:8000/api
```

If your Laravel server runs on a different port, change the base URL.

Recommended Postman environment variables:

```text
base_url = http://127.0.0.1:8000/api
admin_email = admin@gmail.com
faculty_email = ramesh@gmail.com
student_email = aman@gmail.com
password = password
```

Important:

- This project uses Laravel session authentication.
- In Postman, keep cookies enabled.
- Login first, then call protected routes using the same Postman cookie jar.

## Step 1: Register Student

Role: Public user

Purpose: Create a new student account.

API URL:

```text
{{base_url}}/register
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "name": "Manual Test Student",
  "email": "manual.student@example.com",
  "password": "password",
  "phone_no": "1234567890"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Account created successfully",
  "data": {
    "user": {
      "role_id": 1,
      "name": "Manual Test Student",
      "email": "manual.student@example.com",
      "phone_no": "1234567890"
    },
    "has_access": false
  }
}
```

Database effect:

- New row created in `users`.
- `role_id` is student role.
- Password is hashed.
- No row is created in `students` yet.

Internal flow:

```text
Request
-> AuthController@register
-> Validate request
-> Find student role
-> Hash password
-> Create user
-> Login user into session
-> Return response
```

Middleware used:

- None, public route.

Controller method used:

```text
AuthController@register
```

Models used:

- `User`
- `MasRole`

Business logic:

- All self-registered users become students.
- Registration does not mean class access.
- `has_access` remains false until enrollment approval creates a student profile.

Common mistakes:

- Reusing the same email causes validation error.
- Missing `phone_no` causes validation error.
- Password shorter than 6 characters causes validation error.

What concept you learn here:

- Public registration.
- Password hashing.
- Difference between user account and student access.

## Step 2: Login

Role: Any registered user

Purpose: Authenticate and create session.

API URL:

```text
{{base_url}}/login
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "email": "admin@gmail.com",
  "password": "password"
}
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "role_id": 3,
      "email": "admin@gmail.com"
    },
    "has_access": true
  }
}
```

Database effect:

- No table row is created.
- Laravel session data is created.

Internal flow:

```text
Request
-> AuthController@login
-> Validate email/password
-> Auth::attempt
-> Regenerate session
-> Check role
-> Return user and has_access
```

Middleware used:

- None, public route.

Controller method used:

```text
AuthController@login
```

Models used:

- `User`
- `Student`

Business logic:

- Admin and faculty always have `has_access = true`.
- Student has access only if student profile exists.

Common mistakes:

- Calling protected routes before login.
- Disabling cookies in Postman.
- Logging in as one role and testing another role without clearing cookies.

What concept you learn here:

- Session authentication.
- Role-based access flag.

## Step 3: Current User

Role: Authenticated user

Purpose: Verify current logged-in user.

API URL:

```text
{{base_url}}/me
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Authenticated user fetched successfully",
  "data": {
    "id": 1,
    "role_id": 3,
    "name": "Admin",
    "email": "admin@gmail.com"
  }
}
```

Database effect:

- No database change.

Internal flow:

```text
Request
-> auth.session.api middleware
-> AuthController@user
-> Auth::user
-> Return current user
```

Middleware used:

```text
auth.session.api
```

Controller method used:

```text
AuthController@user
```

Models used:

- `User`

Business logic:

- Only authenticated users can see current user.

Common mistakes:

- Not logging in first.
- Losing session cookie.

What concept you learn here:

- How session-protected APIs work.

## Step 4: Logout

Role: Authenticated user

Purpose: Destroy current session.

API URL:

```text
{{base_url}}/logout
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Logout successful"
}
```

Database effect:

- No LMS table changes.
- Session is invalidated.

Internal flow:

```text
Request
-> auth.session.api middleware
-> AuthController@logout
-> Auth::logout
-> Invalidate session
-> Regenerate token
-> Return response
```

Middleware used:

```text
auth.session.api
```

Controller method used:

```text
AuthController@logout
```

Models used:

- None directly.

Business logic:

- User can no longer access protected APIs after logout.

Common mistakes:

- Testing protected APIs after logout and expecting success.

What concept you learn here:

- Session invalidation.

---

# Section 4: Admin Journey

This journey assumes you are testing like a real admin setting up an LMS from scratch.

Before starting:

1. Login as admin.
2. Keep Postman cookies enabled.

Admin login body:

```json
{
  "email": "admin@gmail.com",
  "password": "password"
}
```

## Step 5: Create Faculty User

Role: Admin

Purpose: Create a login account for a faculty member.

API URL:

```text
{{base_url}}/users
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "role_id": 2,
  "name": "Manual Faculty",
  "email": "manual.faculty@example.com",
  "password": "password",
  "phone_no": "9876543210"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 6,
    "role_id": 2,
    "name": "Manual Faculty",
    "email": "manual.faculty@example.com",
    "phone_no": "9876543210"
  }
}
```

Database effect:

- New row in `users`.
- Password stored hashed.
- No row yet in `faculties`.

Internal flow:

```text
Request
-> auth.session.api
-> isAdmin
-> UserController@store
-> Validate role/user data
-> Hash password
-> Create user
```

Middleware used:

- `auth.session.api`
- `isAdmin`

Controller method used:

```text
UserController@store
```

Models used:

- `User`

Business logic:

- Admin creates accounts for faculty/admin/student.
- Faculty account needs a faculty profile before class ownership works fully.

Common mistakes:

- Using duplicate email.
- Using phone number longer than 10 characters.
- Forgetting `role_id = 2` for faculty.

What concept you learn here:

- User account creation is separate from profile creation.

## Step 6: Create Faculty Profile

Role: Admin

Purpose: Attach faculty details to a faculty user.

API URL:

```text
{{base_url}}/faculties
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "user_id": 6,
  "date_of_joining": "2026-06-01",
  "qualification": "M.Sc Computer Science",
  "bio": "Backend and programming faculty"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Faculty created successfully",
  "data": {
    "id": 3,
    "user_id": 6,
    "date_of_joining": "2026-06-01",
    "qualification": "M.Sc Computer Science",
    "bio": "Backend and programming faculty"
  }
}
```

Database effect:

- New row in `faculties`.
- Faculty profile linked to `users.id`.

Internal flow:

```text
Request
-> auth.session.api
-> isAdmin
-> FacultyController@store
-> Validate user exists
-> Validate user role is faculty
-> Prevent duplicate faculty profile
-> Create faculty profile
```

Middleware used:

- `auth.session.api`
- `isAdmin`

Controller method used:

```text
FacultyController@store
```

Models used:

- `Faculty`
- `User`

Business logic:

- Only a user with faculty role can become faculty profile.
- One faculty user should have one faculty profile.

Common mistakes:

- Passing admin/student user ID.
- Passing already-used faculty user ID.
- Creating class before faculty profile.

What concept you learn here:

- Difference between role and profile.

## Step 7: Create Subject

Role: Admin

Purpose: Create a subject under a faculty.

API URL:

```text
{{base_url}}/subjects
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "faculty_id": 3,
  "name": "Laravel Backend Development"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Subject created successfully",
  "data": {
    "id": 3,
    "faculty_id": 3,
    "name": "Laravel Backend Development"
  }
}
```

Database effect:

- New row in `subjects`.

Internal flow:

```text
Request
-> auth.session.api
-> isAdmin
-> SubjectController@store
-> Validate faculty_id
-> Create subject
```

Middleware used:

- `auth.session.api`
- `isAdmin`

Controller method used:

```text
SubjectController@store
```

Models used:

- `Subject`
- `Faculty`

Business logic:

- Subject belongs to a faculty.

Common mistakes:

- Using `user_id` instead of `faculty_id`.
- Using missing faculty ID.

What concept you learn here:

- Foreign keys and ownership setup.

## Step 8: Create Class

Role: Admin

Purpose: Create a class/session under a faculty and subject.

API URL:

```text
{{base_url}}/classes
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "faculty_id": 3,
  "subject_id": 3,
  "name": "Laravel Batch 1",
  "class_link": "https://meet.example.com/laravel-batch-1",
  "class_date": "2026-07-01",
  "start_time": "10:00:00",
  "end_time": "11:00:00"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Class created successfully",
  "data": {
    "id": 3,
    "faculty_id": 3,
    "subject_id": 3,
    "name": "Laravel Batch 1"
  }
}
```

Database effect:

- New row in `classes`.

Internal flow:

```text
Request
-> auth.session.api
-> isAdmin
-> ClassController@store
-> Validate faculty and subject
-> Create class
```

Middleware used:

- `auth.session.api`
- `isAdmin`

Controller method used:

```text
ClassController@store
```

Models used:

- `ClassModel`
- `Faculty`
- `Subject`

Business logic:

- Class is the central object for LMS access.
- Notes, recordings, homework, and enrollments attach to class.

Common mistakes:

- Using subject ID that belongs to another setup is allowed technically, but should be avoided in clean testing.
- `name` max length is limited.

What concept you learn here:

- Class is the main resource container.

## Step 9: Create Note As Admin

Role: Admin

Purpose: Add study material to a class.

API URL:

```text
{{base_url}}/notes
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 3,
  "subject_id": 3,
  "topic": "Laravel Routing Basics",
  "file_url": "https://example.com/laravel-routing.pdf"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Note uploaded successfully",
  "data": {
    "id": 5,
    "class_id": 3,
    "subject_id": 3,
    "topic": "Laravel Routing Basics",
    "file_url": "https://example.com/laravel-routing.pdf"
  }
}
```

Database effect:

- New row in `notes`.

Internal flow:

```text
Request
-> auth.session.api
-> NoteController@store
-> Validate class, subject, topic, file_url
-> Check role admin/faculty
-> Admin bypasses ownership check
-> Create note
```

Middleware used:

- `auth.session.api`
- Admin route may use `isAdmin`
- Shared route relies on controller authorization

Controller method used:

```text
NoteController@store
```

Models used:

- `Note`
- `ClassModel`
- `Subject`
- `Faculty`

Business logic:

- Either file upload or `file_url` is required.
- Admin can create notes for any class.

Common mistakes:

- Sending no file and no `file_url`.
- Sending invalid URL.
- Wrong `subject_id`.

What concept you learn here:

- Content creation and controller-level authorization.

## Step 10: Create Homework As Admin

Role: Admin

Purpose: Assign homework to a class.

API URL:

```text
{{base_url}}/assign-homeworks
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 3,
  "topic": "Build First Laravel Route",
  "description": "Create a route, controller method, and JSON response.",
  "due_date": "2026-07-10",
  "status": "active"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Assigned homework created successfully",
  "data": {
    "id": 1,
    "class_id": 3,
    "topic": "Build First Laravel Route",
    "status": "active"
  }
}
```

Database effect:

- New row in `assign_homeworks`.

Internal flow:

```text
Request
-> auth.session.api
-> AssignHomeworkController@store
-> Validate class/homework data
-> Check role admin/faculty
-> Admin bypasses class ownership check
-> Create homework
```

Middleware used:

- `auth.session.api`
- Admin route may use `isAdmin`
- Shared route relies on controller authorization

Controller method used:

```text
AssignHomeworkController@store
```

Models used:

- `AssignHomework`
- `ClassModel`
- `Faculty`

Business logic:

- Homework belongs to a class.
- Students only see it after approved enrollment in that class.

Common mistakes:

- Missing `status`.
- Wrong class ID.
- Expecting unapproved students to see homework.

What concept you learn here:

- Homework lifecycle starts from class assignment.

## Step 11: Create Recording As Admin

Role: Admin

Purpose: Add recording to a class.

API URL:

```text
{{base_url}}/admin/recordings
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 3,
  "topic": "Laravel Routing Recorded Class",
  "duration": 45,
  "video_link": "https://example.com/videos/laravel-routing"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Recording created (admin)",
  "data": {
    "id": 5,
    "class_id": 3,
    "topic": "Laravel Routing Recorded Class",
    "duration": 45,
    "video_link": "https://example.com/videos/laravel-routing"
  }
}
```

Database effect:

- New row in `recordings`.

Internal flow:

```text
Request
-> auth.session.api
-> isAdmin
-> RecordingController@adminStore
-> Validate recording data
-> Create recording
```

Middleware used:

- `auth.session.api`
- `isAdmin`

Controller method used:

```text
RecordingController@adminStore
```

Models used:

- `Recording`
- `ClassModel`

Business logic:

- Admin can create recordings for any class.

Common mistakes:

- Missing duration.
- Using non-integer duration.

What concept you learn here:

- Role-specific recording endpoints.

---

# Section 5: Student Journey

## Step 12: Register As Student

Role: Public user

Purpose: Create student account.

API URL:

```text
{{base_url}}/register
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "name": "Enrollment Test Student",
  "email": "enrollment.student@example.com",
  "password": "password",
  "phone_no": "1112223333"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Account created successfully"
}
```

Database effect:

- New user row.
- No student profile yet.
- No enrollment yet.

Internal flow:

```text
AuthController@register
-> Create student-role user
-> Login session
```

Middleware used:

- None.

Controller method used:

```text
AuthController@register
```

Models used:

- `User`
- `MasRole`

Business logic:

- Student cannot access LMS content until enrollment approval.

Common mistakes:

- Thinking registration creates `students` row.

What concept you learn here:

- User registration and approval are separate.

## Step 13: Login As Student

Role: Student

Purpose: Authenticate as student.

API URL:

```text
{{base_url}}/login
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "email": "enrollment.student@example.com",
  "password": "password"
}
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "has_access": false
  }
}
```

Database effect:

- No LMS table change.

Internal flow:

```text
AuthController@login
-> Auth::attempt
-> Student profile lookup
-> No profile found
-> has_access false
```

Middleware used:

- None.

Controller method used:

```text
AuthController@login
```

Models used:

- `User`
- `Student`

Business logic:

- New student has login access but not LMS class access.

Common mistakes:

- Expecting `/my-classes` to work before approval.

What concept you learn here:

- Authentication is not the same as authorization.

## Step 14: Request Enrollment

Role: Student

Purpose: Request access to a class.

API URL:

```text
{{base_url}}/enrollments
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 1,
  "dob": "2007-01-01",
  "address": "Manual Test Address"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Enrollment requests submitted successfully",
  "created_count": 1,
  "skipped_count": 0
}
```

Database effect:

- New row in `enrollments`.
- `status = pending`.
- `students` table still unchanged until approval.

Internal flow:

```text
auth.session.api
-> isStudent
-> EnrollmentController@store
-> Validate class_id, dob, address
-> Check duplicate pending/approved enrollment
-> Create pending enrollment
```

Middleware used:

- `auth.session.api`
- `isStudent`

Controller method used:

```text
EnrollmentController@store
```

Models used:

- `Enrollment`
- `ClassModel`

Business logic:

- Duplicate pending/approved request is blocked.
- Rejected enrollment can be requested again.

Common mistakes:

- Sending `class_ids` as a number instead of array.
- Requesting same class twice.

What concept you learn here:

- Enrollment request workflow.

## Step 15: View Enrollment Status

Role: Student

Purpose: View own enrollment requests.

API URL:

```text
{{base_url}}/my-enrollments
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "My enrollments fetched successfully",
  "data": [
    {
      "id": 4,
      "user_id": 6,
      "class_id": 1,
      "status": "pending"
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> isStudent
-> EnrollmentController@myEnrollments
-> Query enrollments where user_id = auth user
```

Middleware used:

- `auth.session.api`
- `isStudent`

Controller method used:

```text
EnrollmentController@myEnrollments
```

Models used:

- `Enrollment`
- `User`
- `ClassModel`
- `Subject`

Business logic:

- Student can see own enrollment history.

Common mistakes:

- Testing after logging in as admin/faculty.

What concept you learn here:

- User-scoped data access.

---

# Section 6: Admin Approval Journey

## Step 16: List Enrollment Requests

Role: Admin

Purpose: Admin views pending/approved/rejected enrollment requests.

API URL:

```text
{{base_url}}/enrollments
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Enrollments fetched successfully",
  "data": [
    {
      "id": 4,
      "status": "pending",
      "user": {},
      "class": {}
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> isAdmin
-> EnrollmentController@index
-> Load enrollments with user and class.subject
```

Middleware used:

- `auth.session.api`
- `isAdmin`

Controller method used:

```text
EnrollmentController@index
```

Models used:

- `Enrollment`
- `User`
- `ClassModel`
- `Subject`

Business logic:

- Admin can view all requests.

Common mistakes:

- Student calling this endpoint expects 403.

What concept you learn here:

- Admin global visibility.

## Step 17: Approve Enrollment

Role: Admin

Purpose: Approve a student's class access.

API URL:

```text
{{base_url}}/enrollments/4
```

Replace `4` with actual enrollment ID.

Method:

```text
PUT
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "status": "approved"
}
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Enrollment updated successfully",
  "data": {
    "id": 4,
    "status": "approved"
  }
}
```

Database effect:

- `enrollments.status` changes to `approved`.
- If student profile does not exist, new row is created in `students`.

Internal flow:

```text
auth.session.api
-> isAdmin
-> EnrollmentController@update
-> Find enrollment
-> Validate status
-> Update enrollment status
-> If approved, check students table
-> Create student profile if missing
```

Middleware used:

- `auth.session.api`
- `isAdmin`

Controller method used:

```text
EnrollmentController@update
```

Models used:

- `Enrollment`
- `Student`

Business logic:

- Approval grants LMS access.
- Student profile is created once.

Common mistakes:

- Using status other than `approved` or `rejected`.
- Approving wrong enrollment ID.

What concept you learn here:

- Admin approval changes authorization state.

## Step 18: Reject Enrollment

Role: Admin

Purpose: Reject enrollment request.

API URL:

```text
{{base_url}}/enrollments/5
```

Method:

```text
PUT
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "status": "rejected"
}
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Enrollment updated successfully",
  "data": {
    "status": "rejected"
  }
}
```

Database effect:

- `enrollments.status` changes to `rejected`.
- No student profile is created.

Internal flow:

```text
auth.session.api
-> isAdmin
-> EnrollmentController@update
-> Validate rejected status
-> Update enrollment only
```

Middleware used:

- `auth.session.api`
- `isAdmin`

Controller method used:

```text
EnrollmentController@update
```

Models used:

- `Enrollment`

Business logic:

- Rejected class does not become accessible.

Common mistakes:

- Expecting rejected enrollment to show in `/my-classes`.

What concept you learn here:

- Status controls access.

---

# Section 7: Approved Student Journey

Login as an approved student before this section.

Example:

```json
{
  "email": "aman@gmail.com",
  "password": "password"
}
```

## Step 19: My Classes

Role: Approved student

Purpose: View classes where enrollment is approved.

API URL:

```text
{{base_url}}/my-classes
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "My classes fetched successfully",
  "data": [
    {
      "id": 1,
      "name": "10th",
      "subject": {},
      "faculty": {}
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> isStudent
-> hasAccess
-> ClassController@myClasses
-> Query approved enrollments for auth user
-> Return related classes
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
ClassController@myClasses
```

Models used:

- `Enrollment`
- `ClassModel`
- `Subject`
- `Faculty`
- `User`

Business logic:

- Only approved enrollments are returned.

Common mistakes:

- Testing with a newly registered unapproved student.

What concept you learn here:

- Approved enrollment unlocks class dashboard.

## Step 20: Class Details

Role: Approved student

Purpose: View one approved enrolled class.

API URL:

```text
{{base_url}}/classes/1
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Class fetched successfully",
  "data": {
    "id": 1,
    "faculty": {},
    "subject": {}
  }
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> ClassController@show
-> Find class
-> If student, check approved enrollment
-> Return class
```

Middleware used:

- `auth.session.api`
- In student group: `isStudent`, `hasAccess`
- Shared route also relies on controller check

Controller method used:

```text
ClassController@show
```

Models used:

- `ClassModel`
- `Enrollment`
- `Faculty`
- `Subject`

Business logic:

- Student cannot open class details unless enrolled and approved.

Common mistakes:

- Trying class ID where student is not enrolled.

What concept you learn here:

- Resource-level authorization.

## Step 21: Notes List

Role: Approved student

Purpose: View notes for all approved classes.

API URL:

```text
{{base_url}}/notes
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Student notes fetched successfully",
  "data": [
    {
      "id": 1,
      "class_id": 1,
      "subject_id": 1,
      "topic": "Algebra Basics"
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> NoteController@index
-> Student branch
-> Get approved class IDs from enrollments
-> Return notes where class_id in approved class IDs
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
NoteController@index
```

Models used:

- `Enrollment`
- `Note`
- `ClassModel`
- `Subject`

Business logic:

- Student sees notes only for approved classes.

Common mistakes:

- Expecting all notes in the system.

What concept you learn here:

- Query filtering by authorized class IDs.

## Step 22: Single Note

Role: Approved student

Purpose: View one note if it belongs to an approved class.

API URL:

```text
{{base_url}}/notes/1
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Note fetched successfully",
  "data": {
    "id": 1,
    "topic": "Algebra Basics"
  }
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> NoteController@show
-> Find note
-> canAccessNote
-> Check approved enrollment for note.class_id
-> Return note
```

Middleware used:

- `auth.session.api`
- Student route may include `isStudent`, `hasAccess`
- Shared route relies on controller authorization

Controller method used:

```text
NoteController@show
```

Models used:

- `Note`
- `Enrollment`
- `ClassModel`
- `Subject`

Business logic:

- Note access depends on note's class.

Common mistakes:

- Testing note from another class.

What concept you learn here:

- Object-level access checks.

## Step 23: Recordings List

Role: Approved student

Purpose: View recordings for one enrolled class.

API URL:

```text
{{base_url}}/classes/1/recordings
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Recordings fetched (student class)",
  "data": [
    {
      "id": 1,
      "class_id": 1,
      "topic": "Algebra Basics"
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> isStudent
-> hasAccess
-> RecordingController@studentClassRecordings
-> ensureStudentEnrolledInClass
-> Return recordings for class_id
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
RecordingController@studentClassRecordings
```

Models used:

- `Recording`
- `Enrollment`
- `ClassModel`

Business logic:

- Student must choose a class and must be approved in that class.

Common mistakes:

- Calling admin recording endpoint as student.

What concept you learn here:

- Role-specific route design.

## Step 24: Single Recording

Role: Approved student

Purpose: View one recording if it belongs to enrolled class.

API URL:

```text
{{base_url}}/student/recordings/1
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Recording fetched (student)",
  "data": {
    "id": 1,
    "topic": "Algebra Basics",
    "video_link": "https://youtube.com/algebra-basics"
  }
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> isStudent
-> hasAccess
-> RecordingController@studentShow
-> Find recording
-> ensureStudentEnrolledInClass(recording.class_id)
-> Return recording
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
RecordingController@studentShow
```

Models used:

- `Recording`
- `Enrollment`

Business logic:

- Recording access is inherited from class enrollment.

Common mistakes:

- Trying `/admin/recordings/{id}` as student.

What concept you learn here:

- Secure single-resource lookup.

## Step 25: Homework List

Role: Approved student

Purpose: View assigned homework for approved classes.

API URL:

```text
{{base_url}}/assign-homeworks
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Student assigned homeworks fetched successfully",
  "data": [
    {
      "id": 1,
      "class_id": 1,
      "topic": "Smoke Homework"
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> AssignHomeworkController@index
-> Student branch
-> Get approved class IDs
-> Return homework for those classes
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
AssignHomeworkController@index
```

Models used:

- `Enrollment`
- `AssignHomework`
- `ClassModel`

Business logic:

- Homework is visible only through approved class access.

Common mistakes:

- Expecting homework from rejected/pending classes.

What concept you learn here:

- Class-scoped homework access.

## Step 26: Single Homework

Role: Approved student

Purpose: View one homework if accessible.

API URL:

```text
{{base_url}}/assign-homeworks/1
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Assigned homework fetched successfully",
  "data": {
    "id": 1,
    "topic": "Smoke Homework"
  }
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> AssignHomeworkController@show
-> Find homework
-> canAccessHomework
-> Student approved enrollment check
-> Return homework
```

Middleware used:

- `auth.session.api`
- Student route may include `isStudent`, `hasAccess`
- Shared route relies on controller authorization

Controller method used:

```text
AssignHomeworkController@show
```

Models used:

- `AssignHomework`
- `Enrollment`
- `ClassModel`
- `Faculty`
- `User`

Business logic:

- Student can view only own class homework.

Common mistakes:

- Trying homework ID from another class.

What concept you learn here:

- Homework ownership and access rule.

---

# Section 8: Homework Submission Journey

## Step 27: Submit Homework

Role: Approved student

Purpose: Submit file or URL for homework.

API URL:

```text
{{base_url}}/submit-homeworks
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "assign_homework_id": 1,
  "file_url": "https://example.com/my-homework-submission.pdf"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Homework submitted successfully",
  "data": {
    "id": 1,
    "assign_homework_id": 1,
    "status": "pending",
    "remarks": null
  }
}
```

Database effect:

- New row in `submit_homeworks`.
- `status = pending`.
- `student_id` is taken from authenticated user's student profile.

Internal flow:

```text
auth.session.api
-> isStudent
-> hasAccess
-> SubmitHomeworkController@store
-> Validate assign_homework_id
-> Require file or file_url
-> Find student profile
-> Find homework
-> Check approved enrollment for homework class
-> Check duplicate submission
-> Create submission
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
SubmitHomeworkController@store
```

Models used:

- `SubmitHomework`
- `Student`
- `AssignHomework`
- `Enrollment`
- `ClassModel`

Business logic:

- Only students can submit.
- Student must be approved in homework class.
- Only one submission per student per homework.

Common mistakes:

- Sending no `file_url` or file.
- Submitting homework from another class.
- Submitting the same homework twice.

What concept you learn here:

- Write operation with ownership and duplicate protection.

## Step 28: View Own Submissions

Role: Approved student

Purpose: See submitted homework and review status.

API URL:

```text
{{base_url}}/submit-homeworks
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "My homework submissions fetched successfully",
  "data": [
    {
      "id": 1,
      "status": "pending",
      "remarks": null,
      "assign_homework": {}
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> SubmitHomeworkController@index
-> Student branch
-> Find student profile
-> Query submit_homeworks where student_id = profile.id
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
SubmitHomeworkController@index
```

Models used:

- `SubmitHomework`
- `Student`
- `AssignHomework`
- `ClassModel`
- `Subject`

Business logic:

- Student sees only own submissions.

Common mistakes:

- Expecting all submissions.

What concept you learn here:

- Personal data scoping.

## Step 29: Duplicate Submission Protection

Role: Approved student

Purpose: Verify duplicate submissions are blocked.

API URL:

```text
{{base_url}}/submit-homeworks
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "assign_homework_id": 1,
  "file_url": "https://example.com/second-submission.pdf"
}
```

Expected status code:

```text
409
```

Expected response:

```json
{
  "success": false,
  "message": "Homework already submitted"
}
```

Database effect:

- No new row created.

Internal flow:

```text
SubmitHomeworkController@store
-> Existing submission query
-> Match assign_homework_id and student_id
-> Return 409
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
SubmitHomeworkController@store
```

Models used:

- `SubmitHomework`
- `Student`
- `AssignHomework`
- `Enrollment`

Business logic:

- One student can submit one answer per homework.

Common mistakes:

- Thinking duplicate request should update existing submission.

What concept you learn here:

- Duplicate prevention and conflict status.

---

# Section 9: Faculty Journey

Login as faculty:

```json
{
  "email": "ramesh@gmail.com",
  "password": "password"
}
```

## Step 30: View Own Classes

Role: Faculty

Purpose: See classes assigned to logged-in faculty.

API URL:

```text
{{base_url}}/faculty/my-classes
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Faculty classes fetched successfully",
  "data": [
    {
      "id": 1,
      "faculty_id": 1,
      "subject": {}
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> isFaculty
-> ClassController@facultyClasses
-> Find faculty profile by auth user
-> Query classes where faculty_id = faculty.id
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
ClassController@facultyClasses
```

Models used:

- `Faculty`
- `ClassModel`
- `Subject`
- `User`

Business logic:

- Faculty sees only assigned classes.

Common mistakes:

- Using user ID instead of faculty profile ID when comparing.

What concept you learn here:

- Profile-based ownership.

## Step 31: Faculty Create Note

Role: Faculty

Purpose: Create note for own class.

API URL:

```text
{{base_url}}/notes
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 1,
  "subject_id": 1,
  "topic": "Faculty Manual Note",
  "file_url": "https://example.com/faculty-note.pdf"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Note uploaded successfully"
}
```

Database effect:

- New row in `notes`.

Internal flow:

```text
auth.session.api
-> isFaculty
-> NoteController@store
-> Validate data
-> Find faculty profile
-> Check class belongs to faculty
-> Create note
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
NoteController@store
```

Models used:

- `Note`
- `Faculty`
- `ClassModel`
- `Subject`

Business logic:

- Faculty can create notes only for own classes.

Common mistakes:

- Using another faculty's class ID.

What concept you learn here:

- Write authorization by ownership.

## Step 32: Faculty Update Note

Role: Faculty

Purpose: Update own class note.

API URL:

```text
{{base_url}}/notes/1
```

Method:

```text
PUT
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 1,
  "subject_id": 1,
  "topic": "Updated Faculty Manual Note",
  "file_url": "https://example.com/updated-faculty-note.pdf"
}
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Note updated successfully"
}
```

Database effect:

- Existing `notes` row updated.

Internal flow:

```text
auth.session.api
-> isFaculty
-> NoteController@update
-> Find note
-> Validate request
-> Check faculty owns current note class
-> Check faculty owns new class_id
-> Update note
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
NoteController@update
```

Models used:

- `Note`
- `Faculty`
- `ClassModel`
- `Subject`

Business logic:

- Faculty cannot move note into another faculty's class.

Common mistakes:

- Updating note ID that belongs to another faculty.

What concept you learn here:

- Update authorization checks both old and new ownership.

## Step 33: Faculty Delete Note

Role: Faculty

Purpose: Delete own class note.

API URL:

```text
{{base_url}}/notes/1
```

Method:

```text
DELETE
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Note deleted successfully"
}
```

Database effect:

- Row deleted from `notes`.

Internal flow:

```text
auth.session.api
-> isFaculty
-> NoteController@destroy
-> Find note
-> Check role
-> Check faculty owns note class
-> Delete note
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
NoteController@destroy
```

Models used:

- `Note`
- `Faculty`
- `ClassModel`

Business logic:

- Faculty can delete own content only.

Common mistakes:

- Deleting seed note needed for later student tests.

What concept you learn here:

- Safe delete authorization.

## Step 34: Faculty Create Homework

Role: Faculty

Purpose: Assign homework to own class.

API URL:

```text
{{base_url}}/assign-homeworks
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 1,
  "topic": "Faculty Homework",
  "description": "Complete the attached exercise.",
  "due_date": "2026-07-15",
  "status": "active"
}
```

Expected status code:

```text
201
```

Expected response:

```json
{
  "success": true,
  "message": "Assigned homework created successfully"
}
```

Database effect:

- New row in `assign_homeworks`.

Internal flow:

```text
auth.session.api
-> isFaculty
-> AssignHomeworkController@store
-> Validate request
-> Find faculty profile
-> Check class belongs to faculty
-> Create homework
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
AssignHomeworkController@store
```

Models used:

- `AssignHomework`
- `Faculty`
- `ClassModel`

Business logic:

- Faculty cannot assign homework to another faculty's class.

Common mistakes:

- Missing `status`.

What concept you learn here:

- Faculty-owned homework creation.

## Step 35: Faculty Update Homework

Role: Faculty

Purpose: Update own class homework.

API URL:

```text
{{base_url}}/assign-homeworks/1
```

Method:

```text
PUT
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 1,
  "topic": "Updated Faculty Homework",
  "description": "Updated exercise instructions.",
  "due_date": "2026-07-20",
  "status": "active"
}
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Assigned homework updated successfully"
}
```

Database effect:

- Existing `assign_homeworks` row updated.

Internal flow:

```text
auth.session.api
-> isFaculty
-> AssignHomeworkController@update
-> Find homework
-> Check faculty owns current class
-> Validate request
-> Check faculty owns new class
-> Update homework
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
AssignHomeworkController@update
```

Models used:

- `AssignHomework`
- `Faculty`
- `ClassModel`

Business logic:

- Faculty cannot move homework to another faculty's class.

Common mistakes:

- Updating homework ID from another faculty.

What concept you learn here:

- Ownership checks on update.

## Step 36: Faculty Delete Homework

Role: Faculty

Purpose: Delete own class homework.

API URL:

```text
{{base_url}}/assign-homeworks/1
```

Method:

```text
DELETE
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Assigned homework deleted successfully"
}
```

Database effect:

- Row deleted from `assign_homeworks`.
- Related submissions may be deleted by cascade if database relation applies.

Internal flow:

```text
auth.session.api
-> isFaculty
-> AssignHomeworkController@destroy
-> Find homework
-> Check faculty owns homework class
-> Delete homework
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
AssignHomeworkController@destroy
```

Models used:

- `AssignHomework`
- `Faculty`
- `ClassModel`

Business logic:

- Delete only own class homework.

Common mistakes:

- Deleting homework before testing student submission.

What concept you learn here:

- Delete with ownership protection.

## Step 37: Faculty View Student Submissions

Role: Faculty

Purpose: View submissions for own classes.

API URL:

```text
{{base_url}}/submit-homeworks
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Faculty homework submissions fetched successfully",
  "data": [
    {
      "id": 1,
      "status": "pending",
      "student": {},
      "assign_homework": {}
    }
  ]
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> isFaculty
-> SubmitHomeworkController@index
-> Faculty branch
-> Find faculty profile
-> Get faculty class IDs
-> Get homework IDs for those classes
-> Return submissions for those homework IDs
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
SubmitHomeworkController@index
```

Models used:

- `SubmitHomework`
- `Faculty`
- `ClassModel`
- `AssignHomework`
- `Student`
- `User`

Business logic:

- Faculty sees submissions only for own classes.

Common mistakes:

- Expecting submissions from another faculty's class.

What concept you learn here:

- Multi-table authorization query.

## Step 38: Faculty Approve Submission

Role: Faculty

Purpose: Approve student homework submission.

API URL:

```text
{{base_url}}/submit-homeworks/1
```

Method:

```text
PUT
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "status": "approved",
  "remarks": "Good work."
}
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Homework submission reviewed successfully",
  "data": {
    "id": 1,
    "status": "approved",
    "remarks": "Good work."
  }
}
```

Database effect:

- `submit_homeworks.status` changes to `approved`.
- `remarks` updated.

Internal flow:

```text
auth.session.api
-> isFaculty
-> SubmitHomeworkController@update
-> Find submission with assignHomework
-> facultyCanReviewSubmission
-> Validate status
-> Update status and remarks
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
SubmitHomeworkController@update
```

Models used:

- `SubmitHomework`
- `AssignHomework`
- `Faculty`
- `ClassModel`
- `Student`

Business logic:

- Faculty can review only own class submissions.

Common mistakes:

- Using `accepted` instead of `approved`.

What concept you learn here:

- Review workflow and status transition.

## Step 39: Faculty Reject Submission

Role: Faculty

Purpose: Reject student homework submission.

API URL:

```text
{{base_url}}/submit-homeworks/1
```

Method:

```text
PUT
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "status": "rejected",
  "remarks": "Please resubmit with complete answer."
}
```

Expected status code:

```text
200
```

Expected response:

```json
{
  "success": true,
  "message": "Homework submission reviewed successfully",
  "data": {
    "status": "rejected",
    "remarks": "Please resubmit with complete answer."
  }
}
```

Database effect:

- `submit_homeworks.status` changes to `rejected`.
- `remarks` updated.

Internal flow:

```text
SubmitHomeworkController@update
-> Validate status in pending/approved/rejected
-> Update row
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
SubmitHomeworkController@update
```

Models used:

- `SubmitHomework`
- `AssignHomework`
- `Faculty`
- `ClassModel`

Business logic:

- Rejection is a review result.

Common mistakes:

- Assuming rejected automatically allows duplicate submit. Duplicate submit is still blocked by current logic.

What concept you learn here:

- Review status is independent from submission creation.

---

# Section 10: Security Testing

This section contains negative test cases. These are supposed to fail.

## Negative Test 1: Unauthenticated User Calls Protected Route

Role: Not logged in

Purpose: Verify protected APIs require login.

API URL:

```text
{{base_url}}/me
```

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
401
```

Expected error:

```json
{
  "success": false,
  "message": "Unauthenticated. Please login to access this resource."
}
```

Database effect:

- No change.

Internal flow:

```text
auth.session.api
-> Auth::check false
-> Return 401
```

Middleware used:

- `auth.session.api`

Controller method used:

- None, middleware blocks before controller.

Models used:

- None.

Business logic:

- Login is mandatory for protected APIs.

Common mistakes:

- Forgetting to clear Postman cookies before unauthenticated tests.

What concept you learn here:

- Authentication boundary.

## Negative Test 2: Student Accesses Another Class

Role: Student

Purpose: Verify cross-class access is blocked.

API URL:

```text
{{base_url}}/classes/999
```

Use a real class ID where the student is not approved.

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
403
```

Expected error:

```json
{
  "success": false,
  "message": "You are not enrolled in this class"
}
```

Database effect:

- No change.

Internal flow:

```text
ClassController@show
-> Find class
-> Student branch
-> Enrollment approved check fails
-> Return 403
```

Middleware used:

- `auth.session.api`
- May include `isStudent`, `hasAccess`

Controller method used:

```text
ClassController@show
```

Models used:

- `ClassModel`
- `Enrollment`

Business logic:

- Approved enrollment is required for class detail access.

Common mistakes:

- Using non-existing class ID and getting 404 instead of 403.

What concept you learn here:

- Difference between not found and forbidden.

## Negative Test 3: Student Accesses Another Class Notes

Role: Student

Purpose: Verify notes are class-protected.

API URL:

```text
{{base_url}}/classes/2/notes
```

Use class ID where student is not approved.

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
403
```

Expected error:

```json
{
  "success": false,
  "message": "Unauthorized access"
}
```

Database effect:

- No change.

Internal flow:

```text
NoteController@classNotes
-> Query approved enrollment
-> Enrollment missing
-> Return 403
```

Middleware used:

- `auth.session.api`
- `isStudent`

Controller method used:

```text
NoteController@classNotes
```

Models used:

- `Enrollment`
- `Note`

Business logic:

- Notes are not public.

Common mistakes:

- Testing with student approved in both seed classes.

What concept you learn here:

- Class-based content protection.

## Negative Test 4: Student Reviews Submission

Role: Student

Purpose: Verify students cannot review homework.

API URL:

```text
{{base_url}}/submit-homeworks/1
```

Method:

```text
PUT
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "status": "approved",
  "remarks": "Trying to approve"
}
```

Expected status code:

```text
403
```

Expected error:

```json
{
  "success": false,
  "message": "Unauthorized access"
}
```

Database effect:

- No change.

Internal flow:

```text
SubmitHomeworkController@update
-> Role check
-> Student is not faculty/admin
-> Return 403
```

Middleware used:

- `auth.session.api`

Controller method used:

```text
SubmitHomeworkController@update
```

Models used:

- None before role check, or `SubmitHomework` after role passes.

Business logic:

- Students submit homework; they do not review it.

Common mistakes:

- Using student session accidentally for faculty tests.

What concept you learn here:

- Action-level authorization.

## Negative Test 5: Faculty Accesses Another Faculty Class Recordings

Role: Faculty

Purpose: Verify faculty ownership.

API URL:

```text
{{base_url}}/faculty/classes/2/recordings
```

Use a class not owned by current faculty.

Method:

```text
GET
```

Headers:

```text
Accept: application/json
```

Body:

```text
No body
```

Expected status code:

```text
403
```

Expected error:

```json
{
  "success": false,
  "message": "You can access only your own class recordings"
}
```

Database effect:

- No change.

Internal flow:

```text
RecordingController@facultyClassRecordings
-> ensureFacultyOwnsClass
-> Faculty profile found
-> Class ownership query fails
-> Return 403
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
RecordingController@facultyClassRecordings
```

Models used:

- `Faculty`
- `ClassModel`
- `Recording`

Business logic:

- Faculty ownership is based on `classes.faculty_id`.

Common mistakes:

- Confusing `users.id` with `faculties.id`.

What concept you learn here:

- Profile ID versus user ID.

## Negative Test 6: Faculty Modifies Another Faculty Homework

Role: Faculty

Purpose: Verify homework ownership protection.

API URL:

```text
{{base_url}}/assign-homeworks/2
```

Use homework belonging to another faculty's class.

Method:

```text
PUT
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "class_id": 2,
  "topic": "Unauthorized Update",
  "description": "Should fail",
  "due_date": "2026-07-20",
  "status": "active"
}
```

Expected status code:

```text
403
```

Expected error:

```json
{
  "success": false,
  "message": "You can only update your own class homework"
}
```

Database effect:

- No change.

Internal flow:

```text
AssignHomeworkController@update
-> Find homework
-> facultyOwnsClass(homework.class_id)
-> Ownership fails
-> Return 403
```

Middleware used:

- `auth.session.api`
- `isFaculty`

Controller method used:

```text
AssignHomeworkController@update
```

Models used:

- `AssignHomework`
- `Faculty`
- `ClassModel`

Business logic:

- Faculty cannot modify another faculty's homework.

Common mistakes:

- Using homework that current faculty actually owns.

What concept you learn here:

- Ownership protection on mutation.

## Negative Test 7: Student Submits Homework From Another Class

Role: Student

Purpose: Verify cross-class homework submission is blocked.

API URL:

```text
{{base_url}}/submit-homeworks
```

Method:

```text
POST
```

Headers:

```text
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "assign_homework_id": 999,
  "file_url": "https://example.com/wrong-class.pdf"
}
```

Use an existing homework ID from a class where student is not approved.

Expected status code:

```text
403
```

Expected error:

```json
{
  "success": false,
  "message": "You can submit only for homework from approved enrolled classes"
}
```

Database effect:

- No submission row created.

Internal flow:

```text
SubmitHomeworkController@store
-> Find student
-> Find homework
-> studentCanAccessHomework
-> Approved enrollment missing
-> Return 403
```

Middleware used:

- `auth.session.api`
- `isStudent`
- `hasAccess`

Controller method used:

```text
SubmitHomeworkController@store
```

Models used:

- `SubmitHomework`
- `Student`
- `AssignHomework`
- `Enrollment`

Business logic:

- Submission requires homework visibility permission.

Common mistakes:

- Using non-existing homework ID and getting 422 validation instead.

What concept you learn here:

- Validation happens before authorization when `exists` rules run.

---

# Section 11: Role Authorization Matrix

| Feature | Student | Faculty | Admin |
| --- | --- | --- | --- |
| Register | Yes, public student account | No self faculty registration | No self admin registration |
| Login | Yes | Yes | Yes |
| Logout | Yes | Yes | Yes |
| Current User | Yes | Yes | Yes |
| Request Enrollment | Yes | No | No |
| View Own Enrollments | Yes | No | No |
| List All Enrollments | No | No | Yes |
| Approve Enrollment | No | No | Yes |
| Reject Enrollment | No | No | Yes |
| View Own Classes | Yes, approved only | Yes, assigned only | Yes, all |
| Create Class | No | No | Yes |
| Update Class | No | No | Yes |
| Delete Class | No | No | Yes |
| View Subjects | Yes | Yes | Yes |
| Create Subject | No | No | Yes |
| Update Subject | No | No | Yes |
| Delete Subject | No | No | Yes |
| View Notes | Approved classes only | Own classes only | All |
| Create Notes | No | Own classes only | All |
| Update Notes | No | Own classes only | All |
| Delete Notes | No | Own classes only | All |
| View Recordings | Approved classes only | Own classes only | All |
| Create Recordings | No | Own classes only | All |
| Update Recordings | No | Own classes only | All |
| Delete Recordings | No | Own classes only | All |
| View Homework | Approved classes only | Own classes only | All |
| Create Homework | No | Own classes only | All |
| Update Homework | No | Own classes only | All |
| Delete Homework | No | Own classes only | All |
| Submit Homework | Approved classes only | No | No |
| View Submissions | Own submissions only | Own class submissions only | All |
| Review Submissions | No | Own class submissions only | All |
| Create Users | No | No | Yes |
| Manage Students | No | No | Yes |
| Manage Faculties | No | No | Yes |
| Manage Roles | No | No | Yes |

---

# Section 12: End-To-End LMS Testing Flow

This is the complete lifecycle.

## Full Lifecycle

```text
Admin Setup
-> Create faculty user
-> Create faculty profile
-> Create subject
-> Create class
-> Create note/recording/homework

Student Onboarding
-> Student registers
-> Student logs in
-> Student requests enrollment

Admin Approval
-> Admin lists enrollments
-> Admin approves enrollment
-> Student profile is created

Student Learning
-> Student views my classes
-> Student views class details
-> Student views notes
-> Student views recordings
-> Student views homework

Homework Submission
-> Student submits homework
-> Student verifies own submission

Faculty Review
-> Faculty logs in
-> Faculty views own classes
-> Faculty views submissions
-> Faculty approves/rejects submission

Completion
-> Student sees updated submission status
```

## Lifecycle Explanation

The lifecycle starts with admin setup because the LMS needs faculty, subject, and class data before students can enroll.

Then the student registers. Registration creates only a login account. The student still cannot access class resources.

The student requests enrollment. This creates a pending row in `enrollments`.

Admin approves the enrollment. Approval changes status to `approved` and creates a student profile if needed.

Now the student can access class-specific data. Every notes, recordings, and homework query filters by approved class IDs.

Faculty creates homework for their class. The homework becomes visible to students approved in that class.

Student submits homework. A submission row is created with pending status.

Faculty reviews the submission. The status changes to approved or rejected, and remarks are stored.

This is a complete real LMS workflow.

---

# Section 13: Postman Collection Execution Order

Follow this exact order for a clean manual test.

## Preparation

1. Start Laravel server.
2. Open Postman.
3. Create environment variable:

```text
base_url = http://127.0.0.1:8000/api
```

4. Enable cookies.

## Admin Setup Order

1. Login as admin: `POST /login`
2. Create faculty user: `POST /users`
3. Create faculty profile: `POST /faculties`
4. Create subject: `POST /subjects`
5. Create class: `POST /classes`
6. Create note: `POST /notes`
7. Create homework: `POST /assign-homeworks`
8. Create recording: `POST /admin/recordings`
9. Logout admin: `POST /logout`

## Student Enrollment Order

10. Register student: `POST /register`
11. View current user: `GET /me`
12. Request enrollment: `POST /enrollments`
13. View own enrollments: `GET /my-enrollments`
14. Try my classes before approval: `GET /my-classes`
15. Expect 403.
16. Logout student: `POST /logout`

## Admin Approval Order

17. Login as admin: `POST /login`
18. List enrollments: `GET /enrollments`
19. Approve enrollment: `PUT /enrollments/{id}`
20. Logout admin: `POST /logout`

## Approved Student Access Order

21. Login as student: `POST /login`
22. View my classes: `GET /my-classes`
23. View class details: `GET /classes/{id}`
24. View notes: `GET /notes`
25. View single note: `GET /notes/{id}`
26. View class recordings: `GET /classes/{class_id}/recordings`
27. View single recording: `GET /student/recordings/{id}`
28. View homework list: `GET /assign-homeworks`
29. View single homework: `GET /assign-homeworks/{id}`
30. Submit homework: `POST /submit-homeworks`
31. View own submissions: `GET /submit-homeworks`
32. Try duplicate submission: `POST /submit-homeworks`
33. Expect 409.
34. Logout student: `POST /logout`

## Faculty Review Order

35. Login as faculty: `POST /login`
36. View own classes: `GET /faculty/my-classes`
37. View submissions: `GET /submit-homeworks`
38. Approve submission: `PUT /submit-homeworks/{id}`
39. Reject submission if testing another submission: `PUT /submit-homeworks/{id}`
40. Create note: `POST /notes`
41. Update note: `PUT /notes/{id}`
42. Delete note: `DELETE /notes/{id}`
43. Create homework: `POST /assign-homeworks`
44. Update homework: `PUT /assign-homeworks/{id}`
45. Delete homework: `DELETE /assign-homeworks/{id}`
46. Create recording: `POST /faculty/classes/{class_id}/recordings`
47. Update recording: `PUT /faculty/recordings/{id}`
48. Delete recording: `DELETE /faculty/recordings/{id}`
49. Logout faculty: `POST /logout`

## Security Testing Order

50. Clear cookies.
51. Call `GET /me`, expect 401.
52. Login as student.
53. Try admin route `GET /users`, expect 403.
54. Try reviewing submission, expect 403.
55. Try another class route, expect 403.
56. Logout.
57. Login as faculty.
58. Try another faculty's class recordings, expect 403.
59. Try another faculty's homework update, expect 403.
60. Logout.

---

# Section 14: Interview Preparation

## Complete Project Explanation

This project is a Laravel LMS backend with role-based workflows for students, faculty, and admins. Students register and request enrollment into classes. Admin approves or rejects enrollment. Once approved, students can access only their enrolled class resources, such as notes, recordings, and homework. Faculty can manage notes, recordings, homework, and submissions only for their own classes. Admin manages the complete system.

## Architecture Explanation

The project follows Laravel MVC architecture.

- Routes are defined in `routes/api.php`.
- Controllers handle request validation, business logic, and JSON responses.
- Middleware handles authentication and role authorization.
- Models represent database tables and relationships.
- Migrations define the database schema.
- Seeders provide demo data.

Authentication is session-based. After login, Laravel stores the user session and protected routes check `Auth::check()`.

Authorization is layered:

- Middleware checks broad roles.
- Controllers check resource ownership.

## Database Explanation

The database is relational.

`users` stores login accounts.

`mas_roles` defines whether a user is student, faculty, or admin.

`students` and `faculties` are profile tables linked to users.

`subjects` and `classes` define learning structure.

`enrollments` controls student access to classes.

`notes`, `recordings`, and `assign_homeworks` belong to classes.

`submit_homeworks` connects students to homework submissions.

## Workflow Explanation

The most important workflow is enrollment.

A student can register, but registration does not give class access. The student requests enrollment. Admin approves the request. Approval changes enrollment status and creates a student profile. After that, the student can access approved class data.

The second important workflow is homework.

Faculty creates homework for own class. Approved students see homework. Student submits homework. Faculty reviews submission and marks it approved or rejected.

## Top 50 Interview Questions With Best Answers

### 1. What is this project?

It is a Laravel-based LMS backend API with student, faculty, and admin roles. It supports enrollment approval, class content management, homework assignment, submission, and review workflows.

### 2. What problem does it solve?

It manages online tutorial class access. Students cannot access class material directly; they must request enrollment and wait for admin approval.

### 3. What are the roles?

Student, faculty, and admin.

### 4. What can a student do?

Register, login, request enrollment, view approved classes, notes, recordings, homework, submit homework, and view own submissions.

### 5. What can a faculty do?

Faculty can view own classes, manage notes, recordings, homework for own classes, and review submissions for own classes.

### 6. What can an admin do?

Admin can manage users, students, faculties, subjects, classes, enrollments, notes, recordings, homework, and submissions.

### 7. What authentication method is used?

Laravel session authentication.

### 8. Why use middleware?

Middleware protects routes before they reach controllers. It checks authentication and broad role access.

### 9. Which middleware is used?

`auth.session.api`, `isStudent`, `isFaculty`, `isAdmin`, and `hasAccess`.

### 10. What does `hasAccess` do?

It ensures a student has an approved profile/access before using protected student LMS routes.

### 11. How is password security handled?

Passwords are hashed using Laravel's `Hash::make()`.

### 12. What is the most important table?

`enrollments`, because it controls student access to classes.

### 13. Why is `students` separate from `users`?

`users` handles login. `students` stores approved student profile data.

### 14. Why is `faculties` separate from `users`?

`users` handles login. `faculties` stores teaching profile data and is used for class ownership.

### 15. How does student access work?

Student access checks approved enrollment for a class before showing class resources.

### 16. Can a student view all notes?

No. A student can view notes only for approved enrolled classes.

### 17. Can a faculty manage all classes?

No. Faculty can manage only classes assigned to their faculty profile.

### 18. Can admin manage all data?

Yes. Admin has global system access.

### 19. What happens when enrollment is approved?

Enrollment status becomes `approved`, and a student profile is created if missing.

### 20. What happens when enrollment is rejected?

Enrollment status becomes `rejected`, and no class access is granted.

### 21. How is duplicate enrollment prevented?

The controller checks for existing pending or approved enrollment for the same user and class.

### 22. How is duplicate homework submission prevented?

The controller checks if a submission already exists for the same student and homework.

### 23. What is the homework flow?

Faculty/admin creates homework, student submits it, faculty/admin reviews it.

### 24. What statuses are used for homework submission?

`pending`, `approved`, and `rejected`.

### 25. What statuses are used for enrollment?

`pending`, `approved`, and `rejected`.

### 26. What is Eloquent used for?

Eloquent is used for models, relationships, and database queries.

### 27. What is `ClassModel`?

It is the Laravel model for the `classes` table.

### 28. Why not name the model `Class`?

`class` is a reserved keyword in PHP, so `ClassModel` avoids conflict.

### 29. How are notes protected?

Controller checks whether the user is admin, owning faculty, or approved enrolled student.

### 30. How are recordings protected?

Separate recording endpoints exist for admin, faculty, and student, with ownership checks.

### 31. How are submissions protected?

Students see own submissions, faculty sees own class submissions, admin sees all.

### 32. What is the role of seeders?

Seeders create demo roles, users, faculties, subjects, classes, students, enrollments, notes, and recordings.

### 33. How do you test this backend?

Using Postman with session cookies enabled, following role-specific workflows.

### 34. What is a 401 error?

Unauthenticated. The user is not logged in.

### 35. What is a 403 error?

Forbidden. The user is logged in but not allowed to perform the action.

### 36. What is a 404 error?

The requested record was not found.

### 37. What is a 422 error?

Validation failed.

### 38. What is a 409 error?

Conflict, used for duplicate homework submission.

### 39. What makes this project resume-worthy?

It has real workflows, roles, authentication, authorization, relationships, validation, and end-to-end business logic.

### 40. What would you improve for production?

Add database unique indexes, token auth option, automated feature tests, OpenAPI docs, file storage policy, and deployment setup.

### 41. Why is controller-level authorization needed if middleware exists?

Middleware checks broad role, but controllers check ownership of specific records.

### 42. Give an example of ownership authorization.

Faculty can update homework only if the homework class belongs to their faculty profile.

### 43. What happens if a faculty user has no faculty profile?

Faculty-owned actions return an error because class ownership cannot be determined.

### 44. How does admin create a complete class setup?

Create faculty user, faculty profile, subject, class, notes, recordings, and homework.

### 45. Can rejected students access content?

No. Only approved enrollments grant access.

### 46. Can a pending enrollment access content?

No. Pending means waiting for admin approval.

### 47. Can a student submit homework without file?

No. They must submit either file upload or `file_url`.

### 48. Can faculty review submissions from another class?

No. Faculty can review only submissions from own classes.

### 49. What is the main security idea?

Role-based access plus ownership-based access.

### 50. Explain the project in one minute.

This is a Laravel LMS backend where students register and request class enrollment. Admin approves or rejects access. Approved students can view only their class notes, recordings, and homework. Faculty can manage content and review homework only for their own classes. Admin manages the entire system. The backend uses Laravel MVC, session authentication, middleware, Eloquent relationships, validation, and controller-level authorization.

## Deep Technical Questions

### How does the backend prevent horizontal privilege escalation?

It checks record ownership. Students are filtered by approved enrollments. Faculty actions are filtered by classes assigned to their faculty profile.

### Why should database constraints also be added?

Controller checks prevent most invalid operations, but database constraints protect integrity even if another code path or race condition occurs.

### Where can race conditions happen?

Duplicate homework submission can theoretically happen if two requests arrive simultaneously. A unique database index on `(assign_homework_id, student_id)` would fully prevent it.

### Why use eager loading?

Eager loading loads related data like class, subject, faculty, and user efficiently and reduces query repetition.

### What is the difference between authentication and authorization?

Authentication verifies who the user is. Authorization verifies what the user is allowed to do.

## Real-World Production Questions

### What would you change before deployment?

Use production database, configure `.env`, disable debug, configure HTTPS, configure CORS, add queue/mail settings, use proper file storage, add logs/monitoring, and write feature tests.

### Should this use token auth?

For mobile/API clients, Sanctum token auth could be used. Current session auth works for cookie-based clients.

### How would file uploads be improved?

Store files in cloud storage, validate size/type, generate signed URLs, and prevent public access to private files.

### How would notifications work?

Send email or in-app notification when enrollment is approved, homework is assigned, or submission is reviewed.

### How would you scale this?

Use database indexes, cache common reads, queue file/email tasks, add pagination, and deploy behind a proper web server.

---

# Section 15: Project Recall Sheet

Read this section in 10 minutes before interviews.

## One-Line Project Summary

Laravel LMS backend with student, faculty, and admin roles, enrollment approval, class content, homework submission, and role/ownership-based authorization.

## Main Roles

```text
Student -> learns and submits
Faculty -> teaches and reviews
Admin -> manages and approves
```

## Main Workflow

```text
Student registers
Student requests enrollment
Admin approves
Student gets access
Faculty assigns homework
Student submits
Faculty reviews
```

## Most Important Tables

```text
users -> login accounts
mas_roles -> student/faculty/admin
students -> approved student profile
faculties -> faculty profile
classes -> central class entity
enrollments -> access control
notes -> study material
recordings -> video links
assign_homeworks -> homework tasks
submit_homeworks -> student submissions
```

## Most Important Security Rule

```text
Role decides broad access.
Ownership decides record access.
```

## Student Access Rule

```text
Student can access a class only if:
enrollments.user_id = current user
enrollments.class_id = requested class
enrollments.status = approved
```

## Faculty Access Rule

```text
Faculty can manage a class only if:
faculties.user_id = current user
classes.faculty_id = faculty.id
```

## Admin Access Rule

```text
Admin can manage everything.
```

## Important Controllers

```text
AuthController -> register, login, logout, me
EnrollmentController -> enrollment request and approval
ClassController -> class access
NoteController -> notes
RecordingController -> recordings
AssignHomeworkController -> homework assignment
SubmitHomeworkController -> homework submission and review
UserController -> admin user CRUD
FacultyController -> faculty profile CRUD
StudentController -> student profile CRUD
SubjectController -> subject CRUD
MasRoleController -> role CRUD
```

## Important Middleware

```text
auth.session.api -> checks login
isStudent -> role_id = 1
isFaculty -> role_id = 2
isAdmin -> role_id = 3
hasAccess -> student has approved access/profile
```

## Important Status Codes

```text
200 -> success
201 -> created
401 -> not logged in
403 -> logged in but forbidden
404 -> record not found
409 -> duplicate conflict
422 -> validation error
```

## Strong Interview Answer

I built a Laravel LMS backend with session authentication and three roles: student, faculty, and admin. Students register and request enrollment into classes. Admin approves enrollment, which gives students access to class content. Faculty can manage notes, recordings, and homework only for their own classes. Students can submit homework, and faculty can review submissions only from their own classes. The system uses Laravel MVC, middleware, Eloquent relationships, validation, and controller-level authorization to protect cross-role and cross-class access.

## Final Confidence Checklist

- I can explain the enrollment workflow.
- I can explain why registration does not equal access.
- I can explain student, faculty, and admin permissions.
- I can explain the database relationships.
- I can explain middleware versus controller authorization.
- I can manually test the project in Postman.
- I can explain homework submission and review.
- I can answer security questions about cross-class access.
- I can discuss production improvements.

End of playbook.
