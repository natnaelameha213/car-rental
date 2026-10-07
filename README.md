# EliteDrive Car Rental

> Car rental booking website with admin dashboard

**Live demo:** https://car-rentall.freedev.app

## Screenshots

![Home](docs/screenshots/home.png)
![Cars](docs/screenshots/cars.png)
![Admin Dashboard](docs/screenshots/admin-dashboard.png)

## Features

- Fleet listing with filters, car details and an availability engine that prevents overlapping bookings
- Customer accounts: register, login, profile, my bookings, forgot/reset password
- Admin dashboard: cars, bookings, customers, payments and deposits, invoices (printable), settings
- Dark charcoal and electric blue UI
- CSRF protection, PDO prepared statements, XSS-safe output

## Tech stack

PHP 8 · MySQL (PDO) · Bootstrap 5 · Font Awesome

## Demo login (local install)

| Role | Email / user | Password |
|---|---|---|
| Admin (at /admin/login.php) | `username: admin` | `password` |
| Customer | `abebe@email.com` | `password` |

These are the default credentials of the seed data. The live demo's admin password is private.

## Run locally (XAMPP)

1. Copy this folder to `C:\xampp\htdocs\car-rental`
2. Start **Apache** and **MySQL** in XAMPP
3. Open phpMyAdmin → **Import** → `database/car_rental.sql`
4. Check the database settings in `includes/db.php` (default: host `localhost`, user `root`, empty password)
5. Open `http://localhost/car-rental/`

### Deploying to shared hosting

Create an empty database in your hosting panel, delete the `CREATE DATABASE` / `USE` / `DROP DATABASE` lines at the top of `database/car_rental.sql`, import it, then put the host's database name, user and password in `includes/db.php`.

## Notes

Portfolio / demo project. Before real production use add HTTPS, rate limiting, audit logs, backups and environment-based configuration.

## Author

Built by [natnaelameha213](https://github.com/natnaelameha213).
