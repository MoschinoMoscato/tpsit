<?php if($_GET["page"] == "signup"){ ?>
 <?php
  // Se l'utente è già loggato, lo reindirizzo alla home
  if(isset($_SESSION["logged"])) 
  {
   header("Location: index.php?page=home");
   exit;
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
  </form>

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
       "INSERT INTO utenti_sito (email, nome, cognome, password_hash, colore, file_fattura) VALUES (?, ?, ?, ?, ?, ?)"
      );

      $stmt->execute([$email, $nome, $cognome, $hashed_password, $colore, $file_fattura]);

      // Copio il file fattura di default
      if(!copy(BASE_PATH . "/fatture/default.xml", BASE_PATH . "/fatture/" . $file_fattura))// Se la copia fallisce
      {
       die("COPIA default.xml FALLITA");
      }

      // Login automatico dopo la registrazione
      $_SESSION["logged"] = true;
      $_SESSION["user"] = 
      [
      "email" => $email,
      "nome" => $nome,
      "cognome" => $cognome,
      "colore" => $colore,
      "file_fattura" => $file_fattura
      ];

      // Reindirazzamento alla home
      header("Location: index.php?page=home");

      exit;
     } 
     else 
     {
      echo "<p style='color:red'>Email già registrata</p>";
     }
    } 
    else 
    {
     echo "<p style='color:red'>Compila tutti i campi correttamente</p>";
    }
   }
  ?>

 </div>
<?php } ?> 