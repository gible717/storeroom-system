<?php
// FILE: admin_header.php
require_once 'auth_check.php';

if ($userRole !== 'Admin') {
    header("Location: staff_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-width: 280px; }
        body { background-color: #f8f9fa; font-family: sans-serif; }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #111827;
            padding-top: 1rem;
        }
        .sidebar-header {
            padding: 1.5rem;
            text-align: center;
            color: white;
            border-bottom: 1px solid #374151;
        }
        .sidebar-nav { padding: 1rem; }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: background-color 0.2s, color 0.2s;
        }
        .sidebar-link:hover { background-color: #374151; color: #ffffff; }
        .sidebar-link.active { background-color: #4f46e5; color: #ffffff; font-weight: 600; }
        .main-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 0;
        }
        .top-navbar {
            background: #fff;
            padding: 1rem 2.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .page-content { padding: 2.5rem; }

        /* --- ADJUSTED STYLES FOR LOGO --- */
        .logo-container {
            background-color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            padding: 8px; /* Reduced padding for a snugger fit */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Scales the image to fit without stretching */
        }
        .sidebar-header h5 {
            font-weight: bold;
            font-size: 1.1rem;
            text-align: left;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php require 'admin_sidebar.php'; ?>
    <div class="main-content-wrapper">
        <?php require 'admin_top_navbar.php'; ?>
        <main class="page-content">
            ```