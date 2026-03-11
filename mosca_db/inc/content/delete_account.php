<?php
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
?>