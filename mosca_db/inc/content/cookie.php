<?php
 // Se l'utente ha inviato il form per creare un cookie
 if(isset($_POST["add"]))
 {
  $nome = $_POST["nome"];
  $valore = $_POST["valore"];
  $scadenza = $_POST["scadenza"];

  if($nome != "" && $valore != "" && $scadenza != "")
  {
   $scadenza = time() + (int)$scadenza;// Calcolo la scadenza

   setcookie($nome, $valore, $scadenza, "/");// Set del cookie nel browser
   header("Location: index.php?page=cookie");
   exit;
  }
 }

 // Se l'utente preme il bottone per eliminare un cookie
 if(isset($_POST["delete_cookie"]))
 {
  $cookie_name = $_POST["cookie_name"];
  setcookie($cookie_name, "", time() - 3600, "/");// Elimina il cookie
  header("Location: index.php?page=cookie");// Ricarica la pagina
  exit;
 }
?>

<div class = "form cookie-page">
 
 <form action="" method="post" class="article-form"> 

  <div class="form-field">
   <label for="nome">Nome:</label>
   <input type="text" id="nome" name="nome"><br>
  </div>

  <div class="form-field">
   <label for="valore">Valore:</label>
   <input type="text" id="valore" name="valore"><br>
  </div>

  <div class="form-field">
   <label for="scadenza">Scadenza (secondi):</label>
   <input type="number" id="scadenza" name="scadenza"><br>
  </div>

  <div class="form-actions">
   <input type="submit" name="add" value="Set Cookie">
  </div>
 </form>
 
 <table class = "tabella">
  <tr>
   <th>Nome</th>
   <th>Valore</th>
   <th>Elimina</th>
  </tr>

  <?php foreach($_COOKIE as $nome => $valore) { ?>
   <tr>
    <td><?php echo htmlspecialchars($nome); ?></td>
    <td><?php echo htmlspecialchars($valore); ?></td>
    <td>
     <form method="post">
       <input type="hidden" name="cookie_name" value="<?php echo htmlspecialchars($nome); ?>">
       <button type="submit" name="delete_cookie">Elimina</button>
     </form>
    </td>
   </tr>
  <?php } ?>
 </table>
</div>