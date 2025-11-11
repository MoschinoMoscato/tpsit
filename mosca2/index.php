<?php require('parsing.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Mosca Leonardo</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" href="css/a-custom.css" media="all">
  </head>
  <body style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;">
  <div class="container-fluid d-flex flex-column min-vh-100">

   <!--Intestazione -->
   <div class="row">
    <?php require('inc/header.php'); ?>
   </div>
   <!--SideBar e Content Frame -->
   <div class="row flex-grow-1"> 
      <?php require('inc/sidebar.php');?> 
      <?php require('inc/content.php'); ?> 
   </div> 
   <!--Footer -->
   <div class="row mt-auto">
    <?php require('inc/footer.php')?>
   </div>
  
</div>
</body>
</html>
