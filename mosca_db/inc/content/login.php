<?php if($_GET["page"] == "login"): ?>
 
 <?php
 // Se l'utente è già loggato, lo reindirizzo alla home
  if (isset($_SESSION["logged"])) 
  {
   header("Location: index.php?page=home");
   exit;
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

   <p>Non hai un account? <a href="index.php?page=signup">Registrati</a></p>
  </form>

  <?php
   if(isset($_POST["login"])) 
   {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Cerco l'utente nel database tramite email
    $stmt = $conn->prepare("SELECT email, nome, cognome, password_hash, colore, file_fattura FROM utenti_sito WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();// Se non trova nulla $u vale falso, altrimenti contiene la riga trovata

    // Se l'utente esiste e la password è corretta, effettuo il login
    if($u && password_verify($password, $u["password_hash"])) 
    {
     $_SESSION["logged"] = true;
     $_SESSION["user"] = 
     [
      "email" => $u["email"],
      "nome" => $u["nome"],
      "cognome" => $u["cognome"],
      "colore" => $u["colore"],
      "file_fattura" => $u["file_fattura"]
     ];

     header("Location: index.php?page=home");
     exit;
    } 
    
    else 
    {
     echo "<p style='color:red'>Credenziali non valide</p>";
    }
   }
  ?>
 </div>
<?php endif; ?> 