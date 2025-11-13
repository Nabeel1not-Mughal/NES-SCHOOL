<?php
session_start();
$conn = new mysqli("localhost", "root", "", "student_dashboard");

if ($conn->connect_error) {
  die("Connection Failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $student_id = intval($_POST['student_id']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM passwords WHERE student_id=? LIMIT 1");
  $stmt->bind_param("i", $student_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $account = $result->fetch_assoc();

    if (password_verify($password, $account['password'])) {
      $_SESSION['student_id'] = $student_id;
      header("Location: student_dashboard.php");
      exit;
    } else {
      $message = "❌ Wrong Password.";
    }
  } else {
    $message = "❌ Account not found. Please <a href='student_signup.php' style='color:#0f766e;'>Create Account</a> first.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Student Login | NES School</title>
  <link rel="icon" href="image/nes_school.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy: #0b2447;
      --green: #0f766e;
      --light-green: #14b8a6;
      --white: #ffffff;
    }

    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #f4f8fa, #e9f9f4);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      color: #333;
    }

    /* Floating Login Box */
    .login-box {
      background: var(--white);
      width: 380px;
      padding: 38px 32px;
      border-radius: 18px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
      animation: floatIn 0.8s ease-out;
      border-top: 6px solid var(--green);
    }

    @keyframes floatIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Header */
    .logo {
      width: 70px;
      height: 70px;
      margin-bottom: 10px;
      border-radius: 50%;
      box-shadow: 0 0 15px rgba(20, 184, 166, 0.3);
      animation: pulse 3s infinite ease-in-out;
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 15px rgba(20, 184, 166, 0.3);
      }
      50% {
        transform: scale(1.05);
        box-shadow: 0 0 25px rgba(20, 184, 166, 0.5);
      }
    }

    h2 {
      color: var(--navy);
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 25px;
    }

    /* Input Fields */
    input {
      width: 100%;
      padding: 14px;
      margin: 12px 0;
      border-radius: 10px;
      border: 1px solid #ccc;
      background: #f9f9f9;
      font-size: 15px;
      transition: all 0.3s ease;
      color: #0b2447;
    }

    input:focus {
      outline: none;
      border-color: var(--green);
      background: #ffffff;
      box-shadow: 0 0 8px rgba(20, 184, 166, 0.4);
    }

    /* Password Toggle */
    .password-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 14px;
      cursor: pointer;
      color: var(--green);
      user-select: none;
    }

    /* Button */
    button {
      width: 100%;
      padding: 14px;
      margin-top: 15px;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--green), var(--light-green));
      color: var(--white);
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 6px 15px rgba(15, 118, 110, 0.3);
    }

    button:hover {
      background: linear-gradient(135deg, var(--light-green), #16d6b6);
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(15, 118, 110, 0.4);
    }

    /* Error / Message Box */
    .msg {
      margin-top: 14px;
      font-size: 14px;
      padding: 10px;
      border-radius: 8px;
      background: rgba(220, 38, 38, 0.1);
      color: #c00;
      display: inline-block;
      width: 100%;
    }

    a {
      text-decoration: none;
      color: var(--green);
      font-weight: 500;
      transition: 0.3s;
    }

    a:hover {
      color: var(--navy);
    }

    p {
      font-size: 13px;
      margin-top: 12px;
      color: #555;
    }

    /* Responsive */
    @media (max-width: 420px) {
      .login-box {
        width: 90%;
        padding: 28px 24px;
      }
    }
  </style>
</head>

<body>
  <div class="login-box">
    <img src="image/nes_school.png" alt="NES School" class="logo">
    <h2>🎓 Student Portal</h2>
    <form method="POST">
      <input type="number" name="student_id" placeholder="Enter Student ID" required>
      <div class="password-wrapper">
        <input type="password" name="password" id="password" placeholder="Enter Password" required>
        <span class="toggle-password" onclick="togglePassword()"><i class="fa-solid fa-eye"></i></span>
      </div>
      <button type="submit">Login</button>
    </form>

    <?php if (!empty($message)) { ?>
      <div class="msg"><?= $message ?></div>
    <?php } ?>

    <p><a href="reset_password.php">Forgot Password?</a></p>
    <p>Don't have an account? <a href="student_signup.php">Create Account</a></p>
  </div>

  <script>
    // Show/Hide Password
    function togglePassword() {
      const pwd = document.getElementById("password");
      pwd.type = pwd.type === "password" ? "text" : "password";
    }
  </script>
</body>

</html>
