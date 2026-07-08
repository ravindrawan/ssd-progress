<?php
require 'db.php';
$superadmin = password_hash('superadmin', PASSWORD_DEFAULT);
$admin = password_hash('admin', PASSWORD_DEFAULT);
$user = password_hash('user', PASSWORD_DEFAULT);

$conn->query("UPDATE users SET password='$superadmin' WHERE username='superadmin'");
$conn->query("UPDATE users SET password='$admin' WHERE username='admin'");
$conn->query("UPDATE users SET password='$user' WHERE username='user'");
echo "Passwords updated to hashed format";
?>
