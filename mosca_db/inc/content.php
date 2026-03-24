<div class="content">
 
 <!---------------------------------------------------------------- Se la page è Home ---------------------------------------------------------------->
 <?php
  if($_GET["page"] == "home") 
  {
   require('content/home.php');
  }
 ?>

 <!---------------------------------------------------------------- Se la page è php_info ---------------------------------------------------------------->
 <?php if($_GET["page"] == "PHP_info"): ?>
  <iframe src="inc/phpinfo.php" title="PHP_info"></iframe> <!-- iframe per mostrare phpinfo -->
 <?php endif; ?>

 <!---------------------------------------------------------------- Se la page è Form ---------------------------------------------------------------->
 <?php
  if($_GET["page"] == "form") 
  {
   require('content/form.php');
  }
 ?>

 <!---------------------------------------------------------------- Se la page è view_json ---------------------------------------------------------------->
 <?php
  if($_GET["page"] == "view_json") 
  {
   require('content/view_json.php');
  }
 ?>

 <!---------------------------------------------------------------- Se la page è Login ---------------------------------------------------------------->
 <?php
  if($_GET["page"] == "login") 
  {
   require('content/login.php');
  }
 ?>

 <!---------------------------------------------------------------- Se la page è Signup ---------------------------------------------------------------->
 <?php
  if($_GET["page"] == "signup") 
  {
   require('content/signup.php');
  }
 ?>

 <!----------------------------------------------------------------- Se la page è area_riservata ----------------------------------------------------------------> 
 <?php
  if($_GET["page"] == "area_riservata") 
  {
   require('content/area_riservata.php');
  }
 ?>

 <!---------------------------------------------------------------- Logout ---------------------------------------------------------------->
 <?php
  if(isset($_POST["logout"])) 
  {
   session_unset(); // Rimuovo tutte le variabili di sessione
   session_destroy(); // Distruggo la sessione
   header("Location: index.php?page=home"); // Reindirizzo alla home
   exit;
  }
 ?>

<!---------------------------------------------------------------- Eliminazione account ---------------------------------------------------------------->
 <?php
  if(isset($_POST["confirm_delete"])) 
  {
   require('content/delete_account.php');
  }
 ?>

</div>