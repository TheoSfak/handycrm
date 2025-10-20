<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Theodore Sfakianakis - theodore.sfakianakis@gmail.com">
    <meta name="copyright" content="© 2025 Theodore Sfakianakis. All rights reserved.">
    <title><?= $title ?? 'HandyCRM' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
            padding-bottom: 80px;
        }
        
        .sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .logo h4 {
            color: white;
            margin: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 0;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 20px;
        }
        
        .content-wrapper {
            padding: 20px;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .table th {
            border-top: none;
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none !important;
            }
        }
        
        /* Status badges */
        .badge-new { background-color: #17a2b8; }
        .badge-in-progress { background-color: #ffc107; color: #212529; }
        .badge-completed { background-color: #28a745; }
        .badge-cancelled { background-color: #dc3545; }
        .badge-scheduled { background-color: #007bff; }
        .badge-confirmed { background-color: #28a745; }
        
        /* Category badges */
        .badge-electrical { background-color: #ff6b35; }
        .badge-plumbing { background-color: #4dabf7; }
        .badge-maintenance { background-color: #69db7c; }
        .badge-emergency { background-color: #f03e3e; }
        
        /* Priority badges */
        .badge-low { background-color: #51cf66; }
        .badge-medium { background-color: #ffd43b; color: #212529; }
        .badge-high { background-color: #ff8787; }
        .badge-urgent { background-color: #ff6b6b; }
        
        /* Application Footer */
        .app-footer {
            background-color: #f8f9fa;
            margin-top: 50px;
            font-size: 0.9rem;
        }
        
        .app-footer .text-muted {
            color: #6c757d !important;
        }
        
        .app-footer a {
            color: #667eea;
            transition: color 0.3s;
        }
        
        .app-footer a:hover {
            color: #764ba2;
        }
        
        .app-footer strong {
            color: #495057;
        }
        
        .app-footer i {
            margin-right: 5px;
        }
        
        /* Update Notification Styling */
        .update-notification {
            animation: pulse-glow 2s infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 10px rgba(102, 126, 234, 0.5);
            }
            50% {
                box-shadow: 0 0 20px rgba(102, 126, 234, 0.8);
            }
        }
        
        .update-notification:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }
        
        #notification-count {
            animation: bounce 0.5s ease;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="sidebar" id="sidebar">
        <div class="logo">
            <h4><i class="fas fa-tools"></i> HandyCRM</h4>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $currentRoute === '/' || $currentRoute === '' || $currentRoute === '/dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard">
                    <i class="fas fa-tachometer-alt"></i> <?= __('menu.dashboard') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/customers') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/customers">
                    <i class="fas fa-users"></i> <?= __('menu.customers') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/projects') !== false && strpos($currentRoute, '/payments') === false ? 'active' : '' ?>" href="<?= BASE_URL ?>/projects">
                    <i class="fas fa-project-diagram"></i> <?= __('menu.projects') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/payments') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/payments">
                    <i class="fas fa-money-bill-wave"></i> <?= __('menu.payments') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/appointments') !== false && strpos($currentRoute, '/calendar') === false ? 'active' : '' ?>" href="<?= BASE_URL ?>/appointments">
                    <i class="fas fa-calendar-alt"></i> <?= __('menu.appointments') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/appointments/calendar') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/appointments/calendar">
                    <i class="fas fa-calendar"></i> <?= __('menu.calendar') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/quotes') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/quotes">
                    <i class="fas fa-file-invoice"></i> <?= __('menu.quotes') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/invoices') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/invoices">
                    <i class="fas fa-receipt"></i> <?= __('menu.invoices') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/materials') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/materials">
                    <i class="fas fa-boxes"></i> <?= __('menu.materials') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/reports') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/reports">
                    <i class="fas fa-chart-line"></i> <?= __('menu.reports') ?>
                </a>
            </li>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/users') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/users">
                    <i class="fas fa-user-cog"></i> <?= __('menu.users') ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentRoute, '/settings') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/settings">
                    <i class="fas fa-cogs"></i> <?= __('menu.settings') ?>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- Sidebar Footer -->
        <div class="sidebar-footer" style="position: absolute; bottom: 0; width: 100%; padding: 15px 20px; border-top: 1px solid rgba(255,255,255,0.1); text-align: center;">
            <small style="color: rgba(255,255,255,0.7); display: block;">Made with <i class="fas fa-heart" style="color: #ff6b6b;"></i> By</small>
            <small style="color: white; font-weight: 600;">Theodore Sfakianakis</small>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Top Navbar -->
        <nav class="navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-primary mobile-menu-btn me-3" type="button" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <h5 class="mb-0">
                    <?php 
                    $breadcrumb = '';
                    $uri = $_SERVER['REQUEST_URI'];
                    if (strpos($uri, '/customers') !== false) $breadcrumb = __('menu.customers');
                    elseif (strpos($uri, '/projects') !== false) $breadcrumb = __('menu.projects');
                    elseif (strpos($uri, '/appointments') !== false) $breadcrumb = __('menu.appointments');
                    elseif (strpos($uri, '/quotes') !== false) $breadcrumb = __('menu.quotes');
                    elseif (strpos($uri, '/invoices') !== false) $breadcrumb = __('menu.invoices');
                    elseif (strpos($uri, '/materials') !== false) $breadcrumb = __('menu.materials');
                    elseif (strpos($uri, '/users') !== false) $breadcrumb = __('menu.users');
                    elseif (strpos($uri, '/settings') !== false) $breadcrumb = __('menu.settings');
                    else $breadcrumb = __('menu.dashboard');
                    echo $breadcrumb;
                    ?>
                </h5>
            </div>
            
            <div class="d-flex align-items-center">
                <!-- Notifications -->
                <div class="dropdown me-3">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger" id="notification-count">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="notifications-list">
                        <li><h6 class="dropdown-header"><?= __('common.notifications') ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-muted" href="#"><?= __('common.no_notifications') ?></a></li>
                    </ul>
                </div>
                
                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?= $_SESSION['first_name'] ?? __('common.user') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?= ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '') ?></h6></li>
                        <li><small class="dropdown-item-text text-muted"><?= ucfirst($_SESSION['role'] ?? 'user') ?></small></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?route=/profile"><i class="fas fa-user-edit"></i> <?= __('common.profile') ?></a></li>
                        <li><a class="dropdown-item" href="?route=/logout"><i class="fas fa-sign-out-alt"></i> <?= __('menu.logout') ?></a></li>
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
            }
            ?>
            
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>