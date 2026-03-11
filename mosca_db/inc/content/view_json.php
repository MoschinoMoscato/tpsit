<div class="view_json">
 <?php
  $json_file = file_get_contents($fattura_corrente); // Leggo il file XML
  $xml_json = simplexml_load_string($json_file) or die("Errore caricamento XML"); // Carico il file XML

  $json = json_encode($xml_json, JSON_PRETTY_PRINT); // Converto in JSON con pretty print per la formattazione

  echo "<pre>" . $json . "</pre>"; // Stampo il JSON formattato
 ?>
</div>