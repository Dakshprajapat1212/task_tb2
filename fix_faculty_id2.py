import os
import glob
import re

files_to_check = [
    '/Users/daksh/intern 2/task_tutorials_backend/app/Http/Controllers/NoteController.php',
    '/Users/daksh/intern 2/task_tutorials_backend/app/Http/Controllers/RecordingController.php',
    '/Users/daksh/intern 2/task_tutorials_backend/app/Http/Controllers/AssignHomeworkController.php'
]

for file in files_to_check:
    with open(file, 'r') as f:
        content = f.read()

    new_content = content

    new_content = re.sub(
        r"ClassModel::where\('id', \$topicNote->class_id\)\n\s*->where\('faculty_id', \$faculty->id\)",
        r"ClassModel::forFaculty($faculty->id)->where('id', $topicNote->class_id)",
        new_content
    )

    new_content = re.sub(
        r"ClassModel::where\('id', \$class_id\)\n\s*->where\('faculty_id', \$faculty->id\)",
        r"ClassModel::forFaculty($faculty->id)->where('id', $class_id)",
        new_content
    )

    new_content = re.sub(
        r"ClassModel::where\('id', \$classId\)\n\s*->where\('faculty_id', \$faculty->id\)",
        r"ClassModel::forFaculty($faculty->id)->where('id', $classId)",
        new_content
    )

    if new_content != content:
        with open(file, 'w') as f:
            f.write(new_content)

print("Done2")
