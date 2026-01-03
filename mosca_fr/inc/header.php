<div class ="header">
 <div class="header-left">
  <?php
   $site = include(BASE_PATH . "/parsing.php");

   echo"<h1>" . $site["header"] . "</h1>" 
  ?>
 </div>

 <div class="header-right">
  <?php if (!isset($_SESSION["logged"])): ?>
   <form method="get">
    <input type="hidden" name="page" value="login">
    <input type="submit" value="Accedi">
   </form>

   <form method="get">
    <input type="hidden" name="page" value="signup">
    <input type="submit" value="Registrati">
   </form>
  <?php endif; ?>
 </div>
</div>