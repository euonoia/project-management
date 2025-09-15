<?php
session_start();
include('../../../database/connect.php');

// Require login (optionally enforce admin role if available)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$session_role = $_SESSION['role'] ?? 'user';
$user_name = trim((($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '')));
if ($user_name === '') $user_name = 'User';

// Helper: HTML escape
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Resolve current schema name
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>
     <form id="signUpForm">
        <div id="signUpMessage"></div>
        <div class="form-group">
            <label for="signUpEmail">Email:</label>
            <input type="email" id="signUpEmail" name="email" required>
        </div>
        <div class="form-group">
            <label for="signUpPassword">Password:</label>
            <input type="password" id="signUpPassword" name="password" required>
        </div>
        <div class="form-group">
            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" required>
        </div>
        <div class="form-group">
            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" name="lastName" required>
        </div>
        <button type="submit">Sign Up</button>
        <a href="index.php">return</a>
    </form>
</body>
<script type="module">
  import { initializeApp, getApps, getApp } from 'https://www.gstatic.com/firebasejs/10.12.1/firebase-app.js';
  import { getAuth, createUserWithEmailAndPassword, signOut } from 'https://www.gstatic.com/firebasejs/10.12.1/firebase-auth.js';

  const signUpForm = document.getElementById('signUpForm');
  const signUpMsg = document.getElementById('signUpMessage');

  const cfg = (typeof window !== 'undefined') ? (window.FIREBASE_CONFIG || null) : null;
  let secondary = null;
  if (cfg) {
    try {
      secondary = getApps().some(a => a.name === 'secondary') ? getApp('secondary') : initializeApp(cfg, 'secondary');
    } catch (e) {
      console.error('Failed to init secondary app', e);
      secondary = null;
    }
  }

  if (signUpForm) {
    signUpForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (signUpMsg) { signUpMsg.textContent=''; signUpMsg.style.color=''; }
      const email = document.getElementById('signUpEmail').value.trim();
      const password = document.getElementById('signUpPassword').value;
      const firstName = (document.getElementById('firstName')?.value || '').trim();
      const lastName = (document.getElementById('lastName')?.value || '').trim();
      if (!secondary) { if (signUpMsg) { signUpMsg.textContent='Config error. Cannot init secondary app.'; signUpMsg.style.color='red'; } return; }
      try {
        const auth2 = getAuth(secondary);
        const cred = await createUserWithEmailAndPassword(auth2, email, password);
        const uid = cred.user?.uid || '';
        try { await signOut(auth2); } catch {}
        const resp = await fetch('./admin_login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'upsert_admin', uid, email, firstName, lastName })
        });
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok || !data.ok) {
          throw new Error(data.error || 'Failed to save admin record');
        }
        if (signUpMsg) { signUpMsg.textContent = 'Admin account created successfully.'; signUpMsg.style.color = 'green'; }
        signUpForm.reset();
      } catch (err) {
        if (signUpMsg) {
          signUpMsg.textContent = (err && err.message) ? err.message : 'Failed to sign up.';
          signUpMsg.style.color = 'red';
        }
      }
    });
  }
</script>
</html>