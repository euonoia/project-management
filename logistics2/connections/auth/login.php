<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/hehe.png">
    <title>TNVS admin</title>
    <script src="./config/firebase-config.php"></script>
    <script type="module" src="./config/firebase.js"></script>
</head>
<body>

    <h1>Sign In</h1>
    <form id="signInForm">
        <div id="signInMessage"></div>
        <div class="form-group">
            <label for="signInEmail">Email:</label>
            <input type="email" id="signInEmail" name="email" required>
        </div>
        <div class="form-group">
            <label for="signInPassword">Password:</label>
            <input type="password" id="signInPassword" name="password" required>
        </div>
        <button type="submit">Sign In</button>
    </form>

<script type="module">
  import { emailSignIn, emailSignUp, getAuthToken, getCurrentUser } from './config/firebase.js';

  const signInForm = document.getElementById('signInForm');
  const signInMsg = document.getElementById('signInMessage');
  if (signInForm) {
    signInForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (signInMsg) { signInMsg.textContent = ''; signInMsg.style.color = ''; }
      const email = document.getElementById('signInEmail').value.trim();
      const password = document.getElementById('signInPassword').value;
      try {
        const user = await emailSignIn(email, password);
        const token = await getAuthToken(true);
        const payload = { uid: (user && user.uid) ? user.uid : (getCurrentUser()?.uid || ''), idToken: token || '', email };
        const resp = await fetch('./admin_login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok || !data.ok) {
          throw new Error(data.error || 'Server session creation failed');
        }
        window.location.href = data.redirect || './index.php';
      } catch (err) {
        if (signInMsg) {
          signInMsg.textContent = (err && err.message) ? err.message : 'Failed to sign in.';
          signInMsg.style.color = 'red';
        }
      }
    });
  }

</script>

</body>
</html>