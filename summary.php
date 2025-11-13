<?php
$conn = mysqli_connect("localhost", "root", "", "student_dashboard");
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Fetch counts
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
$male_query = mysqli_query($conn, "SELECT COUNT(*) AS male FROM students WHERE gender='male'");
$female_query = mysqli_query($conn, "SELECT COUNT(*) AS female FROM students WHERE gender='female'");

$total = mysqli_fetch_assoc($total_query)['total'];
$male = mysqli_fetch_assoc($male_query)['male'];
$female = mysqli_fetch_assoc($female_query)['female'];

$male_percent = $total > 0 ? round(($male / $total) * 100) : 0;
$female_percent = $total > 0 ? round(($female / $total) * 100) : 0;

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quick Stats</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f3f6f9;
      padding: 40px 20px;
      text-align: center;
      color: #243044;
    }

    h1 {
      color: #0D47A1;
      margin-bottom: 40px;
      text-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .stats {
      display: flex;
      justify-content: center;
      gap: 40px;
      flex-wrap: wrap;
    }

    .stat {
      background: #fff;
      padding: 25px 30px;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      text-align: center;
      flex: 1 1 250px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
    }

    .stat i {
      font-size: 60px;
      margin-bottom: 15px;
      padding: 18px;
      border-radius: 50%;
      color: #fff;
    }

    .female i {
      background: #1B5E20;
    }

    .male i {
      background: #0D47A1;
    }

    .percent {
      font-size: 36px;
      font-weight: bold;
      color: #1B5E20;
    }

    .label {
      font-size: 18px;
      color: #555;
    }

    .total-box {
      margin-top: 40px;
      font-size: 22px;
      font-weight: bold;
      background: linear-gradient(90deg, #0D47A1, #1B5E20);
      color: white;
      padding: 14px 30px;
      border-radius: 12px;
      display: inline-block;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Progress Bar */
    .progress-container {
      margin: 50px auto;
      max-width: 650px;
      background: #e0e0e0;
      border-radius: 25px;
      overflow: hidden;
      display: flex;
      height: 35px;
      box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .progress-female {
      background: linear-gradient(90deg, #43A047, #66BB6A);
      text-align: center;
      color: white;
      font-weight: bold;
      line-height: 35px;
      transition: width 1s ease;
    }

    .progress-male {
      background: linear-gradient(90deg, #0D47A1, #1976D2);
      text-align: center;
      color: white;
      font-weight: bold;
      line-height: 35px;
      transition: width 1s ease;
    }

    @media(max-width:768px) {
      .stats {
        flex-direction: column;
        gap: 20px;
      }
    }
  </style>
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

  <h1>📊 Quick Stats</h1>

  <div class="stats">
    <!-- Female -->
    <div class="stat female">
      <i class="fa-solid fa-person-dress"></i>
      <div class="percent" id="femalePercent">0%</div>
      <div class="label">Female (<?php echo $female; ?>)</div>
    </div>

    <!-- Male -->
    <div class="stat male">
      <i class="fa-solid fa-person"></i>
      <div class="percent" id="malePercent">0%</div>
      <div class="label">Male (<?php echo $male; ?>)</div>
    </div>
  </div>

  <!-- Progress Bar -->
  <div class="progress-container">
    <div class="progress-female" style="width: 0%;" id="femaleBar"></div>
    <div class="progress-male" style="width: 0%;" id="maleBar"></div>
  </div>

  <div class="total-box">
    Total Students: <?php echo $total; ?>
  </div>

  <script>
    // Animate percentages
    function animateValue(id, start, end, duration, suffix = "") {
      let obj = document.getElementById(id);
      let startTime = null;

      function step(timestamp) {
        if (!startTime) startTime = timestamp;
        let progress = Math.min((timestamp - startTime) / duration, 1);
        let value = Math.floor(progress * (end - start) + start);
        obj.textContent = value + suffix;
        if (progress < 1) {
          window.requestAnimationFrame(step);
        }
      }
      window.requestAnimationFrame(step);
    }

    // Animate female & male %
    animateValue("femalePercent", 0, <?php echo $female_percent; ?>, 1000, "%");
    animateValue("malePercent", 0, <?php echo $male_percent; ?>, 1000, "%");

    // Animate bars
    document.getElementById("femaleBar").style.width = "<?php echo $female_percent; ?>%";
    document.getElementById("maleBar").style.width = "<?php echo $male_percent; ?>%";
  </script>
</body>

</html>