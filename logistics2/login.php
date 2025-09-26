<?php
include('../database/connect.php');
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>
    <div class="container" id="signup" style="display:none;">
      <h1 class="form-title"></h1>
      <form method="post" action="connections/auth/bawalpumasok.php">
        <div class="input-group">
          <label for="fName">
            <span class="icon-label">
              <i class="fas fa-user"></i>
              <span>First Name</span>
            </span>
          </label>
          <input type="text" name="firstname" id="firstname" placeholder="First Name" required>
        </div>
        <div class="input-group">
          <label for="lName">
            <span class="icon-label">
              <i class="fas fa-user"></i>
              <span>Last Name</span>
            </span>
          </label>
          <input type="text" name="lastname" id="lastname" placeholder="Last Name" required>
        </div>
        <div class="input-group">
          <label for="email">
            <span class="icon-label">
              <i class="fas fa-envelope"></i>
              <span>Email</span>
            </span>
          </label>
          <input type="email" name="email" id="email" placeholder="Email" required>
        </div>
        <div class="input-group">
          <label for="password">
            <span class="icon-label">
              <i class="fas fa-lock"></i>
              <span>Password</span>
            </span>
          </label>
          <input type="password" name="password" id="password" placeholder="Password" required>
        </div>
        <div class="input-group">
          <input type="hidden" name="role" id="role" value="user">
        </div>
        <input type="submit" class="btn" value="Sign Up" name="signUp">
      </form>
 
     
      <div class="links">
        <p>Already Have Account ?</p>
        <button id="signInButton">Sign In</button>
      </div>
    </div>
    
    <div class="container" id="signIn">
        <h1 class="form-title">Sign In</h1>
        <form method="post" action="connections/auth/bawalpumasok.php">
          <div class="input-group">
            <label for="email">
              <span class="icon-label">
                <i class="fas fa-envelope"></i>
                <span>Email</span>
              </span>
            </label>
            <input type="email" name="email" id="email" placeholder="Email" required>
          </div>
          <div class="input-group">
            <label for="password">
              <span class="icon-label">
                <i class="fas fa-lock"></i>
                <span>Password</span>
              </span>
            </label>
            <input type="password" name="password" id="password" placeholder="Password" required>
          </div>
          <input type="submit" class="btn" value="Sign In" name="signIn">
        </form>
        <div class="links">
          <p>Don't have account yet?</p>
          <button id="signUpButton">Sign Up</button>
        </div>
      </div>
        <script>
        const signUpButton=document.getElementById('signUpButton');
        const signInButton=document.getElementById('signInButton');
        const signInForm=document.getElementById('signIn');
        const signUpForm=document.getElementById('signup');

        signUpButton.addEventListener('click',function(){
            signInForm.style.display="none";
            signUpForm.style.display="block";
        });
        signInButton.addEventListener('click', function(){
            signInForm.style.display="block";
            signUpForm.style.display="none";
        });
        </script>
</body>
</html>