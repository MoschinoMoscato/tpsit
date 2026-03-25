<?php if($_GET["page"] == "login"){ ?>
 
 <?php
 // Se l'utente è già loggato, lo reindirizzo alla home
  if(isset($_SESSION["logged"]) && $_SESSION["logged"] === true) 
  {
   header("Location: index.php?page=home");
   exit;
  }

  $errore = "";// Variabile per eventuali messaggi di errore
 ?>

 <?php
  if(isset($_POST["login"])) 
  {
   $email = trim($_POST["email"]);
   $password = $_POST["password"];

   // Cerco l'utente nel database tramite email
   $stmt = $conn->prepare("SELECT id, email, nome, cognome, password_hash, colore, file_fattura FROM utenti_sito WHERE email = ?");
   $stmt->execute([$email]);
   $u = $stmt->fetch(PDO::FETCH_ASSOC);// Se non trova nulla $u vale false, altrimenti contiene la riga trovata; fetch(PDO::FETCH_ASSOC) per avere un array associativo

   // Se l'utente esiste e la password è corretta, effettuo il login
   if($u && password_verify($password, $u["password_hash"])) 
   {
    $_SESSION["logged"] = true;
    $_SESSION["user"] = 
    [
     "id" => $u["id"],
     "email" => $u["email"],
     "nome" => $u["nome"],
     "cognome" => $u["cognome"],
     "colore" => $u["colore"],
     "file_fattura" => $u["file_fattura"]
    ];

    // Pulizia dei token scaduti dal database
    $stmt = $conn->prepare("DELETE FROM cookie_tks WHERE expiry <= NOW()");
    $stmt->execute();

    $token = bin2hex(random_bytes(32));// Generazione di un token casuale per la sessione
    $expiry = date("Y-m-d H:i:s", time() + 60 * 60 * 24 * 30);// Scadenza del token dopo 30 giorni

    // Salvo nel DB
    $stmt = $conn->prepare("INSERT INTO cookie_tks (user_id, token, expiry) VALUES (?, ?, ?)");
    $stmt->execute([$u["id"], $token, $expiry]);

    // Set del cookie nel browser
    setcookie("remember_token", $token, ["expires" => time() + 60 * 60 * 24 * 30, "path" => "/", "secure" => false, "httponly" => true, "samesite" => "Lax"]);

    header("Location: index.php?page=home");
    exit;
   } 
   
   else 
   {
    $errore = "Credenziali non valide";
   }
  }
 ?>

 <!--- Form di login --->
 <div class="login">
  <form method="post"> 
   <label for="email">Email:</label>
   <input type="text" id="email" name="email"><br>

   <label for="password">Password:</label>
   <input type="password" id="password" name="password"><br>

   <input type="submit" name="login" value="Accedi">

   <?php if($errore !== ""){ ?>
    <p style="color:red"><?= htmlspecialchars($errore) ?></p>
   <?php } ?>

   <p>Non hai un account? <a href="index.php?page=signup">Registrati</a></p>
  </form>
 </div>

<?php } ?> 