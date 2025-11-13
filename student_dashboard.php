<?php
session_start();
$conn = new mysqli("localhost", "root", "", "student_dashboard");

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student info
$stmt = $conn->prepare("SELECT s.*, c.name as course_name, c.fee, c.admission_fee 
                        FROM students s 
                        JOIN courses c ON s.course = c.id 
                        WHERE s.id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Student photo
$photoPath = "uploads/Student_ID_" . $student['id'] . "_0";
$extensions = ["jpg", "jpeg", "png", "webp"];
$studentPhoto = "uploads/default.png";
foreach ($extensions as $ext) {
    if (file_exists("$photoPath.$ext")) {
        $studentPhoto = "$photoPath.$ext";
        break;
    }
}

// Attendance
$attendance_total = 0;
$attendance_present = 0;
$att = $conn->query("SELECT * FROM attendance WHERE student_id = $student_id");
while ($row = $att->fetch_assoc()) {
    $attendance_total++;
    if ($row['present'] == 1)
        $attendance_present++;
}
$attendance_absent = $attendance_total - $attendance_present;
$attendance_percent = $attendance_total ? round(($attendance_present / $attendance_total) * 100) : 0;

$notices_query = $conn->query("SELECT message FROM notices ORDER BY created_at DESC LIMIT 5");
$notices = [];
while ($n = $notices_query->fetch_assoc())
    $notices[] = $n['message'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>NES SCHOOL Student Dashboard</title>
    <link rel="icon" href="image/nes_school.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Luxury Theme */
        :root {
            --bg-dark: #0f0f0f;
            --bg-card: #1a1a1a;
            --gold: #FFD700;
            --gold-light: #ffec80;
            --danger: #ff4d4d;
            --success: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: var(--bg-dark);
            color: white;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: #111;
            padding: 20px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar h2 {
            color: var(--gold);
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 12px;
            border-radius: 10px;
            margin: 6px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: #111;
            font-weight: 600;
            transform: translateX(5px);
        }

        /* Main Area */
        .main {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topbar h1 {
            font-size: 26px;
            color: var(--gold);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--gold);
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin-bottom: 15px;
            color: var(--gold);
            border-left: 4px solid var(--gold);
            padding-left: 10px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: var(--gold);
            color: #111;
        }

        tr:nth-child(even) {
            background: #222;
        }

        .fee-paid {
            background: var(--success);
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .fee-unpaid {
            background: var(--danger);
            color: white;
            font-weight: bold;
            text-align: center;
        }

        /* Progress */
        .progress-container {
            background: #333;
            border-radius: 20px;
            height: 25px;
            width: 100%;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            text-align: center;
            color: #111;
            font-weight: bold;
        }

        /* Notices */
        .notices ul {
            list-style: none;
        }

        .notices li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .marquee {
            overflow: hidden;
            white-space: nowrap;
            color: var(--gold);
            font-weight: bold;
            animation: marq 12s linear infinite;
        }

        @keyframes marq {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* Responsive */
        @media(max-width:768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
            }

            .main {
                padding: 15px;
            }
        }
    </style>
    <script>
        function showSection(section) {
            document.querySelectorAll('.dynamic-section').forEach(s => s.style.display = 'none');
            document.getElementById(section).style.display = 'block';
            document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));
            document.querySelector('.sidebar a[data-section="' + section + '"]').classList.add('active');
        }
    </script>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>🎓 NES SCHOOL</h2>
        <a href="javascript:void(0)" data-section="welcome" class="active" onclick="showSection('welcome')"><i
                class="fas fa-home"></i> Dashboard</a>
        <a href="javascript:void(0)" data-section="profile" onclick="showSection('profile')"><i class="fas fa-user"></i>
            Profile</a>
        <a href="javascript:void(0)" data-section="course" onclick="showSection('course')"><i class="fas fa-book"></i>
            Course</a>
        <a href="javascript:void(0)" data-section="attendance" onclick="showSection('attendance')"><i
                class="fas fa-chart-line"></i> Attendance</a>
        <a href="javascript:void(0)" data-section="fee" onclick="showSection('fee')"><i class="fas fa-credit-card"></i>
            Fee</a>
        <a href="javascript:void(0)" data-section="notices" onclick="showSection('notices')"><i class="fas fa-bell"></i>
            Notices</a>
        <a href="student_login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main -->
    <div class="main">
        <!-- Top Bar -->
        <div class="topbar">
            <h1>📊 Student Dashboard</h1>
            <div class="user-info">
                <img src="<?= $studentPhoto ?>" alt="Student">
                <span><?= htmlspecialchars($student['student_name']) ?></span>
            </div>
        </div>

        <!-- Welcome -->
        <div class="card dynamic-section" id="welcome">
            <h3>👋 Welcome</h3>
            <p>Welcome <b><?= htmlspecialchars($student['student_name']) ?></b> to your NES SCHOOL Dashboard.</p>
            <div class="marquee">📢 Latest Notice:
                <?= count($notices) ? htmlspecialchars($notices[0]) : "No new notices." ?></div>
        </div>

        <!-- Profile -->
        <div class="card dynamic-section" id="profile" style="display:none;">
            <h3>👤 Profile</h3>
            <img src="<?= $studentPhoto ?>" width="120"
                style="border-radius:50%;border:3px solid var(--gold);margin-bottom:10px;">
            <p><b>Name:</b> <?= htmlspecialchars($student['student_name']) ?></p>
            <p><b>Father's Name:</b> <?= htmlspecialchars($student['father_name']) ?></p>
            <p><b>Registration No:</b> NES-<?= htmlspecialchars($student['reg_no']) ?></p>
            <p><b>Course:</b> <?= htmlspecialchars($student['course_name']) ?></p>
        </div>

        <!-- Course -->
        <div class="card dynamic-section" id="course" style="display:none;">
            <h3>📘 Course & Fee Details</h3>
            <table>
                <tr>
                    <th>Course</th>
                    <td><?= htmlspecialchars($student['course_name']) ?></td>
                </tr>
                <tr>
                    <th>Admission Fee</th>
                    <td>Rs. <?= htmlspecialchars($student['admission_fee']) ?></td>
                </tr>
                <tr>
                    <th>Monthly Fee</th>
                    <td>Rs. <?= htmlspecialchars($student['fee']) ?></td>
                </tr>
            </table>
        </div>

        <!-- Attendance -->
        <div class="card dynamic-section" id="attendance" style="display:none;">
            <h3>🗓 Attendance</h3>
            <p><b>Total Classes:</b> <?= $attendance_total ?> | <b>Present:</b> <?= $attendance_present ?> |
                <b>Absent:</b> <?= $attendance_absent ?></p>
            <div class="progress-container">
                <div class="progress-bar"
                    style="width:<?= $attendance_percent ?>%;background:<?= $attendance_percent >= 70 ? 'var(--success)' : 'var(--danger)' ?>;">
                    <?= $attendance_percent ?>%
                </div>
            </div>
            <canvas id="attendanceChart" height="120"></canvas>
            <script>
                new Chart(document.getElementById('attendanceChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Absent'],
                        datasets: [{
                            data: [<?= $attendance_present ?>, <?= $attendance_absent ?>],
                            backgroundColor: ['#28a745', '#ff4d4d']
                        }]
                    }
                });
            </script>
        </div>

        <!-- Fee -->
        <div class="card dynamic-section" id="fee" style="display:none;">
            <h3>💰 Fee Status</h3>
            <table>
                <tr>
                    <th>Admission Fee</th>
                    <td>Rs. <?= htmlspecialchars($student['admission_fee']) ?></td>
                </tr>
                <tr>
                    <th>Monthly Fee</th>
                    <td>Rs. <?= htmlspecialchars($student['fee']) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td class="<?= strtolower($student['fee_status']) == 'paid' ? 'fee-paid' : 'fee-unpaid' ?>">
                        <?= strtoupper(htmlspecialchars($student['fee_status'])) ?></td>
                </tr>
            </table>
            <canvas id="feeChart" height="120"></canvas>
            <script>
                new Chart(document.getElementById('feeChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Paid', 'Unpaid'],
                        datasets: [{
                            data: [<?= strtolower($student['fee_status']) == 'paid' ? 1 : 0 ?>, <?= strtolower($student['fee_status']) == 'paid' ? 0 : 1 ?>],
                            backgroundColor: ['#28a745', '#ff4d4d']
                        }]
                    }
                });
            </script>
        </div>

        <!-- Notices -->
        <div class="card dynamic-section notices" id="notices" style="display:none;">
            <h3>📢 Notices</h3>
            <ul>
                <?php foreach ($notices as $n): ?>
                    <li><?= htmlspecialchars($n) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>

</html>