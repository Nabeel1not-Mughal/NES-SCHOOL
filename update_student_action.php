<?php
$conn = new mysqli("localhost", "root", "", "student_dashboard");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id           = $_POST['id'];
$student_name = $_POST['student_name'];
$father_name  = $_POST['father_name'];
$dob          = $_POST['dob'];
$phone        = $_POST['phone'];
$b_form       = $_POST['b_form'];
$address      = $_POST['address'];
$religion     = $_POST['religion'];
$course       = $_POST['course'];
$gender       = $_POST['gender'];

// Get old course
$oldCourseStmt = $conn->prepare("SELECT course FROM students WHERE id=?");
$oldCourseStmt->bind_param("i", $id);
$oldCourseStmt->execute();
$oldResult = $oldCourseStmt->get_result();
$oldCourseRow = $oldResult->fetch_assoc();
$old_course = $oldCourseRow['course'];

// If course changed → reset fee_status = UNPAID
if ($old_course != $course) {
    $stmt = $conn->prepare("UPDATE students SET 
        student_name=?, father_name=?, dob=?, phone=?, b_form=?, address=?, religion=?, course=?, gender=?, fee_status='UNPAID'
        WHERE id=?");
    $stmt->bind_param("sssssssssi", $student_name, $father_name, $dob, $phone, $b_form, $address, $religion, $course, $gender, $id);
} else {
    // Normal update
    $stmt = $conn->prepare("UPDATE students SET 
        student_name=?, father_name=?, dob=?, phone=?, b_form=?, address=?, religion=?, course=?, gender=?
        WHERE id=?");
    $stmt->bind_param("sssssssssi", $student_name, $father_name, $dob, $phone, $b_form, $address, $religion, $course, $gender, $id);
}

if ($stmt->execute()) {
    echo "<script>alert('Student updated successfully!'); window.location='readData.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}
