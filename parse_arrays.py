import re

with open('dummy_results_full.txt', 'r') as f:
    text = f.read()
    match = re.search(r'const DUMMY_RESULTS = (\[.*?\]);', text, re.DOTALL)
    if match:
        with open('extracted_dummy.json', 'w') as out:
            out.write(match.group(1))

with open('recordings_mock_full.txt', 'r') as f:
    text = f.read()
    match = re.search(r'const recordings = (\[.*?\]);', text, re.DOTALL)
    if match:
        with open('extracted_recordings.json', 'w') as out:
            out.write(match.group(1))
    
    match2 = re.search(r'const mockLectures = (\[.*?\]);', text, re.DOTALL)
    if match2:
        with open('extracted_lectures.json', 'w') as out:
            out.write(match2.group(1))
