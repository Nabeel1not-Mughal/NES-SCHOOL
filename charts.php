<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Student Statistics</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f3f6f9;
      margin: 0;
      padding: 40px 20px;
      color: #243044;
      text-align: center;
    }

    h1 {
      color: #0D47A1;
      font-size: 32px;
      margin-bottom: 40px;
      text-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .section {
      margin: 30px auto;
      max-width: 800px;
      background: #fff;
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      text-align: left;
    }

    .section:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
    }

    .section h3 {
      margin: 0;
      font-size: 22px;
      margin-bottom: 20px;
      color: #1B5E20;
      border-left: 6px solid #0D47A1;
      padding-left: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      border-radius: 12px;
      overflow: hidden;
    }

    th,
    td {
      padding: 14px;
      text-align: center;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background: #0D47A1;
      color: #fff;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 14px;
    }

    td {
      background: #fafafa;
      color: #243044;
      font-weight: 500;
      position: relative;
    }

    tr:hover td {
      background: #E8F5E9;
      color: #1B5E20;
      font-weight: bold;
    }

    /* Progress bar style inside table */
    .progress {
      height: 10px;
      border-radius: 6px;
      background: #cfd8dc;
      margin-top: 6px;
      overflow: hidden;
    }

    .progress span {
      display: block;
      height: 100%;
      background: linear-gradient(90deg, #1B5E20, #4CAF50);
    }
  </style>
</head>

<body>

  <h1>📊 Student Statistics</h1>

  <?php
  $conn = new mysqli("localhost", "root", "", "student_dashboard");
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Total Students (for percentage bars)
  $totalStudentsResult = $conn->query("SELECT COUNT(*) as total FROM students");
  $totalStudents = $totalStudentsResult->fetch_assoc()['total'];

  // Students per Course
  echo "<div class='section'>
  <h3>Students Per Course</h3>
  <table>
  <tr>
  <th>Course</th>
  <th>Count</th>
  <th>Percentage</th>
  </tr>";

  $courseResult = $conn->query("
    SELECT c.name AS course_name, COUNT(s.id) as count 
    FROM students s
    JOIN courses c ON s.course = c.id
    GROUP BY c.name
  ");

  while ($row = $courseResult->fetch_assoc()) {
    $percentage = ($totalStudents > 0) ? round(($row['count'] / $totalStudents) * 100, 1) : 0;
    echo "<tr>
      <td>" . htmlspecialchars($row['course_name']) . "</td>
      <td>" . $row['count'] . "</td>
      <td>
        {$percentage}%
        <div class='progress'><span style='width: {$percentage}%;'></span></div>
      </td>
    </tr>";
  }
  echo "</table></div>";

  $conn->close();
  ?>

</body>

</html>