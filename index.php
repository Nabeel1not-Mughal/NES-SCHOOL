<?php
session_start();
if (!isset($_SESSION['adminName'])) {
  header("Location: login.php");
  exit();
}

$conn = new mysqli('localhost', 'root', '', 'student_dashboard');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// --- Stats from DB ---
$students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'] ?? 0;
$courses = $conn->query("SELECT COUNT(DISTINCT course) as total FROM students")->fetch_assoc()['total'] ?? 0;
$pendingFees = $conn->query("SELECT COUNT(*) as total FROM students WHERE fee_status='unpaid'")->fetch_assoc()['total'] ?? 0;
$attendance = $conn->query("SELECT ROUND((COUNT(CASE WHEN present='1' THEN 1 END)/COUNT(*))*100,1) as percent FROM attendance")->fetch_assoc()['percent'] ?? 0;

// --- Notices ---
$notices = $conn->query("SELECT message, created_at FROM notices ORDER BY id DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>NES SCHOOL — Dashboard</title>
  <link rel="icon" href="image/nes_school.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* =======================================
   NES SCHOOL - Premium Dashboard Theme
   ---------------------------------------
   Modern glassmorphism + gradient design
   Clean, responsive & professional look
======================================= */

    body {
      margin: 0;
      font-family: "Poppins", "Segoe UI", Roboto, Arial, sans-serif;
      background: linear-gradient(135deg, #f0fdf4 0%, #e8f5e9 50%, #ffffff 100%);
      color: #0a2540;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
      transition: background 0.5s ease-in-out;
    }

    /* ===== HEADER ===== */
    header {
      background: rgba(255, 255, 255, 0.95);
      padding: 14px 28px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 1000;
      border-bottom: 4px solid #2e7d32;
      backdrop-filter: blur(10px);
    }

    header h1 {
      margin: 0;
      font-size: 28px;
      font-weight: 800;
      color: #0a2540;
      letter-spacing: 0.5px;
    }

    header h1::after {
      content: "";
      display: block;
      width: 50px;
      height: 3px;
      background: linear-gradient(90deg, #2e7d32, #4caf50);
      border-radius: 2px;
      margin-top: 6px;
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 0;
      overflow-x: hidden;
      background: linear-gradient(180deg, #0a2540 0%, #1b5e20 100%);
      padding-top: 70px;
      z-index: 2000;
      border-top-right-radius: 20px;
      border-bottom-right-radius: 20px;
      box-shadow: 4px 0 15px rgba(0, 0, 0, 0.25);
      transition: width 0.4s ease;
    }

    .sidebar.open {
      width: 260px;
    }

    .sidebar a {
      padding: 14px 28px;
      text-decoration: none;
      font-size: 15px;
      color: #eaf2f8;
      display: flex;
      align-items: center;
      gap: 14px;
      position: relative;
      font-weight: 500;
      letter-spacing: 0.4px;
      transition: all 0.3s ease;
    }

    .sidebar a:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
      border-left: 4px solid #4caf50;
      transform: translateX(8px);
    }

    .sidebar a i {
      width: 20px;
      text-align: center;
      color: #9be7a5;
      transition: color 0.3s ease;
    }

    .sidebar a:hover i {
      color: #ffffff;
    }

    .sidebar .closebtn {
      position: absolute;
      top: 18px;
      right: 20px;
      font-size: 28px;
      color: #f44336;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .sidebar .closebtn:hover {
      transform: scale(1.1);
      background: none;
    }

    /* ===== MENU BUTTON ===== */
    .openbtn {
      background: linear-gradient(90deg, #2e7d32, #43a047);
      color: #fff;
      padding: 10px 18px;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
      transition: all 0.3s ease;
    }

    .openbtn:hover {
      background: linear-gradient(90deg, #1b5e20, #2e7d32);
      transform: scale(1.05);
    }

    /* ===== MAIN CONTAINER ===== */
    .container {
      flex: 1;
      padding: 40px 25px;
      transition: margin-left 0.4s ease;
    }

    #main {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      margin: 20px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    /* ===== DASHBOARD GRID ===== */
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 25px;
      width: 100%;
    }

    /* ===== CARDS ===== */
    .card {
      background: linear-gradient(145deg, #ffffff 0%, #f1f8f4 100%);
      border-radius: 18px;
      padding: 25px;
      text-align: center;
      box-shadow: 0 8px 22px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
      transition: all 0.4s ease;
    }

    .card::before {
      content: "";
      position: absolute;
      top: -40%;
      left: -40%;
      width: 180%;
      height: 180%;
      background: radial-gradient(circle, rgba(46, 125, 50, 0.15) 0%, transparent 70%);
      transform: rotate(25deg);
      transition: all 0.5s ease;
    }

    .card:hover::before {
      transform: rotate(0deg) scale(1.1);
      opacity: 0.9;
    }

    .card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 12px 30px rgba(46, 125, 50, 0.25);
    }

    /* ===== CARD TEXT ===== */
    .stat {
      font-size: 36px;
      font-weight: 800;
      color: #2e7d32;
      margin-bottom: 8px;
    }

    .label {
      font-size: 16px;
      font-weight: 600;
      color: #0a2540;
    }

    .subline {
      font-size: 13px;
      color: #6b7280;
      margin-top: 6px;
    }

    /* ===== NOTICE & ACTION CARDS ===== */
    .card h3 {
      color: #0a2540;
      font-weight: 700;
      border-bottom: 2px solid #e8f5e9;
      padding-bottom: 8px;
      margin-bottom: 14px;
    }

    /* ===== BUTTONS ===== */
    button,
    .btn-green,
    .btn-orange {
      font-family: "Poppins", sans-serif;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-green {
      background: linear-gradient(90deg, #2e7d32, #43a047);
      color: #fff;
      box-shadow: 0 3px 8px rgba(46, 125, 50, 0.3);
    }

    .btn-green:hover {
      background: linear-gradient(90deg, #1b5e20, #2e7d32);
      transform: translateY(-2px);
    }

    .btn-orange {
      background: linear-gradient(90deg, #ffb300, #ffa000);
      color: #fff;
      box-shadow: 0 3px 8px rgba(255, 160, 0, 0.3);
    }

    .btn-orange:hover {
      background: linear-gradient(90deg, #f57c00, #ef6c00);
      transform: translateY(-2px);
    }

    /* ===== FOOTER ===== */
    footer {
      text-align: center;
      padding: 16px;
      font-size: 13px;
      color: #6b7280;
      background: rgba(255, 255, 255, 0.95);
      border-top: 3px solid #2e7d32;
      backdrop-filter: blur(8px);
    }

    /* ===== IFRAME ===== */
    #iframeBox {
      display: none;
      width: 100%;
      border: none;
      margin-top: 25px;
      border-radius: 14px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      background: #fff;
      overflow: hidden;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
      header h1 {
        font-size: 22px;
      }

      .sidebar {
        width: 220px;
      }

      .card {
        padding: 18px;
      }

      .grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div id="mySidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">
      <i class="fa-solid fa-circle-xmark"></i>
    </a>
    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="submitData.php" target="contentFrame"><i class="fa fa-user-plus"></i> Add Student</a>
    <a href="attendance.php" target="contentFrame"><i class="fa fa-calendar-check"></i> Attendance</a>
    <a href="readData.php" target="contentFrame"><i class="fa fa-database"></i> Records</a>
    <a href="fee_status.php" target="contentFrame"><i class="fas fa-wallet"></i> Fee-Status</a>
    <a href="notices.php" target="contentFrame"><i class="fa fa-bell"></i> Notices</a>
    <a href="charts.php" target="contentFrame"><i class="fa fa-chart-line"></i> Charts</a>
    <a href="summary.php" target="contentFrame"><i class="fa fa-list"></i> Summary</a>
  </div>

  <!-- Header -->
  <header>
    <button class="openbtn" onclick="openNav()">☰ Menu</button>
    <h1>NES SCHOOL — Admin Dashboard</h1>
    <div>
      Welcome, <strong style="color: green;"><?php echo $_SESSION['adminName']; ?></strong> |
      <a href="login.php" style="font-weight: bolder; text-decoration: none; color: red;">Logout</a>
    </div>
  </header>

  <!-- Content -->
  <div id="main" class="container">

    <!-- Default Dashboard Content -->
    <div id="dashboardContent">
      <!-- Quick Stats -->
      <div class="grid">
        <div class="card">
          <div class="stat"><?php echo $students; ?></div>
          <div class="label">Total Students</div>
          <div class="subline">Active learners enrolled</div>
        </div>
        <div class="card">
          <div class="stat"><?php echo $courses; ?></div>
          <div class="label">Active Courses</div>
          <div class="subline">Programs running this term</div>
        </div>
        <div class="card">
          <div class="stat"><?php echo $pendingFees; ?></div>
          <div class="label">Pending Fees</div>
          <div class="subline">Awaiting clearance</div>
        </div>
        <div class="card">
          <div class="stat"><?php echo $attendance; ?>%</div>
          <div class="label">Attendance Rate</div>
          <div class="subline">Overall participation</div>
        </div>
      </div>

      <!-- Notices -->
      <div class="card" style="margin-top:30px; text-align:left">
        <h3><i class="fa fa-bell"></i> Recent Notices</h3>
        <ul>
          <?php if ($notices->num_rows > 0): ?>
            <?php while ($n = $notices->fetch_assoc()): ?>
              <li>
                <?php echo htmlspecialchars($n['message']); ?>
                <small style="color:#6b7280">(<?php echo $n['created_at']; ?>)</small>
              </li>
            <?php endwhile; ?>
          <?php else: ?>
            <li>No notices yet.</li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Quick Actions -->
      <div class="card" style="margin-top:30px; text-align:left">
        <h3><i class="fa fa-bolt"></i> Quick Actions</h3>
        <div class="actions" style="margin-top:10px">
          <button class="btn-green" onclick="loadInFrame('submitData.php')">➕ Add Student</button>
          <button class="btn-orange" onclick="loadInFrame('attendance.php')">🗓 Mark Attendance</button>
        </div>
      </div>
    </div>

    <!-- Iframe -->
    <iframe id="iframeBox" src="#" name="contentFrame"></iframe>
  </div>

  <!-- Footer -->
  <footer>
    © NES SCHOOL — Administrative Dashboard
  </footer>

  <script>
    function openNav() {
      document.getElementById("mySidebar").style.width = "250px";
      document.getElementById("main").style.marginLeft = "250px";
    }

    function closeNav() {
      document.getElementById("mySidebar").style.width = "0";
      document.getElementById("main").style.marginLeft = "0";
    }

    // Load page inside iframe + hide dashboard cards
    function loadInFrame(url) {
      document.getElementById("dashboardContent").style.display = "none";
      document.getElementById("iframeBox").style.display = "block";
      document.getElementById("iframeBox").src = url;
    }

    // Sidebar links open in iframe + hide dashboard
    document.querySelectorAll('.sidebar a[target="contentFrame"]').forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        loadInFrame(this.getAttribute('href'));
        closeNav();
      });
    });
    document.getElementById("iframeBox").addEventListener("load", function () {
      let iframe = this;
      try {
        let innerDoc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = innerDoc.body.scrollHeight + "px";
      } catch (e) {
        console.log("Cross-domain iframe height adjust nahi ho paya.");
      }
    });

    function loadInFrame(url) {
      document.getElementById("dashboardContent").style.display = "none";
      let iframe = document.getElementById("iframeBox");
      iframe.style.display = "block";
      iframe.src = url;
    }
  </script>
</body>

</html>