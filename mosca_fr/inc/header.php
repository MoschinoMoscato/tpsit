<div class ="header">
 <?php
  $site = include(BASE_PATH . "/parsing.php");

  echo"<h1>" . $site["header"] . "</h1>" 
 ?>

 <?php if (!isset($_SESSION["logged"])): ?>
  <form method="get">
   <input type="hidden" name="page" value="login">
   <input type="submit" value="Login">
  </form>

  <form method="get">
   <input type="hidden" name="page" value="signup">
   <input type="submit" value="Signup">
  </form>
 <?php endif; ?>
</div>