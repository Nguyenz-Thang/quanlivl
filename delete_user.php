<?php
include 'config.php';

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $sql = "DELETE FROM users WHERE username='$username'";
    if ($conn->query($sql) === TRUE) {
        echo "Xóa tài khoản thành công";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
header("Location: admin.php");
?>