<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "student_dashboard";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection']);
    exit;
}
$class_id = intval($_GET['class_id'] ?? 0);
if (!$class_id) {
    echo json_encode(['error' => 'Invalid class']);
    exit;
}

$stmt = $conn->prepare("SELECT id, student_name, father_name FROM students WHERE course = ? ORDER BY student_name");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc())
    $out[] = $r;
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out);
