<?php
// Define the loopback addresses
$loopback_ips = array('127.0.0.1', '::1');

// Get the IP address of the current user
$user_ip = $_SERVER['REMOTE_ADDR'];

// Check if the user's IP address is a loopback address
if (in_array($user_ip, $loopback_ips)) {
    // If the user's IP address matches a loopback address, forbid them from accessing the website
    die("Access denied. You are not allowed to access this website from localhost.");
}
