import tkinter as tk
from tkinter import messagebox

class MyApp:
    def __init__(self):
        self.window = tk.Tk()
        self.window.title("MySQL and IP Camera Setup")
        self.window.geometry("350x250")
        self.close = False

        self.setup_input_field()
        self.window.mainloop()

    def setup_input_field(self):
        # Clear previous widgets
        for widget in self.window.winfo_children():
            widget.destroy()

        # Create a label
        self.label = tk.Label(self.window, text="Enter details:")
        self.label.pack(pady=10)

        # Create input field
        self.input_field = tk.Entry(self.window)
        self.input_field.pack(pady=10)

        # Create a submit button
        self.submit_button = tk.Button(self.window, text="Submit", command=self.handle_submit)
        self.submit_button.pack(pady=10)

    def handle_submit(self):
        user_input = self.input_field.get()
        # Simulate checking the input; replace this with your actual validation logic
        if user_input != "expected_value":
            messagebox.showerror("Error", "Wrong details. Please try again.")
            self.setup_input_field()  # Reset the input field
        else:
            self.window.destroy()

if __name__ == "__main__":
    app = MyApp()
