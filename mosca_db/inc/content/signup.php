<?php if($_GET["page"] == "signup"){ ?>
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
  if(isset($_POST["signup"]))
  {
   // Recupero e sanifico i dati del form
   $email = trim($_POST["email"]);
   $nome = trim($_POST["nome"]);
   $cognome = trim($_POST["cognome"]);
   $password = $_POST["password"];
   $conferma_password = $_POST["conferma_password"];

   // Controllo che i campi non siano vuoti e che le password coincidano
   if(!empty($email) && !empty($nome) && !empty($cognome) && !empty($password) && ($password === $conferma_password))
   {
    // Controllo che l'email non sia già registrata nel database
    $stmt = $conn->prepare("SELECT id FROM utenti_sito WHERE email = ?");
    $stmt->execute([$email]);

    $esistente = $stmt->fetch();// Se non trova nulla $esistente vale falso, altrimenti contiene la riga trovata

    if(!$esistente)// Se l'email non è già registrata 
    {
     $hashed_password = password_hash($password, PASSWORD_DEFAULT);// Hash della password

     // Sanificazione email per creare il file fattura
     $file_fattura = strtolower($email);
     $file_fattura = str_replace(['@', '.'], '_', $file_fattura);
     $file_fattura .= ".xml";

     // Generazione colore profilo casuale per sfondo immagine utente
     $colore = sprintf('#%02X%02X%02X', rand(60,200), rand(60,200), rand(60,200));

     // Inserimento del nuovo utente nel database 
     $stmt = $conn->prepare
     (
      "INSERT INTO utenti_sito(email, nome, cognome, password_hash, colore, file_fattura) VALUES(?, ?, ?, ?, ?, ?)"
     );

     $stmt->execute([$email, $nome, $cognome, $hashed_password, $colore, $file_fattura]);

     $id = $conn->lastInsertId();// Recupero l'id dell'utente appena inserito

     // Copio il file fattura di default
     if(!copy(BASE_PATH . "/fatture/default.xml", BASE_PATH . "/fatture/" . $file_fattura))// Se la copia fallisce
     {
      die("COPIA default.xml FALLITA");
     }

     // Login automatico dopo la registrazione
     $_SESSION["logged"] = true;
     $_SESSION["user"] = 
     [
      "id" => $id,
      "email" => $email,
      "nome" => $nome,
      "cognome" => $cognome,
      "colore" => $colore,
      "file_fattura" => $file_fattura
     ];

     // Pulizia dei token scaduti dal database
     $stmt = $conn->prepare("DELETE FROM cookie_tks WHERE expiry <= NOW()");
     $stmt->execute();

     $token = bin2hex(random_bytes(32));// Generazione di un token casuale per la sessione
     $expiry = date("Y-m-d H:i:s", time() + 60 * 60 * 24 * 30);// Scadenza del token dopo 30 giorni

     // Salvo nel DB
     $stmt = $conn->prepare("INSERT INTO cookie_tks (user_id, token, expiry) VALUES (?, ?, ?)");
     $stmt->execute([$id, $token, $expiry]);

     // Set del cookie nel browser
     setcookie("remember_token", $token, ["expires" => time() + 60 * 60 * 24 * 30, "path" => "/", "secure" => false, "httponly" => true, "samesite" => "Lax"]);

     // Reindirazzamento alla home
     header("Location: index.php?page=home");
     exit;
    } 
    else 
    {
     $errore = "Email già registrata";
    }
   } 
   else 
   {
    $errore = "Compila tutti i campi correttamente";
   }
  }
 ?>

 <!--- Form di registrazione --->
 <div class="signup">
  <form method="post"> 
   <label for="email">Email:</label>
   <input type="text" id="email" name="email"><br>

   <label for="nome">Nome:</label>
   <input type="text" id="nome" name="nome"><br>

   <label for="cognome">Cognome:</label>
   <input type="text" id="cognome" name="cognome"><br>

   <label for="password">Password:</label>
   <input type="password" id="password" name="password"><br>

   <label for="password">Conferfma password:</label>
   <input type="password" id="conferma_password" name="conferma_password"><br>

   <input type="submit" name="signup" value="Registrati">

   <?php if($errore !== ""){ ?>
    <p style="color:red"><?= htmlspecialchars($errore) ?></p>
   <?php } ?>

   <p>Hai già un account? <a href="index.php?page=login">Accedi</a></p>
  </form>
 </div>

<?php } ?> 