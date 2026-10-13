<?php
require_once __DIR__ . '/includes/db.php';
$email = 'admin@example.com';
$stmt = $pdo->prepare('SELECT id FROM users WHERE email=?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    exit('Admin account already exists. Delete setup_admin.php now.');
}
$stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
$stmt->execute(['Project Admin',$email,password_hash('Admin@12345', PASSWORD_DEFAULT),'admin']);
echo 'Admin created. Email: admin@example.com | Password: Admin@12345. Delete setup_admin.php immediately and change the password before using this project.';
