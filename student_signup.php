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
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

  // Check if student exists with same ID and DOB
  $stmt = $conn->prepare("SELECT * FROM students WHERE id=? AND dob=? LIMIT 1");
  $stmt->bind_param("is", $student_id, $dob);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    // Check if already account exists
    $check = $conn->prepare("SELECT * FROM passwords WHERE student_id=?");
    $check->bind_param("i", $student_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
      $message = "⚠️ Account already created. Please <a href='student_login.php' style = 'color: green;text-decoration: none; font-weight: bold;'>Login.</a>";
    } else {
      // Save password in passwords table
      $insert = $conn->prepare("INSERT INTO passwords (student_id, password) VALUES (?, ?)");
      $insert->bind_param("is", $student_id, $password);
      if ($insert->execute()) {
        $message = "✅ Account created successfully! <a href='student_login.php' style = 'color: green;'>Login Now</a>";
      } else {
        $message = "❌ Error creating account.";
      }
    }
  } else {
    $message = "❌ Invalid Student ID or DOB.";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Student Sign Up</title>
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
    .signup-box {
      width: 350px;
      background: rgba(255, 255, 255, 0.06);
      backdrop-filter: blur(8px);
      padding: 30px;
      border-radius: 12px;
      color: #fff;
      text-align: center;
    }
    h2 {
      margin-bottom: 20px;
      color: #FFDB00;
    }
    input {
      width: 325px;
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: none;
      background: rgba(255, 255, 255, 0.08);
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
    }
    .msg { margin-top: 15px; color: yellow; }
  </style>
</head>
<body>
  <div class="signup-box">
    <h2>📝 Create Account</h2>
    <form method="POST">
      <input type="number" name="student_id" placeholder="Enter Student ID" required>
      <input type="date" name="dob" required>
      <input type="password" name="password" placeholder="Create Password" required>
      <button type="submit">Create Account</button>
    </form>
    <div class="msg"><?= $message ?></div>
  </div>
</body>
</html>
