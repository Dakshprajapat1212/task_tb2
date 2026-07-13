import os
import glob
import re

files_to_check = glob.glob('/Users/daksh/intern 2/task_tutorials_backend/app/Http/Controllers/*.php')

for file in files_to_check:
    with open(file, 'r') as f:
        content = f.read()

    new_content = content
    # pattern: ClassModel::where('id', $some_var)->where('faculty_id', $faculty->id)
    # or ->where('faculty_id', $faculty->id)
    
    # Let's just do exact string replacements based on the grep results
    # AssignHomeworkController.php
    new_content = new_content.replace(
        "ClassModel::where('id', $request->class_id)\n\n                ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $request->class_id)"
    )
    new_content = new_content.replace(
        "ClassModel::where('id', $homework->class_id)\n\n                ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $homework->class_id)"
    )
    new_content = new_content.replace(
        "ClassModel::where('id', $homework->class_id)\n            ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $homework->class_id)"
    )
    
    # HomeworkController.php
    new_content = new_content.replace(
        "ClassModel::where('id', $request->class_id)\n\n                ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $request->class_id)"
    )
    new_content = new_content.replace(
        "ClassModel::where('id', $homework->class_id)\n\n                ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $homework->class_id)"
    )
    new_content = new_content.replace(
        "ClassModel::where('id', $homework->class_id)\n                ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $homework->class_id)"
    )

    # NoteController.php
    new_content = new_content.replace(
        "ClassModel::where('id', $note->class_id)\n            ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $note->class_id)"
    )

    # RecordingController.php
    new_content = new_content.replace(
        "ClassModel::where('id', $recording->class_id)\n            ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $recording->class_id)"
    )

    # SubmitHomeworkController.php
    new_content = new_content.replace(
        "ClassModel::where('id', $submission->assignHomework->class_id)\n            ->where('faculty_id', $faculty->id)",
        "ClassModel::forFaculty($faculty->id)->where('id', $submission->assignHomework->class_id)"
    )

    # V2 Admin Controllers
    new_content = re.sub(
        r"ClassModel::where\('id', \$request->class_id\)->where\('faculty_id', \$faculty->id\)",
        r"ClassModel::forFaculty($faculty->id)->where('id', $request->class_id)",
        new_content
    )
    new_content = re.sub(
        r"ClassModel::where\('id', \$chapter->class_id\)->where\('faculty_id', \$faculty->id\)",
        r"ClassModel::forFaculty($faculty->id)->where('id', $chapter->class_id)",
        new_content
    )
    new_content = re.sub(
        r"ClassModel::where\('id', \$topicNote->class_id\)->where\('faculty_id', \$faculty->id\)",
        r"ClassModel::forFaculty($faculty->id)->where('id', $topicNote->class_id)",
        new_content
    )
    new_content = re.sub(
        r"ClassModel::where\('id', \$classId\)->where\('faculty_id', \$faculty->id\)",
        r"ClassModel::forFaculty($faculty->id)->where('id', $classId)",
        new_content
    )

    if new_content != content:
        with open(file, 'w') as f:
            f.write(new_content)

print("Done")
