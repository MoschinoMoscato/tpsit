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
   $email = trim($_POST["email"]);
   $nome = trim($_POST["nome"]);
   $cognome = trim($_POST["cognome"]);
   $password = $_POST["password"];
   $conferma_password = $_POST["conferma_password"];

   if($email && $nome && $cognome && $password && $password === $conferma_password) 
   {
    // Controllo se l'email è già esistente
    $stmt = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0) 
    {
     echo "<p style='color:red'>Email già registrata</p>";
    } 
    
    else 
    {
     $hashed_password = password_hash($password, PASSWORD_DEFAULT);

     // Genera file fattura
     $file_fattura = strtolower($email);
     $file_fattura = str_replace(['@', '.'], '_', $file_fattura);
     $file_fattura .= ".xml";

     // Colore random
     $colore = sprintf('#%02X%02X%02X', rand(60,200), rand(60,200), rand(60,200));

     // insert
     $stmt = $conn->prepare
     (
      "INSERT INTO utenti (email, nome, cognome, password, colore, file_fattura) VALUES (?, ?, ?, ?, ?, ?)"
     );
     $stmt->bind_param("ssssss", $email, $nome, $cognome, $hashed_password, $colore, $file_fattura
     );
     $stmt->execute();

     // Copia fattura default
     copy(BASE_PATH . "/fatture/default.xml", BASE_PATH . "/fatture/" . $file_fattura);

     // Login automatico
     $_SESSION["logged"] = true;
     $_SESSION["user"] = 
     [
      "email" => $email,
      "nome" => $nome,
      "cognome" => $cognome,
      "colore" => $colore,
      "file_fattura" => $file_fattura
     ];

     header("Location: index.php?page=home");
     exit;
    }
   } 
   
   else 
   {
    echo "<p style='color:red'>Compila correttamente i campi</p>";
   }
  }
 ?>

</div>