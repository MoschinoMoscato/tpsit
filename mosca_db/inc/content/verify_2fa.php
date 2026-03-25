<?php
 // Se non esiste una sessione 2FA in corso, reindirizzo al login
 if(!isset($_SESSION["pending_2fa_user"]) || empty($_SESSION["pending_2fa_user"]["twofa_secret"])) 
 {
  header("Location: index.php?page=login");
  exit;
 }

 require_once BASE_PATH . "/inc/2fa.php";// Inclusione del file con le funzioni per 2FA

 // Variabile per eventuali messaggi di errore
 $errore = "";
?>

<?php
 // Se l'utente ha inviato il codice 2FA
 if(isset($_POST["verify_2fa"])) 
 {
  // Recupero il codice inserito
  $twofa_code = trim($_POST["twofa_code"] ?? "");

  // Recupero i dati temporanei dell'utente dalla sessione
  $pending_user = $_SESSION["pending_2fa_user"];

  // Verifico se il codice inserito è corretto
  if(verify_totp_code($pending_user["twofa_secret"], $twofa_code)) 
  {
   // Se il codice è corretto, completo il login
   $_SESSION["logged"] = true;
   $_SESSION["user"] = 
   [
    "id" => $pending_user["id"],
    "email" => $pending_user["email"],
    "nome" => $pending_user["nome"],
    "cognome" => $pending_user["cognome"],
    "colore" => $pending_user["colore"],
    "file_fattura" => $pending_user["file_fattura"]
   ];

   // Rimuovo i dati temporanei del 2FA dalla sessione
   unset($_SESSION["pending_2fa_user"]);

   // Pulizia dei token scaduti dal database
   $stmt = $conn->prepare("DELETE FROM cookie_tks WHERE expiry <= NOW()");
   $stmt->execute();

   $token = bin2hex(random_bytes(32));// Generazione di un token casuale per la sessione
   $expiry = date("Y-m-d H:i:s", time() + 60 * 60 * 24 * 30);// Scadenza del token dopo 30 giorni

   // Salvo il token nel database
   $stmt = $conn->prepare("INSERT INTO cookie_tks (user_id, token, expiry) VALUES (?, ?, ?)");
   $stmt->execute([$pending_user["id"], $token, $expiry]);

   // Set del cookie nel browser
   setcookie("remember_token", $token, ["expires" => time() + 60 * 60 * 24 * 30, "path" => "/", "secure" => false, "httponly" => true, "samesite" => "Lax"]);

   header("Location: index.php?page=home");
   exit;
  }
  else
  {
   $errore = "Codice 2FA non valido";
  }
 }
?>

<div class="login">
 <form method="post">
  <label for="twofa_code">Codice Authenticator:</label>
  <input type="text" id="twofa_code" name="twofa_code" maxlength="6"><br>

  <input type="submit" name="verify_2fa" value="Verifica codice">

  <?php if($errore !== ""){ ?>
   <p style="color:red"><?= htmlspecialchars($errore) ?></p>
  <?php } ?>
 </form>
</div>