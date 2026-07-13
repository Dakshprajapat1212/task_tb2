# Task Tutorials Backend - LMS Project Review and Documentation

Date: 2026-06-08

## 1. Project Overview

`task_tutorials_backend` is a Laravel API backend for a role-based Learning Management System.

The system supports three roles:

- Student
- Faculty
- Admin

Core LMS modules:

- Authentication using Laravel session auth
- Student enrollment request and approval workflow
- Class and subject management
- Faculty profile management
- Student profile management
- Notes management
- Class recordings management
- Homework assignment management
- Homework submission and review workflow

Primary project flow:

1. Student registers and logs in.
2. Student requests enrollment for one or more classes.
3. Admin approves or rejects the enrollment request.
4. Approval creates a student profile if one does not already exist.
5. Approved students can access enrolled classes, notes, recordings, and homework.
6. Students submit homework.
7. Faculty or admin reviews submissions.

## 2. Phase 1 Audit Report

### A. Currently Working

- Routes are registered successfully through `routes/api.php`.
- Session-based login, logout, registration, and current-user API are available.
- Role middleware exists for student, faculty, and admin access.
- Student enrollment request flow exists and prevents duplicate pending/approved requests.
- Admin enrollment approval creates the student profile needed for LMS access.
- Student class access is restricted to approved enrollments.
- Notes can be listed, created, shown, updated, and deleted.
- Recording workflows are split by role:
  - Admin full CRUD
  - Faculty own-class CRUD
  - Student enrolled-class read access
- Homework assignment workflows exist for admin/faculty creation and student read access.
- Homework submission workflow exists:
  - Students submit once per homework
  - Students see their own submissions
  - Faculty sees submissions for their own classes
  - Admin sees all submissions
  - Faculty/admin can review submissions
- Passwords are hashed in registration, user seeding, and admin user CRUD.
- Models and migrations line up for core LMS tables.
- Fresh SQLite migration and seeding completed successfully.

### B. Broken Items Found

No route/controller mismatch remains in the audited LMS endpoints.

Issues found and fixed during this pass:

- Faculty profile CRUD did not validate required fields or enforce that the selected user has faculty role.
- Student profile creation could attach a student profile to a non-student user.
- Subject CRUD accepted missing or invalid fields until database failure.
- Role CRUD allowed missing or duplicate role names.
- Some faculty-owned LMS methods could crash if a faculty-role user existed without a faculty profile.
- Faculty class detail access was broader than the "own classes" workflow.

### C. Still Incomplete

These are intentionally not implemented because they are outside the current stable backend scope:

- Payment workflow
- Attendance tracking
- Live class scheduling integrations
- File download authorization through signed URLs
- Email notifications
- Password reset flow UI/client integration
- Advanced analytics dashboard
- Real-time chat or announcements
- Automated grading

### D. Risky Areas

- `routes/api.php` keeps shared LMS routes available to all authenticated roles for backward compatibility. This is acceptable because the controllers enforce role and ownership checks, but future endpoints must follow the same pattern.
- Database schema relies mostly on controller checks for uniqueness. For production, add unique database indexes for `students.user_id`, `faculties.user_id`, and `(assign_homework_id, student_id)` in `submit_homeworks`.
- `TaskController` is a generic scaffold resource and is not part of the LMS domain.
- The test suite currently contains only placeholder PHPUnit tests. The HTTP smoke suite used in this audit was run externally.
- Vendor packages emit PHP deprecation warnings on the current PHP version, but tests still pass.

## 3. Fixes Completed In This Pass

### Validation Hardening

Updated admin CRUD validation:

- `FacultyController`
  - Requires valid faculty-role user.
  - Prevents duplicate faculty profiles.
  - Validates date of joining, qualification, and bio.
- `StudentController`
  - Requires valid student-role user.
  - Prevents duplicate student profiles.
- `SubjectController`
  - Requires valid faculty and subject name.
- `MasRoleController`
  - Requires unique role name.

### Authorization and Stability Hardening

- `ClassController@show`
  - Admin can view any class.
  - Student can view only approved enrolled classes.
  - Faculty can view only own classes.
- `NoteController`
  - Returns clean JSON if faculty profile is missing.
- `AssignHomeworkController`
  - Returns clean JSON if faculty profile is missing.

## 4. Phase 2 Test Report

### Automated Tests

Command:

```bash
php artisan test
```

Result:

- 2 tests passed.
- Existing tests are placeholders only.
- PHP/vendor deprecation warnings appeared, but no test failures occurred.

### Migration and Seeder Test

Command:

```bash
env DB_CONNECTION=sqlite DB_DATABASE=/private/tmp/task_tutorials_backend_test.sqlite php artisan migrate:fresh --seed
```

Result:

- All migrations completed.
- All seeders completed.
- Foreign key order and seed assumptions are valid.

### HTTP Smoke Test

The API was tested through a local Laravel server using real session cookies.

Result:

- 41 checks passed.
- 0 failures.

Covered checks:

- Unauthenticated `/me` returns 401.
- Admin, faculty, and student login works.
- Current user endpoint works.
- Student registration works.
- New student is blocked from approved-only class access before enrollment approval.
- Enrollment request works.
- Duplicate pending enrollment is blocked.
- Admin approval works.
- Approved student can access own classes.
- Student can access notes for enrolled classes.
- Cross-class notes access is blocked.
- Student can access recordings for enrolled classes.
- Cross-class recording access is blocked.
- Student can list assigned homework.
- Faculty can create homework for own class.
- Other faculty cannot update another faculty's homework.
- Student can submit homework.
- Duplicate homework submission is blocked.
- Cross-class homework submission is blocked.
- Faculty can view submissions for own classes.
- Other faculty cannot review submissions outside own classes.
- Owning faculty can review submissions.
- Student cannot create notes.
- Faculty cannot create notes in another faculty's class.
- Faculty can create notes for own class.
- Admin can list users.
- Student cannot access admin users endpoint.
- Admin can create subject.
- Admin can create class.
- Faculty can create recordings for own class.
- Other faculty cannot access own-class recording endpoint.
- Admin can list recordings.
- Logout works.
- Logged-out session is blocked.
- Invalid IDs return 404.
- Missing required data returns 422.

## 5. Architecture Explanation

The project follows a traditional Laravel MVC API structure:

- `routes/api.php`
  - Defines public auth routes and protected role-based API groups.
- `app/Http/Controllers`
  - Contains the request handling logic for each LMS module.
- `app/Http/Middleware`
  - Enforces authentication and role access.
- `app/Models`
  - Defines Eloquent models and relationships.
- `database/migrations`
  - Defines the database schema.
- `database/seeders`
  - Seeds demo roles, users, faculties, subjects, classes, students, enrollments, notes, and recordings.

Important design choice:

The backend uses session authentication for API routes. Login creates a Laravel session, and protected routes read the session cookie through `auth.session.api`.

## 6. Database Explanation

### Main Tables

- `mas_roles`
  - Stores role names: student, faculty, admin.
- `users`
  - Stores login accounts with `role_id`, name, email, hashed password, and phone.
- `faculties`
  - Stores faculty profile information linked to a user.
- `students`
  - Stores student profile information linked to a user.
- `subjects`
  - Stores subjects linked to faculty.
- `classes`
  - Stores class details linked to faculty and subject.
- `enrollments`
  - Stores student enrollment requests and approval status.
- `notes`
  - Stores note topic and file URL linked to class and subject.
- `recordings`
  - Stores class recording topic, duration, and video link.
- `assign_homeworks`
  - Stores homework assigned to a class.
- `submit_homeworks`
  - Stores student homework submissions and review status.

### Key Relationships

- `User belongsTo MasRole`
- `User hasOne Student`
- `User hasOne Faculty`
- `Faculty hasMany Subject`
- `Faculty hasMany ClassModel`
- `Subject hasMany ClassModel`
- `ClassModel belongsTo Faculty`
- `ClassModel belongsTo Subject`
- `Enrollment belongsTo User`
- `Enrollment belongsTo ClassModel`
- `Note belongsTo ClassModel`
- `Note belongsTo Subject`
- `Recording belongsTo ClassModel`
- `AssignHomework belongsTo ClassModel`
- `AssignHomework hasMany SubmitHomework`
- `SubmitHomework belongsTo AssignHomework`
- `SubmitHomework belongsTo Student`

## 7. API Documentation

Base prefix: `/api`

### Auth

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| POST | `/register` | Public | Register a student account |
| POST | `/login` | Public | Login and create session |
| POST | `/logout` | Authenticated | Logout and destroy session |
| GET | `/me` | Authenticated | Fetch current user |

### Enrollment

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| POST | `/enrollments` | Student | Request enrollment |
| GET | `/my-enrollments` | Student | View own enrollments |
| GET | `/enrollments/{id}` | Student/Admin | View enrollment |
| GET | `/enrollments` | Admin | List all enrollments |
| PUT | `/enrollments/{id}` | Admin | Approve/reject enrollment |
| DELETE | `/enrollments/{id}` | Admin | Delete enrollment |

Enrollment request body:

```json
{
  "class_id": 1,
  "dob": "2007-01-01",
  "address": "Student address"
}
```

Multi-class enrollment is also supported:

```json
{
  "class_ids": [1, 2],
  "dob": "2007-01-01",
  "address": "Student address"
}
```

### Classes

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| GET | `/classes` | Admin | List classes |
| POST | `/classes` | Admin | Create class |
| GET | `/classes/{id}` | Admin/Faculty/Student | View allowed class |
| PUT | `/classes/{id}` | Admin | Update class |
| DELETE | `/classes/{id}` | Admin | Delete class |
| GET | `/my-classes` | Student | View approved enrolled classes |
| GET | `/faculty/my-classes` | Faculty | View own classes |

### Subjects

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| GET | `/subjects` | Authenticated | List subjects |
| GET | `/subjects/{id}` | Authenticated | View subject |
| POST | `/subjects` | Admin | Create subject |
| PUT | `/subjects/{id}` | Admin | Update subject |
| DELETE | `/subjects/{id}` | Admin | Delete subject |

### Notes

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| GET | `/notes` | Admin/Faculty/Student | List accessible notes |
| GET | `/notes/{id}` | Admin/Faculty/Student | View accessible note |
| POST | `/notes` | Admin/Faculty | Create note |
| PUT | `/notes/{id}` | Admin/Faculty | Update note |
| DELETE | `/notes/{id}` | Admin/Faculty | Delete note |
| GET | `/classes/{id}/notes` | Student | View notes for enrolled class |

Create note body:

```json
{
  "class_id": 1,
  "subject_id": 1,
  "topic": "Algebra Basics",
  "file_url": "https://example.com/algebra.pdf"
}
```

### Recordings

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| GET | `/admin/recordings` | Admin | List all recordings |
| POST | `/admin/recordings` | Admin | Create recording |
| GET | `/admin/recordings/{id}` | Admin | View recording |
| PUT | `/admin/recordings/{id}` | Admin | Update recording |
| DELETE | `/admin/recordings/{id}` | Admin | Delete recording |
| GET | `/faculty/classes/{class_id}/recordings` | Faculty | List own class recordings |
| POST | `/faculty/classes/{class_id}/recordings` | Faculty | Create own class recording |
| GET | `/faculty/recordings/{id}` | Faculty | View own class recording |
| PUT | `/faculty/recordings/{id}` | Faculty | Update own class recording |
| DELETE | `/faculty/recordings/{id}` | Faculty | Delete own class recording |
| GET | `/classes/{class_id}/recordings` | Student | List enrolled class recordings |
| GET | `/student/recordings/{id}` | Student | View enrolled class recording |

### Homework Assignment

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| GET | `/assign-homeworks` | Admin/Faculty/Student | List accessible homework |
| GET | `/assign-homeworks/{id}` | Admin/Faculty/Student | View accessible homework |
| POST | `/assign-homeworks` | Admin/Faculty | Create homework |
| PUT | `/assign-homeworks/{id}` | Admin/Faculty | Update homework |
| DELETE | `/assign-homeworks/{id}` | Admin/Faculty | Delete homework |

Create homework body:

```json
{
  "class_id": 1,
  "topic": "Chapter 1 Practice",
  "description": "Solve all examples",
  "due_date": "2026-06-30",
  "status": "active"
}
```

### Homework Submission

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| GET | `/submit-homeworks` | Admin/Faculty/Student | List accessible submissions |
| POST | `/submit-homeworks` | Student | Submit homework |
| PUT | `/submit-homeworks/{id}` | Admin/Faculty | Review submission |

Submit body:

```json
{
  "assign_homework_id": 1,
  "file_url": "https://example.com/submission.pdf"
}
```

Review body:

```json
{
  "status": "approved",
  "remarks": "Good work"
}
```

### Admin User/Profile CRUD

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| GET | `/users` | Admin | List users |
| POST | `/users` | Admin | Create user |
| GET | `/users/{id}` | Admin | View user |
| PUT | `/users/{id}` | Admin | Update user |
| DELETE | `/users/{id}` | Admin | Delete user |
| GET | `/students` | Admin | List students |
| POST | `/students` | Admin | Create student profile |
| PUT | `/students/{id}` | Admin | Update student profile |
| DELETE | `/students/{id}` | Admin | Delete student profile |
| GET | `/faculties` | Admin | List faculties |
| POST | `/faculties` | Admin | Create faculty profile |
| GET | `/faculties/{id}` | Admin | View faculty |
| PUT | `/faculties/{id}` | Admin | Update faculty |
| DELETE | `/faculties/{id}` | Admin | Delete faculty |
| GET | `/mas-roles` | Admin | List roles |
| POST | `/mas-roles` | Admin | Create role |
| GET | `/mas-roles/{id}` | Admin | View role |
| PUT | `/mas-roles/{id}` | Admin | Update role |
| DELETE | `/mas-roles/{id}` | Admin | Delete role |

## 8. Student Workflow

1. Register using `/api/register`.
2. Login using `/api/login`.
3. Request enrollment using `/api/enrollments`.
4. Wait for admin approval.
5. After approval, access:
   - `/api/my-classes`
   - `/api/notes`
   - `/api/classes/{class_id}/notes`
   - `/api/classes/{class_id}/recordings`
   - `/api/assign-homeworks`
6. Submit homework using `/api/submit-homeworks`.
7. Check submission status using `/api/submit-homeworks`.

Important student security rule:

Students can only access resources from approved enrolled classes.

## 9. Faculty Workflow

1. Admin creates a faculty user.
2. Admin creates a faculty profile.
3. Faculty logs in.
4. Faculty views own classes using `/api/faculty/my-classes`.
5. Faculty creates and manages notes for own classes.
6. Faculty creates and manages recordings for own classes.
7. Faculty creates and manages homework for own classes.
8. Faculty views and reviews submissions for own classes.

Important faculty security rule:

Faculty can only manage classes assigned to their faculty profile.

## 10. Admin Workflow

Admin can:

- Manage users.
- Manage student profiles.
- Manage faculty profiles.
- Manage roles.
- Manage subjects.
- Manage classes.
- Approve or reject enrollments.
- Manage all notes.
- Manage all recordings.
- Manage all homework.
- Review all submissions.

Admin is the system-level authority and can see all LMS data.

## 11. Security Model

### Authentication

The API uses Laravel session authentication:

- Login validates email and password.
- Laravel stores the authenticated user ID in the session.
- Protected routes require `auth.session.api`.
- Logout invalidates the session.

### Authorization

Authorization is layered:

- Middleware enforces broad role access.
- Controllers enforce ownership and cross-resource access.

Examples:

- Student class access checks approved enrollment.
- Student note/recording/homework access checks approved enrollment.
- Faculty note/recording/homework access checks class ownership.
- Faculty submission review checks the submitted homework belongs to one of their classes.
- Admin can access all records.

### Validation

The project validates:

- Login and registration fields.
- Enrollment request fields.
- Class fields.
- Notes file or URL requirements.
- Recording fields.
- Homework fields.
- Submission file or URL requirements.
- Admin CRUD role/profile/subject data.

### Password Security

Passwords are hashed using Laravel `Hash::make()` in:

- Registration
- User seeder
- Admin user create/update

## 12. Major Project Readiness Review

### 1. Is this now a complete internship-level LMS backend?

Yes. It has enough real backend scope for an internship-level Laravel LMS: auth, roles, enrollment approval, content modules, homework, submissions, review workflow, and access control.

### 2. Is this suitable as a major project?

Yes. It is suitable as a college major project backend because it covers multiple real workflows, database relationships, role-based access, and CRUD-heavy admin operations.

### 3. Is this suitable for placements?

Yes, if presented honestly as a backend API project. It is strongest when explained through the enrollment and homework workflows, not just as CRUD.

### 4. Is this suitable for junior backend roles?

Yes. It demonstrates Laravel MVC, routing, middleware, validation, Eloquent relationships, migrations, seeders, authentication, authorization, and API testing.

### 5. Implemented Features

- Student registration and login
- Session authentication
- Role middleware
- Admin user management
- Student and faculty profile management
- Subject management
- Class management
- Enrollment request, duplicate prevention, approval, rejection, and deletion
- Student access after approval
- Notes module
- Recordings module
- Homework assignment module
- Homework submission module
- Faculty/admin submission review
- Cross-role and cross-class authorization checks
- Seed data for demo users and LMS content

### 6. Intentionally Out Of Scope

- Frontend UI
- Payment integration
- Live video integration
- Attendance
- Notifications
- File CDN/storage policy
- Analytics
- Chat
- Automated grading
- Production deployment automation

### 7. Score

Current score: 8.2/10

Why:

- Strong domain coverage and role-based workflow.
- Good Laravel structure.
- Main security boundaries are in place.
- Fresh migration/seed and HTTP smoke testing pass.

What keeps it below 9:

- Placeholder PHPUnit tests should be replaced with real feature tests.
- Database uniqueness constraints should be added for production-grade integrity.
- API docs could be converted to an OpenAPI/Postman collection.
- Some older scaffold routes such as `tasks` are not part of the polished LMS domain.

## 13. Interview Explanation

Short answer:

> I built a Laravel-based LMS backend with student, faculty, and admin roles. The main workflow is that students register, request enrollment into classes, admins approve them, and after approval students can access notes, recordings, homework, and submit homework. Faculty can manage only their own class content and review only submissions from their classes. Admin can manage the complete system.

Architecture answer:

> The project follows Laravel MVC. Routes are defined in `routes/api.php`, controllers handle API logic, middleware enforces authentication and role access, models define Eloquent relationships, and migrations define the schema. I used session authentication for API access and layered authorization through both middleware and controller ownership checks.

Security answer:

> The important part is that access is not only role-based, it is also ownership-based. A student must have an approved enrollment for the class before accessing notes, recordings, homework, or submissions. A faculty member can only manage classes assigned to their faculty profile. Admin has full system access.

Database answer:

> The database is relational. Users belong to roles. Faculty and student profiles extend users. Classes belong to faculty and subjects. Enrollments connect student users to classes with a status. Notes, recordings, and homework belong to classes. Homework submissions connect students to assigned homework and include review status and remarks.

Testing answer:

> I verified the project by running syntax checks, Laravel tests, fresh migrations and seeders on SQLite, and a 41-case HTTP smoke test covering auth, enrollment, notes, recordings, homework, submissions, admin CRUD, and forbidden cross-access cases.

Resume bullet:

> Built a Laravel LMS REST API with session authentication, role-based access control, enrollment approval workflow, class content management, homework submission/review system, Eloquent relationships, migrations, seeders, validation, and end-to-end API smoke testing.

