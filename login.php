<?php
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: /dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AJ TMP API</title>
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
        <h2>Login</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="text-center">No account? <a href="/register.php">Register</a></div>
    </div>
</body>
</html>