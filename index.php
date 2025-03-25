<?php require_once 'includes/db.php'; ?>
<?php
$username = $email = $password = $confirm_password = '';
$errors = [];
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? '')) ;
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username)) {
      $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
      $errors['username'] = 'Username must be between 3 and 50 characters';
    }
    
    if (empty($email)) {
      $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Please enter a valid email';
    }

    if (empty($password)) {
      $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
      $errors['password'] = 'Password must be at least 8 characters long';
    }

    if ($password !== $confirm_password) {
      $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($errors['username'])) {
      $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ?");
      $stmt->execute([$username]);
      if ($stmt->fetchColumn()) {
        $errors['username'] = 'Username already exists';
      }
    }

    if (empty($errors['email'])) {
      $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetchColumn()) {
        $errors['email'] = 'Email already registered';
      }
    }

    if (empty($errors)) {
      try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
          INSERT INTO users (username, email, password)
          VALUES (?, ?, ?)
        ");
        $stmt->execute([$username, $email, $hashed_password]);
        $success = 'Registration successful';
        $username = $email = $password = $confirm_password = '';
      } catch (PDOException $e) {
        $errors['db'] = 'Registration failed: ' . $e->getMessage();
      }
    }
  }
?>

<!doctype HTML>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="assets/css/global.css">
  </head>
  <body>
    <h1>Account Creation</h1>
    
    <?php if (isset($success)): ?>    
      <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (isset($errors['db'])): ?>    
      <div class="error"><?php echo htmlspecialchars($errors['db']); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
      <div class="form-group">
        <label for="username">Username</label>
        <input id="username" type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
        <?php if (isset($errors['username'])): ?>
          <div class="error"><?php echo htmlspecialchars($errors['username']); ?></div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <?php if (isset($errors['email'])): ?>
          <div class="error"><?php echo htmlspecialchars($errors['email']); ?></div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" value="<?php echo htmlspecialchars($password); ?>">
        <?php if (isset($errors['password'])): ?>
          <div class="error"><?php echo htmlspecialchars($errors['password']); ?></div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" type="password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>">
        <?php if (isset($errors['confirm_password'])): ?>
          <div class="error"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <button type="submit">Register</button>
      </div>   
    </form>
  </body>
</html>
