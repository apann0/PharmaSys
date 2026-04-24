<?php
/**
 * Global Header Include
 * Sets up session, loads DB config, and renders the sidebar + topbar.
 *
 * Variables expected before including:
 *   $pageTitle  – string – Title for <title> tag and topbar
 *   $activePage – string – Key for active nav highlight
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$pageTitle  = $pageTitle  ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';
$flash = getFlash();
$currentUser = $_SESSION['full_name'] ?? 'User';

// Build base URL dynamically
$basePath = '';
// Determine depth from project root to adjust paths
if (!isset($baseUrl)) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $depth = substr_count(str_replace('\\', '/', $scriptDir), '/');
    $basePath = ($depth > 1) ? str_repeat('../', $depth - 1) : './';
    // If running from root
    if ($scriptDir === '/' || $scriptDir === '\\') $basePath = './';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Pharmacy Management System – Manage inventory, finances, and reports.">
  <title><?php echo e($pageTitle); ?> – PharmaSys</title>
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;opacity:0;visibility:hidden;transition:.3s;"></div>
<style>#sidebar-overlay.show{opacity:1;visibility:visible;}</style>

<!-- Sidebar Navigation -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    <h1>Pharma<span>Sys</span></h1>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-group">Main</div>
    <a href="<?php echo $basePath; ?>index.php" class="<?php echo $activePage==='dashboard'?'active':''; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.955-8.955a1.124 1.124 0 0 1 1.59 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
      Dashboard
    </a>

    <div class="nav-group">Inventory</div>
    <a href="<?php echo $basePath; ?>modules/medicine/list.php" class="<?php echo $activePage==='medicines'?'active':''; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
      Medicines
    </a>
    <a href="<?php echo $basePath; ?>modules/medicine/add.php" class="<?php echo $activePage==='add_medicine'?'active':''; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
      Add Medicine
    </a>

    <div class="nav-group">Finance</div>
    <a href="<?php echo $basePath; ?>modules/finance/inflow.php" class="<?php echo $activePage==='inflow'?'active':''; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
      Record Sale
    </a>
    <a href="<?php echo $basePath; ?>modules/finance/outflow.php" class="<?php echo $activePage==='outflow'?'active':''; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
      Record Expense
    </a>
    <a href="<?php echo $basePath; ?>modules/finance/report.php" class="<?php echo $activePage==='report'?'active':''; ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
      Reports
    </a>
  </nav>

  <div class="sidebar-footer">
    <div style="display:flex;align-items:center;justify-content:space-between;">
      <span><?php echo e($currentUser); ?></span>
      <a href="<?php echo $basePath; ?>modules/auth/logout.php" class="btn btn-sm btn-danger" title="Logout">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
      </a>
    </div>
  </div>
</aside>

<!-- Main Content Wrapper -->
<div class="main-content" id="main-content">
  <header class="topbar">
    <div class="topbar-left">
      <button class="btn-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:22px;height:22px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
      </button>
      <h2><?php echo e($pageTitle); ?></h2>
    </div>
    <div class="topbar-right">
      <span style="font-size:.8rem;color:var(--text-secondary);"><?php echo date('D, d M Y'); ?></span>
    </div>
  </header>

  <main class="page-content">
    <?php if ($flash): ?>
    <div class="flash flash-<?php echo e($flash['type']); ?>">
      <?php echo e($flash['message']); ?>
    </div>
    <?php endif; ?>
