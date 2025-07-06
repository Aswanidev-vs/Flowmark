# Flowmark - To-Do List Web Application

Flowmark is a PHP-based To-Do List web application that allows users to sign up, log in, manage tasks, and update their profiles. It features task CRUD operations, password management, and a modern, responsive UI.

## Features

- **User Authentication:** Sign up, log in, log out, and session management.
- **Task Management:** Add, view, update, delete, and filter tasks by name or status.
- **Password Management:** Change password (while logged in) and reset password (via email).
- **Profile Management:** Update username from the settings panel.
- **Voice Features:** Add or update task descriptions using speech-to-text and listen to descriptions with text-to-speech.
- **Responsive Design:** Works well on desktop and mobile devices.

## Folder Structure

```
.
├── Todo.php                # Main dashboard (task list, filtering, settings)
├── task.php                # Add new task (with voice features)
├── update.php              # Edit existing task (with voice features)
├── viewtask.php            # View task details
├── delete.php              # Delete a task
├── display.php             # (Legacy) Display all tasks (not user-specific)
├── signup.html             # Signup form (frontend)
├── signup.php              # Signup logic (backend)
├── login.html              # Login form (frontend)
├── login.php               # Login logic (backend)
├── logout.php              # Logout and session destroy
├── home.html               # Public landing page
├── home.php                # Simple PHP home (shows session email)
├── forgot_password.php     # Forgot password form
├── reset_password.php      # Reset password logic (checks email)
├── update_password.php     # Form to set new password (after reset)
├── save_new_password.php   # Save new password after reset
├── change_password.php     # Change password (while logged in)
├── cpass.php               # Form and logic for changing password
├── update_user.php         # Update username (from settings)
├── config.php              # Database connection
├── styles.css              # Login/signup styles
├── todo.css                # Main app styles
├── todo.js                 # JS for settings panel
├── img1.jpg ... img5.jpg   # Images for home page slider
├── logo.jpg                # App logo
├── settings.png            # Settings icon
└── .vscode/                # VSCode config
```

## Database Structure

- **login**: Stores user info (`uid`, `username`, `email`, `pwd`)
- **task**: Stores tasks (`taskid`, `taskname`, `description`, `status`, `user_id`, `created_at`, `updated_at`)

## Setup Instructions

1. **Clone or Download** this repository to your XAMPP `htdocs` directory.
2. **Database:**
   - Create a MySQL database named `todo`.
   - Create tables `login` and `task` as per the fields above.
3. **Configure Database:**
   - Edit [`config.php`](config.php) if your MySQL credentials differ.
4. **Start XAMPP** and ensure Apache & MySQL are running.
5. **Access the App:**
   - Open [http://localhost/todov2/todo/home.html](http://localhost/todov2/todo/home.html) in your browser.

## Usage

- **Sign Up:** Create a new account via the signup page.
- **Log In:** Access your to-do dashboard.
- **Add/Edit Tasks:** Use the plus button or edit icons. Voice features are available for descriptions.
- **Filter Tasks:** Use the filter form to search by name or status.
- **Profile & Password:** Click the settings icon (top right) to update your username or change your password.
- **Forgot Password:** Use the "Forgot Password?" link on the login page.

## Security Notes

- Passwords are stored in plaintext in the current implementation. **For production, always hash passwords** (e.g., using `password_hash()` and `password_verify()` in PHP).
- SQL queries are not parameterized. **Use prepared statements** to prevent SQL injection.

## Credits

- UI design: Custom CSS
- Voice features: Web Speech API (browser support required)

---

**Project files:**  
- [Todo.php](Todo.php)  
- [task.php](task.php)  
- [update.php](update.php)  
- [viewtask.php](viewtask.php)  
- [delete.php](delete.php)  
- [signup.html](signup.html)  
- [signup.php](signup.php)  
- [login.html](login.html)  
- [login.php](login.php)  
- [logout.php](logout.php)  
- [home.html](home.html)  
- [forgot_password.php](forgot_password.php)  
- [reset_password.php](reset_password.php)  
- [update_password.php](update_password.php)  
- [save_new_password.php](save_new_password.php)  
- [change_password.php](change_password.php)  
- [cpass.php](cpass.php)  
- [update_user.php](update_user.php)  
- [config.php](config.php)  
- [styles.css](styles.css)  
- [todo.css](todo.css)  
- [todo.js](todo.js)
