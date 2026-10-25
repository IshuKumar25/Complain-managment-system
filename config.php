<?php
session_start();

$conn = new mysqli("localhost", "root", "", "complaint_db");

if ($conn->connect_error) {
    die("Database connection failed");
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}
?>