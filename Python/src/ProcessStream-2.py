import cv2
import sys
import time
import numpy as np
import ctypes


# Functions

def grab_frame(stream):             # -> only for built-in camera video stream
    ret, frame = stream.read()
       
    if not ret:
        sys.exit("Error: Could not read frame.")
     
    return frame



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





def analyze_img_opt(frame):
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
        locObjImage = frame[locObjY:locObjY + locObjHeight, locObjX:locObjX + locObjWidth].copy()
        
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

        locObjX, locObjY = bestX, bestY

        # Update the stored object image with the best match found
        if bestError > 0:
            locObjImage = frame[locObjY: locObjY + locObjHeight, locObjX: locObjX + locObjWidth].copy()


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







# Variable Establishment

initial_run = True
running = True
cap = cv2.VideoCapture(0)


# While Loop

while running:                         
    frame = grab_frame(cap)

    #### VARIABLE "pixels"

    buffer = frame.ctypes.data_as(ctypes.POINTER(ctypes.c_ubyte))
    if 'pixels' not in locals():
        pixels = np.zeros_like(frame, dtype=np.int64)
        pixels = pixels.flatten()
    np.copyto(pixels, buffer.contents)


    analyze_img_opt(frame)

    cv2.imshow("Image", frame)
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break



cap.release()
cv2.destroyAllWindows()