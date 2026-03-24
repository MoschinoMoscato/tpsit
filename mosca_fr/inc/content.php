<div class="content">
 
 <!---------------------------------------------------------------- Se la page è Home ---------------------------------------------------------------->
 <?php if ($_GET["page"] == "home"): ?>
  <div class="home">
   <?php if (isset($_SESSION["logged"])): ?>
     <p>Welcome <?php echo $_SESSION["user"]["nome"] . " " . $_SESSION["user"]["cognome"]; ?></p>
   <?php else: ?>
     <p style="margin-bottom:20px;" title="Benvenuto nel sito di Leonardo Mosca">Welcome to Leonardo Mosca's site<br></p>
   <?php endif; ?>

   <!-- Messaggio di conferma eliminazione account -->
   <?php if (isset($_GET["deleted"])): ?>
    <p style="color:green">Account eliminato con successo</p>
   <?php endif; ?>
  </div>
 <?php endif; ?>

 <!---------------------------------------------------------------- Se la page è php_info ---------------------------------------------------------------->
 <?php if($_GET["page"] == "PHP_info"){ ?>
  <iframe src="inc/phpinfo.php" title="PHP_info"></iframe> <!-- iframe per mostrare phpinfo -->
 <?php } ?>

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
    <form action="" method="post" class="article-form"> 
     <?php $xml_sx = simplexml_load_file($fattura_corrente) or die("Errore caricamento XML"); // Apro il file XML ?>

     <div class="form-field">
      <label for="codice">Codice articolo:</label>
      <input type="number" id="codice" name="codice"><br>
     </div>

     <div class="form-field">
      <label for="descrizione">Descrizione articolo:</label>
      <input type="text" id="descrizione" name="descrizione"><br>
     </div>

     <div class="form-row">
      <div class="form-field">
       <label for="quanto">Quantità:</label>
       <input type="number" id="quanto" name="quanto"><br>
      </div>

      <div class="form-field">
       <label for="fname">Prezzo unitario:</label>
       <input type="number" step="0.01" id="price" name="price"><br>
      </div>
     </div>

     <div class="form-actions">
      <input type="submit" name="add" value="Aggiungi articolo">
      <input type="reset" value="Reset campi">
     </div>
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

     $codice_esistente = false;

     foreach($xml_sx->Articoli->Articolo as $art) 
     {
      if((string)$art->Codice === (string)$codice) 
      {
       $codice_esistente = true;
       break;
      }
     }

     if($codice_esistente) 
     {
      echo "<p style='color:red'>Codice articolo già esistente</p>";
     }

     // Controllo che i campi non siano stati lasciati vuoti e aggiungo l'articolo
     if(!empty($codice) && !empty($descrizione) && !empty($quantita) && !empty($prezzo_unitario) && !$codice_esistente)
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
      $utenti = simplexml_load_file(BASE_PATH . "/utenti.xml") or die("Errore caricamento file utenti.xml");// Carico utenti.xml

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

      if(!$esistente)// Se l'email non è già registrata 
      {
       $hashed_password = password_hash($password, PASSWORD_DEFAULT);// Hash della password

       // Aggiungo il nuovo utente all'XML
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
 <?php endif; ?> 

 <!----------------------------------------------------------------- Se la page è area_riservata ----------------------------------------------------------------> 
 <?php if($_GET["page"] == "area_riservata"): ?>
  <div class="area-riservata">
   <?php
    // Controllo se l'utente è loggato, altrimenti lo reindirizzo al login
    if(!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) 
    {
     header("Location: index.php?page=login");
     exit;
    }
   ?>

  <div class="user-box">
   <!-- Form di modifica profilo -->
   <h2>Area riservata</h2>

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

   <div class="user-actions">
    <form method="post">
     <input type="submit" name="logout" value="Logout">
    </form>

    <form method="post">
     <input type="submit" name="ask_delete" value="Elimina account">
    </form>
   </div>
  </div>

  <!--- Se l'utente ha cliccato su salva modifiche --->
  <?php
   if(isset($_POST["save_changes"])) 
   {
    // Recupero e sanifico i dati del form
    $nome = trim($_POST["nome"]);
    $cognome = trim($_POST["cognome"]);
    $email = $_SESSION["user"]["email"];

    $utenti = simplexml_load_file(BASE_PATH."/utenti.xml");// Carico utenti.xml

    // Aggiorno i dati nell'XML
    foreach($utenti->Utente as $u) 
    {
     if((string)$u->Email === $email) 
     {
      $u->Nome = $nome;
      $u->Cognome = $cognome;
      break;
     }
    }

    // Salvo le modifiche a utenti.xml
    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($utenti->asXML());
    $dom->save(BASE_PATH."/utenti.xml");

    // Aggiorno i dati nella sessione
    $_SESSION["user"]["nome"] = $nome;
    $_SESSION["user"]["cognome"] = $cognome;

    header("Location: index.php?page=area_riservata&saved=1");
    exit;
   }
  ?>
  
  <!-- Form di conferma eliminazione account -->
  <?php if(isset($_POST["ask_delete"]) || isset($_POST["confirm_delete"])): ?>
   <form method="post" style="margin-top:15px">
    <input type="hidden" name="ask_delete" value="1"> <!--Mantengo lo stato di richiesta eliminazione -->

    <label>Conferma password</label><br>
    <input type="password" name="delete_password"><br>

    <input type="submit" name="confirm_delete" value="Conferma eliminazione">
   </form>
  <?php endif; ?>

 </div>
 <?php endif; ?>

 <!---------------------------------------------------------------- Logout ---------------------------------------------------------------->
 <?php
 if(isset($_POST["logout"])) 
 {
  session_unset(); // Rimuovo tutte le variabili di sessione
  session_destroy(); // Distruggo la sessione
  header("Location: index.php?page=home"); // Reindirizzo alla home
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
    echo "<p style='color:red'>Inserisci la password per confermare</p>";
   } 
   
   else 
   {
    // Recupero email e file fattura dell'utente loggato
    $email = $_SESSION["user"]["email"];
    $file_fattura = BASE_PATH . "/fatture/" . $_SESSION["user"]["file_fattura"];

    $utenti = simplexml_load_file(BASE_PATH . "/utenti.xml") or die("Errore caricamento utenti.xml");// Carico utenti.xml

    $index = 0;// Indice per tenere traccia della posizione dell'utente nell'XML

    foreach($utenti->Utente as $u) 
    {
     if((string)$u->Email === $email) 
     {
      // Se la password inserita è errata
      if(!password_verify($password_input, (string)$u->Password)) 
      {
       echo "<p style='color:red'>Password errata</p>";
       $keep_delete_open = true;
       break; // Esco dal ciclo senza eliminare l'utente
      }

      unset($utenti->Utente[$index]);// Rimuovo l'utente dall'XML se la password è corretta
      $delete_ok = true;
      break;
     }
     $index++;
    }

    if(!empty($delete_ok))// Se l'eliminazione è andata a buon fine
    {
     $dom = new DOMDocument("1.0", "UTF-8");// Creo un nuovo DOMDocument
     $dom->preserveWhiteSpace = false;// Rimuovo gli spazi bianchi inutili
     $dom->formatOutput = true;// Indento il file
     $dom->loadXML($utenti->asXML());// Carico l'XML da salvare
     $dom->save(BASE_PATH . "/utenti.xml");// Salvo le modifiche a utenti.xml

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
   }
  }
 ?>

</div>