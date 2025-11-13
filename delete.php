<?php
$conn = new mysqli('localhost', 'root', '', 'student_dashboard');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];


    $imageSql = "SELECT path FROM images WHERE std_id = '$id'";
    $imageResult = $conn->query($imageSql);
    if ($imageResult->num_rows > 0) {
        while ($row = $imageResult->fetch_assoc()) {
            $file = 'uploads/' . $row['path'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    $deleteImageSql = "DELETE FROM images WHERE std_id = '$id'";
    $conn->query($deleteImageSql);


    $sql = "DELETE FROM students WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result) {
        header("Location: readData.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
