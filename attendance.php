<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "student_dashboard";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error)
    die("DB error");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $class = $_POST['class_name'] ?? '';
    $date = $_POST['att_date'] ?? date('Y-m-d');
    $presentList = $_POST['present'] ?? [];

    foreach ($presentList as $sid => $val) {
        $sid = (int) $sid;
        $present = 1;
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, class_name, attendance_date, present)
          VALUES (?, ?, ?, ?)
          ON DUPLICATE KEY UPDATE present = VALUES(present), created_at = CURRENT_TIMESTAMP");
        $stmt->bind_param("issi", $sid, $class, $date, $present);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: attendance.php?status=ok");
    exit;
}

$classes = [];
$res = $conn->query("SELECT id,name FROM courses ORDER BY id");
while ($r = $res->fetch_assoc())
    $classes[] = $r;
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Attendance Sheet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #F3F6FA;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }

        .card {
            width: 1100px;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            height: 550px;
        }

        h2 {
            color: #0D47A1;
            text-align: center;
            margin-bottom: 20px;
        }

        .class-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 20px;
        }

        .class-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: #0D47A1;
            color: #fff;
            font-weight: 700;
            transition: 0.3s;
        }

        .class-buttons button.active {
            background: #2E7D32;
        }

        .class-buttons button:hover {
            opacity: 0.85;
        }

        .controls {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            justify-content: center;
            align-items: center;
        }

        select,
        input[type=date] {
            padding: 10px 14px;
            border-radius: 8px;
            border: 2px solid #ccc;
            outline: none;
            font-size: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            color: #111;
            border-radius: 10px;
            overflow: hidden;
        }

        thead {
            background: #0D47A1;
            color: #fff;
            position: sticky;
            top: 0;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #E8F5E9;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 700;
        }

        .btn.green {
            background: #2E7D32;
            color: #fff;
        }

        .btn.green:hover {
            background: #1B5E20;
        }

        .btn.navy {
            background: #0D47A1;
            color: #fff;
        }

        .btn.navy:hover {
            background: #08306b;
        }

        .save-wrap {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
        }

        .switch input {
            display: none;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            background-color: #ccc;
            border-radius: 24px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .4s;
        }

        input:checked+.slider {
            background: #2E7D32;
        }

        input:checked+.slider:before {
            transform: translateX(22px);
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>📋 Attendance</h2>

        <!-- Class buttons -->
        <div class="class-buttons">
            <?php foreach ($classes as $c): ?>
                <button class="class-btn"
                    data-id="<?php echo $c['id'] ?>"><?php echo htmlspecialchars($c['name']) ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Date picker -->
        <div class="controls">
            <label>Date:</label>
            <input type="date" id="attDate" value="<?php echo date('Y-m-d') ?>">
        </div>

        <!-- Attendance form -->
        <form method="POST" id="attendanceForm">
            <input type="hidden" name="save_attendance" value="1">
            <input type="hidden" name="class_name" id="class_name_input" value="">
            <input type="hidden" name="att_date" id="att_date_input" value="">

            <div id="studentsArea" style="display:none">
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Roll #</th>
                            <th>Name</th>
                            <th>Father</th>
                            <th>Present</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="save-wrap">
                    <div>
                        <button type="button" class="btn green" id="markAllPresent">Mark All Present</button>
                        <button type="button" class="btn navy" id="markAllAbsent">Mark All Absent</button>
                    </div>
                    <button class="btn green" type="submit">Save Attendance</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function () {
            const $studentsArea = $('#studentsArea');
            const $tbody = $('#studentsTable tbody');

            // When a class button is clicked
            $('.class-btn').on('click', function () {
                $('.class-btn').removeClass('active');
                $(this).addClass('active');

                let classId = $(this).data('id');
                let date = $('#attDate').val();

                $('#class_name_input').val(classId);
                $('#att_date_input').val(date);

                $tbody.html('<tr><td colspan="5">Loading students...</td></tr>');
                $studentsArea.show();

                $.getJSON('students_by_class.php', { class_id: classId }, function (data) {
                    if (data.error) { toastr.error(data.error); $tbody.html(''); return; }
                    if (!data.length) { $tbody.html('<tr><td colspan="5">No students found</td></tr>'); return; }

                    $tbody.empty();
                    data.forEach((s, i) => {
                        $tbody.append(`<tr>
                    <td>${i + 1}</td>
                    <td>${s.id}</td>
                    <td>${s.student_name}</td>
                    <td>${s.father_name || ''}</td>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="present[${s.id}]" value="1" checked>
                            <span class="slider"></span>
                        </label>
                    </td>
                </tr>`);
                    });
                }).fail(() => { toastr.error('Server error'); $tbody.html(''); });
            });

            $('#markAllPresent').on('click', () => $tbody.find('input[type=checkbox]').prop('checked', true));
            $('#markAllAbsent').on('click', () => $tbody.find('input[type=checkbox]').prop('checked', false));
        });
    </script>
</body>

</html>