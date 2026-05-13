<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Theodore Sfakianakis - theodore.sfakianakis@gmail.com">
    <meta name="copyright" content="© 2025 Theodore Sfakianakis. All rights reserved.">
    <?php if (isset($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <?php endif; ?>
    <?php 
    require_once __DIR__ . '/../../helpers/app_display_name.php';
    $appName = getAppDisplayName();
    ?>
    <title><?= $title ?? $appName ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        /* ─── CSS Variables ───────────────────────────────── */
        :root {
            --sidebar-bg:       #0f172a;
            --sidebar-width:    258px;
            --accent:           #0ea5e9;
            --accent-dark:      #0284c7;
            --accent-glow:      rgba(14,165,233,0.18);
            --success:          #10b981;
            --warning:          #f59e0b;
            --danger:           #f43f5e;
            --info:             #8b5cf6;
            --body-bg:          #f1f5f9;
            --card-bg:          #ffffff;
            --text-primary:     #0f172a;
            --text-muted:       #64748b;
            --border:           #e2e8f0;
            --shadow-sm:        0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md:        0 4px 16px rgba(0,0,0,0.10);
            --shadow-lg:        0 10px 30px rgba(0,0,0,0.12);
            --radius:           12px;
            --radius-sm:        8px;
            --radius-pill:      50px;
        }

        /* ─── Base ────────────────────────────────────────── */
        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            font-size: 0.9rem;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Sidebar ─────────────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(.4,0,.2,1);
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 80px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.08) transparent;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        /* Logo area */
        .sidebar .logo {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .logo .logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--accent) 0%, #6366f1 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(14,165,233,0.35);
        }

        .sidebar .logo .logo-text h5 {
            color: #f8fafc;
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        .sidebar .logo .logo-text small {
            color: rgba(255,255,255,0.4);
            font-size: 0.7rem;
            font-weight: 400;
        }

        /* Nav group labels */
        .sidebar .nav-group-label {
            color: rgba(255,255,255,0.28);
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 18px 20px 6px;
        }

        /* Nav links */
        .sidebar .nav-link {
            color: rgba(255,255,255,0.6);
            padding: 9px 12px;
            margin: 1px 8px;
            border-radius: var(--radius-sm);
            transition: all 0.18s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .sidebar .nav-link:hover {
            color: #f8fafc;
            background: rgba(255,255,255,0.07);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: var(--accent-glow);
            border: 1px solid rgba(14,165,233,0.25);
        }

        .sidebar .nav-link.active i {
            color: var(--accent);
        }

        .sidebar .nav-link i {
            width: 16px;
            text-align: center;
            font-size: 0.85rem;
            flex-shrink: 0;
            color: rgba(255,255,255,0.4);
            transition: color 0.18s;
        }

        .sidebar .nav-link:hover i {
            color: rgba(255,255,255,0.8);
        }

        /* Trash badge in sidebar */
        .sidebar .badge {
            margin-left: auto;
            font-size: 0.65rem;
            padding: 2px 6px;
        }

        /* Sidebar footer */
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 12px 20px;
            border-top: 1px solid rgba(255,255,255,0.06);
            background: rgba(0,0,0,0.2);
        }

        .sidebar-footer small {
            display: block;
            color: rgba(255,255,255,0.3);
            font-size: 0.68rem;
            text-align: center;
        }

        .sidebar-footer strong {
            color: rgba(255,255,255,0.55);
        }

        /* ─── Main Content ────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(.4,0,.2,1);
        }

        /* ─── Top Navbar ──────────────────────────────────── */
        .navbar {
            background: #ffffff;
            box-shadow: var(--shadow-sm);
            padding: 10px 24px;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .navbar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent) 0%, #6366f1 100%);
        }

        .navbar .page-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        /* User avatar circle */
        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent) 0%, #6366f1 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            flex-shrink: 0;
        }

        .btn-user-dropdown {
            background: none;
            border: 1px solid var(--border);
            border-radius: var(--radius-pill);
            padding: 4px 12px 4px 4px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
            font-size: 0.82rem;
            font-weight: 500;
            transition: all 0.18s;
        }

        .btn-user-dropdown:hover {
            border-color: var(--accent);
            background: var(--accent-glow);
            color: var(--text-primary);
        }

        .btn-user-dropdown::after { display: none; } /* hide default caret */

        /* Notification bell */
        .btn-bell {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: none;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.18s;
            position: relative;
        }

        .btn-bell:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: var(--accent-glow);
        }

        /* ─── Content Wrapper ─────────────────────────────── */
        .content-wrapper {
            padding: 24px;
        }

        /* ─── Cards ───────────────────────────────────────── */
        .card {
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            border-radius: var(--radius);
            background: var(--card-bg);
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header {
            background: var(--sidebar-bg);
            color: #f8fafc;
            border-radius: var(--radius) var(--radius) 0 0 !important;
            border-bottom: none;
            padding: 14px 20px;
            font-weight: 600;
            font-size: 0.88rem;
        }

        .card-header.bg-primary   { background: var(--accent) !important; }
        .card-header.bg-success   { background: var(--success) !important; }
        .card-header.bg-warning   { background: var(--warning) !important; color: #1a1a1a !important; }
        .card-header.bg-danger    { background: var(--danger) !important; }
        .card-header.bg-info      { background: var(--info) !important; }
        .card-header.bg-light     { background: #f8fafc !important; color: var(--text-primary) !important; border-bottom: 1px solid var(--border) !important; }
        .card-header.bg-secondary { background: #475569 !important; }

        /* Stat cards (dashboard) */
        .stat-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--card-bg);
            padding: 20px 24px;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .stat-card .stat-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.08;
        }

        .stat-card .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .stat-card .stat-value {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-card .stat-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .stat-card-accent {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: var(--radius) 0 0 var(--radius);
        }

        /* ─── Buttons ─────────────────────────────────────── */
        .btn {
            font-weight: 500;
            font-size: 0.84rem;
            border-radius: var(--radius-sm);
            transition: all 0.18s ease;
        }

        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .btn-primary:hover, .btn-primary:focus {
            background: var(--accent-dark);
            border-color: var(--accent-dark);
            color: #fff;
            box-shadow: 0 4px 12px rgba(14,165,233,0.35);
        }

        .btn-success { background: var(--success); border-color: var(--success); }
        .btn-success:hover { background: #059669; border-color: #059669; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }

        .btn-danger { background: var(--danger); border-color: var(--danger); }
        .btn-danger:hover { background: #e11d48; border-color: #e11d48; box-shadow: 0 4px 12px rgba(244,63,94,0.3); }

        .btn-warning { background: var(--warning); border-color: var(--warning); color: #1a1a1a; }
        .btn-warning:hover { background: #d97706; border-color: #d97706; color: #fff; }

        .btn-info { background: var(--info); border-color: var(--info); color: #fff; }
        .btn-info:hover { background: #7c3aed; border-color: #7c3aed; box-shadow: 0 4px 12px rgba(139,92,246,0.3); }

        .btn-secondary { background: #475569; border-color: #475569; color: #fff; }
        .btn-secondary:hover { background: #334155; border-color: #334155; }

        /* Outline variants */
        .btn-outline-primary { color: var(--accent); border-color: var(--accent); }
        .btn-outline-primary:hover { background: var(--accent); border-color: var(--accent); color: #fff; }

        /* ─── Tables ──────────────────────────────────────── */
        .table {
            font-size: 0.85rem;
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
            border-top: none;
            padding: 12px 14px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 11px 14px;
            vertical-align: middle;
            border-color: var(--border);
            color: var(--text-primary);
        }

        .table tbody tr {
            transition: background 0.12s;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .table-responsive {
            border-radius: var(--radius-sm);
            overflow-x: auto;
        }

        /* ─── Badges ──────────────────────────────────────── */
        .badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 4px 9px;
            border-radius: var(--radius-pill);
            letter-spacing: 0.02em;
        }

        /* Status badges — soft tones */
        .badge-new          { background: #e0f2fe; color: #0369a1; }
        .badge-in-progress  { background: #fef9c3; color: #92400e; }
        .badge-completed    { background: #dcfce7; color: #166534; }
        .badge-cancelled    { background: #fee2e2; color: #991b1b; }
        .badge-scheduled    { background: #ede9fe; color: #5b21b6; }
        .badge-confirmed    { background: #dcfce7; color: #166534; }

        /* Category badges */
        .badge-electrical   { background: #fff7ed; color: #c2410c; }
        .badge-plumbing     { background: #eff6ff; color: #1d4ed8; }
        .badge-maintenance  { background: #f0fdf4; color: #15803d; }
        .badge-emergency    { background: #fff1f2; color: #be123c; }

        /* Priority badges */
        .badge-low          { background: #f0fdf4; color: #15803d; }
        .badge-medium       { background: #fefce8; color: #a16207; }
        .badge-high         { background: #fff7ed; color: #c2410c; }
        .badge-urgent       { background: #fff1f2; color: #be123c; }

        /* Override Bootstrap bg- badges to match theme */
        .bg-primary   { background-color: var(--accent) !important; }
        .bg-success   { background-color: var(--success) !important; }
        .bg-warning   { background-color: var(--warning) !important; }
        .bg-danger    { background-color: var(--danger) !important; }
        .bg-info      { background-color: var(--info) !important; }
        .bg-secondary { background-color: #475569 !important; }

        /* ─── Alerts ──────────────────────────────────────── */
        .alert {
            border-radius: var(--radius-sm);
            border: none;
            font-size: 0.875rem;
            padding: 14px 18px;
        }

        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger  { background: #fee2e2; color: #991b1b; }
        .alert-warning { background: #fef9c3; color: #92400e; }
        .alert-info    { background: #e0f2fe; color: #0369a1; }

        /* ─── Forms ───────────────────────────────────────── */
        .form-control, .form-select {
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            font-size: 0.875rem;
            padding: 9px 13px;
            transition: border-color 0.18s, box-shadow 0.18s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(14,165,233,0.15);
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 5px;
        }

        /* ─── Application Footer ──────────────────────────── */
        .app-footer {
            background: #ffffff;
            border-top: 1px solid var(--border);
            margin-top: 40px;
            font-size: 0.8rem;
            padding: 14px 0;
        }

        .app-footer .text-muted { color: var(--text-muted) !important; }

        .app-footer a {
            color: var(--accent);
            text-decoration: none;
            transition: color 0.18s;
        }

        .app-footer a:hover { color: var(--accent-dark); }
        .app-footer strong  { color: var(--text-primary); }
        .app-footer i       { margin-right: 4px; }

        /* ─── Mobile ──────────────────────────────────────── */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: var(--shadow-lg);
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-menu-btn {
                display: flex !important;
            }
            .content-wrapper {
                padding: 16px;
            }
        }

        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none !important;
            }
        }

        /* ─── Update Notification ─────────────────────────── */
        .update-notification {
            animation: pulse-glow 2.5s infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 8px rgba(14,165,233,0.3); }
            50%       { box-shadow: 0 0 18px rgba(14,165,233,0.6); }
        }

        .update-notification:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }

        #notification-count {
            font-size: 0.6rem;
            padding: 2px 5px;
        }

        /* ─── Misc utilities ──────────────────────────────── */
        .breadcrumb {
            font-size: 0.8rem;
            background: none;
            padding: 0;
            margin-bottom: 16px;
        }

        .breadcrumb-item a {
            color: var(--accent);
            text-decoration: none;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h3, .page-header h4 {
            font-weight: 700;
            color: var(--text-primary);
        }

        hr { border-color: var(--border); opacity: 1; }

        .text-primary   { color: var(--accent) !important; }
        .text-success   { color: var(--success) !important; }
        .text-warning   { color: var(--warning) !important; }
        .text-danger    { color: var(--danger) !important; }
        .text-info      { color: var(--info) !important; }
        .text-muted     { color: var(--text-muted) !important; }

        /* Dropdown menus */
        .dropdown-menu {
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-md);
            font-size: 0.85rem;
            padding: 6px;
        }

        .dropdown-item {
            border-radius: 6px;
            padding: 7px 12px;
            transition: background 0.12s;
        }

        .dropdown-item:hover { background: var(--body-bg); }

        .dropdown-divider { border-color: var(--border); margin: 4px 0; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <?php
        // Load AuthMiddleware for permission checks
        require_once __DIR__ . '/../../classes/AuthMiddleware.php';
        
        // Get user role for permission checks (backward compatibility)
        $userRole = $_SESSION['role'] ?? 'technician';
        $isAdmin = $userRole === 'admin';
        $isSupervisor = $userRole === 'supervisor';
        $isTechnician = $userRole === 'technician';
        $isAssistant = $userRole === 'assistant';
    ?>
    <nav class="sidebar" id="sidebar">
        <!-- Logo -->
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="logo-text">
                <h5><?= $appName ?></h5>
                <small>Management Suite</small>
            </div>
        </div>

        <ul class="nav flex-column mt-1">

            <!-- ── ΚΥΡΙΟ ───────────────────────────────────── -->
            <li class="nav-group-label">Κύριο</li>

            <?php if ($isAdmin || $isSupervisor || can('dashboard.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentRoute === '/' || $currentRoute === '' || $currentRoute === '/dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard">
                    <i class="fas fa-chart-pie"></i> <?= __('menu.dashboard') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || can('customers.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/customers') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/customers">
                    <i class="fas fa-users"></i> <?= __('menu.customers') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || $isSupervisor || can('projects.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/projects') !== false && strpos($currentRoute, '/payments') === false ? 'active' : '' ?>" href="<?= BASE_URL ?>/projects">
                    <i class="fas fa-layer-group"></i> <?= __('menu.projects') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || can('payments.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/payments') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/payments">
                    <i class="fas fa-coins"></i> <?= __('menu.payments') ?>
                </a>
            </li>
            <?php endif; ?>

            <!-- ── ΕΡΓΑΣΙΕΣ ────────────────────────────────── -->
            <li class="nav-group-label">Εργασίες</li>

            <?php if ($isAdmin || can('transformer_maintenance.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/maintenances') !== false && strpos($currentRoute, '/maintenance-offers') === false ? 'active' : '' ?>" href="<?= BASE_URL ?>/maintenances">
                    <i class="fas fa-bolt"></i> Συντηρήσεις Υ/Σ
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/maintenance-offers') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/maintenance-offers">
                    <i class="fas fa-file-contract"></i> Προσφορά Συντήρησης
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || can('daily_task.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/daily-tasks') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/daily-tasks">
                    <i class="fas fa-clipboard-check"></i> Εργασίες Ημέρας
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || can('appointments.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/appointments') !== false && strpos($currentRoute, '/calendar') === false ? 'active' : '' ?>" href="<?= BASE_URL ?>/appointments">
                    <i class="fas fa-calendar-alt"></i> <?= __('menu.appointments') ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/appointments/calendar') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/appointments/calendar">
                    <i class="fas fa-calendar-days"></i> <?= __('menu.calendar') ?>
                </a>
            </li>
            <?php endif; ?>

            <!-- ── ΔΙΑΧΕΙΡΙΣΗ ──────────────────────────────── -->
            <li class="nav-group-label">Διαχείριση</li>

            <?php if ($isAdmin || can('quotes.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/quotes') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/quotes">
                    <i class="fas fa-file-invoice"></i> <?= __('menu.quotes') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || $isSupervisor || can('materials.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/materials') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/materials">
                    <i class="fas fa-boxes-stacked"></i> <?= __('menu.materials') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin || can('reports.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/reports') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/reports">
                    <i class="fas fa-chart-bar"></i> <?= __('menu.reports') ?>
                </a>
            </li>
            <?php endif; ?>

            <!-- My Profile -->
            <?php if ($isTechnician || $isAssistant || $isSupervisor): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/users/show') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/users/show/<?= $_SESSION['user_id'] ?>">
                    <i class="fas fa-id-card"></i> Η Καρτέλα μου
                </a>
            </li>
            <?php endif; ?>

            <!-- ── ΣΥΣΤΗΜΑ ──────────────────────────────────── -->
            <?php if ($isAdmin): ?>
            <li class="nav-group-label">Σύστημα</li>

            <?php if (can('users.view') || $isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/users') !== false && strpos($currentRoute, '/users/show') === false ? 'active' : '' ?>" href="<?= BASE_URL ?>/users">
                    <i class="fas fa-user-gear"></i> <?= __('menu.users') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php if (can('roles.manage') || $isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/roles') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/roles">
                    <i class="fas fa-shield-halved"></i> Ρόλοι & Δικαιώματα
                </a>
            </li>
            <?php endif; ?>

            <?php
                require_once __DIR__ . '/../../models/Trash.php';
                $trashModel = new Trash($GLOBALS['db']->connect());
                $trashedCounts = $trashModel->getDeletedCountByType();
                $totalTrashed = ($trashedCounts['project'] ?? 0)
                              + ($trashedCounts['daily_task'] ?? 0)
                              + ($trashedCounts['maintenance'] ?? 0)
                              + ($trashedCounts['material'] ?? 0);
            ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/trash') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/trash">
                    <i class="fas fa-trash-can"></i> Κάδος Απορριμμάτων
                    <?php if ($totalTrashed > 0): ?>
                        <span class="badge bg-danger"><?= $totalTrashed ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <?php if (can('settings.view') || $isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/settings') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/settings">
                    <i class="fas fa-sliders"></i> <?= __('menu.settings') ?>
                </a>
            </li>
            <?php endif; ?>

            <?php endif; /* $isAdmin */ ?>

        </ul>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <small>Made with <i class="fas fa-heart" style="color:#f43f5e;"></i> by <strong>Theodore Sfakianakis</strong></small>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Top Navbar -->
        <nav class="navbar">
            <div class="d-flex align-items-center gap-2">
                <!-- Mobile hamburger -->
                <button class="btn-bell mobile-menu-btn" type="button" onclick="toggleSidebar()" title="Μενού">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Page title -->
                <p class="page-title mb-0">
                    <?php
                    $uri = $_SERVER['REQUEST_URI'];
                    if (strpos($uri, '/customers') !== false)       echo __('menu.customers');
                    elseif (strpos($uri, '/payments') !== false)    echo __('menu.payments');
                    elseif (strpos($uri, '/maintenance-offers') !== false) echo 'Προσφορές Συντήρησης';
                    elseif (strpos($uri, '/maintenances') !== false) echo 'Συντηρήσεις Υ/Σ';
                    elseif (strpos($uri, '/daily-tasks') !== false) echo 'Εργασίες Ημέρας';
                    elseif (strpos($uri, '/appointments/calendar') !== false) echo __('menu.calendar');
                    elseif (strpos($uri, '/appointments') !== false) echo __('menu.appointments');
                    elseif (strpos($uri, '/quotes') !== false)      echo __('menu.quotes');
                    elseif (strpos($uri, '/materials') !== false)   echo __('menu.materials');
                    elseif (strpos($uri, '/reports') !== false)     echo __('menu.reports');
                    elseif (strpos($uri, '/users') !== false)       echo __('menu.users');
                    elseif (strpos($uri, '/roles') !== false)       echo 'Ρόλοι &amp; Δικαιώματα';
                    elseif (strpos($uri, '/settings') !== false)    echo __('menu.settings');
                    elseif (strpos($uri, '/trash') !== false)       echo 'Κάδος Απορριμμάτων';
                    elseif (strpos($uri, '/projects') !== false)    echo __('menu.projects');
                    elseif (strpos($uri, '/profile') !== false)     echo __('common.profile');
                    else echo __('menu.dashboard');
                    ?>
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Notification Bell -->
                <div class="dropdown">
                    <button class="btn-bell" type="button" data-bs-toggle="dropdown" aria-label="Ειδοποιήσεις">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger position-absolute" id="notification-count"
                              style="top:-4px;right:-4px;font-size:0.55rem;padding:2px 4px;display:none;">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="notifications-list" style="min-width:300px;">
                        <li><h6 class="dropdown-header fw-bold"><?= __('common.notifications') ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text text-muted small"><?= __('common.no_notifications') ?></span></li>
                    </ul>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <?php
                    $firstName = $_SESSION['first_name'] ?? '';
                    $lastName  = $_SESSION['last_name']  ?? '';
                    $initials  = mb_strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
                    if ($initials === '') $initials = '?';
                    ?>
                    <button class="btn-user-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
                        <span class="d-none d-md-inline"><?= htmlspecialchars($firstName ?: __('common.user')) ?></span>
                        <i class="fas fa-chevron-down" style="font-size:0.65rem;color:var(--text-muted);"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2">
                            <div class="fw-600" style="font-size:0.88rem;font-weight:600;"><?= htmlspecialchars("$firstName $lastName") ?></div>
                            <div class="text-muted" style="font-size:0.75rem;"><?= ucfirst($_SESSION['role'] ?? 'user') ?></div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?route=/profile"><i class="fas fa-user-pen me-2 text-muted"></i><?= __('common.profile') ?></a></li>
                        <li><a class="dropdown-item text-danger" href="?route=/logout"><i class="fas fa-right-from-bracket me-2"></i><?= __('menu.logout') ?></a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php endif; ?>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Flash Messages -->
            <?php 
            $flash = null;
            if (isset($_SESSION['flash'])) {
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
            } elseif (isset($_SESSION['success'])) {
                $flash = ['type' => 'success', 'message' => $_SESSION['success']];
                unset($_SESSION['success']);
            } elseif (isset($_SESSION['error'])) {
                $flash = ['type' => 'error', 'message' => $_SESSION['error']];
                unset($_SESSION['error']);
            }
            ?>
            
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>