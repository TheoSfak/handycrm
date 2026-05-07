<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once __DIR__ . '/../../helpers/app_display_name.php';
    $appName = getAppDisplayName();
    ?>
    <title><?= $title ?? 'Σύνδεση - ' . $appName ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            min-height: 100vh;
            height: 100vh;
            display: flex;
            background: #f1f5f9;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
        }

        /* ── Left branding panel ──────────────────────── */
        .login-brand {
            width: 46%;
            background: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 56px;
            position: relative;
            overflow: hidden;
        }

        /* Subtle decorative circles */
        .login-brand::before {
            content: '';
            position: absolute;
            width: 420px; height: 420px;
            border-radius: 50%;
            border: 1px solid rgba(14,165,233,0.08);
            top: -80px; left: -80px;
        }

        .login-brand::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            border: 1px solid rgba(99,102,241,0.1);
            bottom: -40px; right: -60px;
        }

        .brand-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 12px 30px rgba(14,165,233,0.35);
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .brand-title {
            color: #f8fafc;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0 0 10px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .brand-subtitle {
            color: rgba(255,255,255,0.45);
            font-size: 0.9rem;
            text-align: center;
            line-height: 1.6;
            margin: 0 0 48px;
            position: relative;
            z-index: 1;
        }

        .brand-features {
            width: 100%;
            max-width: 280px;
            position: relative;
            z-index: 1;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            color: rgba(255,255,255,0.55);
            font-size: 0.85rem;
        }

        .brand-feature-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .brand-copyright {
            position: absolute;
            bottom: 24px;
            color: rgba(255,255,255,0.2);
            font-size: 0.72rem;
            z-index: 1;
            text-align: center;
        }

        /* ── Right form panel ─────────────────────────── */
        .login-form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            background: #ffffff;
            overflow-y: auto;
        }

        .login-form-inner {
            width: 100%;
            max-width: 400px;
        }

        .form-heading {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .form-subheading {
            color: #64748b;
            font-size: 0.88rem;
            margin-bottom: 36px;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 0.9rem;
            font-family: inherit;
            transition: border-color 0.18s, box-shadow 0.18s;
            background: #f8fafc;
        }

        .form-control:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,0.15);
            background: #fff;
            outline: none;
        }

        .input-group .form-control {
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding: 0 14px;
            cursor: pointer;
            color: #94a3b8;
            transition: color 0.18s;
        }

        .input-group-text:hover { color: #0ea5e9; }

        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text {
            border-color: #0ea5e9;
        }

        .input-group:focus-within .input-group-text {
            box-shadow: 0 0 0 3px rgba(14,165,233,0.15);
        }

        .btn-login {
            background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: 600;
            font-size: 0.9rem;
            font-family: inherit;
            width: 100%;
            color: #fff;
            letter-spacing: 0.02em;
            transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(14,165,233,0.4);
            color: #fff;
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login.loading { color: transparent; pointer-events: none; }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
            top: 50%; left: 50%;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .form-check-input:checked {
            background-color: #0ea5e9;
            border-color: #0ea5e9;
        }

        .forgot-link {
            color: #0ea5e9;
            font-size: 0.82rem;
            text-decoration: none;
            transition: color 0.18s;
        }

        .forgot-link:hover { color: #0284c7; text-decoration: underline; }

        .version-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            border-radius: 50px;
            padding: 4px 12px;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 32px;
        }

        .version-badge .dot {
            width: 6px; height: 6px;
            background: #10b981;
            border-radius: 50%;
        }

        .alert {
            border-radius: 10px;
            border: none;
            font-size: 0.875rem;
            padding: 12px 16px;
        }

        .alert-danger  { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-warning { background: #fef9c3; color: #92400e; }
        .alert-info    { background: #e0f2fe; color: #0369a1; }

        /* ── Mobile ───────────────────────────────────── */
        @media (max-width: 768px) {
            body { flex-direction: column; height: auto; min-height: 100vh; overflow: auto; }
            .login-brand {
                width: 100%;
                padding: 36px 24px 32px;
                min-height: auto;
            }
            .login-brand::before, .login-brand::after { display: none; }
            .brand-features { display: none; }
            .brand-icon { margin-bottom: 16px; width: 56px; height: 56px; font-size: 1.4rem; }
            .brand-title { font-size: 1.35rem; margin-bottom: 6px; }
            .login-form-panel { flex: 1; padding: 36px 20px; min-height: 0; }
        }
    </style>
</head>
<body>

    <!-- Left branding panel -->
    <div class="login-brand">
        <div class="brand-icon"><i class="fas fa-bolt"></i></div>
        <h1 class="brand-title"><?= htmlspecialchars($appName) ?></h1>
        <p class="brand-subtitle">Σύστημα Διαχείρισης Πελατών,<br>Έργων &amp; Πληρωμών</p>

        <div class="brand-features">
            <div class="brand-feature">
                <div class="brand-feature-icon" style="background:rgba(14,165,233,0.15);color:#0ea5e9;">
                    <i class="fas fa-users"></i>
                </div>
                <span>Διαχείριση Πελατών &amp; Έργων</span>
            </div>
            <div class="brand-feature">
                <div class="brand-feature-icon" style="background:rgba(16,185,129,0.15);color:#10b981;">
                    <i class="fas fa-coins"></i>
                </div>
                <span>Παρακολούθηση Πληρωμών</span>
            </div>
            <div class="brand-feature">
                <div class="brand-feature-icon" style="background:rgba(99,102,241,0.15);color:#6366f1;">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <span>Αναφορές &amp; Στατιστικά</span>
            </div>
            <div class="brand-feature">
                <div class="brand-feature-icon" style="background:rgba(244,63,94,0.12);color:#f43f5e;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span>Ραντεβού &amp; Ημερολόγιο</span>
            </div>
        </div>

        <div class="brand-copyright">
            © <?= date('Y') ?> ECOWATT Ενεργειακές Λύσεις
        </div>
    </div>

    <!-- Right form panel -->
    <div class="login-form-panel">
        <div class="login-form-inner">
            <h2 class="form-heading">Καλωσήρθατε</h2>
            <p class="form-subheading">Συνδεθείτε στον λογαριασμό σας για να συνεχίσετε</p>

            <!-- Flash Messages -->
            <?php if (isset($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label" for="username">Χρήστης ή Email</label>
                    <input type="text"
                           class="form-control"
                           id="username"
                           name="username"
                           placeholder="username ή email@example.com"
                           required
                           autocomplete="username"
                           autofocus>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label" for="password">Κωδικός πρόσβασης</label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               required
                               autocomplete="current-password">
                        <span class="input-group-text" onclick="togglePassword()" title="Εμφάνιση/Απόκρυψη">
                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                        </span>
                    </div>
                </div>

                <!-- Remember + Forgot -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember" style="font-size:0.85rem;">Να με θυμάσαι</label>
                    </div>
                    <a href="<?= BASE_URL ?>/forgot-password" class="forgot-link">Ξεχάσατε τον κωδικό;</a>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-arrow-right-to-bracket me-2"></i>Σύνδεση
                </button>
            </form>

            <div class="text-center">
                <div class="version-badge">
                    <span class="dot"></span>
                    <?= htmlspecialchars($appName) ?> &nbsp;·&nbsp; v<?= defined('APP_VERSION') ? APP_VERSION : '1.7.0' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            const inp  = document.getElementById('password');
            const icon = document.getElementById('passwordToggleIcon');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                inp.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const username = this.querySelector('input[name="username"]').value.trim();
            const password = this.querySelector('input[name="password"]').value;
            if (!username || !password) {
                e.preventDefault();
                showError('Παρακαλώ συμπληρώστε όλα τα πεδία');
                return;
            }
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.disabled = true;
            setTimeout(() => { btn.classList.remove('loading'); btn.disabled = false; }, 6000);

            if (username) localStorage.setItem('handycrm_username', username);
        });

        function showError(msg) {
            document.querySelectorAll('.alert').forEach(a => a.remove());
            const div = document.createElement('div');
            div.className = 'alert alert-danger alert-dismissible fade show';
            div.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.getElementById('loginForm').insertAdjacentElement('beforebegin', div);
        }

        // Pre-fill username from localStorage
        const saved = localStorage.getItem('handycrm_username');
        if (saved) {
            document.getElementById('username').value = saved;
            document.getElementById('password').focus();
        }

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(a => { try { new bootstrap.Alert(a).close(); } catch(e){} });
        }, 5000);
    </script>
</body>
</html>