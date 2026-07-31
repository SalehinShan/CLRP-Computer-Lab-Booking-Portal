# CLRP Local Testing & Setup Guide

This guide explains how to set up and run the **Computer Laboratory Resource Portal (CLRP)** on your local machine for development and testing.

---

## Prerequisites

To run this project, you need:
1. **PHP** (v7.4 or higher recommended)
2. **MySQL / MariaDB** server (e.g., via XAMPP, MAMP, Laragon, or standalone installation)

---

## Getting Started

### Step 1: Clone or Copy the Project
Ensure the project files are located in your web server's root directory (e.g., `htdocs` for XAMPP, `www` for WAMP) or any local workspace directory.

### Step 2: Configure Database Settings
Open [config/db.php](file:///Users/tawhid/Desktop/Shan/CLRP-Computer-Lab-Booking-Portal/config/db.php) and adjust your database connection credentials if necessary:
```php
$host = '127.0.0.1';
$dbname = 'clrp_db';
$username = 'root'; // Change if your MySQL user is different
$password = '';     // Change to your MySQL password
```

> [!NOTE]
> **Auto-Provisioning Feature**: You do **not** need to manually create the database or run SQL scripts. When you run the application for the first time, it checks if `clrp_db` exists. If not, it automatically runs the [clrp.sql](file:///Users/tawhid/Desktop/Shan/CLRP-Computer-Lab-Booking-Portal/clrp.sql) schema and seeds it with default data.

### Step 3: Run the Web Server

#### Option A: Using the PHP Built-in Server (Recommended for Quick Testing)
1. Open your terminal in the project root directory.
2. Run the following command:
   ```bash
   php -S localhost:8000
   ```
3. Open your browser and navigate to `http://localhost:8000`.

#### Option B: Using XAMPP / MAMP
1. Move the project folder into your server's document root (e.g. `/Applications/XAMPP/xamppfiles/htdocs/` or `C:\xampp\htdocs\`).
2. Start the **Apache** and **MySQL** services from the Control Panel.
3. Open your browser and navigate to `http://localhost/CLRP-Computer-Lab-Booking-Portal`.

---

## Test Accounts & Roles

Once the portal is running, you can log in using any of the following seeded user credentials:

### 1. System Administrator
*   **Email**: `admin.sys@northsouth.edu`
*   **Password**: `password123`
*   **Use Cases**: Manage users, labs, computers, software catalog, map software, and approve/reject bookings.

### 2. Lab Technician
*   **Email**: `kamrul.hasan@northsouth.edu`
*   **Password**: `password123`
*   **Use Cases**: View maintenance queue, claim pending tickets, and update system repair statuses.

### 3. Student
*   **Email**: `abu.shan.241@northsouth.edu`
*   **Password**: `password123`
*   **Use Cases**: Browse lab computer availability, submit reservation requests, and file maintenance reports.

---

## Verifying Features

*   **Database connection**: If the page loads without SQL errors and shows the login interface, the connection is successful and the database has auto-seeded.
*   **Role Redirect**: Logging in with any of the accounts above should redirect you to the corresponding dashboard ([admin/dashboard.php](file:///Users/tawhid/Desktop/Shan/CLRP-Computer-Lab-Booking-Portal/admin/dashboard.php), [technician/dashboard.php](file:///Users/tawhid/Desktop/Shan/CLRP-Computer-Lab-Booking-Portal/technician/dashboard.php), or [student/dashboard.php](file:///Users/tawhid/Desktop/Shan/CLRP-Computer-Lab-Booking-Portal/student/dashboard.php)).
