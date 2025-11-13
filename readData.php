<!DOCTYPE html>
<html lang="en">

<?php
$conn = new mysqli('localhost', 'root', '', 'student_dashboard');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// pagination setup
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
  $page = 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// total students count
$count_sql = "SELECT COUNT(DISTINCT students.id) AS total
              FROM students
              LEFT JOIN courses ON students.course = courses.id";
if (!empty($search)) {
  $count_sql .= " WHERE students.student_name LIKE '%$search%'
                  OR students.id LIKE '%$search%'
                  OR courses.name LIKE '%$search%'
                  OR students.father_name LIKE '%$search%'";
}
$result_total = $conn->query($count_sql);
$total_rows = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// fetch students with pagination
$sql = "SELECT students.*, courses.name AS course_name
        FROM students
        LEFT JOIN courses ON students.course = courses.id";

if (!empty($search)) {
  $sql .= " WHERE students.student_name LIKE '%$search%'
            OR students.father_name LIKE '%$search%'";
}
$sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

$students = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    if (!isset($students[$id])) {
      $students[$id] = [
        'id' => $row['id'],
        'student_name' => $row['student_name'],
        'dob' => $row['dob'],
        'father_name' => $row['father_name'],
        'phone' => $row['phone'],
        'b_form' => $row['b_form'],
        'address' => $row['address'],
        'religion' => $row['religion'],
        'course' => $row['course_name'],
        'gender' => $row['gender'],
        'images' => []
      ];
    }
  }
}

// fetch images
$student_ids = array_column($students, 'id');
if (!empty($student_ids)) {
  $ids_str = implode(",", $student_ids);
  $img_sql = "SELECT * FROM images WHERE std_id IN ($ids_str)";
  $img_result = $conn->query($img_sql);
  while ($img = $img_result->fetch_assoc()) {
    $students[$img['std_id']]['images'][] = $img['path'];
  }
}
?>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Students Record</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f0f8f5;
      padding: 40px 20px;
      color: #2c3e50;
    }

    .container {
      max-width: 1400px;
      margin: auto;
      background: #FFFFFF;
      padding: 25px 35px;
      border-radius: 18px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    h1 {
      text-align: center;
      color: #0d47a1;
      font-size: 45px;
      margin-bottom: 20px;
      letter-spacing: 2px;
    }

    h3 {
      text-align: center;
      margin-bottom: 20px;
      color: #2e7d32;
      font-size: 20px;
    }

    #search {
      display: block;
      width: 100%;
      max-width: 400px;
      margin: 0 auto 25px;
      padding: 12px 15px;
      border-radius: 10px;
      border: 2px solid #0d47a1;
      outline: none;
      font-size: 15px;
      font-weight: bold;
      transition: 0.3s;
    }

    #search:focus {
      border-color: #2e7d32;
      box-shadow: 0 0 8px #2e7d32;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    th,
    td {
      padding: 12px 10px;
      text-align: center;
      font-size: 15px;
    }

    th {
      background: #0d47a1;
      color: white;
      font-size: 16px;
    }

    tr:nth-child(even) {
      background: #f9f9f9;
    }

    tr:hover {
      background: #e8f5e9;
    }

    td {
      color: #2c3e50;
      font-weight: 500;
    }

    .slideshow img {
      width: 75px;
      height: 75px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #2e7d32;
    }

    /* Buttons */
    .delete,
    .update,
    .btn {
      padding: 8px 14px;
      border-radius: 8px;
      font-weight: bold;
      text-decoration: none;
      transition: all 0.3s;
      display: inline-block;
      margin: 4px 0;
    }

    .delete {
      background: #b71c1c;
      color: #fff;
    }

    .delete:hover {
      background: red;
    }

    .update {
      background: #2e7d32;
      color: #fff;
    }

    .update:hover {
      background: #66bb6a;
    }

    .btn {
      background: #0d47a1;
      color: #fff;
    }

    .btn:hover {
      background: #1565c0;
    }

    /* Export buttons */
    .export-buttons {
      text-align: right;
      margin-bottom: 20px;
    }

    .export-buttons a {
      background: #2e7d32;
      color: white;
      padding: 10px 16px;
      border-radius: 8px;
      font-weight: bold;
      text-decoration: none;
      margin-left: 10px;
      transition: 0.3s;
    }

    .export-buttons a:hover {
      background: #1b5e20;
    }

    /* Pagination */
    .pagination {
      text-align: center;
      margin-top: 20px;
    }

    .pagination a {
      padding: 8px 14px;
      margin: 0 4px;
      border-radius: 8px;
      text-decoration: none;
      border: 1px solid #0d47a1;
      color: #0d47a1;
      font-weight: bold;
      transition: 0.3s;
    }

    .pagination a.active,
    .pagination a:hover {
      background: #0d47a1;
      color: #fff;
    }
  </style>
</head>

<body>

  <div class="container">
    <h1>🎓 Students Record</h1>
    <h3>Total Students: <?php echo $total_rows ?></h3>

    <div class="export-buttons">
      <a href="export_all_pdf.php" target="_blank"><i class="fa-solid fa-file-pdf"></i> Download PDF</a>
      <a href="export_all_excel.php"><i class="fa-solid fa-file-excel"></i> Download Excel</a>
    </div>

    <form action="readData.php" method="GET" style="text-align:center;">
      <input id="search" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search) ?>">
    </form>

    <table>
      <thead>
        <tr>
          <th>Sr.No.</th>
          <th>Name</th>
          <th>Image</th>
          <th>Father</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Religion</th>
          <th>Course</th>
          <th>Gender</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($students)) {
          $i = $offset + 1;
          foreach ($students as $stu) { ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($stu['student_name']) ?></td>
              <td>
                <?php if (!empty($stu['images'])): ?>
                  <div class="slideshow" data-images='<?= json_encode($stu['images']) ?>'></div>
                <?php else: ?>
                  <span>No Image</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($stu['father_name']) ?></td>
              <td><?= htmlspecialchars($stu['phone']) ?></td>
              <td><?= htmlspecialchars($stu['address']) ?></td>
              <td><?= htmlspecialchars($stu['religion']) ?></td>
              <td><?= htmlspecialchars($stu['course']) ?></td>
              <td><?= htmlspecialchars($stu['gender']) ?></td>
              <td>
                <a class="update" href="update_form.php?id=<?= $stu['id'] ?>">UPDATE</a><br>
                <a class="delete" href="delete.php?id=<?= $stu['id'] ?>"
                  onclick="return confirm('Are You Sure! You Want to Delete this Record?')">DELETE</a><br>
                <a class="btn" href="student_pdf.php?id=<?= $stu['id'] ?>" target="_blank">Export PDF</a>
              </td>
            </tr>
          <?php }
        } else {
          echo "<tr><td colspan='10'>No Student's Data</td></tr>";
        } ?>
      </tbody>
    </table>

    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll(".slideshow").forEach(function (box) {
        let images = JSON.parse(box.dataset.images);
        if (images.length === 0) return;
        let img = document.createElement("img");
        img.src = images[0];
        box.appendChild(img);
        let index = 0;
        setInterval(() => {
          index = (index + 1) % images.length;
          img.src = images[index];
        }, 3000);
      });
    });
  </script>
</body>

</html>