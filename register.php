<?php
// NOTE: header.php partial DOES NOT AFFECT THIS FILE
$page_title = "Register";

require_once 'includes/utils.php';
if (isset($_SESSION['user_id'])) redirect("index.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?> - CodeForum</title>
  <link rel="stylesheet" href="assets/css/global.css">
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="assets/js/auth.js" defer></script>
</head>

<body>
  <header>
    <a href="index.php">
      <h1>CodeForum</h1>
    </a>
  </header>
  <div class="auth-container">
    <div class="auth-box register-box">
      <div class="auth-header">
        <h2>Create Your Account</h2>
        <p>Join the discussion with fellow CS students</p>
      </div>
      <?php if (isset($_SESSION['register_error'])): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          <?php echo $_SESSION['register_error']; ?>
        </div>
        <?php unset($_SESSION['register_error']); ?>
      <?php endif; ?>

      <form id="register-form" class="auth-form" action="register.php" method="post">
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-with-icon">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" placeholder="Choose a username" required>
          </div>
          <div class="input-help">Username must be between 3-20 characters</div>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-with-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" placeholder="Your McMaster email" required pattern=".*@mcmaster\.ca$">
          </div>
          <div class="input-help">Must be a valid McMaster email address</div>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Create a strong password" required minlength="8">
            <button type="button" class="toggle-password" area-label="Toggle password visibility">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label for="confirm-password">Confirm Password</label>
          <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required>
          </div>
        </div>
        <div class="password-requirements">
          <p>Password must include:</p>
          <ul>
            <li id="length-check"><i class="fas fa-times-circle"></i>At least 8 characters</li>
            <li id="uppercase-check"><i class="fas fa-times-circle"></i>One uppercase letter</li>
            <li id="lowercase-check"><i class="fas fa-times-circle"></i>One lowercase letter</li>
            <li id="number-check"><i class="fas fa-times-circle"></i>One number</li>
            <li id="special-check"><i class="fas fa-times-circle"></i>One special character</li>
          </ul>
        </div>
        <div class="form-group terms">
          <input type="checkbox" id="terms" name="terms" required>
          <label for="terms">I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a></label>
        </div>
        <button type="submit" class="auth-button">Create Account</button>
        <div class="auth-footer">
          <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>
      </form>
    </div>
  </div>

  <?php include 'includes/partials/footer.php'; ?>
</body>

</html>