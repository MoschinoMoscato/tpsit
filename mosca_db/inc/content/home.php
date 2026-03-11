<div class="home">
 <?php if (isset($_SESSION["logged"])): ?>
   <p>Welcome <?php echo $_SESSION["user"]["nome"] . " " . $_SESSION["user"]["cognome"]; ?></p>
 <?php else: ?>
   <p style="margin-bottom:20px;" title="Benvenuto nel sito di Leonardo Mosca">Welcome to Leonardo Mosca's site<br></p>
 <?php endif; ?>

 <!-- Messaggio di conferma eliminazione account -->
 <?php if (isset($_GET["deleted"])): ?>
  <p style="color:green">Account eliminato con successo</p>
 <?php endif; ?>
</div>