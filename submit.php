<?php
$conn = new mysqli('localhost', 'root', '', 'student_dashboard');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $regno = $_POST["regno"];
    $studentname = strtoupper($_POST["studentName"]);
    $fathername = strtoupper($_POST["fatherName"]);
    $dob = $_POST["dob"];
    $phone = $_POST["phone"];
    $bform = $_POST["bform"];
    $address = strtoupper($_POST["address"]);
    $religion = strtoupper($_POST["religion"]);
    $course = $_POST["course"];
    $gender = strtoupper($_POST["gender"]);

    // 1. Insert into students
    $stmt = $conn->prepare("INSERT INTO students (reg_no, student_name, father_name, dob, phone, b_form, address, religion, course, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssss",$regno , $studentname, $fathername,$dob , $phone, $bform, $address, $religion, $course, $gender);

    if ($stmt->execute()) {
        $student_id = $stmt->insert_id;

        // 2. Handle Multiple Images
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (empty($_FILES['images']['name'][$key])) {
                    continue;
                }

                $original_name = basename($_FILES['images']['name'][$key]);
                $image_type = $_FILES['images']['type'][$key];
                $image_temp = $_FILES['images']['tmp_name'][$key];

                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $ext = pathinfo($original_name, PATHINFO_EXTENSION);
                $new_image_name = "Student_ID_" . $student_id . "_{$key}." . $ext;
                $image_path = $upload_dir . $new_image_name;

                if (move_uploaded_file($image_temp, $image_path)) {
                    $img_stmt = $conn->prepare("INSERT INTO images (std_id, path, name, type) VALUES (?, ?, ?, ?)");
                    $img_stmt->bind_param("isss", $student_id, $image_path, $new_image_name, $image_type);
                    $img_stmt->execute();
                    $img_stmt->close();
                } else {
                    echo "Image upload failed for file: " . $original_name;
                    // exit();
                }
            }
        }

        //   var_dump($_FILES);
        //   die;
        // 4. Redirect
        header("Location: submitData.php");
        exit();
    } else {
        echo "Student insert error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>