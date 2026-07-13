#!/bin/bash
BASE="http://localhost:8000/api"

test_api() {
    local name=$1
    local url=$2
    local method=${3:-GET}
    
    local res=$(curl -s -w "\n%{http_code}" -X $method -b cookie.txt $url -H "Accept: application/json" -H "Referer: http://localhost:8000")
    local status=$(echo "$res" | tail -n1)
    local body=$(echo "$res" | head -n-1)
    
    if [[ "$status" == "200" || "$status" == "201" ]]; then
        echo "✅ [PASS] $name (Status: $status)"
    else
        echo "❌ [FAIL] $name (Status: $status)"
        echo "   $body" | head -c 200
        echo ""
    fi
}

echo "--- ADMIN APIS ---"
curl -s -c cookie.txt http://localhost:8000/sanctum/csrf-cookie > /dev/null
XSRF=$(awk '/XSRF-TOKEN/ {print $7}' cookie.txt | sed 's/%3D/=/g')
curl -s -c cookie.txt -b cookie.txt -X POST $BASE/login -H "Accept: application/json" -H "Content-Type: application/json" -H "X-XSRF-TOKEN: $XSRF" -H "Referer: http://localhost:8000" -d '{"email":"admin@tasktutorials.com","password":"Password@123"}' > /dev/null

test_api "Admin Users" "$BASE/users"
test_api "Admin Classes" "$BASE/classes"
test_api "Admin Subjects" "$BASE/subjects"
test_api "Admin Chapters" "$BASE/v2/admin/chapters"
test_api "Admin Notes" "$BASE/notes"
curl -s -X POST -b cookie.txt -H "Accept: application/json" -H "Referer: http://localhost:8000" $BASE/logout > /dev/null

echo ""
echo "--- STUDENT APIS ---"
curl -s -c cookie.txt http://localhost:8000/sanctum/csrf-cookie > /dev/null
XSRF=$(awk '/XSRF-TOKEN/ {print $7}' cookie.txt | sed 's/%3D/=/g')
curl -s -c cookie.txt -b cookie.txt -X POST $BASE/login -H "Accept: application/json" -H "Content-Type: application/json" -H "X-XSRF-TOKEN: $XSRF" -H "Referer: http://localhost:8000" -d '{"email":"student1@tasktutorials.com","password":"Password@123"}' > /dev/null

test_api "Library Classes" "$BASE/library/classes"
CLASS_ID=$(curl -s -b cookie.txt $BASE/library/classes -H "Accept: application/json" -H "Referer: http://localhost:8000" | jq -r '.data[0].id')

if [ "$CLASS_ID" != "null" ] && [ -n "$CLASS_ID" ]; then
    test_api "Library Subjects" "$BASE/library/classes/$CLASS_ID/subjects"
    SUBJECT_ID=$(curl -s -b cookie.txt $BASE/library/classes/$CLASS_ID/subjects -H "Accept: application/json" -H "Referer: http://localhost:8000" | jq -r '.data.subjects[0].id')
    
    if [ "$SUBJECT_ID" != "null" ] && [ -n "$SUBJECT_ID" ]; then
        test_api "Library Chapters" "$BASE/library/class-subjects/$CLASS_ID/chapters"
        CHAPTER_ID=$(curl -s -b cookie.txt $BASE/library/class-subjects/$CLASS_ID/chapters -H "Accept: application/json" -H "Referer: http://localhost:8000" | jq -r '.data[0].id')
        
        if [ "$CHAPTER_ID" != "null" ] && [ -n "$CHAPTER_ID" ]; then
            test_api "Library Notes" "$BASE/library/chapters/$CHAPTER_ID/notes"
            test_api "Library Progress" "$BASE/library/chapters/$CHAPTER_ID/progress"
            test_api "Library Chapter Quiz" "$BASE/library/chapters/$CHAPTER_ID/quiz"
        fi
    fi
fi
