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