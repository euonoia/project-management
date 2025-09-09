<?php
session_start();
include('../database/connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User with no vehicle</title>
</head>
<body>
  <p>this is just a user side that just want to use the system</p>
 
  <a href="fleetvehiclemanagement/index.php">want to become our driver?</a> <br>
  <a href="logout.php">Logout</a> <br>
  <a href="reservation/reserve.php">Make a Reservation</a>

</body>
</html>