<div class="content">
 <?php
  $acconti = "acconti.xml";
  $versato = "versato.xml";

  // Carico DOMDocument 
  $xml_dom = new DOMDocument();
  $xml_dom->preserveWhiteSpace = false;
  $xml_dom->formatOutput = true; 
  $xml_dom->load("$acconti"); 

  $xml = new DOMDocument();
  $xml->preserveWhiteSpace = false;
  $xml->formatOutput = true; 
  $xml->load("$versato"); 

 ?>

 <!-- Valido l'XML contro lo schema XSD -->
 <?php if($xml_dom->schemaValidate("acconti.xsd")) : ?>
  <div class="xml-status">XML valido</div>

 <?php else: ?>
  <div class="xml-status">XML non valido</div>

 <?php endif; ?>

 <?php $xml_list = simplexml_load_file($acconti); ?>

 <?php
  $host = "localhost";
  $user = "root";
  $pass = "";
  $db   = "5inb";

  $conn = mysqli_connect($host, $user, $pass, $db);

  if(!$conn) 
  {
   die("Connessione fallita: " . mysqli_connect_error());
  }
 ?>

 <?php 
  foreach($xml_list->clienti->cliente as $client)
  {
   $stmt = $conn->prepare
   (
    "INSERT INTO versato (codcli, descli, descli) VALUES (?, ?, ?)"
   );
   $stmt->bind_param($cliente->codiceCliente, $cliente->descrizioneCliente, $cliente->acconto);
   $stmt->execute();
  }

  $stmt = $conn->prepare
  (
   "SELECT codcli, descli, importo FROM versato"
  );
  $stmt->execute();
  $res = $stmt->store_result();
  
  $xml_ver = simplexml_load_file($versato);

  //foreach($stmt)
  //{
  // $new_article = $xml_ver->clienti->addChild("cliente");

 //  $new_article->addChild("codiceCliente", $stmt->codcli);
 //  $new_article->addChild("descrizioneCliente", $stmt->descli);
 //  $new_article->addChild("acconto", $stmt->descli);
 // }
  
 ?>
</div>