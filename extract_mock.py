import json
import re

with open('/Users/daksh/.gemini/antigravity/brain/4bb96e5c-9144-4fa6-be3b-8eb9dbaff96c/.system_generated/logs/transcript_full.jsonl', 'r') as f:
    for line in f:
        try:
            data = json.loads(line)
            if 'content' in data:
                if 'const DUMMY_RESULTS = [' in data['content']:
                    with open('dummy_results_full.txt', 'w') as out:
                        out.write(data['content'])
                if 'const recordings = [' in data['content'] or 'const mockLectures = [' in data['content']:
                    with open('recordings_mock_full.txt', 'w') as out:
                        out.write(data['content'])
        except Exception as e:
            pass
