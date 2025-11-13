<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'student_dashboard');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// ----------------- LOGIN -----------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
      $_SESSION['adminName'] = $row['adminName'];
      header("Location: loader.php");
      exit();
    } else {
      $error = "❌ Invalid password.";
    }
  } else {
    $error = "❌ User not found.";
  }
}

// ----------------- REGISTER -----------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
  $adminName = trim($_POST['adminName']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  // Pehle check karo email already to nahi
  $check = $conn->prepare("SELECT id FROM admins WHERE email=?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $error = "⚠️ Email already registered!";
  } else {
    $stmt = $conn->prepare("INSERT INTO admins (adminName, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $adminName, $email, $hashedPassword);
    if ($stmt->execute()) {
      $_SESSION['adminName'] = $adminName;
      header("Location: index.php");
      exit();
    } else {
      $error = "❌ Registration failed!";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>NES SCHOOL — Admin Access</title>
  <link rel="icon" href="image/nes_school.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Roboto, Arial, sans-serif;
      background: linear-gradient(135deg, #e8f5e9, #bbdefb);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .login-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      padding: 30px 25px;
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .login-card img {
      width: 70px;
      margin-bottom: 10px;
    }

    h2 {
      margin: 10px 0 20px;
      color: #2e7d32;
    }

    .input-group {
      margin-bottom: 16px;
      text-align: left;
    }

    .input-group label {
      font-size: 14px;
      font-weight: 600;
      color: #374151;
      display: block;
      margin-bottom: 6px;
    }

    .input-group input {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #cbd5e1;
      outline: none;
      transition: 0.3s;
      font-size: 15px;
    }

    .input-group input:focus {
      border-color: #2e7d32;
      box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.2);
    }

    .btn {
      width: 100%;
      padding: 12px;
      background: linear-gradient(90deg, #2e7d32, #43a047);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
      margin-top: 5px;
    }

    .btn:hover {
      background: linear-gradient(90deg, #1b5e20, #2e7d32);
    }

    .error {
      color: #d32f2f;
      background: #ffebee;
      border-radius: 8px;
      padding: 10px;
      margin-bottom: 15px;
      font-size: 14px;
    }

    .toggle-link {
      margin-top: 15px;
      font-size: 14px;
      color: #2e7d32;
      cursor: pointer;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <div class="login-card">
    <img src="image/nes_school.png" alt="NES Logo">
    <h2 id="formTitle">Admin Login</h2>

    <?php if ($error): ?>
      <div class="error">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" id="loginForm">
      <div class="input-group">
        <label for="email"><i class="fa fa-envelope"></i> Email</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="input-group">
        <label for="password"><i class="fa fa-lock"></i> Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit" name="login" class="btn">Login</button>
    </form>

    <!-- Register Form -->
    <form method="POST" id="registerForm" style="display:none;">
      <div class="input-group">
        <label for="adminName"><i class="fa fa-user"></i> Name</label>
        <input type="text" id="adminName" name="adminName" required>
      </div>

      <div class="input-group">
        <label for="email"><i class="fa fa-envelope"></i> Email</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="input-group">
        <label for="password"><i class="fa fa-lock"></i> Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit" name="register" class="btn">Create Account</button>
    </form>

    <div class="toggle-link" onclick="toggleForms()">Don’t have an account? Create Account</div>

    <div class="footer" style="margin-top:15px; font-size:12px; color:#6b7280;">
      © NES SCHOOL — Secure Admin Access
    </div>
  </div>

  <script>
    function toggleForms() {
      const loginForm = document.getElementById("loginForm");
      const registerForm = document.getElementById("registerForm");
      const title = document.getElementById("formTitle");
      const toggle = document.querySelector(".toggle-link");

      if (loginForm.style.display === "none") {
        loginForm.style.display = "block";
        registerForm.style.display = "none";
        title.innerText = "Admin Login";
        toggle.innerText = "Don’t have an account? Create Account";
      } else {
        loginForm.style.display = "none";
        registerForm.style.display = "block";
        title.innerText = "Create Account";
        toggle.innerText = "Already have an account? Login";
      }
    }
  </script>
</body>

</html>