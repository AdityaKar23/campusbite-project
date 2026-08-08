<?php
/**
 * CampusBite - Logout
 * Destroy session and redirect to login
 */
require_once 'auth.php';

logoutUser();

header('Location: login.php');
exit;
