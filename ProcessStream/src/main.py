import cv2
import time
import mysql.connector
from statistics import mean
import tkinter as tk
from tkinter import messagebox
import ffmpeg
import numpy as np


# Classes

# Tracker that can follow a given point
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
    

    # Main method for tracking
    def analyze_img(self, frame, draw_analysisspeed_counter):
        start = time.time()

        if self.initial_run:
            self.locObjY = self.startY = self.resetY
            self.locObjX = self.startX = self.resetX
            self.deviationX = 0 
            self.deviationY = 0
            self.locObjImage = frame[self.locObjY:self.locObjY + self.locObjHeight, self.locObjX:self.locObjX + self.locObjWidth]
       
        # Scans the image and tracks the given point
        else:
            result = cv2.matchTemplate(frame, self.locObjImage, self.match_method)
            min_val, max_val, min_loc, max_loc = cv2.minMaxLoc(result)
            if self.match_method in [cv2.TM_SQDIFF, cv2.TM_SQDIFF_NORMED]:
                self.locObjX, self.locObjY = min_loc
            else:
                self.locObjX, self.locObjY = max_loc


        # Sending output to the database (self.locObjX, self.locObjY)
        update_query = "UPDATE rtvirtualiovalue SET actualValue = %s WHERE iocFunId = %s AND iocFunIOIndex = %s"     
        values_x = (str(self.locObjX - self.startX), str(1200), str(0))
        values_y = (str(self.locObjY - self.startY), str(1200), str(1))
        self.cursor.execute(update_query, values_x)
        self.cursor.execute(update_query, values_y)
        self.connection.commit()


        end = time.time()

         
        analysis_time = end - start
        self.samples_analysis.append(analysis_time)
        
        # Average and Maximum time it takes to analyze 20 frames (miliseconds)
        if draw_analysisspeed_counter % 20 == 0: 
            self.avg_frame = mean(self.samples_analysis)
            self.max_frame = max(self.samples_analysis)
            self.samples_analysis = []

        # Visualization 
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


    # Sesets the point to follow, based on a mouse click
    def set_click_coords(self, event, x, y, flags, param):
        if event == cv2.EVENT_LBUTTONDOWN:
            self.initial_run = True
            self.resetX = x
            self.resetY = y
    
    def setup(self, frame):
        self.resetY = self.centerY = (frame.shape[0] // 2) - (self.locObjHeight // 2)
        self.resetX = self.centerX = (frame.shape[1] // 2) - (self.locObjWidth // 2)
        self.initial_run = True


# Input field window
class LoginDetailsWindow:
    def __init__(self):
        self.window = tk.Tk()
        self.window.title("MySQL and IP Camera Setup")
        self.window.geometry("350x250")
        self.close = False


    def submit(self):
        # Fetch the data from the entry fields
        self.mysql_host = self.host_entry.get()
        self.mysql_user = self.user_entry.get()
        self.mysql_password = self.password_entry.get()
        self.mysql_database = self.database_entry.get()
        self.camera_url = self.ip_camera_url_entry.get()

        # Validate that all fields are filled
        if not self.mysql_host or not self.mysql_user or not self.mysql_password or not self.mysql_database or not self.camera_url:
            messagebox.showerror("Input Error", "All the fields have to be filled!")
            
        else:
            self.window.destroy()
            self.window.quit()  # Close the window after successful submission

    def on_closing(self):
        if messagebox.askokcancel("Quit", "Do you want to quit?"):
            self.window.destroy()
            self.window.quit()
            self.close = True
        else:
            self.close = False


    def login_details(self):
        #Create labels and entry fields
        tk.Label(self.window, text="MySQL Host:").grid(row=0, column=0, padx=10, pady=5, sticky="e")
        self.host_entry = tk.Entry(self.window)
        self.host_entry.grid(row=0, column=1, padx=10, pady=5)

        tk.Label(self.window, text="MySQL User:").grid(row=1, column=0, padx=10, pady=5, sticky="e")
        self.user_entry = tk.Entry(self.window)
        self.user_entry.grid(row=1, column=1, padx=10, pady=5)

        tk.Label(self.window, text="MySQL Password:").grid(row=2, column=0, padx=10, pady=5, sticky="e")
        self.password_entry = tk.Entry(self.window, show="*")  # Hide the password characters
        self.password_entry.grid(row=2, column=1, padx=10, pady=5)

        tk.Label(self.window, text="Database Name:").grid(row=3, column=0, padx=10, pady=5, sticky="e")
        self.database_entry = tk.Entry(self.window)
        self.database_entry.grid(row=3, column=1, padx=10, pady=5)

        tk.Label(self.window, text="IP Camera URL:").grid(row=4, column=0, padx=10, pady=5, sticky="e")
        self.ip_camera_url_entry = tk.Entry(self.window)
        self.ip_camera_url_entry.grid(row=4, column=1, padx=10, pady=5)

        # Create the "OK" button
        ok_button = tk.Button(self.window, text="OK", command=self.submit)
        ok_button.grid(row=5, columnspan=2, pady=20)

        self.window.protocol("WM_DELETE_WINDOW", self.on_closing)
        

        

        # Start the Tkinter event loop
        self.window.mainloop()
        return self.close





# Loop that runs the video stream
def video_stream(win, tracker, close):

    # Establish connection with database
    connection = mysql.connector.connect(
        host=win.mysql_host,          
        user=win.mysql_user,      
        password=win.mysql_password,  
        database=win.mysql_database   
    )

    # Create a cursor object to interact with the database
    cursor = connection.cursor()
    tracker.connection = connection
    tracker.cursor = cursor

    cv2.namedWindow("Camera Stream")
    cv2.setMouseCallback("Camera Stream", tracker.set_click_coords)

    
    beginning = True
    running = True
    draw_analysisspeed_counter = 0
    draw_framespeed_counter = 0
    samples_frame_grab = []
    max_frame = 0
    avg_frame = 0
    rtsp_url = win.camera_url

    # if the image resolution is increased, the fps may need to be lowered 
    # to avoid delays in real-time stream
    # original resolution:
    # frame_width = 1280
    # frame_height = 720

    frame_width = 640
    frame_height = 480
    fps = 15

    # reads the frames from the rtsp stream using ffmpeg
    process = (
        ffmpeg
        .input(rtsp_url, rtsp_transport='udp', f='rtsp', fflags='nobuffer', threads=1)      
        .output('pipe:', format='rawvideo', pix_fmt='bgr24', r=fps, s=f'{frame_width}x{frame_height}')
        .run_async(pipe_stdout=True)
    )



    while running:

        start = time.time()

        in_bytes = process.stdout.read(frame_width * frame_height * 3) 
        if not in_bytes:
            break
        frame = np.frombuffer(in_bytes, np.uint8).reshape([frame_height, frame_width, 3]).copy()

        end = time.time()
        frame_receival_speed = end - start



        draw_framespeed_counter = draw_framespeed_counter + 1
        samples_frame_grab.append(frame_receival_speed)


        # READING input from database

        # Step 1: Write a query to fetch the 4th column where 1st column = 2200 and 3rd column = 0
        query = "SELECT actualValue FROM rtvirtualiovalue WHERE iocFunId = %s AND iocFunIOIndex = %s LIMIT 1"

        # Step 2: Set the values for the placeholders (2200 for column_1 and 0 for column_3)
        values = (2200, 0)

        # Step 3: Execute the query
        cursor.execute(query, values)
        
        # Step 4: Retrieve the tuple value
        signal = cursor.fetchone()
    
        
        # Turns on/off the tracking, based on the input from a database. "0.0"-off "1.0"-on
        if signal[0] == "1.0":
            if beginning:
                tracker.setup(frame)
                beginning = False
            
            draw_analysisspeed_counter += 1 
            tracker.analyze_img(frame, draw_analysisspeed_counter)  # tracking function
        
        else:
            draw_analysisspeed_counter = 0
            beginning = True


        
        # Average and Maximum time it takes to grab 20 frames (miliseconds)
        if draw_framespeed_counter % 20 == 0: 
            avg_frame = mean(samples_frame_grab)
            max_frame = max(samples_frame_grab)
            samples_frame_grab = []

        # Displaying speed info 
        cv2.putText(frame, f'frame grab: average{int(avg_frame*1000)}ms  max{int(max_frame*1000)}ms', (0, int(frame.shape[0] * 0.91)), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 255), 1, lineType=cv2.LINE_AA)

        # Showing the frame
        cv2.imshow("Camera Stream", frame)

        # If "X" button has been clicked, shut down the stream
        keyCode = cv2.waitKey(1)
        if cv2.getWindowProperty("Camera Stream", cv2.WND_PROP_VISIBLE) <1:
            running = False
        
        # If "q" key has been clicked, shut down the stream
        if cv2.waitKey(1) & 0xFF == ord('q'):
            running = False

    if not close:
        cv2.destroyAllWindows()




        
# Main loop that manages the code flow
def main():
    global run
    tracker = ObjectTracker()

    while run:
        win = LoginDetailsWindow()
        close = win.login_details()

        try:
            video_stream(win, tracker, close)
            run = False
        
        except Exception as error:
            if close:
               run = False

            else:
                messagebox.showerror("Error Description", error)



run = True

if __name__ == "__main__":
     main()
