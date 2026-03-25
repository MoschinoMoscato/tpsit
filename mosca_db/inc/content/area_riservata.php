<?php
 // Controllo se l'utente è loggato, altrimenti lo reindirizzo al login
 if(!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) 
 {
  header("Location: index.php?page=login");
  exit;
 }

 // Variabile per eventuali messaggi di errore da mostrare all'utente
 $errore = "";
?>

<!--- Se l'utente ha cliccato su salva modifiche --->
<?php
 if(isset($_POST["save_changes"])) 
 {
  // Recupero e sanifico i dati del form
  $nome = trim($_POST["nome"]);
  $cognome = trim($_POST["cognome"]);

  // Aggiorno i dati dell'utente nel database solo se i campi non sono vuoti
  if($nome !== "" && $cognome !== "") 
  {
   $stmt = $conn->prepare("UPDATE utenti_sito SET nome = ?, cognome = ? WHERE id = ?");
   $stmt->execute([$nome, $cognome, $_SESSION["user"]["id"]]);

   // Aggiorno i dati nella sessione
   $_SESSION["user"]["nome"] = $nome;
   $_SESSION["user"]["cognome"] = $cognome;

   // Redirect per evitare il resubmission del form e con conferma
   header("Location: index.php?page=area_riservata&saved=1");

   exit;
  }
  else
  {
   $errore = "Nome e cognome non possono essere vuoti";
  }
 }
?>

<!---------------------------------------------------------------- Logout ---------------------------------------------------------------->
<?php
 if(isset($_POST["logout"])) 
 {
  // Se è presente un token di "ricordami", lo elimino dal database ed elimino il cookie
  if(isset($_COOKIE["remember_token"]))
  {
   $token = $_COOKIE["remember_token"];

   $stmt = $conn->prepare("DELETE FROM cookie_tks WHERE token = ?");
   $stmt->execute([$token]);

   setcookie("remember_token", "", ["expires" => time() - 3600, "path" => "/", "secure" => false, "httponly" => true, "samesite" => "Lax"]);// Elimino il cookie dal browser
  }

  session_unset();// Rimuove tutte le variabili di sessione
  session_destroy();// Distrugge la sessione

  header("Location: index.php?page=home");
  exit;
 }
?>

<!---------------------------------------------------------------- Eliminazione account ---------------------------------------------------------------->
<?php
 if(isset($_POST["confirm_delete"]))
 {
  $password_input = $_POST["delete_password"] ?? "";// Recupero la password inserita

  if(empty($password_input)) 
  {
   $errore = "Inserisci la password per confermare l'eliminazione";
  } 
  else
  {
   // Recupero file fattura dell'utente loggato
   $file_fattura = BASE_PATH . "/fatture/" . $_SESSION["user"]["file_fattura"];

   $stmt = $conn->prepare("SELECT password_hash FROM utenti_sito WHERE id = ?");
   $stmt->execute([$_SESSION["user"]["id"]]);
   $user = $stmt->fetch(PDO::FETCH_ASSOC);// Recupero l'hash della password dal database

   if($user && password_verify($password_input, $user["password_hash"]))// Verifico che la password inserita corrisponda all'hash memorizzato
   {
    // Elimino l'utente dal database (i cookie si elimineranno automaticamente grazie alla condizione ON DELETE CASCADE)
    $stmt = $conn->prepare("DELETE FROM utenti_sito WHERE id = ?");
    $stmt->execute([$_SESSION["user"]["id"]]);

    // Elimino eventuali cookie dal browser
    if(isset($_COOKIE["remember_token"]))
    {
     setcookie("remember_token", "", ["expires" => time() - 3600, "path" => "/", "secure" => false, "httponly" => true, "samesite" => "Lax"]);
    }

    // Elimino la fattura legata all'utente
    if(file_exists($file_fattura)) 
    {
     unlink($file_fattura);
    }

    // Distruggo la sessione
    session_unset();
    session_destroy();

    // Redirect con conferma
    header("Location: index.php?page=home&deleted=1");

    exit;
   }
   else 
   {
    $errore = "Password non corretta";
   }
  }
 }
?>

<!---------------------------------------------------------------- Area riservata HTML ---------------------------------------------------------------->
<div class="area-riservata">

 <div class="user-box">
  <!-- Form di modifica profilo -->
  <h2>Area riservata</h2>

  <?php if($errore !== ""){ ?>
   <p style="color:red"><?= htmlspecialchars($errore) ?></p>
  <?php } ?>

  <?php if(isset($_GET["saved"])) { ?>
   <p style="color:green">Modifiche salvate con successo</p>
  <?php } ?>

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

  <!-- Form di conferma eliminazione account -->
  <?php if(isset($_POST["ask_delete"]) || isset($_POST["confirm_delete"])){ ?>
   <div class="delete-confirmation">
    <h3>Sei sicuro di voler eliminare il tuo account?</h3>
    <p>Questa azione è irreversibile e comporterà la perdita di tutti i tuoi dati.</p>

    <form method="post" style="margin-top:15px">
     <input type="hidden" name="ask_delete" value="1"> <!--Mantengo lo stato di richiesta eliminazione -->

     <label>Conferma password</label><br>
     <input type="password" name="delete_password"><br>

     <input type="submit" name="confirm_delete" value="Conferma eliminazione">
    </form>
   </div>
  <?php } ?>
 </div>
</div>