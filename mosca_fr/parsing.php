<?php
 $header = '';
 $footer = '';
 
	$text = [];
 $page = [];
 $pos = [];

	$site = 
	[
  "header" => "",
  "footer" => "",
  "text"   => [],
  "page"   => [],
  "pos"    => []
 ];

 if(file_exists("config.xml"))
 {
  $xml=simplexml_load_file("config.xml") or die("Errore XML");

		$site["header"] = (string)$xml->header;

		// Sidebar
  foreach($xml->sidebar->link as $link) 
		{
	 	$site["text"][] = (string)$link->text;
	 	$site["page"][] = (string)$link->page;
			$site["pos"][] = (int)$link->pos;
  }

		$site["footer"] = (string)$xml->footer;
 }
	
	return $site;
?>