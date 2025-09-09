<?php
// Authentication handler for signup and signin
// Sets session variables so other pages (e.g., Fleet Vehicle Management) can display the current user's name

session_start();
include('../../../database/connect.php');

function sanitize($value) {
    return is_string($value) ? trim($value) : $value;
}

function generateUserId(): string {
    return 'USR-' . strtoupper(bin2hex(random_bytes(4)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SIGN UP
    if (isset($_POST['signUp'])) {
        // Accept multiple possible field names from the form
        $user_id   = sanitize($_POST['registration'] ?? '') ?: generateUserId();
        $firstName = sanitize($_POST['firstname'] ?? ($_POST['fName'] ?? ''));
        $lastName  = sanitize($_POST['lastname'] ?? ($_POST['lName'] ?? ''));
        $email     = sanitize($_POST['email'] ?? '');
        $password  = sanitize($_POST['password'] ?? '');
        $role      = sanitize($_POST['role'] ?? '');

        if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || $role === '') {
            echo 'All fields are required.';
            exit();
        }

        $passwordHashed = md5($password); // Keep MD5 to match existing schema

        // Check if email already exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        if (!$stmt) { echo 'DB Error: prepare failed'; exit(); }
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            echo 'Email Address Already Exists!';
            $stmt->close();
            exit();
        }
        $stmt->close();

        // Insert new user
        $stmt = $conn->prepare('INSERT INTO users (user_id, firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?, ?)');
        if (!$stmt) { echo 'DB Error: prepare failed'; exit(); }
        $stmt->bind_param('ssssss', $user_id, $firstName, $lastName, $email, $passwordHashed, $role);
        if ($stmt->execute()) {
            // Set session for the new user
            $_SESSION['user_id']   = $user_id;
            $_SESSION['firstname'] = $firstName;
            $_SESSION['lastname']  = $lastName;
            $_SESSION['email']     = $email;
            $_SESSION['role']      = $role;
            $stmt->close();
            header('Location: /PM-TNVS/logistics2/index.php');
            exit();
        } else {
            echo 'Error: ' . $stmt->error;
            $stmt->close();
            exit();
        }
    }

    // SIGN IN
    if (isset($_POST['signIn'])) {
        $email    = sanitize($_POST['email'] ?? '');
        $password = sanitize($_POST['password'] ?? '');
        if ($email === '' || $password === '') {
            echo 'Email and password are required.';
            exit();
        }
        $passwordHashed = md5($password);

        $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? AND password = ? LIMIT 1');
        if (!$stmt) { echo 'DB Error: prepare failed'; exit(); }
        $stmt->bind_param('ss', $email, $passwordHashed);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            // Set session variables for the logged-in user
            $_SESSION['user_id']   = $row['user_id'] ?? ($row['id'] ?? null);
            $_SESSION['firstname'] = $row['firstname'] ?? '';
            $_SESSION['lastname']  = $row['lastname'] ?? '';
            $_SESSION['email']     = $row['email'] ?? '';
            $_SESSION['role']      = $row['role'] ?? '';
            $stmt->close();
            header('Location: /PM-TNVS/logistics2/index.php');
            exit();
        } else {
            $stmt->close();
            echo 'Not Found, Incorrect Email or Password';
            exit();
        }
    }
}

// If reached here without a handled POST action
header('Location: /PM-TNVS/logistics2/index.php');
exit();
