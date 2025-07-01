import face_recognition
import cv2
import json
import numpy as np
import os
from datetime import datetime

ENCODINGS_PATH = "stored_encodings.json"

# Load known encodings
if not os.path.exists(ENCODINGS_PATH):
    print("Encoding file not found. No face data to compare.")
    exit()

with open(ENCODINGS_PATH, 'r') as f:
    try:
        known_data = json.load(f)
    except json.JSONDecodeError:
        print("Encoding file is empty or corrupted.")
        exit()

known_encodings = [np.array(entry["encoding"]) for entry in known_data]
known_ids = [entry["employee_id"] for entry in known_data]
known_names = [entry["name"] for entry in known_data]

# Start webcam
cap = cv2.VideoCapture(0)
cv2.namedWindow("Face Scan - Press Q to quit")

recognized = False

while True:
    ret, frame = cap.read()
    if not ret:
        print("Failed to grab frame")
        break

    small_frame = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    rgb_small = small_frame[:, :, ::-1]

    face_locations = face_recognition.face_locations(rgb_small)
    face_encodings = face_recognition.face_encodings(rgb_small, face_locations)

    for face_encoding in face_encodings:
        matches = face_recognition.compare_faces(known_encodings, face_encoding, tolerance=0.5)
        face_distances = face_recognition.face_distance(known_encodings, face_encoding)

        if matches:
            best_match_index = np.argmin(face_distances)
            if matches[best_match_index]:
                employee_id = known_ids[best_match_index]
                name = known_names[best_match_index]
                print(f"[✓] Recognized: {name} (ID: {employee_id}) at {datetime.now()}")
                recognized = True
                break

    cv2.imshow("Face Scan - Press Q to quit", frame)

    if recognized:
        break

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()

if not recognized:
    print("[✗] No match found.")
