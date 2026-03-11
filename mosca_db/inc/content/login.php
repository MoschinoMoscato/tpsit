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

   if($email && $password)
   {
    $stmt = $conn->prepare
    (
     "SELECT email, nome, cognome, password, colore, file_fattura FROM utenti WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $res = $stmt->get_result();
   }
  
   if($res->num_rows === 1) 
   {
    $u = $res->fetch_assoc();

    if(password_verify($password, $u["password"])) 
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
   }

   echo "<p style='color:red'>Credenziali non valide</p>";
  }
 ?>
</div>