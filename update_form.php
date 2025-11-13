<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'student_dashboard';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? '';
if (!$id) {
    die("No ID provided for update.");
}

// Fetch all courses with fees
$course_selection = "SELECT * FROM courses";
$course_result = $conn->query($course_selection);
$courses = [];
while ($row = $course_result->fetch_assoc()) {
    $courses[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = strtoupper($_POST['student_name']);
    $father_name = strtoupper($_POST['father_name']);
    $dob = $_POST["dob"];
    $phone = $_POST['phone'];
    $b_form = $_POST['bform'];
    $address = strtoupper($_POST['address']);
    $religion = strtoupper($_POST['religion']);
    $course = ($_POST['course']);
    $gender = strtoupper($_POST['gender']);

    $stmt = $conn->prepare("UPDATE students SET student_name=?, father_name=?, dob=?, phone=?, b_form=?, address=?, religion=?, course=?, gender=? WHERE id=?");
    $stmt->bind_param("sssssssssi", $student_name, $father_name, $dob, $phone, $b_form, $address, $religion, $course, $gender, $id);

    if ($stmt->execute()) {
        // Delete old images if new images uploaded
        if (!empty($_FILES['images']['name'][0])) {
            $oldImgQuery = $conn->prepare("SELECT path FROM images WHERE std_id=?");
            $oldImgQuery->bind_param("i", $id);
            $oldImgQuery->execute();
            $oldRes = $oldImgQuery->get_result();
            while ($oldRow = $oldRes->fetch_assoc()) {
                if (file_exists($oldRow['path'])) {
                    unlink($oldRow['path']);
                }
            }
            $conn->query("DELETE FROM images WHERE std_id=$id");

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (empty($_FILES['images']['name'][$key]))
                    continue;

                $original_name = basename($_FILES['images']['name'][$key]);
                $image_type = $_FILES['images']['type'][$key];
                $image_temp = $_FILES['images']['tmp_name'][$key];

                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                $ext = pathinfo($original_name, PATHINFO_EXTENSION);
                $new_image_name = "Student_ID_" . $id . "_" . "_{$key}." . $ext;
                $image_path = $upload_dir . $new_image_name;

                if (move_uploaded_file($image_temp, $image_path)) {
                    $img_stmt = $conn->prepare("INSERT INTO images (std_id, path, name, type) VALUES (?, ?, ?, ?)");
                    $img_stmt->bind_param("isss", $id, $image_path, $new_image_name, $image_type);
                    $img_stmt->execute();
                    $img_stmt->close();
                }
            }
        }

        header("Location: readData.php");
        exit();
    } else {
        echo "Update failed: " . $conn->error;
    }
}

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student)
    die("Student not found.");

// Fetch image
$imageQuery = $conn->prepare("SELECT name FROM images WHERE std_id = ?");
$imageQuery->bind_param("i", $id);
$imageQuery->execute();
$imageResult = $imageQuery->get_result();

$images = [];
while ($row = $imageResult->fetch_assoc())
    $images[] = $row['name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Student</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0B142D;
            padding: 30px;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.6s ease-in-out;
        }

        h2 {
            text-align: center;
            color: #FFDB00;
            font-size: 40px;
            font-weight: bolder;
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            color: #FFDB00;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
            transition: 0.3s;
        }

        input[type="text"],
        input[type="date"] {
            width: 878px;
        }

        input:focus,
        select:focus {
            border-color: #2E7D32;
            box-shadow: 0 0 5px rgba(46, 125, 50, 0.5);
        }

        .btns {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            width: 50%;
            border-radius: 8px;
            transition: 0.3s;
            font-size: 16px;
        }

        .update {
            background: linear-gradient(145deg, #00e676, #00c853);
            color: #fff;
            text-align: center;
            border-radius: 50px;
            box-shadow: 0 6px 15px rgba(0, 230, 118, 0.4),
                inset 2px 2px 6px rgba(105, 240, 174, 0.3);
            transition: all 0.3s ease-in-out;
        }

        .update:hover {
            background: linear-gradient(145deg, #69f0ae, #00e676);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 230, 118, 0.6),
                0 0 20px rgba(0, 230, 118, 0.8),
                0 0 40px rgba(105, 240, 174, 0.9);
        }

        .cancel {
            text-decoration: none;
            background: linear-gradient(145deg, #ff1744, #d50000);
            color: #fff;
            text-align: center;
            border-radius: 50px;
            box-shadow: 0 6px 15px rgba(255, 23, 68, 0.4),
                inset 2px 2px 6px rgba(255, 82, 82, 0.3);
            transition: all 0.3s ease-in-out;
        }

        .cancel:hover {
            background: linear-gradient(145deg, #ff5252, #ff1744);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(255, 23, 68, 0.6),
                0 0 20px rgba(255, 23, 68, 0.8),
                0 0 40px rgba(255, 82, 82, 0.9);
        }


        #add_image {
            padding: 14px 28px;
            background: linear-gradient(135deg, #00c853, #b2ff59);
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 200, 83, 0.4);
            transition: all 0.3s ease-in-out;
        }

        #add_image:hover {
            background: linear-gradient(135deg, #76ff03, #00e676);
            box-shadow: 0 6px 25px rgba(0, 255, 120, 0.6);
            transform: translateY(-3px) scale(1.05);
        }

        #add_image:active {
            transform: translateY(1px) scale(0.98);
            box-shadow: 0 3px 10px rgba(0, 200, 83, 0.3);
        }

        #add_input {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        [id^="box-"] {
            position: relative;
            background: #f0f4ff;
            border: 2px dashed #a0bfff;
            border-radius: 12px;
            width: 220;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: 0.3s;
        }

        [id^="box-"]:hover {
            border-color: #6c63ff;
            background: #eef0ff;
        }

        .del-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 18px;
            font-weight: bold;
            color: white;
            background: crimson;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            line-height: 30px;
            text-align: center;
            transition: 0.2s;
        }

        .del-btn:hover {
            background: darkred;
            transform: scale(1.1);
        }

        img {
            width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: 10px;
        }

        input[type='file'] {
            width: 220px;
            height: 220px;
            opacity: 0;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Update Student Information</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Student Name</label>
            <input type="text" name="student_name" value="<?= htmlspecialchars($student['student_name']); ?>" required>

            <label>Father Name</label>
            <input type="text" name="father_name" value="<?= htmlspecialchars($student['father_name']); ?>" required>

            <label>DOB</label>
            <input type="date" name="dob" value="<?= htmlspecialchars($student['dob']); ?>" required>

            <label>Phone</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($student['phone']); ?>" required>

            <label>B Form</label>
            <input type="text" id="bform" name="bform" value="<?= htmlspecialchars($student['b_form']); ?>" required>

            <label>Address</label>
            <input type="text" name="address" value="<?= htmlspecialchars($student['address']); ?>" required>

            <label>Religion</label>
            <input type="text" name="religion" value="<?= htmlspecialchars($student['religion']); ?>" required>

            <label for="course">Course</label>
            <select id="course" name="course" required>
                <option disabled>--Select Course--</option>
                <?php foreach ($courses as $row) { ?>
                    <option value="<?= $row['id'] ?>" data-fee="<?= $row['fee'] ?>" <?= ($row['id'] == $student['course']) ? 'selected' : ''; ?>>
                        <?= $row['name'] ?>
                    </option>
                <?php } ?>
            </select>

            <label>Monthly Fee</label>

            <input type="text" id="monthly_fee" readonly style="font-weight: bolder;background:#eee; color: green;">

            <label>Gender</label>
            <select name="gender" required>
                <option value="Male" <?= $student["gender"] === 'MALE' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?= $student["gender"] === 'FEMALE' ? 'selected' : ''; ?>>Female</option>
            </select>
            <br>

            <br><button type="button" id="add_image" onclick="show_box()">+ Add Image</button>
            <div id="add_input"></div>

            <?php if ($images): ?>
                <div style="margin-top:15px;">
                    <strong>Current Images:</strong><br>
                    <?php foreach ($images as $img): ?>
                        <img src="uploads/<?= htmlspecialchars($img) ?>" alt="Student Image">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="btns">
                <a href="readData.php" class="btn cancel">Cancel</a>
                <button type="submit" class="btn update">Update</button>
            </div>
        </form>
    </div>

    <script>
        let box_counter = 0;
        function show_box() {
            box_counter++;
            const wrap = document.createElement('div');
            wrap.id = `box-${box_counter}`;

            const newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.name = 'images[]';
            newInput.accept = 'image/*';
            newInput.onchange = function (event) {
                image_box(event.target.files, wrap.id);
            };

            wrap.appendChild(newInput);
            document.getElementById('add_input').appendChild(wrap);
        }

        function image_box(files, id) {
            const preview = document.getElementById(id);
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        preview.appendChild(img);

                        const btn = document.createElement('button');
                        btn.className = "del-btn";
                        btn.innerText = "×";
                        btn.onclick = function () { preview.remove(); };
                        preview.appendChild(btn);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        document.getElementById('phone').addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            if (value.length > 4) value = value.slice(0, 4) + '-' + value.slice(4);
            this.value = value;
        });

        document.getElementById('bform').addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 13) value = value.slice(0, 13);
            if (value.length > 12) {
                value = value.slice(0, 5) + '-' + value.slice(5, 12) + '-' + value.slice(12);
            } else if (value.length > 5) {
                value = value.slice(0, 5) + '-' + value.slice(5);
            }
            this.value = value;
        });

        function updateFee() {
            let courseSelect = document.getElementById("course");
            let fee = courseSelect.options[courseSelect.selectedIndex].getAttribute("data-fee");
            document.getElementById("monthly_fee").value = fee ? fee : "";
        }
        document.getElementById("course").addEventListener("change", updateFee);
        window.onload = updateFee;
    </script>
</body>

</html>