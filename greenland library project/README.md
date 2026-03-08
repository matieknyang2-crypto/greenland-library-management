# Greenland Secondary School Library Management System
## Complete Setup Guide

**Student:** Matik Nyang 667161  
**Instructor:** Fredrick Ogore  
**Date:** February 2026

---

## 📋 Table of Contents
1. [System Requirements](#system-requirements)
2. [Installation Steps](#installation-steps)
3. [Database Setup](#database-setup)
4. [Testing the System](#testing-the-system)
5. [File Structure](#file-structure)
6. [Default Login Credentials](#default-login-credentials)
7. [Troubleshooting](#troubleshooting)

---

## 🖥️ System Requirements

### Minimum Requirements:
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **PHP:** Version 7.4 or higher (PHP 8.0+ recommended)
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **Disk Space:** 100 MB minimum
- **RAM:** 512 MB minimum (1 GB recommended)

### Software Stack (Recommended):
- **XAMPP** (Windows/Linux) - easiest for development
- **LAMP** (Linux) or **WAMP** (Windows) or **MAMP** (Mac)

---

## 📦 Installation Steps

### Step 1: Install Web Server (Using XAMPP - Recommended for Beginners)

#### Windows:
1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer
3. Select Apache, MySQL, and PHP components
4. Install to `C:\xampp\` (default location)
5. Start Apache and MySQL from XAMPP Control Panel

#### Linux (Ubuntu/Debian):
```bash
# Install Apache, MySQL, and PHP
sudo apt update
sudo apt install apache2 mysql-server php php-mysql libapache2-mod-php

# Start services
sudo systemctl start apache2
sudo systemctl start mysql
```

#### Mac:
1. Download MAMP from https://www.mamp.info/
2. Install and launch MAMP
3. Start servers from MAMP control panel

---

### Step 2: Copy Project Files

#### For XAMPP (Windows):
1. Copy all project files to: `C:\xampp\htdocs\greenland_lms\`
2. Your structure should look like:
```
C:\xampp\htdocs\greenland_lms\
├── index.html (frontend)
├── config.php
├── api/
│   ├── dashboard.php
│   └── (other API files)
└── database/
    └── greenland_library_schema.sql
```

#### For Linux/Mac:
```bash
# Copy files to web root
sudo cp -r greenland_lms /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/greenland_lms
sudo chmod -R 755 /var/www/html/greenland_lms
```

---

## 🗄️ Database Setup

### Step 1: Access MySQL

#### Using XAMPP:
1. Open your browser
2. Go to: `http://localhost/phpmyadmin`
3. Username: `root`
4. Password: (leave blank)

#### Using Command Line:
```bash
# Linux/Mac
mysql -u root -p

# Windows (in XAMPP shell)
cd C:\xampp\mysql\bin
mysql -u root
```

### Step 2: Create Database

#### Option A: Using phpMyAdmin (Easiest)
1. Click "New" in the left sidebar
2. Database name: `greenland_library`
3. Collation: `utf8mb4_unicode_ci`
4. Click "Create"
5. Click "Import" tab
6. Choose file: `greenland_library_schema.sql`
7. Click "Go"
8. Wait for success message

#### Option B: Using Command Line
```bash
# Login to MySQL
mysql -u root -p

# Run the SQL file
source /path/to/greenland_library_schema.sql
```

OR copy-paste the entire SQL file content into the MySQL prompt.

### Step 3: Create Database User (Important for Security!)

```sql
-- Create user
CREATE USER 'greenland_user'@'localhost' IDENTIFIED BY 'GreenLib2026!';

-- Grant permissions
GRANT ALL PRIVILEGES ON greenland_library.* TO 'greenland_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

### Step 4: Update Config File

Open `config.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'greenland_library');
define('DB_USER', 'greenland_user');
define('DB_PASS', 'GreenLib2026!'); // Use the password you set above
```

---

## 🧪 Testing the System

### Step 1: Test Frontend
1. Open browser
2. Navigate to: `http://localhost/greenland_lms/index.html`
3. You should see the library dashboard

### Step 2: Test Database Connection
Create a test file: `test_connection.php`
```php
<?php
require_once 'config.php';
$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "✓ Database connection successful!<br>";
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM materials");
    $result = $stmt->fetch();
    echo "✓ Found " . $result['count'] . " books in database<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type='student'");
    $result = $stmt->fetch();
    echo "✓ Found " . $result['count'] . " students in database<br>";
    
    echo "<br>System is ready to use!";
} else {
    echo "✗ Connection failed!";
}
?>
```

Visit: `http://localhost/greenland_lms/test_connection.php`

### Step 3: Test API Endpoint
Visit: `http://localhost/greenland_lms/api/dashboard.php`

You should see JSON data with:
```json
{
    "success": true,
    "message": "Dashboard data retrieved successfully",
    "data": {
        "summary": {
            "total_books": 10,
            "total_students": 9,
            ...
        }
    }
}
```

---

## 📁 File Structure

```
greenland_lms/
├── index.html                          # Main frontend interface
├── config.php                          # Database configuration
├── test_connection.php                 # Connection test file
│
├── api/                                # API endpoints
│   ├── dashboard.php                   # Dashboard statistics
│   ├── books.php                       # Book management (to be created)
│   ├── students.php                    # Student management (to be created)
│   ├── circulation.php                 # Borrowing/returning (to be created)
│   ├── fines.php                       # Fine management (to be created)
│   └── reports.php                     # Reports generation (to be created)
│
├── database/
│   └── greenland_library_schema.sql    # Database structure + sample data
│
├── assets/                             # (Optional - for images/files)
│   ├── images/
│   └── uploads/
│
└── README.md                           # This file
```

---

## 🔐 Default Login Credentials

### Sample Users in Database:

| Role | Name | ID/Admission | Password (if implemented) |
|------|------|--------------|---------------------------|
| Admin | Head Librarian | LIB-001 | admin123 |
| Librarian | Jane Kiden | LIB-002 | librarian123 |
| Teacher | Fredrick Ogore | TCH-001 | teacher123 |
| Student | John Deng | S-1042 | student123 |

*Note: The current system doesn't have authentication implemented yet. This is for future development.*

---

## 🛠️ Troubleshooting

### Problem: "Database connection failed"

**Solution:**
1. Check if MySQL is running:
   - XAMPP: Open Control Panel, ensure MySQL is green
   - Linux: `sudo systemctl status mysql`
2. Verify credentials in `config.php`
3. Check if database exists:
   ```sql
   SHOW DATABASES;
   ```

### Problem: "Access denied for user"

**Solution:**
1. Reset MySQL password:
   ```bash
   # Linux
   sudo mysql
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'newpassword';
   FLUSH PRIVILEGES;
   ```
2. Update `config.php` with correct password

### Problem: "Can't find config.php"

**Solution:**
1. Check file location - should be in project root
2. Ensure `require_once` path is correct
3. Check file permissions (Linux/Mac):
   ```bash
   chmod 644 config.php
   ```

### Problem: "Page not found - 404"

**Solution:**
1. Check if Apache is running
2. Verify project folder is in correct location:
   - XAMPP: `C:\xampp\htdocs\`
   - Linux: `/var/www/html/`
3. Check URL: `http://localhost/greenland_lms/index.html`

### Problem: "JSON data not showing"

**Solution:**
1. Check PHP errors: Add to top of PHP file:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Check browser console (F12) for errors
3. Verify API endpoint URL in frontend code

---

## 🚀 Next Steps (Future Development)

1. **Authentication System**
   - Login/logout functionality
   - Password hashing (bcrypt)
   - Session management

2. **Complete CRUD Operations**
   - Add/Edit/Delete books
   - Register/Manage students
   - Process transactions

3. **Search & Filter**
   - Real-time search
   - Advanced filters
   - Barcode scanning

4. **Notifications**
   - Email reminders for due dates
   - SMS alerts for overdues
   - Reservation notifications

5. **Reports**
   - PDF generation
   - Excel exports
   - Donor impact reports

6. **Backup System**
   - Automated database backups
   - Data export/import
   - Recovery procedures

---

## 📞 Support

For issues or questions:
- **Student:** Matik Nyang (667161)
- **Instructor:** Fredrick Ogore
- **Email:** library@greenlandsecondary.edu.ss

---

## 📝 License

This project is developed for academic purposes as part of the Midterm Project for Semester 2026 at Greenland Secondary School.

---

**Last Updated:** February 2026  
**Version:** 1.0.0
