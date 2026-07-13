import requests
import json
import urllib.parse

BASE_URL = "http://localhost:8000/api"

def print_result(name, res):
    if res.status_code in [200, 201]:
        print(f"✅ [PASS] {name} (Status: {res.status_code})")
    else:
        print(f"❌ [FAIL] {name} (Status: {res.status_code})")
        print(f"   Response: {res.text[:200]}")

# 1. Test Admin
session = requests.Session()
# Manually bypass CSRF for script testing by not using web middleware, wait, Sanctum requires CSRF if using session cookie. 
# Better: just use Bearer token! Wait, AuthController doesn't return Bearer token, it uses Session!
# To use Session with python requests, we need to handle CSRF.
session.get("http://localhost:8000/sanctum/csrf-cookie")
xsrf = urllib.parse.unquote(session.cookies.get('XSRF-TOKEN', ''))
session.headers.update({
    'Accept': 'application/json',
    'X-XSRF-TOKEN': xsrf,
    'Referer': 'http://localhost:8000'
})

print("\n--- ADMIN APIS ---")
res = session.post(f"{BASE_URL}/login", json={"email": "admin@tasktutorials.com", "password": "Password@123"})
print_result("Admin Login", res)

if res.status_code == 200:
    print_result("Admin Users", session.get(f"{BASE_URL}/users"))
    print_result("Admin Classes", session.get(f"{BASE_URL}/classes"))
    print_result("Admin Subjects", session.get(f"{BASE_URL}/subjects"))
    print_result("Admin Chapters", session.get(f"{BASE_URL}/chapters"))
    print_result("Admin Notes", session.get(f"{BASE_URL}/notes"))

session.post(f"{BASE_URL}/logout")

# 2. Test Student
session = requests.Session()
session.get("http://localhost:8000/sanctum/csrf-cookie")
xsrf = urllib.parse.unquote(session.cookies.get('XSRF-TOKEN', ''))
session.headers.update({'Accept': 'application/json', 'X-XSRF-TOKEN': xsrf, 'Referer': 'http://localhost:8000'})

print("\n--- STUDENT APIS ---")
res = session.post(f"{BASE_URL}/login", json={"email": "student1@tasktutorials.com", "password": "Password@123"})
print_result("Student Login", res)

if res.status_code == 200:
    classes_res = session.get(f"{BASE_URL}/library/classes")
    print_result("Library Classes", classes_res)
    
    if classes_res.status_code == 200 and classes_res.json().get('data'):
        class_id = classes_res.json()['data'][0]['id']
        subjects_res = session.get(f"{BASE_URL}/library/classes/{class_id}/subjects")
        print_result("Library Subjects", subjects_res)
        
        if subjects_res.status_code == 200 and subjects_res.json().get('data', {}).get('subjects'):
            subject_id = subjects_res.json()['data']['subjects'][0]['id']
            chapters_res = session.get(f"{BASE_URL}/library/class-subjects/{subject_id}/chapters")
            print_result("Library Chapters", chapters_res)
            
            if chapters_res.status_code == 200 and chapters_res.json().get('data'):
                chapter_id = chapters_res.json()['data'][0]['id']
                notes_res = session.get(f"{BASE_URL}/library/chapters/{chapter_id}/notes")
                print_result("Library Notes", notes_res)
                progress_res = session.get(f"{BASE_URL}/library/chapters/{chapter_id}/progress")
                print_result("Library Progress", progress_res)
                quiz_res = session.get(f"{BASE_URL}/library/chapters/{chapter_id}/quiz")
                print_result("Library Chapter Quiz", quiz_res)

