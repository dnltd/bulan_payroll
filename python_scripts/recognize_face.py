import face_recognition
import numpy as np
from PIL import Image
import os
import sys
import json

# Check command line argument
if len(sys.argv) < 2:
    print(json.dumps({"result":"error","message":"No image provided"}))
    sys.exit(1)

image_path = sys.argv[1]

# Load unknown image
try:
    unknown_pil = Image.open(image_path).convert("RGB")
    unknown_image = np.array(unknown_pil)
except Exception as e:
    print(json.dumps({"result":"error","message":f"Failed to load image: {e}"}))
    sys.exit(1)

# Detect face in unknown image
face_locations = face_recognition.face_locations(unknown_image)
if len(face_locations) == 0:
    print(json.dumps({"result":"no_face","message":"No face detected"}))
    sys.exit(0)

unknown_encoding = face_recognition.face_encodings(unknown_image, known_face_locations=face_locations)[0]

# Folder of known faces
KNOWN_DIR = os.path.join(os.path.dirname(__file__), "known_faces")

match_found = False
match_name = None
match_distance = None

for filename in os.listdir(KNOWN_DIR):
    if filename.lower().endswith((".jpg",".png")):
        known_path = os.path.join(KNOWN_DIR, filename)
        try:
            known_pil = Image.open(known_path).convert("RGB")
            known_image = np.array(known_pil)
        except:
            continue

        known_locations = face_recognition.face_locations(known_image)
        if len(known_locations) == 0:
            continue

        known_encoding = face_recognition.face_encodings(known_image, known_face_locations=known_locations)[0]

        # Compare
        results = face_recognition.compare_faces([known_encoding], unknown_encoding, tolerance=0.65)
        distance = face_recognition.face_distance([known_encoding], unknown_encoding)[0]

        if results[0]:
            match_found = True
            match_name = os.path.splitext(filename)[0]
            match_distance = distance
            break

# Output JSON
if match_found:
    print(json.dumps({
        "result": "match",
        "name": match_name,
        "distance": float(match_distance)
    }))
else:
    print(json.dumps({
        "result": "no_match",
        "message": "No matching face found"
    }))
