<div class="content">
 
 <!---------------------------------------------------------------- Se la page è Home ---------------------------------------------------------------->
 <?php if ($_GET["page"] == "home"): ?>
  <div class="home">
   <?php if (isset($_SESSION["logged"])): ?>
     <p>Welcome <?php echo $_SESSION["user"]["nome"] . " " . $_SESSION["user"]["cognome"]; ?></p>
   <?php else: ?>
     <p title="Benvenuto nel sito di Leonardo Mosca">Welcome to Leonardo Mosca's site</p>
   <?php endif; ?>
  </div>
 <?php endif; ?>

 <!---------------------------------------------------------------- Se la page è php_info ---------------------------------------------------------------->
 <?php if($_GET["page"] == "PHP_info"): ?>
  <iframe src="inc/phpinfo.php" title="PHP_info"></iframe> <!-- iframe per mostrare phpinfo -->
 <?php endif; ?>

 <!---------------------------------------------------------------- Se la page è Form ---------------------------------------------------------------->
 <?php if($_GET["page"] == "form"): ?>
  <div class="form">

   <div class="form-top">
    
   <!-- Esportazione in JSON --> 
   <div class ="json">
     <form method="post">
      <input type="submit" name="export_json" value="Esporta JSON">
     </form>

     <!-- Visualizzazione JSON -->
     <form method="get">
      <input type="hidden" name="page" value="view_json">
      <input type="submit" value="Visualizza JSON">
     </form>
    </div>

    <!-- Form per l'inserimento di un nuovo articolo -->
    <form action="" method="post"> 
     <?php $xml_sx = simplexml_load_file($fattura_corrente) or die("Errore caricamento XML"); // Apro il file XML ?>

     <label for="codice">Codice articolo:</label><br>
     <input type="number" id="codice" name="codice"><br>

     <label for="descrizione">Descrizione articolo:</label><br>
     <input type="text" id="descrizione" name="descrizione"><br>

     <label for="quanto">Quantità:</label><br>
     <input type="number" id="quanto" name="quanto"><br>

     <label for="fname">Prezzo unitario:</label><br>
     <input type="number" step="0.01" id="price" name="price"><br>

     <input type="submit" name="add" value="Aggiungi articolo">
     <input type="reset" value="Reset campi">
     <input type="submit" name="reset_xml" value="Reset tabella">
    </form>

    <!-- Valido l'XML contro lo schema XSD -->
    <?php if($xml_dom->schemaValidate("fatture.xsd")) : ?>
     <div class="xml-status">XML valido</div>

    <?php else: ?>
     <div class="xml-status">XML non valido</div>

    <?php endif; ?>

   </div>

   <?php
    if(isset($_POST["add"]))
    {
     $codice = $_POST["codice"];
     $descrizione = $_POST["descrizione"];
     $prezzo_unitario = $_POST["price"];
     $quantita = $_POST["quanto"];

     // Controllo che i campi non siano stati lasciati vuoti e aggiungo l'articolo
     if(!empty($codice) && !empty($descrizione) && !empty($quantita) && !empty($prezzo_unitario))
     {
      $prezzo_totale = $quantita * $prezzo_unitario;

      $new_article = $xml_sx->Articoli->addChild("Articolo"); // Aggiunge un nuovo nodo <Articolo> all'XML e mi restituisce il riferimento a quel nodo in $new_article
      // Aggiungo i vari sotto-nodi
      $new_article->addChild("Codice", $codice);
      $new_article->addChild("Descrizione", $descrizione);
      $new_article->addChild("Quantita", $quantita);
      $new_article->addChild("PrezzoUnitario", $prezzo_unitario);
      $new_article->addChild("PrezzoTotale", $prezzo_totale);

      $xml_sx->asXML($fattura_corrente); // Salvo le modifiche al file XML

      // Riformatto il file XML per renderlo leggibile    
      $xml_dom->load($fattura_corrente);
      $xml_dom->save($fattura_corrente);
      header("Location: index.php?page=form");
     }
    }

    /* RESET TABELLA */
    if(isset($_POST["reset_xml"])) 
    {
     // Cancello tutti gli Articolo
     unset($xml_sx->Articoli->Articolo);

     // Salvo l'XML vuoto
     $xml_sx->asXML($fattura_corrente);

     // Ricarico la pagina
     header("Location: index.php?page=form");
     exit;
    }
   ?>
   
			<!--- Ricarico il file per poi stampare la tabella HTML --->
			<?php $xml_list = simplexml_load_file($fattura_corrente); ?>

			<table class = "tabella">
				<tr>
					<th>Codice</th>
					<th>Descrizione</th>
					<th>Quantità</th>
					<th>Prezzo unitario</th>
					<th>Prezzo totale</th>
			 </tr>
				
				<?php foreach($xml_list->Articoli->Articolo as $art): ?>
			 	<tr>
			 		<td> <?php echo $art->Codice; ?> </td>
			 		<td> <?php echo $art->Descrizione; ?> </td>
						<td> <?php echo $art->Quantita; ?> </td>
						<td> <?php echo $art->PrezzoUnitario. " €"; ?> </td>
						<td> <?php echo $art->PrezzoTotale . " €"; ?> </td>
			  </tr>	
				<?php endforeach; ?>

			</table>
  </div>
 <?php endif; ?>

 <!---------------------------------------------------------------- Se la page è view_json ---------------------------------------------------------------->
 <?php if($_GET["page"] == "view_json"): ?>
  <div class="view_json">
   <?php
    $json_file = file_get_contents($fattura_corrente); // Leggo il file XML
    $xml_json = simplexml_load_string($json_file) or die("Errore caricamento XML"); // Carico il file XML

    $json = json_encode($xml_json, JSON_PRETTY_PRINT); // Converto in JSON con pretty print per la formattazione

    echo "<pre>" . $json . "</pre>"; // Stampo il JSON formattato
   ?>
  </div>
 <?php endif; ?>

 <!---------------------------------------------------------------- Se la page è Login ---------------------------------------------------------------->
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
    <label for="email">e-mail:</label><br>
    <input type="text" id="email" name="email"><br>

    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password"><br>

    <input type="submit" name="login" value="Accedi">
   </form>

   <?php
    if(isset($_POST["login"])) 
    {
     $email = trim($_POST["email"]);
     $password = $_POST["password"];

     $utenti = simplexml_load_file(BASE_PATH . "/utenti.xml") or die("Errore caricamento utenti");

     $trovato = false;

     foreach($utenti->Utente as $u) 
     {
      if((string)$u->Email === $email && password_verify($password, (string)$u->Password)) 
      {
       $_SESSION["logged"] = true;
       $_SESSION["user"] = 
       [
        "email" => $email,
        "nome" => (string)$u->Nome,
        "cognome" => (string)$u->Cognome,
        "colore" => (string)$u->Colore,
        "file_fattura" => (string)$u->FileFattura
       ];

       header("Location: index.php?page=home");
       exit;
      }
     }

     echo "<p style='color:red'>Credenziali non valide</p>";
    }
   ?>
  </div>
 <?php endif; ?> 

 <!---------------------------------------------------------------- Se la page è Signup ---------------------------------------------------------------->
 <?php if($_GET["page"] == "signup"): ?>
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
    <label for="email">e-mail:</label><br>
    <input type="text" id="email" name="email"><br>

    <label for="nome">Nome:</label><br>
    <input type="text" id="nome" name="nome"><br>

    <label for="cognome">Cognome:</label><br>
    <input type="text" id="cognome" name="cognome"><br>

    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password"><br>

    <label for="password">Conferfma password:</label><br>
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

     // Controllo che i campi non siano vuoti e che le password coincidano
     if(!empty($email) && !empty($nome) && !empty($cognome) && !empty($password) && ($password === $conferma_password))
     {
      $utenti = simplexml_load_file(BASE_PATH . "/utenti.xml") or die("Errore caricamento file utenti.xml");

      // Controllo che l'email non sia già registrata
      $esistente = false;
      
      foreach($utenti->Utente as $u) 
      {
       if((string)$u->Email === $email) 
       {
        $esistente = true;
        break;
       }
      }

      if(!$esistente) 
      {
       $hashed_password = password_hash($password, PASSWORD_DEFAULT);// Hash della password

       $new_user = $utenti->addChild("Utente");
       $new_user->addChild("Email", $email);
       $new_user->addChild("Nome", $nome);
       $new_user->addChild("Cognome", $cognome);
       $new_user->addChild("Password", $hashed_password);

       // Sanificazione email per creare il file fattura
       $file_fattura = strtolower($email);
       $file_fattura = str_replace(['@', '.'], '_', $file_fattura);
       $file_fattura .= ".xml";
       $new_user->addChild("FileFattura", $file_fattura);// Aggiungo il nome del file fattura all'XML utenti

       // Generazione colore profilo casuale per sfondo immagine utente
       $colore = sprintf('#%02X%02X%02X', rand(60,200), rand(60,200), rand(60,200));
       $new_user->addChild("Colore", $colore);// Aggiungo il colore all'XML utenti

       // Salvo le modifiche al file utenti.xml
       $dom = new DOMDocument("1.0", "UTF-8");
       $dom->preserveWhiteSpace = false;
       $dom->formatOutput = true;
       $dom->loadXML($utenti->asXML());
       $dom->save(BASE_PATH . "/utenti.xml");

       // Copio il file fattura di default
       if(!copy(BASE_PATH . "/fatture/default.xml", BASE_PATH . "/fatture/" . $file_fattura)) 
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
 <?php endif; ?> 

</div>