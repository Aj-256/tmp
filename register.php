<?php
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    $error = null;
    
    if ($password !== $confirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {
            // Create API key using username + random string
            $api_key = $username . '_' . bin2hex(random_bytes(16));
            
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, api_key) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed, $api_key])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                header('Location: /dashboard.php');
                exit;
            } else {
                $error = 'Registration failed';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AJ TMP API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #000; color: #fff; font-family: monospace; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #0a0a0a; border: 1px solid #222; border-radius: 16px; padding: 48px; max-width: 450px; width: 100%; }
        .logo { font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 32px; display: block; text-decoration: none; color: #fff; }
        h2 { text-align: center; margin-bottom: 32px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 8px; color: #888; font-size: 14px; }
        .form-control { width: 100%; padding: 12px 16px; background: #000; border: 1px solid #333; border-radius: 8px; color: #fff; font-family: monospace; }
        .form-control:focus { outline: none; border-color: #fff; }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; background: #fff; color: #000; }
        .btn:hover { background: #ddd; }
        .error { background: #2a0a0a; border: 1px solid #f00; color: #f00; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .text-center { text-align: center; margin-top: 24px; color: #888; }
        .text-center a { color: #fff; text-decoration: none; }
        @media (max-width: 768px) { .card { padding: 32px 24px; } }
    </style>
</head>
<body>
    <div class="card">
        <a href="/" class="logo">⚡ AJ TMP API</a>
        <h2>Create Account</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div class="text-center">Already have an account? <a href="/login.php">Login</a></div>
    </div>
</body>
</html>