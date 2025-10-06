<?
session_name('user_session');
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = (string)$_SESSION['user_id'];
$is_logged_in = true;
?>