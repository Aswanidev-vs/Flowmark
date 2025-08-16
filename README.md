# 📋 Flowmark UI2

**UI2** is an alternate user interface for the Flowmark PHP To-Do List application. It provides all core features of Flowmark, including user authentication, task management, account settings, and password management, with a dedicated asset directory for styles, scripts, and images.

---

## 🚀 Features

- **User Authentication**: Login, Signup, Logout
- **Task Management**: Add, View, Edit, Delete, Filter Tasks
- **Account Settings**: Update user info, change or reset password, delete account
- **Password Management**: Forgot/reset password, change password
- **Chatbot**: Task-related chatbot support
- **Responsive UI**: Mobile-friendly design
- **Assets**: Custom CSS, JS, and images

---

## 📁 Folder Structure

```bash
ui2/
├── home.html
├── README.md
├── account/
│   ├── current_password.php
│   ├── delete_account.php
│   └── update_user.php
├── auth/
│   ├── about.html
│   ├── home.php
│   ├── login.html
│   ├── login.php
│   ├── logout.php
│   ├── signup.html
│   └── signup.php
├── config/
│   └── config.php
├── password/
│   ├── change_password.php
│   ├── cpass.php
│   ├── forgot_password.php
│   ├── reset_password.php
│   ├── save_new_password.php
│   └── update_password.php
├── public/
│   └── assets/
│       ├── css/
│       │   ├── styles.css
│       │   └── todo.css
│       ├── images/
│       │   ├── android-chrome-192x192.png
│       │   ├── checked.png
│       │   ├── checkmark.png
│       │   ├── img1.jpg ... img5.jpg
│       │   ├── logo.jpg
│       │   ├── profile.png
│       │   ├── settings.png
│       │   └── tick.png
│       ├── js/
│       │   └── todo.js
│       └── video/
│           └── tutorial.mp4
├── tasks/
│   ├── chatbot.php
│   ├── delete.php
│   ├── display.php
│   ├── task.php
│   ├── Todo.php
│   ├── update.php
│   └── viewtask.php
```

---

## 🛠️ Setup Instructions

1. **Configure Database**
   - Edit [`config/config.php`](ui2/config/config.php) with your MySQL credentials.

2. **Database Tables**
   - Use the following tables (see main project README for details):
     - `login`: Stores user accounts
     - `task`: Stores user tasks

3. **Run the App**
   - Place the `ui2` folder in your web server directory (e.g., `htdocs/` for XAMPP).
   - Access via browser:  
     `http://localhost/Flowmark/ui2/home.html`  
     or use the entry point in [`ui2.php`](../ui2.php).

---

## 🔐 Security Notes

- Passwords may be stored in plain text.  
  **Use `password_hash()` and `password_verify()` in production.**
- SQL queries may not be parameterized.  
  **Use prepared statements to prevent SQL injection.**

---

## 📂 Key Files

- **Entry Point:** [`home.html`](ui2/home.html)
- **Authentication:** [`auth/login.php`](ui2/auth/login.php), [`auth/signup.php`](ui2/auth/signup.php)
- **Task Manager:** [`tasks/Todo.php`](ui2/tasks/Todo.php)
- **Account Settings:** [`account/update_user.php`](ui2/account/update_user.php)
- **Password Management:** [`password/forgot_password.php`](ui2/password/forgot_password.php)
- **Assets:** [`public/assets/css/styles.css`](ui2/public/assets/css/styles.css), [`public/assets/js/todo.js`](ui2/public/assets/js/todo.js)

---

For more details, see the main [Flowmark README](../README.md)