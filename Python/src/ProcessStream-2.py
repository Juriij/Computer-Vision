import cv2
import time
import numpy as np
import cupy as cp
import mysql.connector
from statistics import mean

# Functions

def grab_frame(stream):             # -> only for built-in camera video stream and IP cameras
    start = time.time()
    ret, frame = stream.read()
    end = time.time()

    elapsed_time = end - start
       
    if not ret:
        print("Error: Could not read frame.")
     
    return frame, elapsed_time




def analyze_img(frame):
    global initial_run, locObjY, locObjX, locObjMaxDist, locObjHeight, locObjWidth, locObjRowWidth, locObjX_offset, locObjImage, locObjIdx

    ######### initial run ##########

    if initial_run:
        locObjMaxDist=40
        locObjHeight=60
        locObjWidth=60
        locObjRowWidth = [1] * locObjHeight  # Initialize with 60 ones
        locObjX_offset = [0] * locObjHeight  # Initialize with 60 zeros

        locObjY=(frame.shape[0]//2)-(locObjHeight//2)
        locObjX=(frame.shape[1]//2)-(locObjWidth//2)

        locObjRowWidth[0]=locObjWidth
        locObjRowWidth[30]=locObjWidth
        locObjRowWidth[59]=locObjWidth

        # lock and copy central object
        locObjImage = np.zeros(locObjHeight * locObjWidth *3, dtype=np.int64)
        locObjIdx = 0


        for y in range(locObjHeight):
            locObjIdx += locObjX_offset[y] * 3  # Jump unused pixels at the beginning of the row
            for x in range(locObjX_offset[y], locObjX_offset[y] + locObjRowWidth[y]):  # One row
                for px in range(3):  # 3 RGB values
                    imgIdx = (locObjY + y) * frame.shape[1] * 3 + (locObjX + x) * 3 + px
                    locObjImage[locObjIdx] = pixels[imgIdx]
                    locObjIdx += 1

            locObjIdx += (locObjWidth - locObjX_offset[y] - locObjRowWidth[y]) * 3  # jump unused pixels at the end



        origX=locObjX
        origY=locObjY
        
        initial_run = False

    ######### initial run ##########




    ######## runs constantly ########

    else:
        tstamp_A = int(time.time() * 1000)
        bestX = 0
        bestY = 0
        bestError = 1000000000
        startY=locObjY - locObjMaxDist

        if startY < 1:
            startY = 1

        endY = locObjY + locObjMaxDist
        if endY > frame.shape[0]:
            endY = frame.shape[0]


        for acty in range(startY, endY + 1):
            startX = locObjX - locObjMaxDist
            if startX < 1:
                startX = 1
            
            endX = locObjX + locObjMaxDist
            if endX > frame.shape[1]:
                endX = frame.shape[1]

            for actx in range(startX, endX + 1):
                #evaluate error of SUM abs(image - locked object)
                actErr=0
                locObjIdx = 0
                for y in range(locObjHeight):  # for all rows
                    locObjIdx += locObjX_offset[y] * 3  # jump unused pixels at the beginning of the row
                    for x in range(locObjX_offset[y], locObjX_offset[y] + locObjRowWidth[y]):  # one row
                        imgIdx = (acty + y) * frame.shape[1] * 3 + (actx + x) * 3
                        if imgIdx > (len(pixels) - 3):  # border
                            actErr += 1000000

                        else:
                            actErr += abs(locObjImage[locObjIdx] + locObjImage[locObjIdx + 1] + locObjImage[locObjIdx + 2] - pixels[imgIdx] - pixels[imgIdx + 1] - pixels[imgIdx + 2])
                        locObjIdx += 3
                        locObjIdx += (locObjWidth - locObjX_offset[y] - locObjRowWidth[y]) * 3  # jump unused pixels at the end

                ##check if new better than previous
                if bestError > actErr:
                    bestError = actErr
                    bestX = actx
                    bestY = acty
                #gIm.drawRect(actx, acty, locObjWidth,locObjHeight);					    
            #for actx
        #for acty

        locObjX=bestX
        locObjY=bestY;			
        if bestError>0:             #copy new object image
            locObjIdx = 0;				
            for i in range(locObjHeight):  # for all rows
                locObjIdx += locObjX_offset[i] * 3  # jump unused pixels at the beginning of the row
                for x in range(locObjX_offset[i], locObjX_offset[i] + locObjRowWidth[i]):  # one row
                    for px in range(3):  # 3 RGB values
                        imgIdx = (locObjY + i) * frame.shape[1] * 3 + (locObjX + x) * 3 + px
                        locObjImage[locObjIdx] = pixels[imgIdx]
                        locObjIdx += 1
                    
                
                locObjIdx += (locObjWidth - locObjX_offset[i]-locObjRowWidth[i])*3 #jump unused pixels at the end


        ###update RTUDP outputs
        # rtudp_output[0]= locObjX - origX
        # rtudp_output[1]= locObjY - origY
        tstamp_A = int(time.time() * 1000)
            


    ######## runs constantly ########

    ######## draw the central object ###########


    yellow = (0, 255, 255)  

    cv2.rectangle(frame, (locObjX, locObjY), (locObjX + locObjWidth, locObjY + locObjHeight), yellow, 2)

    # Prepare the text
    # posS = f"x={rtudp_output[0]}, y={rtudp_output[1]}"  # Replace rtudp_output with actual values
    posS = "idk"


    font = cv2.FONT_HERSHEY_SIMPLEX  
    font_scale = 0.5  
    font_thickness = 1 

    # Calculate text size to adjust position if necessary
    (text_width, text_height), baseline = cv2.getTextSize(posS, font, font_scale, font_thickness)

    # Draw text
    cv2.putText(frame, posS, (locObjX + locObjWidth, locObjY + text_height), font, font_scale, yellow, font_thickness, lineType=cv2.LINE_AA)


    ######## draw the central object ###########





################ GPU ACCELERATION ATTEMPT ####################################


def analyze_img_gpu(frame):
    global initial_run, locObjY, locObjX, locObjMaxDist, locObjHeight, locObjWidth, locObjImage

    frame = cp.asarray(frame)  # move the frame to gpu

    ######## initial run ########

    if initial_run:
        locObjMaxDist = 40
        locObjHeight = 60
        locObjWidth = 60
        # Initial setup: Center the object in the frame
        locObjY = (frame.shape[0] // 2) - (locObjHeight // 2)
        locObjX = (frame.shape[1] // 2) - (locObjWidth // 2)
        
        # Copy the initial object image
        locObjImage = frame[locObjY:locObjY + locObjHeight, locObjX:locObjX + locObjWidth].copy()

        frame = cp.asnumpy(frame)  # move the frame to cpu
        
        initial_run = False

    ######## initial run ########


    ######## runs constantly ########

    else:
        bestX, bestY = locObjX, locObjY
        bestError = float('inf')
        frame_height, frame_width, _ = frame.shape

        # Define search area boundaries
        startY = max(locObjY - locObjMaxDist, 0)
        endY = min(locObjY + locObjMaxDist, frame_height - locObjHeight)
        startX = max(locObjX - locObjMaxDist, 0)
        endX = min(locObjX + locObjMaxDist, frame_width - locObjWidth)

        # Search for the best match within the allowed movement area
        for y in range(startY, endY + 1):
            for x in range(startX, endX + 1):
                current_patch = frame[y: y + locObjHeight, x: x + locObjWidth]
                error = cp.sum(cp.abs(current_patch - locObjImage))

                if error < bestError:
                    bestError = error
                    bestX, bestY = x, y

        locObjX, locObjY = bestX, bestY

        # Update the stored object image with the best match found
        if bestError < 1000000:
            locObjImage = frame[locObjY: locObjY + locObjHeight, locObjX: locObjX + locObjWidth].copy()


    ######## runs constantly ########



    ######## draw the central object ###########
    
    frame = cp.asnumpy(frame)  # move the frame to cpu

    yellow = (0, 255, 255)  

    cv2.rectangle(frame, (locObjX, locObjY), (locObjX + locObjWidth, locObjY + locObjHeight), yellow, 2)

    # Prepare the text
    # posS = f"x={rtudp_output[0]}, y={rtudp_output[1]}"  # Replace rtudp_output with actual values
    posS = "text"


    font = cv2.FONT_HERSHEY_SIMPLEX  
    font_scale = 0.5  
    font_thickness = 1 

    # Calculate text size to adjust position if necessary
    (text_width, text_height), baseline = cv2.getTextSize(posS, font, font_scale, font_thickness)

    # Draw text
    cv2.putText(frame, posS, (locObjX + locObjWidth, locObjY + text_height), font, font_scale, yellow, font_thickness, lineType=cv2.LINE_AA)


    ######## draw the central object ###########




################ GPU ACCELERATION ATTEMPT ####################################









def analyze_img_manual(frame):
    global initial_run, locObjY, locObjX, locObjMaxDist, locObjHeight, locObjWidth, locObjImage

    ######## initial run ########

    if initial_run:
        locObjMaxDist = 40
        locObjHeight = 60
        locObjWidth = 60
        # Initial setup: Center the object in the frame
        locObjY = (frame.shape[0] // 2) - (locObjHeight // 2)
        locObjX = (frame.shape[1] // 2) - (locObjWidth // 2)
        
        # Copy the initial object image
        locObjImage = frame[locObjY:locObjY + locObjHeight, locObjX:locObjX + locObjWidth]
        
        initial_run = False

    ######## initial run ########


    ######## runs constantly ########

    else:
        bestX, bestY = locObjX, locObjY
        bestError = float('inf')
        frame_height, frame_width, _ = frame.shape

        # Define search area boundaries
        startY = max(locObjY - locObjMaxDist, 0)
        endY = min(locObjY + locObjMaxDist, frame_height - locObjHeight)
        startX = max(locObjX - locObjMaxDist, 0)
        endX = min(locObjX + locObjMaxDist, frame_width - locObjWidth)

        # Search for the best match within the allowed movement area
        for y in range(startY, endY + 1):
            for x in range(startX, endX + 1):
                current_patch = frame[y: y + locObjHeight, x: x + locObjWidth]
                error = np.sum(np.abs(current_patch - locObjImage))

                if error < bestError:
                    bestError = error
                    bestX, bestY = x, y


        # Update the stored object image with the best match found
        if bestError < 1000000:
            locObjX, locObjY = bestX, bestY
            locObjImage = frame[locObjY: locObjY + locObjHeight, locObjX: locObjX + locObjWidth]


    ######## runs constantly ########



    ######## draw the central object ###########

    yellow = (0, 255, 255)  

    cv2.rectangle(frame, (locObjX, locObjY), (locObjX + locObjWidth, locObjY + locObjHeight), yellow, 2)

    # Prepare the text
    # posS = f"x={rtudp_output[0]}, y={rtudp_output[1]}"  # Replace rtudp_output with actual values
    posS = "text"


    font = cv2.FONT_HERSHEY_SIMPLEX  
    font_scale = 0.5  
    font_thickness = 1 

    # Calculate text size to adjust position if necessary
    (text_width, text_height), baseline = cv2.getTextSize(posS, font, font_scale, font_thickness)

    # Draw text
    cv2.putText(frame, posS, (locObjX + locObjWidth, locObjY + text_height), font, font_scale, yellow, font_thickness, lineType=cv2.LINE_AA)


    ######## draw the central object ###########







class ObjectTracker:
    def __init__(self):
        self.locObjHeight = 60
        self.locObjWidth = 60
        self.locObjX = 0
        self.locObjY = 0
        self.locObjImage = None
        self.match_method = cv2.TM_CCOEFF_NORMED       
        self.deviationX = 0
        self.deviationY = 0
        self.startX = 0
        self.startY = 0
        self.resetX = 0
        self.resetY = 0
        self.centerX = 0
        self.centerY = 0
        self.samples_analysis = []
        self.avg_frame = 0
        self.max_frame = 0

    def analyze_img(self, frame, draw_analysisspeed_counter):
        start = time.time()

        if self.initial_run:
            self.locObjY = self.startY = self.resetY
            self.locObjX = self.startX = self.resetX
            self.deviationX = 0 
            self.deviationY = 0
            self.locObjImage = frame[self.locObjY:self.locObjY + self.locObjHeight, self.locObjX:self.locObjX + self.locObjWidth]
        
        else:
            result = cv2.matchTemplate(frame, self.locObjImage, self.match_method)
            min_val, max_val, min_loc, max_loc = cv2.minMaxLoc(result)
            if self.match_method in [cv2.TM_SQDIFF, cv2.TM_SQDIFF_NORMED]:
                self.locObjX, self.locObjY = min_loc
            else:
                self.locObjX, self.locObjY = max_loc


        ###### Sending output to the database (self.locObjX, self.locObjY)

        update_query = "UPDATE rtvirtualiovalue SET actualValue = %s WHERE iocFunId = %s AND iocFunIOIndex = %s"
        
        values_x = (str(self.locObjX - self.startX), str(2200), str(0))
        values_y = (str(self.locObjY - self.startY), str(2200), str(1))

        self.cursor.execute(update_query, values_x)
        self.cursor.execute(update_query, values_y)

        self.connection.commit()




        end = time.time()

        analysis_time = end - start
        self.samples_analysis.append(analysis_time)

        if draw_analysisspeed_counter % 20 == 0: 
            self.avg_frame = mean(self.samples_analysis)
            self.max_frame = max(self.samples_analysis)
            self.samples_analysis = []


        yellow = (0, 255, 255)
        font = cv2.FONT_HERSHEY_SIMPLEX
        font_scale = 0.5
        font_thickness = 1

        cv2.rectangle(frame, (self.locObjX, self.locObjY), (self.locObjX + self.locObjWidth, self.locObjY + self.locObjHeight), yellow, 2)
        posS = f'[{int(self.locObjX - self.startX)}; {int(self.locObjY - self.startY)}]'
        (text_width, text_height), baseline = cv2.getTextSize(posS, font, font_scale, font_thickness)
        cv2.putText(frame, posS, (self.locObjX + self.locObjWidth, self.locObjY + text_height), font, font_scale, yellow, font_thickness, lineType=cv2.LINE_AA)
        cv2.putText(frame, f'analysis time: average{int(self.avg_frame*1000)}ms  max{int(self.max_frame*1000)}ms', (0, int(frame.shape[0] * 0.96)), font, font_scale, yellow, font_thickness, lineType=cv2.LINE_AA)

        self.initial_run = False


    def set_click_coords(self, event, x, y, flags, param):
        if event == cv2.EVENT_LBUTTONDOWN:
            print(f'x,y deviation from the center of the screen: [{x - self.centerX}; {y - self.centerY}]')
            self.initial_run = True
            self.resetX = x
            self.resetY = y

    def setup(self, frame):
        self.resetY = self.centerY = (frame.shape[0] // 2) - (self.locObjHeight // 2)
        self.resetX = self.centerX = (frame.shape[1] // 2) - (self.locObjWidth // 2)
        self.initial_run = True



def main():
    tracker = ObjectTracker()

    # Establish connection with database
    connection = mysql.connector.connect(
        host='localhost',          
        user='root',      
        password='mysqlpassword',  
        database='dcuusercommconfig'   
    )

    # Create a cursor object to interact with the database
    cursor = connection.cursor()
    tracker.connection = connection
    tracker.cursor = cursor

    
    # Open the integrated camera video stream
    cap = cv2.VideoCapture(0)
    cv2.namedWindow("Camera Stream")
    cv2.setMouseCallback("Camera Stream", tracker.set_click_coords)

    beginning = True
    running = True
    draw_analysisspeed_counter = 0
    draw_framespeed_counter = 0
    samples_frame_grab = []
    max_frame = 0
    avg_frame = 0



    while running:
        # grabs a frame
        frame, frame_receival_speed = grab_frame(cap)
        draw_framespeed_counter = draw_framespeed_counter + 1
        samples_frame_grab.append(frame_receival_speed)

        # READING input from database

        # Step 1: Write a query to fetch the 4th column where 1st column = 1200 and 3rd column = 0
        query = "SELECT actualValue FROM rtvirtualiovalue WHERE iocFunId = %s AND iocFunIOIndex = %s LIMIT 1"

        # Step 2: Set the values for the placeholders (1200 for column_1 and 0 for column_3)
        values = (1200, 0)

        # Step 3: Execute the query
        cursor.execute(query, values)
        
        # Step 4: Retrieve the tuple value
        signal = cursor.fetchone()
    

        if signal[0] == "1.0":
            if beginning:
                tracker.setup(frame)
                beginning = False
            
            draw_analysisspeed_counter += 1 
            tracker.analyze_img(frame, draw_analysisspeed_counter)
        
        else:
            draw_analysisspeed_counter = 0
            beginning = True


        
        # displays the frame and elapsed time of grabing a frame 
        if draw_framespeed_counter % 20 == 0: 
            avg_frame = mean(samples_frame_grab)
            max_frame = max(samples_frame_grab)
            samples_frame_grab = []

        
        cv2.putText(frame, f'frame grab: average{int(avg_frame*1000)}ms  max{int(max_frame*1000)}ms', (0, int(frame.shape[0] * 0.91)), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 255), 1, lineType=cv2.LINE_AA)
        cv2.imshow("Camera Stream", frame)
        if cv2.waitKey(1) & 0xFF == ord('q'):
            running = False


    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()