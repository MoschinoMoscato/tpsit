<?php
 $host = "localhost";
 $user = "root";
 $pass = "";
 $db   = "sito_tpsit";

 $conn = mysqli_connect($host, $user, $pass, $db);

 if(!$conn) 
 {
  die("Connessione fallita: " . mysqli_connect_error());
 }
?>