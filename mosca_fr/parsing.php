<?php
 // Inizializzo le variabili
 $header = '';
 $footer = '';
 
	$text = [];
 $page = [];
 $pos = [];

	// Inizializzo l'array da ritornare
 $site = 
	[
  "header" => "",
  "footer" => "",
  "text"   => [],
  "page"   => [],
  "pos"    => []
 ];

 // Controllo se il file di configurazione esiste
 if(file_exists("config.xml"))
 {
  $xml=simplexml_load_file("config.xml") or die("Errore XML");// Carico il file XML

		$site["header"] = (string)$xml->header;// Header

		// Sidebar
  foreach($xml->sidebar->link as $link) 
		{
	 	$site["text"][] = (string)$link->text;
	 	$site["page"][] = (string)$link->page;
			$site["pos"][] = (int)$link->pos;
  }

		$site["footer"] = (string)$xml->footer;// Footer
 }
	
	return $site;// Ritorno l'array con i dati del sito
?>