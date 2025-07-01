import sys
import cv2
import face_recognition
import pickle
import os
from datetime import datetime

# Argument: path to uploaded image
if len(sys.argv) < 2:
    print("NO_IMAGE")
    sys.exit(1)

image_path = sys.argv[1]

encodings_dir = 'face_encodings'
known_encodings = []
employee_ids = []

# Load encodings
for filename in os.listdir(encodings_dir):
    if filename.endswith('.pkl'):
        employee_id = filename.replace('.pkl', '')
        with open(os.path.join(encodings_dir, filename), 'rb') as f:
            encoding = pickle.load(f)
            known_encodings.append(encoding)
            employee_ids.append(employee_id)

# Load the uploaded image
image = cv2.imread(image_path)
rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)

# Locate and encode faces
boxes = face_recognition.face_locations(rgb)
encodings = face_recognition.face_encodings(rgb, boxes)

# Compare
for encoding in encodings:
    results = face_recognition.compare_faces(known_encodings, encoding, tolerance=0.45)
    if True in results:
        matched_id = employee_ids[results.index(True)]
        timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        print(f"{matched_id},{timestamp}")
        sys.exit(0)

print("NO_MATCH")
sys.exit(0)
