<?php
session_start();
$conn = new mysqli("localhost", "root", "", "student_dashboard");
if ($conn->connect_error) {
  die("Connection Failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $student_id = intval($_POST['student_id']);
  $dob = trim($_POST['dob']); 
  $new_password = $_POST['new_password'] ?? "";
  $confirm_password = $_POST['confirm_password'] ?? "";

  // Basic checks
  if (empty($new_password) || empty($confirm_password)) {
    $message = "❌ Please provide the new password and confirm it.";
  } elseif ($new_password !== $confirm_password) {
    $message = "❌ Passwords do not match.";
  } else {
    // Verify student exists with given ID and DOB
    $stmt = $conn->prepare("SELECT * FROM students WHERE id=? AND dob=? LIMIT 1");
    $stmt->bind_param("is", $student_id, $dob);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
      // Hash password
      $hashed = password_hash($new_password, PASSWORD_BCRYPT);

      // Check if an entry already exists in passwords table
      $check = $conn->prepare("SELECT id FROM passwords WHERE student_id=? LIMIT 1");
      $check->bind_param("i", $student_id);
      $check->execute();
      $r = $check->get_result();

      if ($r->num_rows === 1) {
        // Update existing password
        $update = $conn->prepare("UPDATE passwords SET password=? WHERE student_id=?");
        $update->bind_param("si", $hashed, $student_id);
        if ($update->execute()) {
          $message = "✅ Password updated successfully. <a href='student_login.php' style='color:#FFDB00;'>Login now</a>.";
        } else {
          $message = "❌ Error while updating password. Try again.";
        }
      } else {
        // Insert new password record
        $insert = $conn->prepare("INSERT INTO passwords (student_id, password) VALUES (?, ?)");
        $insert->bind_param("is", $student_id, $hashed);
        if ($insert->execute()) {
          $message = "✅ Account password set successfully. <a href='student_login.php' style='color:#FFDB00;'>Login now</a>.";
        } else {
          $message = "❌ Error while creating password record. Try again.";
        }
      }

    } else {
      $message = "❌ Invalid Student ID or DOB.";
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link rel="icon" href="image/nes_school.png">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #062845, #5D0E41);
      display: flex;
      height: 100vh;
      justify-content: center;
      align-items: center;
      margin: 0;
    }
    .box {
      width: 380px;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(8px);
      padding: 30px;
      border-radius: 12px;
      color: #fff;
      text-align: center;
    }
    h2 { color: #FFDB00; margin-bottom: 12px; }
    input {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border-radius: 8px;
      border: none;
      background: rgba(255,255,255,0.08);
      color: #fff;
    }
    button {
      width: 100%;
      padding: 12px;
      background: #409A44;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
    }
    .msg { margin-top: 12px; color: yellow; }
    .hint { font-size: 13px; color: #ddd; margin-top: 10px; }
    a { color: #FFDB00; }
  </style>
</head>
<body>
  <div class="box">
    <h2>🔒 Reset Password</h2>
    <form method="POST">
      <input type="number" name="student_id" placeholder="Enter Student ID" required>
      <input type="date" name="dob" required>
      <input type="password" name="new_password" placeholder="New Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
      <button type="submit">Reset Password</button>
    </form>
    <div class="msg"><?= $message ?></div>
    <div class="hint">Tip: If account isn't created yet, this will create password entry (after verifying ID & DOB).</div>
    <p style="margin-top:12px;">Back to <a href="student_login.php">Login</a> | <a href="student_signup.php">Create Account</a></p>
  </div>
</body>
</html>
