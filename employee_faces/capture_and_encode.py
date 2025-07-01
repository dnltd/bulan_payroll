import cv2
import face_recognition
import os
import sys
import json

# Arguments from Laravel (employee_id and full_name)
if len(sys.argv) < 3:
    print("Usage: python capture_and_encode.py <employee_id> <full_name>")
    sys.exit(1)

employee_id = sys.argv[1]
employee_name = sys.argv[2]
image_dir = "employee_faces"
os.makedirs(image_dir, exist_ok=True)
image_path = os.path.join(image_dir, f"{employee_id}_{employee_name}.jpg")

# Step 1: Capture image from webcam
cap = cv2.VideoCapture(0)
cv2.namedWindow("Capture Face (Press SPACE to capture, ESC to exit)")

while True:
    ret, frame = cap.read()
    if not ret:
        print("Camera not working!")
        break
    cv2.imshow("Capture Face (Press SPACE to capture, ESC to exit)", frame)

    key = cv2.waitKey(1)
    if key % 256 == 27:  # ESC
        print("Escape hit, closing...")
        break
    elif key % 256 == 32:  # SPACE
        cv2.imwrite(image_path, frame)
        print(f"[✓] Image saved to {image_path}")
        break

cap.release()
cv2.destroyAllWindows()

# Step 2: Encode face
image = face_recognition.load_image_file(image_path)
encodings = face_recognition.face_encodings(image)

if not encodings:
    print("[✗] No face detected.")
    sys.exit(1)

face_encoding = encodings[0]

# Step 3: Store encoding to JSON
enc_file = "stored_encodings.json"
data = []

if os.path.exists(enc_file):
    with open(enc_file, "r") as f:
        try:
            data = json.load(f)
        except:
            data = []

# Remove old entry for this employee_id
data = [entry for entry in data if entry["employee_id"] != employee_id]

data.append({
    "employee_id": employee_id,
    "name": employee_name,
    "encoding": face_encoding.tolist()
})

with open(enc_file, "w") as f:
    json.dump(data, f)

print("[✓] Face encoding stored successfully.")
