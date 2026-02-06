<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Η Σελίδα Δεν Βρέθηκε | HandyCRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 90%;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
            line-height: 1;
        }
        .error-message {
            font-size: 24px;
            color: #333;
            margin: 20px 0;
        }
        .error-description {
            color: #666;
            margin-bottom: 40px;
            font-size: 16px;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="fas fa-exclamation-triangle" style="font-size: 80px; color: #ffc107; margin-bottom: 20px;"></i>
        <h1 class="error-code">404</h1>
        <h2 class="error-message">Η Σελίδα Δεν Βρέθηκε</h2>
        <p class="error-description">
            Λυπούμαστε, αλλά η σελίδα που ψάχνετε δεν υπάρχει ή έχει μετακινηθεί.
        </p>
        
        <div class="d-grid gap-2 d-md-block">
            <a href="?route=/" class="btn btn-home">
                <i class="fas fa-home"></i> Επιστροφή στην Αρχική
            </a>
            <a href="?route=/customers" class="btn btn-outline-primary">
                <i class="fas fa-users"></i> Πελάτες
            </a>
        </div>
        
        <hr class="my-4">
        
        <p class="text-muted">
            <small>
                Αν πιστεύετε ότι αυτό είναι λάθος, παρακαλώ επικοινωνήστε με τον διαχειριστή.
            </small>
        </p>
    </div>
</body>
</html>