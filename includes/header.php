<?php
// includes/header.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipSip - Water Intake Tracker</title>
    <link rel="stylesheet" href="/sipsip/css/style.css">
</head>
<body>
    <header>
        <h1>SipSip</h1>
        <?php if (is_logged_in()): ?>
        <nav class="main-nav">
            <ul>
                <li><a href="/sipsip/dashboard.php">Dashboard</a></li>
                <li><a href="/sipsip/history.php">History</a></li>
                <li><a href="/sipsip/statistics.php">Statistics</a></li>
                <li><a href="/sipsip/settings.php">Settings/Profile</a></li>
                <li><a href="/sipsip/actions/logout.php">Logout</a></li>
            </ul>
        </nav>
        <?php endif; ?>
    </header>
    <main>
