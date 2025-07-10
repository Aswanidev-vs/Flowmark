# 📋 Flowmark - PHP To-Do List App

**Flowmark** is a responsive, user-friendly To-Do List web application built using PHP. It includes robust task management, user authentication, voice-enabled input, and account settings features — all wrapped in a clean, mobile-first UI.

---

## 🚀 Features

- ✅ **User Authentication** (Login, Signup, Logout)
- 🗂️ **Task Dashboard** (Add, View, Edit, Delete, Filter Tasks)
- 🗣️ **Voice Support**  
  Use **speech-to-text** to add/edit tasks and **text-to-speech** to listen to them.
- 🔐 **Account Management**  
  Change username, update or reset password, and delete your account securely.
- 📱 **Responsive UI**  
  Clean layout that works well on desktops, tablets, and mobile devices.

---

## 📁 Project Structure

```bash
todov2/
├── home.html                     # ✅ Entry point (public landing page)
├── config/
│   └── config.php
├── public/
│   └── assets/
│       ├── css/
│       │   ├── styles.css
│       │   └── todo.css
│       ├── js/
│       │   └── todo.js
│       └── images/
│           ├── img1.jpg ... img5.jpg
│           ├── logo.jpg
│           └── settings.png
├── auth/
│   ├── login.html
│   ├── login.php
│   ├── signup.html
│   ├── signup.php
│   ├── logout.php
│   └── home.php
├── password/
│   ├── forgot_password.php
│   ├── reset_password.php
│   ├── update_password.php
│   ├── save_new_password.php
│   ├── change_password.php
│   └── cpass.php
├── account/
│   ├── update_user.php
│   ├── delete_account.php
│   └── current_password.php
├── tasks/
│   ├── Todo.php
│   ├── task.php
│   ├── update.php
│   ├── viewtask.php
│   ├── delete.php
│   └── display.php
└── .vscode/
```
## 🛠️ Setup Instructions

### 1. Clone or Download
Clone or download this repo into your `htdocs/` folder (if using XAMPP).

### 2. Database Setup
- Open [phpMyAdmin](http://localhost/phpmyadmin)
- Create a database named `todo`
- Create the following tables:

### Table: `login`

| Field     | Type    | Description          |
|-----------|---------|----------------------|
| uid       | INT     | Primary Key          |
| username  | VARCHAR | User's display name  |
| email     | VARCHAR | Email ID (unique)    |
| pwd       | VARCHAR | Password (plaintext 🔴) |

### Table: `task`

| Field        | Type     | Description                  |
|--------------|----------|------------------------------|
| taskid       | INT      | Primary Key                  |
| taskname     | VARCHAR  | Task title                   |
| description  | TEXT     | Task details                 |
| status       | VARCHAR  | "pending" or "done"          |
| user_id      | INT      | Foreign Key to `login.uid`   |
| created_at   | DATETIME | Timestamp                    |
| updated_at   | DATETIME | Timestamp                    |

### 3. Configure Database
- Open [`config/config.php`](config/config.php)
- Update the database credentials to match your local MySQL setup

### 4. Run Application
- Start Apache and MySQL in XAMPP
- Visit the app in your browser:  
  👉 [http://localhost/todov2/home.html](http://localhost/todov2/home.html)

---

## 🔐 Security Notes

- ⚠️ **Passwords are stored in plain text.**  
  Use `password_hash()` and `password_verify()` in production.

- ⚠️ **SQL Queries are not parameterized.**  
  Use **prepared statements** to prevent SQL injection.

---

## 📂 Open Files (Click to View)

### 🔧 Core
- [`home.html`](home.html)
- [`config/config.php`](config/config.php)

### 🔐 Authentication
- [`auth/signup.html`](auth/signup.html)
- [`auth/signup.php`](auth/signup.php)
- [`auth/login.html`](auth/login.html)
- [`auth/login.php`](auth/login.php)
- [`auth/logout.php`](auth/logout.php)
- [`auth/home.php`](auth/home.php)

### 🗝️ Password Management
- [`password/forgot_password.php`](password/forgot_password.php)
- [`password/reset_password.php`](password/reset_password.php)
- [`password/update_password.php`](password/update_password.php)
- [`password/save_new_password.php`](password/save_new_password.php)
- [`password/change_password.php`](password/change_password.php)
- [`password/cpass.php`](password/cpass.php)

### 👤 Account Settings
- [`account/update_user.php`](account/update_user.php)
- [`account/delete_account.php`](account/delete_account.php)
- [`account/current_password.php`](account/current_password.php)

### ✅ Task Manager
- [`tasks/Todo.php`](tasks/Todo.php)
- [`tasks/task.php`](tasks/task.php)
- [`tasks/update.php`](tasks/update.php)
- [`tasks/viewtask.php`](tasks/viewtask.php)
- [`tasks/delete.php`](tasks/delete.php)
- [`tasks/display.php`](tasks/display.php)

### 🎨 Styles & Scripts
- [`public/assets/css/styles.css`](public/assets/css/styles.css)
- [`public/assets/css/todo.css`](public/assets/css/todo.css)
- [`public/assets/js/todo.js`](public/assets/js/todo.js)
