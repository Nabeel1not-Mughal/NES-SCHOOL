<?php
$conn = new mysqli("localhost", "root", "", "student_dashboard");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';
$limit = 10;
$page = $_GET['page'] ?? 1;
$page = max(1, (int) $page);
$offset = ($page - 1) * $limit;

// WHERE conditions
$where = "WHERE (students.id LIKE '%$search%' OR students.student_name LIKE '%$search%')";
if ($status === "paid") {
  $where .= " AND students.fee_status='paid'";
} elseif ($status === "unpaid") {
  $where .= " AND students.fee_status='unpaid'";
}

$count_sql = "SELECT COUNT(*) AS total 
              FROM students 
              LEFT JOIN courses ON students.course = courses.id
              $where";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT students.id, students.student_name, students.father_name, students.fee_status, 
               courses.name AS course_name
        FROM students 
        LEFT JOIN courses ON students.course = courses.id
        $where
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Student Records</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #F2F6FB;
      padding: 40px 20px;
    }

    .container {
      max-width: 1200px;
      margin: auto;
      background: #FFFFFF;
      padding: 25px 35px;
      border-radius: 18px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
      animation: fadeIn 0.8s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h2 {
      text-align: center;
      color: #0D47A1;
      font-size: 32px;
      margin-bottom: 25px;
      font-weight: bold;
    }

    /* Search + Filter */
    .search-box {
      text-align: center;
      margin-bottom: 25px;
    }

    .search-box input,
    .search-box select {
      padding: 12px 15px;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 15px;
      outline: none;
      margin-right: 8px;
    }

    .search-box button {
      padding: 12px 18px;
      border-radius: 10px;
      border: none;
      background: #2E7D32;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    .search-box button:hover {
      background: #1B5E20;
      box-shadow: 0 0 8px #2E7D32;
    }

    /* Table Styling */
    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 12px;
      overflow: hidden;
      margin-top: 10px;
    }

    th,
    td {
      padding: 14px;
      text-align: center;
      font-size: 15px;
      border-bottom: 1px solid #ddd;
    }

    thead {
      background: #0D47A1;
      color: #fff;
      position: sticky;
      top: 0;
      z-index: 2;
    }

    tr {
      transition: 0.3s;
    }

    tr:hover {
      background: #E8F5E9;
      transform: scale(1.01);
    }

    td {
      color: #111;
    }

    .paid {
      color: #2E7D32;
      font-weight: bold;
    }

    .unpaid {
      color: #C62828;
      font-weight: bold;
    }

    /* Pagination */
    .pagination {
      text-align: center;
      margin-top: 25px;
    }

    .pagination a {
      display: inline-block;
      padding: 9px 14px;
      margin: 0 5px;
      border-radius: 8px;
      text-decoration: none;
      border: 1px solid #0D47A1;
      color: #0D47A1;
      font-weight: bold;
      transition: 0.3s;
    }

    .pagination a.active,
    .pagination a:hover {
      background: #0D47A1;
      color: #fff;
      box-shadow: 0 0 8px #0D47A1;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>📋 Student's Fee Record</h2>

    <!-- Search + Filter -->
    <div class="search-box">
      <form method="GET">
        <input type="text" name="search" placeholder="🔍 Search by ID or Name"
          value="<?php echo htmlspecialchars($search); ?>">
        <select name="status">
          <option value="all" <?= $status == 'all' ? 'selected' : '' ?>>All</option>
          <option value="paid" <?= $status == 'paid' ? 'selected' : '' ?>>Paid</option>
          <option value="unpaid" <?= $status == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>

    <!-- Table -->
    <table>
      <thead>
        <tr>
          <th>Roll #</th>
          <th>Student Name</th>
          <th>Father Name</th>
          <th>Course</th>
          <th>Fee Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['id']); ?></td>
              <td><?php echo htmlspecialchars($row['student_name']); ?></td>
              <td><?php echo htmlspecialchars($row['father_name']); ?></td>
              <td><?php echo $row['course_name'] ? htmlspecialchars($row['course_name']) : "N/A"; ?></td>
              <td>
                <?php if ($row['fee_status'] === 'paid'): ?>
                  <span class="paid">✅ Paid</span>
                <?php else: ?>
                  <span class="unpaid">❌ Unpaid</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"
          class="<?= ($i == $page) ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>


  </div>
</body>

</html>