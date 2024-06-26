import cv2
import sys

# Functions

def grab_frame(stream):             # only for built-in camera video stream
    ret, frame = stream.read()
       
    if not ret:
        sys.exit("Error: Could not read frame.")
     
    return frame



def analyze():
    pass
    # -> find centeral object and track it




# Variable Establishment

running = True
cap = cv2.VideoCapture(0)





# While Loop

while running:                          
    frame = grab_frame(cap)
    cv2.imshow('Frame', frame)
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break



cap.release()
cv2.destroyAllWindows()