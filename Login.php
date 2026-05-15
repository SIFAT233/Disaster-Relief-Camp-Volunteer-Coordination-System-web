<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Volunteer Portal | Disaster Relief Network</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background: transparent;
    }
    .auth-card {
      backdrop-filter: blur(20px);
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(239, 68, 68, 0.1);
    }
    .alert {
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 6px;
      font-size: 14px;
    }
    .alert-error {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fecaca;
    }
    .alert-success {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }
  </style>
</head>
<body class="auth-page">

  <div class="auth-card">
    <a href="index.php" class="sidebar-logo" style="justify-content: center; margin-bottom: 32px; text-decoration: none; color: var(--gold);">
      <span>🛟</span> Disaster Relief Network
    </a>
    
    <div style="position: absolute; top: 24px; left: 24px;">
      <a href="index.php" style="color: var(--muted); font-size: 14px; display: flex; align-items: center; gap: 8px;">
        ← Back to Home
      </a>
    </div>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div id="loginForm">
      <h2>Welcome Back</h2>
      <p>Log in to access your volunteer dashboard and assigned tasks.</p>
      
      <form action="auth.php" method="POST">
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="name@example.com" required>
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        
        <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Sign In</button>
      </form>
      
      <p style="margin-top: 24px; font-size: 14px;">
        New volunteer? <a href="#" onclick="toggleAuth()" style="color: var(--gold); font-weight: 600;">Register here</a>
      </p>
    </div>

    <div id="registerForm" style="display: none;">
      <h2>Join the Mission</h2>
      <p>Register as a volunteer to help affected communities.</p>
      
      <form action="auth.php" method="POST">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" placeholder="John Doe" required>
        </div>
        
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="name@example.com" required>
        </div>
        
        <div class="form-group">
          <label>Skills / Expertise</label>
          <select name="skills">
            <option>First Aid</option>
            <option>Logistics</option>
            <option>Food Distribution</option>
            <option>Search & Rescue</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        
        <button type="submit" name="register" class="btn btn-primary" style="width: 100%;">Register Now</button>
      </form>
      
      <p style="margin-top: 24px; font-size: 14px;">
        Already have an account? <a href="#" onclick="toggleAuth()" style="color: var(--gold); font-weight: 600;">Sign in here</a>
      </p>
    </div>
  </div>

  <script>
    function toggleAuth() {
      const login = document.getElementById('loginForm');
      const register = document.getElementById('registerForm');
      if (login.style.display === 'none') {
        login.style.display = 'block';
        register.style.display = 'none';
      } else {
        login.style.display = 'none';
        register.style.display = 'block';
      }
    }
  </script>
</body>
</html>
