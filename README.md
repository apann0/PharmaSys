# 💊 PharmaSys – Pharmacy Management System

A modern, secure, and production-ready Pharmacy Management System built with **PHP**, **MySQL**, and a **Glassmorphism UI**. Designed for small-to-medium pharmacies to manage inventory, track finances, and generate reports.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| **Dashboard** | Overview of total stock, today's income/expenses, alerts, and recent transactions |
| **Medicine CRUD** | Add, edit, delete medicines with category, stock, price, and expiry tracking |
| **Sales Recording** | Record medicine sales with automatic stock deduction |
| **Expense Tracking** | Track medicine purchases and operational costs |
| **Financial Reports** | Daily/monthly income & expense summaries with filtering |
| **Authentication** | Secure login with `password_hash()` and session management |
| **Audit Logging** | All critical actions are logged with user ID, action, and IP address |

## 🔒 Security Features

- ✅ **SQL Injection Prevention** – All queries use PDO Prepared Statements
- ✅ **XSS Protection** – All outputs sanitized with `htmlspecialchars()`
- ✅ **CSRF Protection** – Token-based form validation
- ✅ **Session Security** – Timeout, regeneration, and fixation prevention
- ✅ **Anti-Duplicate** – Idempotency tokens prevent double-entry on refresh
- ✅ **Server-side Validation** – Robust input validation (stock ≥ 0, required fields, etc.)
- ✅ **Brute-force Mitigation** – Login delay on failed attempts

## 🎨 UI/UX

- **Glassmorphism / Liquid Glass** aesthetic with frosted glass cards and sidebar
- Animated gradient background orbs
- Fully **responsive** – works on mobile, tablet, and desktop
- Clean data tables with color-coded badges (stock alerts, expiry warnings)
- Smooth micro-animations and transitions

---

## 📁 Folder Structure

```
pharmacy-management-system/
├── assets/
│   ├── css/style.css          # Glassmorphism design system
│   ├── js/main.js             # Client-side interactivity
│   └── img/                   # Static images
├── config/
│   └── database.php           # PDO connection & helper functions
├── includes/
│   ├── header.php             # Global header with sidebar
│   ├── footer.php             # Footer with delete modal
│   └── auth_check.php         # Authentication guard
├── modules/
│   ├── auth/
│   │   ├── login.php          # Login page
│   │   └── logout.php         # Logout handler
│   ├── medicine/
│   │   ├── list.php           # Medicine listing (search + pagination)
│   │   ├── add.php            # Add new medicine
│   │   ├── edit.php           # Edit medicine
│   │   └── delete.php         # Delete medicine
│   └── finance/
│       ├── inflow.php         # Record sales
│       ├── outflow.php        # Record expenses/purchases
│       └── report.php         # Financial reports
├── index.php                  # Dashboard
├── database.sql               # Database schema + seed data
├── README.md
└── LICENSE                    # MIT License
```

---

## 🚀 Installation

### Prerequisites

- **PHP** 8.0 or higher
- **MySQL** 5.7+ or **MariaDB** 10.3+
- **Apache** or **Nginx** web server (or use XAMPP/Laragon)

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/pharmacy-management-system.git
   cd pharmacy-management-system
   ```

2. **Create the database**
   ```bash
   mysql -u root -p < database.sql
   ```
   Or import `database.sql` via phpMyAdmin.

3. **Configure database credentials**
   
   Edit `config/database.php` and update the constants:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'pharmacy_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Set up the web server**
   
   Point your web server's document root to the project folder, or place it inside `htdocs` (XAMPP) or `www` (Laragon).

5. **Access the application**
   
   Open `http://localhost/pharmacy-management-system/` in your browser.

6. **Login with default credentials**
   ```
   Username: admin
   Password: admin123
   ```

> ⚠️ **Important:** Change the default password after first login in a production environment.

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0+ (Procedural + OOP helpers) |
| Database | MySQL / MariaDB with PDO |
| Frontend | HTML5, CSS3 (Custom Glassmorphism), Vanilla JS |
| Security | Prepared Statements, CSRF tokens, XSS sanitization |

---

## 📋 Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Authentication credentials and roles |
| `categories` | Medicine categories |
| `medicines` | Core inventory data |
| `transactions` | Financial records (sales & expenses) |
| `logs` | Audit trail for all critical actions |

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License** – see the [LICENSE](LICENSE) file for details.

---

<p align="center">
  Built with ❤️ for pharmacy professionals
</p>
