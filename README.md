# CLRP — Computer Laboratory Resource Portal

A web-based **Computer Laboratory Resource Portal** developed for **CSE311 — Database Management Systems**. The system manages laboratory resources, users, software, and computer bookings through a centralized database.

## Features

* 🔐 User authentication & role-based access
* 👨‍🎓 Student dashboard and booking
* 🛠️ Technician resource management
* 👨‍💼 Administrator management
* 💻 Computer & software management
* 🗄️ MySQL database integration
* 🔒 Password hashing, CSRF protection & secure sessions

## Technologies

* **Backend:** PHP
* **Database:** MySQL
* **Frontend:** HTML, CSS, JavaScript, Bootstrap
* **Environment:** XAMPP / phpMyAdmin
* **Version Control:** Git & GitHub

## Setup

1. Clone the repository:

```bash
git clone https://github.com/SalehinShan/CLRP-Computer-Lab-Booking-Portal.git
```

2. Place the project inside XAMPP's `htdocs` folder.
3. Start **Apache** and **MySQL**.
4. Import `clrp.sql` into phpMyAdmin.
5. Configure your local `.env` using `.env.example`.
6. Open:

```text
http://localhost/CLRP-Computer-Lab-Booking-Portal/
```

## Project Structure

```text
admin/       → Administrator features
student/     → Student features
technician/  → Technician features
config/      → Database configuration
includes/    → Authentication & security
views/       → Frontend views
clrp.sql     → Database schema & sample data
```

## Author

**Salehin Shan**
[GitHub Repository](https://github.com/SalehinShan/CLRP-Computer-Lab-Booking-Portal)

> Academic project for **CSE311 — Database Management Systems**.
