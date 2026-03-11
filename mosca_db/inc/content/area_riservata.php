<div class="area-riservata">
 <?php
  // Controllo se l'utente è loggato, altrimenti lo reindirizzo al login
  if(!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) 
  {
   header("Location: index.php?page=login");
   exit;
  }
 ?>

<div class="user-box">
 <!-- Form di modifica profilo -->
 <h2>Area riservata</h2>

 <form method="post" class="user-form">

  <div class="form-field">
   <label>Email</label>
   <input type="email" value="<?= htmlspecialchars($_SESSION["user"]["email"]) ?>" disabled>
  </div>

  <div class="form-field">
   <label>Nome</label>
   <input type="text" name="nome" value="<?= htmlspecialchars($_SESSION["user"]["nome"]) ?>" required>
  </div>

  <div class="form-field">
   <label>Cognome</label>
   <input type="text" name="cognome" value="<?= htmlspecialchars($_SESSION["user"]["cognome"]) ?>" required>
  </div>

  <div class="form-actions">
   <input type="submit" name="save_changes" value="Salva modifiche">
  </div>

 </form>

 <div class="user-actions">
  <form method="post">
   <input type="submit" name="logout" value="Logout">
  </form>

  <form method="post">
   <input type="submit" name="ask_delete" value="Elimina account">
  </form>
 </div>
</div>

<!--- Se l'utente ha cliccato su salva modifiche --->
<?php
 if(isset($_POST["save_changes"])) 
 {
  // Recupero e sanifico i dati del form
  $nome = trim($_POST["nome"]);
  $cognome = trim($_POST["cognome"]);
  $email = $_SESSION["user"]["email"];

  $utenti = simplexml_load_file(BASE_PATH."/utenti.xml");// Carico utenti.xml

  // Aggiorno i dati nell'XML
  foreach($utenti->Utente as $u) 
  {
   if((string)$u->Email === $email) 
   {
    $u->Nome = $nome;
    $u->Cognome = $cognome;
    break;
   }
  }

  // Salvo le modifiche a utenti.xml
  $dom = new DOMDocument("1.0", "UTF-8");
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $dom->loadXML($utenti->asXML());
  $dom->save(BASE_PATH."/utenti.xml");

  // Aggiorno i dati nella sessione
  $_SESSION["user"]["nome"] = $nome;
  $_SESSION["user"]["cognome"] = $cognome;

  header("Location: index.php?page=area_riservata&saved=1");
  exit;
 }
?>

<!-- Form di conferma eliminazione account -->
<?php if(isset($_POST["ask_delete"]) || isset($_POST["confirm_delete"])): ?>
 <form method="post" style="margin-top:15px">
  <input type="hidden" name="ask_delete" value="1"> <!--Mantengo lo stato di richiesta eliminazione -->

  <label>Conferma password</label><br>
  <input type="password" name="delete_password"><br>

  <input type="submit" name="confirm_delete" value="Conferma eliminazione">
 </form>
<?php endif; ?>

</div>