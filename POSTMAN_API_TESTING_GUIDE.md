# Complete Postman API Testing Guide - Task Tutorials Backend

## 📋 Table of Contents
1. [Setup Instructions](#setup-instructions)
2. [Base URL](#base-url)
3. [Test Credentials](#test-credentials)
4. [Complete API Workflow](#complete-api-workflow)
5. [All API Endpoints with Request/Response Examples](#all-api-endpoints)

---

## 🚀 Setup Instructions

1. **Start Laravel Development Server:**
   ```bash
   cd task_tutorials_backend
   php artisan serve
   ```
   Server will start at: `http://localhost:8000`

2. **Database Setup (if not done):**
   ```bash
   php artisan migrate --seed
   ```
   This creates all tables and populates with sample data.

3. **Import into Postman:**
   - Create a new Collection named "Task Tutorials API"
   - Set Collection Variable: `base_url` = `http://localhost:8000/api`
   - For authenticated requests, you'll need to capture the session cookie after login

---

## 🌐 Base URL

```
http://localhost:8000/api
```

All endpoints below should be prefixed with this base URL.

---

## 🔑 Test Credentials

After running `php artisan migrate --seed`, these accounts are created:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@tasktutorials.com` | `Password@123` |
| Faculty | `faculty1@tasktutorials.com` | `Password@123` |
| Faculty | `faculty2@tasktutorials.com` | `Password@123` |
| Faculty | `faculty3@tasktutorials.com` | `Password@123` |
| Student | `student1@tasktutorials.com` | `Password@123` |
| Student | `student2@tasktutorials.com` | `Password@123` |
| ... up to student30 | `student30@tasktutorials.com` | `Password@123` |

---

## 🔄 Complete API Workflow

### Step 1: Login (All Roles)
**Endpoint:** `POST {{base_url}}/login`

**Request Body (JSON):**
```json
{
  "email": "admin@tasktutorials.com",
  "password": "Password@123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@tasktutorials.com",
    "role_id": 3,
    "mas_role": {
      "id": 3,
      "name": "admin"
    }
  },
  "message": "Login successful"
}
```

**Important:** After successful login, Postman will receive a session cookie. For subsequent requests, ensure "Send cookies" is enabled in Postman settings.

---

### Step 2: Get Current User Info
**Endpoint:** `GET {{base_url}}/me`

**Headers:** (Session cookie automatically included if logged in)

**Response:**
```json
{
  "id": 1,
  "name": "Admin",
  "email": "admin@tasktutorials.com",
  "role_id": 3,
  "mas_role": {
    "id": 3,
    "name": "admin"
  }
}
```

---

## 📚 All API Endpoints with Examples

### 🔓 PUBLIC AUTH ROUTES

#### 1. Register New User
**Endpoint:** `POST {{base_url}}/register`

**Request Body:**
```json
{
  "name": "Test User",
  "email": "testuser@example.com",
  "password": "Password@123",
  "password_confirmation": "Password@123",
  "role_id": 1,
  "phone_no": "9876543210"
}
```

**Response:**
```json
{
  "user": {
    "id": 31,
    "name": "Test User",
    "email": "testuser@example.com",
    "role_id": 1,
    "phone_no": "9876543210"
  },
  "message": "User registered successfully"
}
```

#### 2. Login
**Endpoint:** `POST {{base_url}}/login`

**Request Body:**
```json
{
  "email": "admin@tasktutorials.com",
  "password": "Password@123"
}
```

#### 3. Google OAuth Redirect
**Endpoint:** `GET {{base_url}}/auth/google/redirect`

#### 4. Google OAuth Callback
**Endpoint:** `GET {{base_url}}/auth/google/callback`

---

### 🔒 PROTECTED ROUTES (Require Authentication)

#### 5. Logout
**Endpoint:** `POST {{base_url}}/logout`

#### 6. Get Current User
**Endpoint:** `GET {{base_url}}/me`

---

### 👨‍🎓 STUDENT ROUTES (Requires `isStudent` middleware)

#### 7. Get My Enrollments
**Endpoint:** `GET {{base_url}}/my-enrollments`

**Response:**
```json
[
  {
    "id": 1,
    "user_id": 2,
    "class_id": 1,
    "status": "approved",
    "class": {
      "id": 1,
      "name": "Grade 10"
    }
  }
]
```

#### 8. Request Enrollment
**Endpoint:** `POST {{base_url}}/enrollments`

**Request Body:**
```json
{
  "class_id": 1
}
```

**Response:**
```json
{
  "enrollment": {
    "id": 2,
    "user_id": 2,
    "class_id": 1,
    "status": "pending"
  },
  "message": "Enrollment request submitted successfully"
}
```

#### 9. Get Class Notes
**Endpoint:** `GET {{base_url}}/classes/{id}/notes`

---

### 📖 STUDENT ROUTES (After Access Granted - `hasAccess` middleware)

#### 10. Get My Classes
**Endpoint:** `GET {{base_url}}/my-classes`

**Response:**
```json
[
  {
    "id": 1,
    "name": "Grade 10",
    "subjects": [
      {
        "id": 1,
        "name": "Simple Algebra",
        "pivot": {
          "faculty_id": 2
        }
      }
    ]
  }
]
```

#### 11. Get Class Details
**Endpoint:** `GET {{base_url}}/classes/{id}`

#### 12. Get All Notes
**Endpoint:** `GET {{base_url}}/notes`

#### 13. Get Note by ID
**Endpoint:** `GET {{base_url}}/notes/{id}`

#### 14. Get Class Recordings
**Endpoint:** `GET {{base_url}}/classes/{class_id}/recordings`

**Response:**
```json
[
  {
    "id": 1,
    "class_id": 1,
    "topic": "Lecture 1: Introduction to Power Rules",
    "duration": 60,
    "video_link": "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
  }
]
```

#### 15. Get Recording Details
**Endpoint:** `GET {{base_url}}/student/recordings/{id}`

#### 16. Get All Assignments
**Endpoint:** `GET {{base_url}}/assign-homeworks`

#### 17. Get Assignment by ID
**Endpoint:** `GET {{base_url}}/assign-homeworks/{id}`

#### 18. Get Homework Submissions
**Endpoint:** `GET {{base_url}}/submit-homeworks`

#### 19. Submit Homework
**Endpoint:** `POST {{base_url}}/submit-homeworks`

**Request Body:**
```json
{
  "assignment_id": 1,
  "submission_text": "My homework answer here",
  "attachment_url": "https://example.com/file.pdf"
}
```

#### 20. Get All Subjects
**Endpoint:** `GET {{base_url}}/subjects`

#### 21. Get Subject by ID
**Endpoint:** `GET {{base_url}}/subjects/{id}`

---

### 📚 LIBRARY ROUTES (Student)

#### 22. Get Library Classes
**Endpoint:** `GET {{base_url}}/library/classes`

#### 23. Get Class Subjects
**Endpoint:** `GET {{base_url}}/library/classes/{class}/subjects`

#### 24. Get Class-Subject Chapters
**Endpoint:** `GET {{base_url}}/library/class-subjects/{id}/chapters`

#### 25. Get Chapter Notes
**Endpoint:** `GET {{base_url}}/library/chapters/{chapter}/notes`

#### 26. Get Note Details
**Endpoint:** `GET {{base_url}}/library/notes/{note}`

#### 27. Complete Note
**Endpoint:** `POST {{base_url}}/library/notes/{note}/complete`

#### 28. Get Chapter Progress
**Endpoint:** `GET {{base_url}}/library/chapters/{chapter}/progress`

#### 29. Get Chapter Quiz
**Endpoint:** `GET {{base_url}}/library/chapters/{chapter}/quiz`

#### 30. Submit Quiz
**Endpoint:** `POST {{base_url}}/library/quizzes/{quiz}/submit`

**Request Body:**
```json
{
  "answers": [
    {
      "question_id": 1,
      "answer": "My answer"
    },
    {
      "question_id": 2,
      "answer": "Another answer"
    }
  ]
}
```

#### 31. Get Quiz Result
**Endpoint:** `GET {{base_url}}/library/quizzes/{quiz}/result`

#### 32. Get Chapter Flashcards
**Endpoint:** `GET {{base_url}}/library/chapters/{chapter}/flashcards`

#### 33. Get Module Quiz
**Endpoint:** `GET {{base_url}}/library/modules/{note}/quiz`

#### 34. Get Module Flashcards
**Endpoint:** `GET {{base_url}}/library/modules/{note}/flashcards`

---

### 🎯 V2 LIBRARY ROUTES

#### 35. V2 Get Chapters
**Endpoint:** `GET {{base_url}}/v2/library/class-subjects/{id}/chapters`

#### 36. V2 Get Topic Notes
**Endpoint:** `GET {{base_url}}/v2/library/chapters/{chapter}/topic-notes`

#### 37. V2 Get Chapter Question Bank
**Endpoint:** `GET {{base_url}}/v2/library/chapters/{chapter}/question-bank`

#### 38. V2 Get Topic Note Question Bank
**Endpoint:** `GET {{base_url}}/v2/library/topic-notes/{topic_note}/question-bank`

---

### 👨‍🏫 FACULTY ROUTES

#### 39. Get Faculty's Classes
**Endpoint:** `GET {{base_url}}/faculty/my-classes`

#### 40. Get Faculty Class Recordings
**Endpoint:** `GET {{base_url}}/faculty/classes/{class_id}/recordings`

#### 41. Create Recording
**Endpoint:** `POST {{base_url}}/faculty/classes/{class_id}/recordings`

**Request Body:**
```json
{
  "topic": "New Lecture Topic",
  "duration": 45,
  "video_link": "https://www.youtube.com/watch?v=example"
}
```

#### 42. Get Recording Details
**Endpoint:** `GET {{base_url}}/faculty/recordings/{id}`

#### 43. Update Recording
**Endpoint:** `PUT {{base_url}}/faculty/recordings/{id}`

**Request Body:**
```json
{
  "topic": "Updated Lecture Topic",
  "duration": 60
}
```

#### 44. Delete Recording
**Endpoint:** `DELETE {{base_url}}/faculty/recordings/{id}`

#### 45. Get All Notes (Faculty)
**Endpoint:** `GET {{base_url}}/notes`

#### 46. Create Note
**Endpoint:** `POST {{base_url}}/notes`

**Request Body:**
```json
{
  "title": "New Note Title",
  "content": "Note content here",
  "class_id": 1,
  "subject_id": 1
}
```

#### 47. Update Note
**Endpoint:** `PUT {{base_url}}/notes/{id}`

**Request Body:**
```json
{
  "title": "Updated Note Title",
  "content": "Updated content"
}
```

#### 48. Delete Note
**Endpoint:** `DELETE {{base_url}}/notes/{id}`

#### 49. Get All Assignments (Faculty)
**Endpoint:** `GET {{base_url}}/assign-homeworks`

#### 50. Create Assignment
**Endpoint:** `POST {{base_url}}/assign-homeworks`

**Request Body:**
```json
{
  "title": "Homework Assignment",
  "description": "Complete chapter 5 exercises",
  "class_id": 1,
  "subject_id": 1,
  "due_date": "2024-12-31"
}
```

#### 51. Update Assignment
**Endpoint:** `PUT {{base_url}}/assign-homeworks/{id}`

#### 52. Delete Assignment
**Endpoint:** `DELETE {{base_url}}/assign-homeworks/{id}`

#### 53. Get All Submissions (Faculty)
**Endpoint:** `GET {{base_url}}/submit-homeworks`

#### 54. Update/Grade Submission
**Endpoint:** `PUT {{base_url}}/submit-homeworks/{id}`

**Request Body:**
```json
{
  "grade": "A",
  "feedback": "Good work!"
}
```

---

### 👨‍💼 ADMIN ROUTES

#### 55. Admin: Get All Recordings
**Endpoint:** `GET {{base_url}}/admin/recordings`

#### 56. Admin: Get Recording by ID
**Endpoint:** `GET {{base_url}}/admin/recordings/{id}`

#### 57. Admin: Create Recording
**Endpoint:** `POST {{base_url}}/admin/recordings`

**Request Body:**
```json
{
  "class_id": 1,
  "topic": "Admin Created Lecture",
  "duration": 90,
  "video_link": "https://www.youtube.com/watch?v=example"
}
```

#### 58. Admin: Update Recording
**Endpoint:** `PUT {{base_url}}/admin/recordings/{id}`

#### 59. Admin: Delete Recording
**Endpoint:** `DELETE {{base_url}}/admin/recordings/{id}`

#### 60. Admin: Get All Users
**Endpoint:** `GET {{base_url}}/users`

#### 61. Admin: Create User
**Endpoint:** `POST {{base_url}}/users`

**Request Body:**
```json
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "Password@123",
  "password_confirmation": "Password@123",
  "role_id": 1,
  "phone_no": "1234567890"
}
```

#### 62. Admin: Get User by ID
**Endpoint:** `GET {{base_url}}/users/{id}`

#### 63. Admin: Update User
**Endpoint:** `PUT {{base_url}}/users/{id}`

**Request Body:**
```json
{
  "name": "Updated Name",
  "phone_no": "9999999999"
}
```

#### 64. Admin: Delete User
**Endpoint:** `DELETE {{base_url}}/users/{id}`

#### 65. Admin: Get All Students
**Endpoint:** `GET {{base_url}}/students`

#### 66. Admin: Create Student
**Endpoint:** `POST {{base_url}}/students`

**Request Body:**
```json
{
  "user_id": 31,
  "dob": "2005-01-15",
  "address": "123 Student Street"
}
```

#### 67. Admin: Update Student
**Endpoint:** `PUT {{base_url}}/students/{id}`

#### 68. Admin: Delete Student
**Endpoint:** `DELETE {{base_url}}/students/{id}`

#### 69. Admin: Get All Enrollments
**Endpoint:** `GET {{base_url}}/enrollments`

#### 70. Admin: Update Enrollment (Approve/Reject)
**Endpoint:** `PUT {{base_url}}/enrollments/{id}`

**Request Body:**
```json
{
  "status": "approved"
}
```

#### 71. Admin: Delete Enrollment
**Endpoint:** `DELETE {{base_url}}/enrollments/{id}`

#### 72. Admin: Get All Faculties
**Endpoint:** `GET {{base_url}}/faculties`

#### 73. Admin: Create Faculty
**Endpoint:** `POST {{base_url}}/faculties`

**Request Body:**
```json
{
  "user_id": 31,
  "qualification": "PhD in Computer Science",
  "date_of_joining": "2024-01-01"
}
```

#### 74. Admin: Update Faculty
**Endpoint:** `PUT {{base_url}}/faculties/{id}`

#### 75. Admin: Delete Faculty
**Endpoint:** `DELETE {{base_url}}/faculties/{id}`

#### 76. Admin: Get All Subjects
**Endpoint:** `GET {{base_url}}/subjects`

#### 77. Admin: Create Subject
**Endpoint:** `POST {{base_url}}/subjects`

**Request Body:**
```json
{
  "name": "Advanced Mathematics",
  "faculty_id": 2
}
```

#### 78. Admin: Update Subject
**Endpoint:** `PUT {{base_url}}/subjects/{id}`

#### 79. Admin: Delete Subject
**Endpoint:** `DELETE {{base_url}}/subjects/{id}`

#### 80. Admin: Get All Classes
**Endpoint:** `GET {{base_url}}/classes`

#### 81. Admin: Create Class
**Endpoint:** `POST {{base_url}}/classes`

**Request Body:**
```json
{
  "name": "Grade 11 Science"
}
```

#### 82. Admin: Get Class by ID
**Endpoint:** `GET {{base_url}}/classes/{id}`

#### 83. Admin: Update Class
**Endpoint:** `PUT {{base_url}}/classes/{id}`

#### 84. Admin: Delete Class
**Endpoint:** `DELETE {{base_url}}/classes/{id}`

#### 85. Admin: Assign Subject to Class
**Endpoint:** `POST {{base_url}}/classes/{id}/assign-subject`

**Request Body:**
```json
{
  "subject_id": 1,
  "faculty_id": 2
}
```

#### 86. Admin: Get All Notes
**Endpoint:** `GET {{base_url}}/notes`

#### 87. Admin: Create Note
**Endpoint:** `POST {{base_url}}/notes`

#### 88. Admin: Update Note
**Endpoint:** `PUT {{base_url}}/notes/{id}`

#### 89. Admin: Delete Note
**Endpoint:** `DELETE {{base_url}}/notes/{id}`

#### 90. Admin: Get All Assignments
**Endpoint:** `GET {{base_url}}/assign-homeworks`

#### 91. Admin: Create Assignment
**Endpoint:** `POST {{base_url}}/assign-homeworks`

#### 92. Admin: Update Assignment
**Endpoint:** `PUT {{base_url}}/assign-homeworks/{id}`

#### 93. Admin: Delete Assignment
**Endpoint:** `DELETE {{base_url}}/assign-homeworks/{id}`

#### 94. Admin: Get All Submissions
**Endpoint:** `GET {{base_url}}/submit-homeworks`

#### 95. Admin: Update Submission
**Endpoint:** `PUT {{base_url}}/submit-homeworks/{id}`

#### 96. Admin: Get All MAS Roles
**Endpoint:** `GET {{base_url}}/mas-roles`

#### 97. Admin: Create MAS Role
**Endpoint:** `POST {{base_url}}/mas-roles`

**Request Body:**
```json
{
  "name": "super_admin"
}
```

#### 98. Admin: Update MAS Role
**Endpoint:** `PUT {{base_url}}/mas-roles/{id}`

#### 99. Admin: Delete MAS Role
**Endpoint:** `DELETE {{base_url}}/mas-roles/{id}`

---

### 📋 TASKS (All Authenticated Users)

#### 100. List All Tasks
**Endpoint:** `GET {{base_url}}/tasks`

#### 101. Create Task
**Endpoint:** `POST {{base_url}}/tasks`

**Request Body:**
```json
{
  "title": "New Task",
  "description": "Task description",
  "status": "pending"
}
```

#### 102. Get Task by ID
**Endpoint:** `GET {{base_url}}/tasks/{id}`

#### 103. Update Task
**Endpoint:** `PUT {{base_url}}/tasks/{id}`

**Request Body:**
```json
{
  "title": "Updated Task",
  "status": "completed"
}
```

#### 104. Delete Task
**Endpoint:** `DELETE {{base_url}}/tasks/{id}`

---

### 🎯 V2 ADMIN CONTENT MANAGEMENT

#### 105. V2 Admin: List Chapters
**Endpoint:** `GET {{base_url}}/v2/admin/chapters`

#### 106. V2 Admin: Create Chapter
**Endpoint:** `POST {{base_url}}/v2/admin/chapters`

**Request Body:**
```json
{
  "name": "New Chapter",
  "class_id": 1,
  "subject_id": 1
}
```

#### 107. V2 Admin: Get Chapter by ID
**Endpoint:** `GET {{base_url}}/v2/admin/chapters/{id}`

#### 108. V2 Admin: Update Chapter
**Endpoint:** `PUT {{base_url}}/v2/admin/chapters/{id}`

#### 109. V2 Admin: Delete Chapter
**Endpoint:** `DELETE {{base_url}}/v2/admin/chapters/{id}`

#### 110. V2 Admin: List Topic Notes
**Endpoint:** `GET {{base_url}}/v2/admin/topic-notes`

#### 111. V2 Admin: Create Topic Note
**Endpoint:** `POST {{base_url}}/v2/admin/topic-notes`

**Request Body:**
```json
{
  "title": "New Topic Note",
  "content": "Topic content",
  "class_id": 1,
  "subject_id": 1,
  "chapter_id": 1
}
```

#### 112. V2 Admin: Get Topic Note by ID
**Endpoint:** `GET {{base_url}}/v2/admin/topic-notes/{id}`

#### 113. V2 Admin: Update Topic Note
**Endpoint:** `PUT {{base_url}}/v2/admin/topic-notes/{id}`

#### 114. V2 Admin: Delete Topic Note
**Endpoint:** `DELETE {{base_url}}/v2/admin/topic-notes/{id}`

#### 115. V2 Admin: List Question Banks
**Endpoint:** `GET {{base_url}}/v2/admin/question-banks`

#### 116. V2 Admin: Create Question Bank
**Endpoint:** `POST {{base_url}}/v2/admin/question-banks`

**Request Body:**
```json
{
  "title": "Question Bank",
  "chapter_id": 1
}
```

#### 117. V2 Admin: Get Question Bank by ID
**Endpoint:** `GET {{base_url}}/v2/admin/question-banks/{id}`

#### 118. V2 Admin: Update Question Bank
**Endpoint:** `PUT {{base_url}}/v2/admin/question-banks/{id}`

#### 119. V2 Admin: Delete Question Bank
**Endpoint:** `DELETE {{base_url}}/v2/admin/question-banks/{id}`

#### 120. V2 Admin: List Questions
**Endpoint:** `GET {{base_url}}/v2/admin/questions`

#### 121. V2 Admin: Create Question
**Endpoint:** `POST {{base_url}}/v2/admin/questions`

**Request Body:**
```json
{
  "question_bank_id": 1,
  "question_text": "What is 2+2?",
  "question_type": "multiple_choice",
  "options": ["3", "4", "5", "6"],
  "correct_answer": "4",
  "difficulty_level": "Easy"
}
```

#### 122. V2 Admin: Get Question by ID
**Endpoint:** `GET {{base_url}}/v2/admin/questions/{id}`

#### 123. V2 Admin: Update Question
**Endpoint:** `PUT {{base_url}}/v2/admin/questions/{id}`

#### 124. V2 Admin: Delete Question
**Endpoint:** `DELETE {{base_url}}/v2/admin/questions/{id}`

---

### 🧪 V2 TEST GENERATION

#### 125. Generate Chapter Test
**Endpoint:** `GET {{base_url}}/v2/tests/chapters/{chapter}/generate`

#### 126. Generate Topic Note Test
**Endpoint:** `GET {{base_url}}/v2/tests/topic-notes/{topic_note}/generate`

---

## 📝 Testing Workflow Example

### Complete Student Workflow:

1. **Login as Student:**
   ```
   POST http://localhost:8000/api/login
   Body: {"email": "student1@tasktutorials.com", "password": "Password@123"}
   ```

2. **Get Current User:**
   ```
   GET http://localhost:8000/api/me
   ```

3. **Get My Enrollments:**
   ```
   GET http://localhost:8000/api/my-enrollments
   ```

4. **Get My Classes:**
   ```
   GET http://localhost:8000/api/my-classes
   ```

5. **Get Library Classes:**
   ```
   GET http://localhost:8000/api/library/classes
   ```

6. **Get Class Subjects:**
   ```
   GET http://localhost:8000/api/library/classes/1/subjects
   ```

7. **Get Chapters:**
   ```
   GET http://localhost:8000/api/library/class-subjects/1/chapters
   ```

8. **Get Chapter Notes:**
   ```
   GET http://localhost:8000/api/library/chapters/1/notes
   ```

9. **Complete a Note:**
   ```
   POST http://localhost:8000/api/library/notes/1/complete
   ```

10. **Get Chapter Quiz:**
    ```
    GET http://localhost:8000/api/library/chapters/1/quiz
    ```

11. **Submit Quiz:**
    ```
    POST http://localhost:8000/api/library/quizzes/1/submit
    Body: {"answers": [{"question_id": 1, "answer": "4"}]}
    ```

12. **Get Quiz Result:**
    ```
    GET http://localhost:8000/api/library/quizzes/1/result
    ```

### Complete Admin Workflow:

1. **Login as Admin:**
   ```
   POST http://localhost:8000/api/login
   Body: {"email": "admin@tasktutorials.com", "password": "Password@123"}
   ```

2. **Get All Users:**
   ```
   GET http://localhost:8000/api/users
   ```

3. **Create New User:**
   ```
   POST http://localhost:8000/api/users
   Body: {"name": "Test User", "email": "test@example.com", "password": "Password@123", "role_id": 1}
   ```

4. **Get All Classes:**
   ```
   GET http://localhost:8000/api/classes
   ```

5. **Create New Class:**
   ```
   POST http://localhost:8000/api/classes
   Body: {"name": "Grade 12 Commerce"}
   ```

6. **Get All Enrollments:**
   ```
   GET http://localhost:8000/api/enrollments
   ```

7. **Approve Enrollment:**
   ```
   PUT http://localhost:8000/api/enrollments/1
   Body: {"status": "approved"}
   ```

---

## 🔧 Postman Tips

1. **Use Collection Variables:**
   - Set `base_url` = `http://localhost:8000/api`
   - Use `{{base_url}}` in all requests

2. **Handle Authentication:**
   - After login, Postman automatically stores session cookies
   - Ensure "Send cookies" is enabled in Postman settings
   - For API token auth, you might need to capture and use Sanctum tokens

3. **Environment Setup:**
   - Create separate environments for local, staging, production
   - Store base URLs and credentials as environment variables

4. **Testing:**
   - Use Postman's test scripts to validate responses
   - Chain requests using environment variables to store IDs

---

## 🐛 Common Issues & Solutions

1. **401 Unauthorized:**
   - Make sure you're logged in
   - Check if session cookies are being sent

2. **403 Forbidden:**
   - You don't have the required role/permission
   - Try logging in with a different account

3. **404 Not Found:**
   - Check if the resource ID exists
   - Verify the endpoint URL is correct

4. **500 Server Error:**
   - Check Laravel logs: `storage/logs/laravel.log`
   - Verify database is properly seeded

---

## 📊 Sample Data Available After Seeding

- **3 Roles:** student, faculty, admin
- **1 Admin User:** admin@tasktutorials.com
- **3 Faculty Users:** faculty1-3@tasktutorials.com
- **30 Student Users:** student1-30@tasktutorials.com
- **5 Classes:** Grade 8-12
- **25 Subjects:** 5 per class
- **75 Chapters:** 3 per subject
- **150 Topic Notes:** 2 per chapter
- **150 Quizzes:** 1 per chapter + 1 per topic note
- **3000 Quiz Questions:** 20 per quiz
- **25 Recordings:** 5 per class
- **Multiple Enrollments:** Each student enrolled in 3 classes

---

**Happy Testing! 🚀**