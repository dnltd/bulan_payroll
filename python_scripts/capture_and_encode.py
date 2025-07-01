import face_recognition
import os
import cv2
import sys
import pickle

# Ensure the 'faces' directory exists
faces_dir = os.path.join(os.getcwd(), 'public', 'faces')
encodings_file = os.path.join(os.getcwd(), 'public', 'face_encodings.pkl')

def encode_face(employee_name):
    image_path = os.path.join(faces_dir, employee_name + ".jpg")

    if not os.path.exists(image_path):
        print("Image not found for:", employee_name)
        return

    # Load image using OpenCV and convert to RGB
    bgr_image = cv2.imread(image_path)
    if bgr_image is None:
        print(f"Failed to load image from {image_path}")
        return
    image = cv2.cvtColor(bgr_image, cv2.COLOR_BGR2RGB)

    face_encodings = face_recognition.face_encodings(image)
    if not face_encodings:
        print("No face detected in image:", image_path)
        return

    new_encoding = face_encodings[0]

    if os.path.exists(encodings_file):
        with open(encodings_file, 'rb') as f:
            known_encodings = pickle.load(f)
    else:
        known_encodings = {}

    known_encodings[employee_name] = new_encoding

    with open(encodings_file, 'wb') as f:
        pickle.dump(known_encodings, f)

    print(f"Face encoded and saved for {employee_name}")

