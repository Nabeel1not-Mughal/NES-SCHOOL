<?php
// DB connect
$conn = new mysqli("localhost", "root", "", "student_dashboard");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Add Notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['message'])) {
  $title = $conn->real_escape_string($_POST['title']);
  $message = $conn->real_escape_string($_POST['message']);
  $conn->query("INSERT INTO notices (title, message) VALUES ('$title', '$message')");
  header("Location: notices.php?added=1");
  exit;
}

// Delete Notice
if (isset($_GET['delete'])) {
  $id = (int) $_GET['delete'];
  $conn->query("DELETE FROM notices WHERE id=$id");
  header("Location: notices.php?deleted=1");
  exit;
}

// Fetch notices
$result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>NES SCHOOL — Notices</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: Inter, Arial, sans-serif;
      background: #f3f6f9;
      padding: 20px;
      color: #243044;
    }

    .container {
      max-width: 900px;
      margin: auto;
    }

    h2 {
      color: #0D47A1;
      margin-bottom: 20px;
      text-align: center;
    }

    .form-box {
      background: #fff;
      padding: 20px;
      border-radius: 14px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      margin-bottom: 25px;
      border-left: 6px solid #1B5E20;
    }

    .form-box input,
    .form-box textarea {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .form-box button {
      background: linear-gradient(90deg, #1B5E20, #4CAF50);
      border: none;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .form-box button:hover {
      background: linear-gradient(90deg, #2E7D32, #66BB6A);
      transform: scale(1.05);
    }

    .notice-list {
      background: #fff;
      padding: 20px;
      border-radius: 14px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .notice {
      padding: 15px;
      border-bottom: 1px solid #eee;
      transition: 0.3s;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .notice:hover {
      background: #f9fbff;
      transform: scale(1.01);
    }

    .notice:last-child {
      border-bottom: none;
    }

    .notice h4 {
      margin: 0;
      font-size: 16px;
      color: #0D47A1;
    }

    .notice p {
      margin: 6px 0;
      font-size: 14px;
      color: #444;
    }

    .notice time {
      font-size: 12px;
      color: #888;
    }

    .actions {
      margin-left: 15px;
    }

    .delete-btn {
      background: none;
      border: none;
      color: #d32f2f;
      font-size: 18px;
      cursor: pointer;
      transition: 0.2s;
    }

    .delete-btn:hover {
      color: #b71c1c;
      transform: scale(1.2);
    }
  </style>
</head>

<body>
  <div class="container">
    <h2><i class="fa fa-bell"></i> Manage Notices</h2>

    <!-- Add Notice -->
    <div class="form-box">
      <form method="POST">
        <input type="text" name="title" placeholder="Notice Title" required>
        <textarea name="message" rows="3" placeholder="Notice Message" required></textarea>
        <button type="submit"><i class="fa fa-plus"></i> Add Notice</button>
      </form>
    </div>

    <!-- Notices List -->
    <div class="notice-list">
      <h3>📢 Latest Notices</h3>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="notice">
            <div>
              <h4><?= htmlspecialchars($row['title']) ?></h4>
              <p><?= htmlspecialchars($row['message']) ?></p>
              <time>🕒 <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></time>
            </div>
            <div class="actions">
              <a href="notices.php?delete=<?= $row['id'] ?>"
                onclick="return confirm('Are you sure you want to delete this notice?')">
                <button class="delete-btn"><i class="fa fa-trash"></i></button>
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No notices available.</p>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>