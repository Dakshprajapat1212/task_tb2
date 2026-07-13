import os

replacements = [
    ('Topic::', 'Chapter::'),
    ('Note::', 'TopicNote::'),
    ('App\\Models\\Topic', 'App\\Models\\Chapter'),
    ('App\\Models\\Note', 'App\\Models\\TopicNote'),
    ('class Topic extends', 'class Chapter extends'),
    ('class Note extends', 'class TopicNote extends'),
    ('topic_id', 'chapter_id'),
    ('note_id', 'topic_note_id'),
    ('topics', 'chapters'),
    ("('topic')", "('chapter')"),
    ("('note')", "('topic_note')"),
    ('$topic->', '$chapter->'),
    ('$note->', '$topicNote->'),
    ('topics()', 'chapters()'),
    ('notes()', 'topicNotes()'),
    ('$table->foreignId(\'chapter_id\')', '$table->foreignId(\'chapter_id\')'), # handled above
]

dirs = ['app', 'database', 'routes']

for d in dirs:
    for root, _, files in os.walk(d):
        for file in files:
            if not file.endswith('.php'):
                continue
            path = os.path.join(root, file)
            with open(path, 'r') as f:
                content = f.read()
            
            orig = content
            for old, new in replacements:
                content = content.replace(old, new)
            
            # special case for relations
            content = content.replace('hasMany(Note::class', 'hasMany(TopicNote::class')
            content = content.replace('belongsTo(Topic::class', 'belongsTo(Chapter::class')
            content = content.replace('public function topic()', 'public function chapter()')
            content = content.replace('public function note()', 'public function topicNote()')
            content = content.replace('TopicSeeder', 'ChapterSeeder')
            content = content.replace('NoteSeeder', 'TopicNoteSeeder')
            
            if content != orig:
                with open(path, 'w') as f:
                    f.write(content)
                print(f"Refactored {path}")
