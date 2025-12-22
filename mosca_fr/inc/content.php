<div class="content">
 
 <!-- Se la page è Home -->
 <?php if($_GET["page"] == "home"): ?>
  <div class="home">
   <p title="Benvenuto nel sito di Leonardo Mosca">Welcome to Leonardo Mosca's site</p>
  </div>
 <?php endif; ?>

 <!-- Se la page è php_info -->
 <?php if($_GET["page"] == "PHP_info"): ?>
  <iframe src="inc/phpinfo.php" title="PHP_info"></iframe> <!-- iframe per mostrare phpinfo -->
 <?php endif; ?>

 <!-- Se la page è Form -->
 <?php if($_GET["page"] == "form"): ?>
  <div class="form">

   <div class="form-top">

    <?php
     // Carico DOMDocument
     $xml_dom = new DOMDocument();
     $xml_dom->preserveWhiteSpace = false;
     $xml_dom->formatOutput = true; 
     $xml_dom->load("fatture.xml"); 
    ?> 

    <!-- Form per l'inserimento di un nuovo articolo -->
    <form action="" method="post"> 
     <?php $xml_sx = simplexml_load_file("fatture.xml") or die("Errore caricamento XML"); // Apro il file XML ?>

     <label for="codice">Codice articolo:</label><br>
     <input type="number" id="codice" name="codice"><br>

     <label for="descrizione">Descrizione articolo:</label><br>
     <input type="text" id="descrizione" name="descrizione"><br>

     <label for="quanto">Quantità:</label><br>
     <input type="number" id="quanto" name="quanto"><br>

     <label for="fname">Prezzo unitario €:</label><br>
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

      $xml_sx->asXML("fatture.xml"); // Salvo le modifiche al file XML

      // Riformatto il file XML per renderlo leggibile    
      $xml_dom->load("fatture.xml");
      $xml_dom->save("fatture.xml");
      header("Location: index.php?page=form");
     }
    }

    /* RESET TABELLA */
    if (isset($_POST["reset_xml"])) 
    {
     // Cancello tutti gli Articolo
     unset($xml_sx->Articoli->Articolo);

     // Salvo l'XML vuoto
     $xml_sx->asXML("fatture.xml");

     // Ricarico la pagina
     header("Location: index.php?page=form");
     exit;
    }
   ?>
   
			<!--- Ricarico il file per poi stampare la tabella HTML --->
			<?php $xml_list = simplexml_load_file("fatture.xml"); ?>

			<table class = "tabella">
				<tr>
					<th>Codice</th>
					<th>Descrizione</th>
					<th>Quantità</th>
					<th>Prezzo unitario €</th>
					<th>Prezzo totale €</th>
			 </tr>
				
				<?php foreach($xml_list->Articoli->Articolo as $art): ?>
			 	<tr>
			 		<td> <?php echo $art->Codice; ?> </td>
			 		<td> <?php echo $art->Descrizione; ?> </td>
						<td> <?php echo $art->Quantita; ?> </td>
						<td> <?php echo $art->PrezzoUnitario; ?> </td>
						<td> <?php echo $art->PrezzoTotale; ?> </td>
			  </tr>	
				<?php endforeach; ?>

			</table>
  </div>
 <?php endif; ?>


</div>