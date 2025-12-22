<?php
 // Carico DOMDocument
 $xml_dom = new DOMDocument();
 $xml_dom->preserveWhiteSpace = false;
 $xml_dom->formatOutput = true; 
 $xml_dom->load("fatture.xml"); 

 // Esportazione in JSON            validando prima l'XML contro lo schema XSD
 if(isset($_POST["export_json"]) && $xml_dom->schemaValidate("fatture.xsd"))
 {
  $xml_json = simplexml_load_file("fatture.xml") or die("Errore caricamento XML"); // Apro il file XML

  $json = json_encode($xml_json, JSON_PRETTY_PRINT); // Converto in JSON

  // Header HTTP per forzare il download
  header("Content-Type: application/json");
  header("Content-Disposition: attachment; filename=fattura.json");

  // Invio il file al browser
  echo $json;
  exit;
 }
?>

<!DOCTYPE html>

<?php
 if(isset($_GET["page"]) == false)
 {
  header("Location: ?page=home");
  exit;
 }
?>

<html lang="en-IT">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/a-custom.css">
  <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
  <title>Leonardo Mosca's site</title>
 </head>

 <body>
  
  <div class="wrapper">
   <?php require('inc/header.php'); ?>
   
   <div class="container_sidebar_content">
    <?php require('inc/sidebar.php'); ?>
    <?php require('inc/content.php'); ?>
   </div>

   <?php require('inc/footer.php'); ?>
  </div>

 </body>
</html>