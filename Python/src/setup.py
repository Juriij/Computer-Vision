import tkinter as tk
from tkinter import messagebox


class LoginDetailsWindow:
    def __init__(self):
        self.window = tk.Tk()
        self.window.title("MySQL and IP Camera Setup")
        self.window.geometry("350x250")


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

    #Disable the close button (X button) to enforce validation
    def on_closing(self):
        messagebox.showwarning("Warning", "Please complete all fields before closing.")




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

    # Start the Tkinter event loop
        self.window.mainloop()

def func(host):

    print(host)

obj = LoginDetailsWindow()
obj.login_details()
func(obj.mysql_host)