import cv2
import sys
import time

# Functions

def grab_frame(stream):             # -> only for built-in camera video stream
    ret, frame = stream.read()
       
    if not ret:
        sys.exit("Error: Could not read frame.")
     
    return frame



def find_center(current_frame, prev_frame, center_x, center_y):   # -> find centeral object
    if prev_frame is not None:      # calculate motion
        # conversion to grayscale
        prev_frame = cv2.cvtColor(prev_frame, cv2.COLOR_BGR2GRAY)
        current_frame = cv2.cvtColor(current_frame, cv2.COLOR_BGR2GRAY)

        prev_frame = cv2.GaussianBlur(prev_frame, (5, 5), 0)
        current_frame = cv2.GaussianBlur(current_frame, (5, 5), 0)

        # calculates image motion
        flow = cv2.calcOpticalFlowFarneback(prev_frame, current_frame, None, 
                                            pyr_scale=0.5, levels=5, winsize=30, 
                                            iterations=8, poly_n=5, poly_sigma=1.2, flags=0)
        

        avg_flow = cv2.reduce(flow, dim=0, rtype=cv2.REDUCE_AVG).flatten()
        dx, dy = avg_flow[:2]  

        if abs(dx) < 0.05:
            dx = 0 

        if abs(dy) < 0.05:
            dy = 0 

        # print(dx)



        scalerx = 7 if abs(dx) < 1 else 3
        scalery = 3 
        dx = dx * scalerx
        dy = dy * scalery
        
    
    else:    # first iteration: set center
        center_x, center_y = int(current_frame.shape[1]//2), int(current_frame.shape[0]//2)
        dx = 0
        dy = 0

    
    return int(center_x+dx), int(center_y+dy)





    
def track(frame, x, y):   # -> display central object
    cv2.rectangle(frame, (int(x-(frame.shape[1]*0.1)),int(y-(frame.shape[0]*0.1))), (int(x+(frame.shape[1]*0.1)),int(y+(frame.shape[0]*0.1))), (0,0,255), thickness=1)



# Variable Establishment

running = True
cap = cv2.VideoCapture(0)
prev_frame = None
center_x = 0
center_y = 0




# While Loop

while running:                         
    frame = grab_frame(cap)

    current_frame = frame

    center_x, center_y = find_center(current_frame, prev_frame, center_x, center_y)
    # print(center_x, center_y)

    track(current_frame,center_x,center_y)

    cv2.imshow("frame", current_frame)

    prev_frame = current_frame
    

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break



cap.release()
cv2.destroyAllWindows()