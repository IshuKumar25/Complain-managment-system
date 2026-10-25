# College Complaint Management System

A simple web-based complaint management system built using PHP and MySQL. Students can submit complaints with photo or video evidence, while admins can review, reply, and update complaint status.

## Features

- Student registration and login
- Submit complaints by category
- Upload photo or video as evidence
- Track complaint status
- Admin dashboard to manage complaints
- Admin can update status and add replies

## Technologies Used

- PHP
- MySQL
- HTML
- CSS
- Bootstrap
- JavaScript

## Setup Instructions

1. Install XAMPP.
2. Copy the project folder to `htdocs`.
3. Start Apache and MySQL.
4. Open phpMyAdmin and import `complaint_db.sql`.
5. Open the project in your browser:

```text
http://localhost/complaint-system/
```

## Admin Access

1. Register a normal user.
2. Open phpMyAdmin and run:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

3. Login and open `admin.php`.

## Project Structure

```text
complaint-system/
├── config.php
├── index.php
├── admin.php
├── complaint_db.sql
└── uploads/
```
