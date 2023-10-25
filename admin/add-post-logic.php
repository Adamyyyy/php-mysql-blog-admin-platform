<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
  $author_id = $_SESSION['user-id'];
  $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $category_id = filter_var($_POST['category-id'], FILTER_SANITIZE_NUMBER_INT);
  $is_featured = filter_var($_POST['is_featured'], FILTER_SANITIZE_NUMBER_INT);
  $thumbnail = $_FILES['thumbnail'];

  // set is_featured to 0 if unchecked
  $is_featured = $is_featured == 1 ?: 0;

  // validate form data
  if (!$title) {
    $_SESSION['add-post'] = "Please enter post title";
  } elseif (!$body) {
    $_SESSION['add-post'] = "Please enter post body";
  } elseif (!$thumbnail['name']) {
    $_SESSION['add-post'] = "Please add thumbnail";
  } else {
    // WORK ON TUMBNAIL
    // rename the image
    $time = time(); // make sure the image name is unique
    $thumbnail_name = $time . $thumbnail['name'];
    $thumbnail_tmp_name = $thumbnail['tmp_name'];
    $thumbnail_destination_path = '../images/' . $thumbnail_name;

    // make sure file is an image
    $allowed_files = ['jpg', 'jpeg', 'png'];
    $extention = explode('.', $thumbnail_name);
    $extention = strtolower(end($extention));
    if (in_array($extention, $allowed_files)) {
      // make sure image is not too big (2mb+)
      if ($thumbnail['size'] < 2_000_000) {
        // upload image
        move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
      } else {
        $_SESSION['add-post'] = "Image size is too large. Should not be more than 2mb";
      }
    } else {
      $_SESSION['add-post'] = "File should be png, jpg or jpeg";
    }
  }
  //redirect back to add post page if there is an error, with form data
  if (isset($_SESSION['add-post'])) {
    $_SESSION['add-post-data'] = $_POST;
    header('location: ' . ROOT_URL . 'admin/add-post.php');
    die();
  } else {
    // set is_featured of all posts to 0 if new post is featured
    if ($is_featured == 1) {
      $zero_all_is_featured_query = "UPDATE posts SET is_featured = 0";
      $zero_all_is_featured_result = mysqli_query(
        $connection,
        $zero_all_is_featured_query
      );
    }

    // insert post into database
    $query = "INSERT INTO posts (title, body, thumbnail, category_id, author_id, is_featured) VALUES('$title', '$body', '$thumbnail_name', $category_id, $author_id, $is_featured)";

    $result = mysqli_query($connection, $query);

    if (!mysqli_errno($connection)) {
      $_SESSION['add-post-success'] = "New post added successfully";
      header('location: ' . ROOT_URL . 'admin/');
      die();
    }
  }
}

header('location: ' . ROOT_URL . 'admin/add-post.php');
die();
