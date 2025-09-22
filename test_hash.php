<?php
// Test hash cho mật khẩu 123456 và admin123
$plain1 = '123456';
$plain2 = 'admin123';
echo '123456: ' . password_hash($plain1, PASSWORD_DEFAULT) . "\n";
echo 'admin123: ' . password_hash($plain2, PASSWORD_DEFAULT) . "\n";
?>