<?php
$conn = new mysqli('localhost', 'root', '', 'student_dashboard');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);

}

$sql = "SELECT * FROM courses";
$result = $conn->query($sql);
$fees = [];
while ($row = $result->fetch_assoc()) {
  $fees[$row['id']] = [
    "class_name" => $row['name'],
    "admission_fee" => $row['admission_fee'],
    "monthly_fee" => $row['fee']
  ];
}

function generateRegNo($conn)
{
  do {
    $regNo = str_pad(mt_rand(100000, 999999), 6, "0", STR_PAD_LEFT);
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM students WHERE reg_no = ?");
    $check->bind_param("s", $regNo);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();
  } while ($result['cnt'] > 0);
  return $regNo;
}

$regNo = generateRegNo($conn);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🎓 Admission Form</title>
  <style>
    :root {
      --primary: #006400;
      /* Green */
      --secondary: #0a192f;
      /* Navy */
      --light: #ffffff;
      --danger: #e74c3c;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--light);
      margin: 0;
      padding: 40px;
      color: var(--secondary);
      transition: background 0.3s, color 0.3s;
    }

    body.dark {
      background: #0a192f;
      color: #fff;
    }

    .container {
      max-width: 1100px;
      margin: auto;
      background: var(--light);
      padding: 30px 40px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      animation: fadeIn 0.6s ease-in-out;
      transition: background 0.3s;
    }

    body.dark .container {
      background: #112240;
    }

    h1 {
      text-align: center;
      color: var(--primary);
      font-size: 42px;
      margin-bottom: 25px;
      letter-spacing: 2px;
    }

    /* Registration Card */
    .reg-card {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: #fff;
      font-size: 22px;
      font-weight: bold;
      padding: 18px;
      border-radius: 12px;
      text-align: center;
      margin-bottom: 25px;
    }

    /* Floating Labels */
    .form-group {
      position: relative;
      margin-bottom: 20px;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 14px;
      border: 2px solid #ccc;
      border-radius: 10px;
      background: transparent;
      font-size: 16px;
      color: inherit;
      transition: border-color 0.3s;
    }

    .form-group label {
      position: absolute;
      top: 50%;
      left: 14px;
      transform: translateY(-50%);
      font-size: 14px;
      color: gray;
      pointer-events: none;
      transition: 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: var(--primary);
    }

    .form-group input:focus+label,
    .form-group input:not(:placeholder-shown)+label,
    .form-group select:focus+label,
    .form-group select:valid+label {
      top: -8px;
      left: 12px;
      background: var(--light);
      padding: 0px 5px;
      font-size: 15px;
      border-radius: 10px;
      color: var(--primary);
    }

    /* Submit Button */
    .submit {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      font-size: 18px;
      font-weight: bold;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      margin-top: 25px;
      transition: 0.3s;
    }

    .submit:hover {
      box-shadow: 0 0 15px var(--primary);
      transform: translateY(-2px);
    }

    /* Dark mode toggle */
    .toggle-btn {
      position: fixed;
      top: 20px;
      right: 20px;
      background: var(--primary);
      color: #fff;
      border: none;
      padding: 10px 15px;
      border-radius: 50px;
      cursor: pointer;
    }

    /* Toaster */
    .toaster {
      position: fixed;
      top: 20px;
      right: 20px;
      min-width: 250px;
      background: var(--primary);
      color: white;
      padding: 15px 20px;
      border-radius: 8px;
      display: none;
      z-index: 1000;
      animation: slideIn 0.5s ease, fadeOut 0.5s ease 3s forwards;
    }

    .toaster.error {
      background: var(--danger);
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(100px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateX(100px);
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
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
      width: 220px;
      /* add width */
      height: 220px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      transition: 0.3s;
    }

    [id^="box-"] img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      /* ensures full fit */
      border-radius: 12px;
    }

    [id^="box-"]:hover {
      border-color: lightgreen;
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
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
      position: absolute;
      top: 0;
      left: 0;
    }

    /* input[type='file'] {
      width: 220px;
      height: 220px;
      opacity: 0;
      cursor: pointer;
    } */
  </style>
</head>

<body>
  <button class="toggle-btn" onclick="toggleMode()">🌙</button>

  <div class="container">
    <h1>🎓 Admission Form</h1>

    <div class="reg-card">
      Registration No: <span id="regno"></span>
    </div>

    <form id="studentForm" action="submit.php" enctype="multipart/form-data" method="POST">
      <input type="hidden" name="regno" id="hiddenReg">

      <div class="form-group">
        <input type="text" id="studentName" name="studentName" placeholder=" " required>
        <label for="studentName">Student Name</label>
      </div>

      <div class="form-group">
        <input type="text" id="fatherName" name="fatherName" placeholder=" " required>
        <label for="fatherName">Father's Name</label>
      </div>

      <div class="form-group">
        <input type="date" id="dob" name="dob" placeholder=" " required>
        <label for="dob">DOB</label>
      </div>

      <div class="form-group">
        <input id="phone" name="phone" type="text" placeholder=" " maxlength="12" required>
        <label for="phone">Phone Number</label>
      </div>

      <div class="form-group">
        <input type="text" id="bform" name="bform" placeholder=" " maxlength="15" required>
        <label for="bform">B-Form</label>
      </div>

      <div class="form-group">
        <input type="text" name="address" id="address" placeholder=" " required>
        <label for="address">Address</label>
      </div>

      <div class="form-group">
        <select id="course" name="course" onchange="showFee()" required>
          <option disabled selected value=""></option>
          <?php foreach ($fees as $id => $data): ?>
            <option value="<?= $id ?>"><?= $data['class_name'] ?></option>
          <?php endforeach; ?>
        </select>
        <label for="course">Course</label>
      </div>

      <div class="form-group">
        <select id="gender" name="gender" required>
          <option disabled selected value=""></option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
        <label for="gender">Gender</label>
      </div>

      <div class="form-group">
        <input type="text" id="admission_fee" readonly placeholder=" ">
        <label>Admission Fee</label>
      </div>

      <div class="form-group">
        <input type="text" id="fee" readonly placeholder=" ">
        <label>Monthly Fee</label>
      </div>

      <div class="form-group">
        <input type="text" id="total" readonly placeholder=" ">
        <label>Total Fee</label>
      </div>
      <button type="button" id="add_image" onclick="show_box()">+ Add Image</button>
      <div id="add_input"></div>

      <button type="submit" name="submit" class="submit">Submit Student</button>
    </form>
  </div>

  <div id="toaster" class="toaster"></div>

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


    var regNo = <?php echo json_encode($regNo); ?>;
    document.getElementById('regno').innerText = regNo;
    document.getElementById('hiddenReg').value = regNo;

    var feeData = <?php echo json_encode($fees); ?>;
    function showFee() {
      var classId = document.getElementById("course").value;
      if (classId && feeData[classId]) {
        var admission = parseInt(feeData[classId]['admission_fee']);
        var monthly = parseInt(feeData[classId]['monthly_fee']);
        var total = admission + monthly;
        document.getElementById("admission_fee").value = admission;
        document.getElementById("fee").value = monthly;
        document.getElementById("total").value = total;
      }
    }

    function showToaster(message, type = "success") {
      const toaster = document.getElementById("toaster");
      toaster.textContent = message;
      toaster.className = `toaster ${type}`;
      toaster.style.display = "block";
      setTimeout(() => { toaster.style.display = "none"; }, 3500);
    }

    document.getElementById("studentForm").addEventListener("submit", function () {
      showToaster("✅ Student data submitted successfully!");
    });


    function toggleMode() {
      document.body.classList.toggle("dark");
    }
  </script>
</body>

</html>