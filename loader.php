<?php
session_start();
if (!isset($_SESSION['adminName'])) {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <title>NES SCHOOL — Loading...</title>
  <link rel="icon" href="image/nes_school.png">
  <style>
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #e8f5e9, #bbdefb);
      overflow: hidden
    }

    #main {
      width: 100%;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .loader {
      width: 100px;
      aspect-ratio: 1;
      display: grid;
      border: 4px solid #0000;
      border-radius: 50%;
      border-right-color: #25b09b;
      animation: l15 1s infinite linear;
    }

    .loader::before,
    .loader::after {
      content: "";
      grid-area: 1/1;
      margin: 2px;
      border: inherit;
      border-radius: 50%;
      animation: l15 2s infinite;
    }

    .loader::after {
      margin: 8px;
      animation-duration: 3s;
    }

    @keyframes l15 {
      100% {
        transform: rotate(1turn)
      }
    }
  </style>
</head>

<body>
  <div id="main">
    <div class="loader"></div>
  </div>
  <script>
    setTimeout(() => {
      window.location.href = "index.php";
    }, 5000);
  </script>

</body>

</html>