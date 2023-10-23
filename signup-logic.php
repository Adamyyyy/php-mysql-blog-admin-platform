<?php
require 'config/database.php';

// get sigup form data if signup button is clicked
if (isset($_POST['submit'])) {
  $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_SPECIAL_CHARS);
  $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_SPECIAL_CHARS);
  $username = filter_var($_POST['username'], FILTER_SANITIZE_SPECIAL_CHARS);
  $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
  $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_SPECIAL_CHARS);
  $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_SPECIAL_CHARS);
  $avatar = $_FILES['avatar'];

  // validate input values
  if (!$firstname) {
    $_SESSION['signup'] = "Please enter your First Name";
  } elseif (!$lastname) {
    $_SESSION['signup'] = "Please enter your Last Name";
  } elseif (!$username) {
    $_SESSION['signup'] = "Please enter your Username";
  } elseif (!$email) {
    $_SESSION['signup'] = "Please enter your Email";
  } elseif (strlen($createpassword) < 8 || strlen($confirmpassword) < 8) {
    $_SESSION['signup'] = "Password must be at least 8 characters";
  } elseif (!$avatar['name']) {
    $_SESSION['signup'] = "Please add avatar";
  } else {
    // check if password and confirm password match
    if ($createpassword !== $confirmpassword) {
      $_SESSION['signup'] = "Password and confirm password do not match";
    } else {
      // hash password
      $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);

      // check if usename and email already exist in database
      $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
      $user_check_result = mysqli_query($connection, $user_check_query);
      if (mysqli_num_rows($user_check_result) > 0) {
        $_SESSION['signup'] = "Username or Email already exist";
      } else {
        // work on avatar
        // rename avatar
        $time = time();
        $avatar_name = $time . $avatar['name'];
        $avatar_tmp_name = $avatar['tmp_name'];
        $avatar_destination_path = 'images/' . $avatar_name;

        $allowed_files = ['jpg', 'jpeg', 'png'];
        $extention = explode('.', $avatar_name);
        $extention = strtolower(end($extention));
        if (in_array($extention, $allowed_files)) {
          // make sure image is not more than 1mb
          if ($avatar['size'] < 1000000) {
            // upload avatar
            move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
          } else {
            $_SESSION['signup'] = "Image size is too large. Should not be more than 1mb";
          }
        } else {
          $_SESSION['signup'] = "File should be png, jpg or jpeg format";
        }
      }
    }
  }

  // redirect to signup page if there is an error
  if (isset($_SESSION['signup'])) {
    // pass form data back to signup page
    $_SESSION['signup-data'] = $_POST;
    header('location:' . ROOT_URL . 'signup.php');
    die();
  } else {
    // insert new user data into database
    $insert_user_query = "INSERT INTO users (firstname, lastname, username, email, password, avatar, is_admin) VALUES ('$firstname', '$lastname', '$username', '$email', '$hashed_password', '$avatar_name', 0)";

    $insert_user_result = mysqli_query($connection, $insert_user_query);

    if (!mysqli_errno($connection)) {
      // redirect to login page with success message
      $_SESSION['signup-success'] = "You have successfully signed up. Please login";
      header('location:' . ROOT_URL . 'signin.php');
      die();
    }
  }
  var_dump($avatar);
  echo "File name: " . $avatar_tmp_name . "<br>";
  echo "name" . $avatar_name . "<br>";
  echo "path" . $avatar_destination_path . "<br>";
  echo "File extension: $extention <br>";
  echo "File size: " . $avatar['size'] . "<br>";
} else {

  // if button is not clicked, bounce back to signup page
  header('location:' . ROOT_URL . 'signup.php');
  die();
}
