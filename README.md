STUDENT EVENT PASS & QR VERIFICATION SYSTEM
=============================================

Requirements:
- XAMPP with Apache
- XAMPP MySQL/MariaDB configured to port 3307
- PHP 8+ recommended

INSTALLATION
1. Extract this folder into your XAMPP htdocs directory.
   Example: C:\xampp\htdocs\StudentEventSystem
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin: http://localhost/phpmyadmin/
4. Create a database named: student_event_system
5. Click the database, open the SQL tab, and import/paste database.sql.
6. Check includes/db.php. The example uses MySQL port 3307, username root, blank password.
   If your XAMPP MySQL uses a different port or password, update that file.
7. Open: http://localhost/StudentEventSystem/

DEMO ADMIN SETUP
1. Visit http://localhost/StudentEventSystem/setup_admin.php once.
2. It creates an admin account only if one doesn't already exist:
   Email: admin@example.com
   Password: Admin@12345
3. Log in and immediately change/remove demo credentials before using this project.
4. DELETE setup_admin.php after setup.

FEATURES IN THIS STARTER
- Student registration and login
- Role-based dashboard (student/admin/staff)
- Admin event creation and listing
- Student event registration
- Random per-registration QR token (token is stored; QR image generation/scan verification
  requires the next implementation step and is not included in this starter zip)
- Staff attendance page can mark a registered token as checked in manually
- Bootstrap-based responsive UI

SECURITY NOTES
- This is a student project starter, not production-ready.
- Use HTTPS and secure deployment settings before exposing it publicly.
- Keep secrets out of source control.
- Never reuse the demo admin password.
