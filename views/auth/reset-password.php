<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
    require_once __DIR__ . '/../../helpers/app_display_name.php';
    $appName = getAppDisplayName();
    ?>
    <title>Επαναφορά Κωδικού - <?= $appName ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .reset-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }
        
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }
        
        .reset-header h2 {
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .reset-header .logo {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .reset-form {
            padding: 40px 30px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 12px 15px;
            height: auto;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-reset:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-login a {
            color: #667eea;
            text-decoration: none;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid #eee;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 10px;
        }
        
        .password-requirements ul {
            padding-left: 20px;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <div class="logo">
                <i class="fas fa-lock-open"></i>
            </div>
            <h2>Νέος Κωδικός</h2>
            <p class="mb-0">Εισάγετε τον νέο σας κωδικό</p>
        </div>
        
        <div class="reset-form">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] === 'error' ? 'danger' : $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $_SESSION['flash']['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?>"></i>
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash']); endif; ?>
            
            <form method="POST" action="<?= BASE_URL ?>/reset-password" id="resetForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           name="password" 
                           id="password"
                           placeholder="Νέος κωδικός"
                           required
                           minlength="6"
                           autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                    </button>
                </div>
                
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           name="password_confirm" 
                           id="password_confirm"
                           placeholder="Επιβεβαίωση κωδικού"
                           required
                           minlength="6"
                           autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirm')">
                        <i class="fas fa-eye" id="passwordConfirmToggleIcon"></i>
                    </button>
                </div>
                
                <div class="password-requirements">
                    <i class="fas fa-info-circle me-1"></i> Απαιτήσεις κωδικού:
                    <ul class="mt-2">
                        <li>Τουλάχιστον 6 χαρακτήρες</li>
                        <li>Οι κωδικοί πρέπει να ταιριάζουν</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn btn-reset mt-3">
                    <i class="fas fa-check me-1"></i> Αλλαγή Κωδικού
                </button>
            </form>
            
            <div class="back-to-login">
                <a href="<?= BASE_URL ?>/login">
                    <i class="fas fa-arrow-left me-1"></i>Επιστροφή στη Σύνδεση
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + 'ToggleIcon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Οι κωδικοί δεν ταιριάζουν!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες!');
                return false;
            }
        });
    </script>
</body>
</html>
