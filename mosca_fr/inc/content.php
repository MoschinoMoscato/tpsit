<div class="content">
 
 <!-- Se la page è Home -->
 <?php if($_GET["page"] == "home"): ?>
  <p title="Benvenuto nel sito di Leonardo Mosca">Welcome to Leonardo Mosca's site</p>
 <?php endif; ?>

 <!-- Se la page è php_info -->
 <?php if($_GET["page"] == "PHP_info"): ?>
  <iframe src="inc/phpinfo.php" title="PHP_info"></iframe> <!-- iframe per mostrare phpinfo -->
 <?php endif; ?>

 <!-- Se la page è Form -->
 <?php if($_GET["page"] == "elenco_articoli"): ?>
  
 <?php endif; ?>

</div>