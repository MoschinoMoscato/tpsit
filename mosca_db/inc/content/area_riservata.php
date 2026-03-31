<?php
 // Controllo se l'utente è loggato, altrimenti lo reindirizzo al login
 if(!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) 
 {
  header("Location: index.php?page=login");
  exit;
 }

 require_once BASE_PATH . "/inc/2fa.php";// Inclusione del file con le funzioni per 2FA

 // Recupero i dati 2FA aggiornati dell'utente loggato
 $stmt = $conn->prepare("SELECT twofa_enabled, twofa_secret, twofa_temp_secret FROM utenti_sito WHERE id = ?");
 $stmt->execute([$_SESSION["user"]["id"]]);
 $twofa_data = $stmt->fetch(PDO::FETCH_ASSOC);

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

<!---------------------------------------------------------------- Attivazione 2FA ---------------------------------------------------------------->
<?php
 // Se l'utente ha cliccato sul pulsante per attivare il 2FA
 if(isset($_POST["start_2fa"])) 
 {
  // Genero un secret temporaneo solo se il 2FA non è già attivo
  if(!$twofa_data["twofa_enabled"]) 
  {
   $temp_secret = generate_twofa_secret();

   // Salvo il secret temporaneo nel database
   $stmt = $conn->prepare("UPDATE utenti_sito SET twofa_temp_secret = ? WHERE id = ?");
   $stmt->execute([$temp_secret, $_SESSION["user"]["id"]]);

   // Ricarico i dati 2FA aggiornati dell'utente
   $stmt = $conn->prepare("SELECT twofa_enabled, twofa_secret, twofa_temp_secret FROM utenti_sito WHERE id = ?");
   $stmt->execute([$_SESSION["user"]["id"]]);
   $twofa_data = $stmt->fetch(PDO::FETCH_ASSOC);
  }
 }
?>

<!---------------------------------------------------------------- Conferma 2FA ---------------------------------------------------------------->
<?php
 // Se l'utente ha inserito il codice per confermare il 2FA
 if(isset($_POST["confirm_2fa"])) 
 {
  // Recupero il codice inserito dall'utente
  $twofa_code = trim($_POST["twofa_code"] ?? "");

  // Controllo che esista una configurazione 2FA in corso
  if($twofa_data["twofa_temp_secret"] === null || $twofa_data["twofa_temp_secret"] === "") 
  {
   $errore = "Nessuna configurazione 2FA in corso";
  }
  else
  {
   // Verifico se il codice inserito corrisponde a quello generato dall'app Authenticator
   if(verify_totp_code($twofa_data["twofa_temp_secret"], $twofa_code)) 
   {
    // Se il codice è corretto, attivo il 2FA e sposto il secret temporaneo in quello definitivo
    $stmt = $conn->prepare("UPDATE utenti_sito SET twofa_enabled = 1, twofa_secret = ?, twofa_temp_secret = NULL WHERE id = ?");
    $stmt->execute([$twofa_data["twofa_temp_secret"], $_SESSION["user"]["id"]]);

    // Ricarico i dati 2FA aggiornati dell'utente
    $stmt = $conn->prepare("SELECT twofa_enabled, twofa_secret, twofa_temp_secret FROM utenti_sito WHERE id = ?");
    $stmt->execute([$_SESSION["user"]["id"]]);
    $twofa_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Redirect con conferma
    header("Location: index.php?page=area_riservata&twofa_enabled=1");
    exit;
   }
   else
   {
    $errore = "Codice 2FA non valido";
   }
  }
 }
?>

<!---------------------------------------------------------------- Disattivazione 2FA ---------------------------------------------------------------->
<?php
 // Se l'utente ha cliccato sul pulsante per disattivare il 2FA
 if(isset($_POST["disable_2fa"])) 
 {
  // Disattivo il 2FA e rimuovo tutti i dati collegati
  $stmt = $conn->prepare("UPDATE utenti_sito SET twofa_enabled = 0, twofa_secret = NULL, twofa_temp_secret = NULL WHERE id = ?");
  $stmt->execute([$_SESSION["user"]["id"]]);

  // Ricarico i dati 2FA aggiornati dell'utente
  $stmt = $conn->prepare("SELECT twofa_enabled, twofa_secret, twofa_temp_secret FROM utenti_sito WHERE id = ?");
  $stmt->execute([$_SESSION["user"]["id"]]);
  $twofa_data = $stmt->fetch(PDO::FETCH_ASSOC);

  // Redirect con conferma
  header("Location: index.php?page=area_riservata&twofa_disabled=1");
  exit;
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

<?php
 // Se l'utente ha cliccato sul pulsante per annullare la configurazione del 2FA
 if(isset($_POST["cancel_2fa_setup"])) 
 {
  // Elimino il secret temporaneo dal database
  $stmt = $conn->prepare("UPDATE utenti_sito SET twofa_temp_secret = NULL WHERE id = ?");
  $stmt->execute([$_SESSION["user"]["id"]]);

  // Ricarico i dati 2FA aggiornati dell'utente
  $stmt = $conn->prepare("SELECT twofa_enabled, twofa_secret, twofa_temp_secret FROM utenti_sito WHERE id = ?");
  $stmt->execute([$_SESSION["user"]["id"]]);
  $twofa_data = $stmt->fetch(PDO::FETCH_ASSOC);

  // Redirect con conferma
  header("Location: index.php?page=area_riservata&twofa_cancelled=1");
  exit;
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

  <!-- Messaggio di conferma annullamento configurazione 2FA -->
  <?php if(isset($_GET["twofa_cancelled"])) { ?>
   <p style="color:green">Configurazione 2FA annullata con successo</p>
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

  <!-- Sezione 2FA -->
  <div class="twofa-box">
   <h3>Autenticazione a due fattori</h3>

   <!-- Messaggio di conferma attivazione 2FA -->
   <?php if(isset($_GET["twofa_enabled"])) { ?>
    <p style="color:green">2FA attivato con successo</p>
   <?php } ?>

   <!-- Messaggio di conferma disattivazione 2FA -->
   <?php if(isset($_GET["twofa_disabled"])) { ?>
    <p style="color:green">2FA disattivato con successo</p>
   <?php } ?>

   <!-- Se il 2FA non è attivo e non è ancora stata avviata una configurazione -->
   <?php if(!$twofa_data["twofa_enabled"] && ($twofa_data["twofa_temp_secret"] === null || $twofa_data["twofa_temp_secret"] === "")) { ?>
    <p>Il 2FA non è attivo</p>

    <form method="post">
     <input type="submit" name="start_2fa" value="Attiva 2FA">
    </form>
   <?php } ?>

   <!-- Se il 2FA non è attivo ma è già stata avviata la configurazione -->
   <?php if(!$twofa_data["twofa_enabled"] && $twofa_data["twofa_temp_secret"] !== null && $twofa_data["twofa_temp_secret"] !== "") { ?>
    <?php
     // Costruisco l'URI otpauth da usare per il QR code
     $otpauth_uri = build_otpauth_uri($_SESSION["user"]["email"], $twofa_data["twofa_temp_secret"]);

     // Genero il QR code come immagine base64
     $qrcode_data_uri = generate_qrcode_data_uri($otpauth_uri);
    ?>

    <p>Scansiona il QR code con Google Authenticator e inserisci il codice generato</p>

    <!-- Mostro il QR code da scansionare -->
    <p><img src="<?= htmlspecialchars($qrcode_data_uri) ?>" alt="QR Code 2FA"></p>

    <!-- Mostro anche il secret come alternativa manuale -->
    <p>
     <strong>Secret:</strong>
     <span style="word-break: break-all; overflow-wrap: anywhere;">
      <?= htmlspecialchars($twofa_data["twofa_temp_secret"]) ?>
     </span>
    </p>

    <form method="post">
     <label>Codice 2FA </label>
     <input type="text" name="twofa_code" maxlength="6">

     <input type="submit" name="confirm_2fa" value="Conferma 2FA">

     <form method="post" style="margin-top:10px">
      <input type="submit" name="cancel_2fa_setup" value="Annulla configurazione 2FA">
     </form>
    </form>
   <?php } ?>

   <!---------------------------------------------------------------- Annullamento configurazione 2FA ---------------------------------------------------------------->
   
   <!-- Se il 2FA è attivo -->
   <?php if($twofa_data["twofa_enabled"]) { ?>
    <p style="color:green">Il 2FA è attivo</p>

    <form method="post">
     <input type="submit" name="disable_2fa" value="Disattiva 2FA">
    </form>
   <?php } ?>
  </div>

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