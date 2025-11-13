<?php
$conn = new mysqli('localhost', 'root', '', 'student_dashboard');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=students_list.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo '<html><head>
<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
    }
    th {
        background-color: #1b5e20;
        color: #fff;
        font-weight: bold;
        padding: 8px;
        border: 1px solid #000;
        text-align: center;
    }
    td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
    }
    h2 {
        text-align: center;
        color: navy;
    }
    .footer {
        margin-top:20px;
        text-align:center;
        font-size:12px;
        color:gray;
    }
</style>
</head><body>';

echo "<h2>NES School - All Students List</h2>";
echo "<table>
        <tr>
            <th>Sr.No</th>
            <th>Name</th>
            <th>Father</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Course</th>
            <th>DOB</th>
            <th>Gender</th>
        </tr>";

$sql = "SELECT students.*, courses.name AS course_name 
        FROM students 
        LEFT JOIN courses ON students.course = courses.id 
        ORDER BY students.id DESC";
$result = $conn->query($sql);

$sr = 1;
while ($row = $result->fetch_assoc()) {
    $dob = !empty($row['dob']) ? date("d-m-Y", strtotime($row['dob'])) : 'N/A';
    echo "<tr>
            <td>{$sr}</td>
            <td>".htmlspecialchars($row['student_name'])."</td>
            <td>".htmlspecialchars($row['father_name'])."</td>
            <td>".htmlspecialchars($row['phone'])."</td>
            <td>".htmlspecialchars($row['address'])."</td>
            <td>".htmlspecialchars($row['course_name'])."</td>
            <td>{$dob}</td>
            <td>".htmlspecialchars($row['gender'])."</td>
          </tr>";
    $sr++;
}
echo "</table>";

echo '<div class="footer">Generated on '.date("d-m-Y H:i:s").' by NES School Dashboard</div>';
echo "</body></html>";
exit;
