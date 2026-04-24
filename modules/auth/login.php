<?php
/**
 * Login Page
 * Authenticates users with password_hash() verification.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/database.php';

// Already logged in? Redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT id, username, password, full_name, role FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['full_name']  = $user['full_name'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['logged_in']  = true;
            $_SESSION['last_activity'] = time();
            $_SESSION['created']    = time();

            logAction($user['id'], 'LOGIN', 'User logged in successfully.');

            header('Location: ../../index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
            // Rate-limit: small delay to slow brute-force
            usleep(500000);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Login to PharmaSys Pharmacy Management System">
  <title>Login – PharmaSys</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card glass">
      <div style="text-align:center;margin-bottom:1rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:48px;height:48px;color:var(--accent-light);margin:0 auto;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
      </div>
      <h1>Pharma<span style="color:var(--accent-light)">Sys</span></h1>
      <p>Sign in to your account</p>

      <?php if ($error): ?>
        <div class="flash flash-error"><?php echo e($error); ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['timeout'])): ?>
        <div class="flash flash-warning">Your session has expired. Please log in again.</div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input type="text" id="username" name="username" class="form-input"
                 value="<?php echo e($_POST['username'] ?? ''); ?>"
                 placeholder="Enter your username" required autofocus>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-input"
                 placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
          Sign In
        </button>
      </form>

      <p style="text-align:center;margin-top:1.25rem;font-size:.75rem;color:var(--text-secondary);">
        Default: <strong>admin</strong> / <strong>admin123</strong>
      </p>
    </div>
  </div>
</body>
</html>
