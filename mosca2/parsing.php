<?php

    $parti = [];
    $ref = [];
    $posi = [];
    $header = '';
    $footer = '';

    if(file_exists('config.xml'))
    {
        $xml = simplexml_load_file('config.xml');
        
        if($xml === false) {
            exit('Errore XML');
        }
        
        if(isset($xml->sidebar->part)) {
            foreach($xml->sidebar->part as $parte)
            {
                $parti[] = $parte;
            }

            foreach($xml->sidebar->rif as $href)
            {
                $ref[] = $href;
            }

            foreach($xml->sidebar->pos as $pos)
            {
                $posi[] = (int)$pos;
            }

            array_multisort($posi, SORT_ASC, $parti, $ref); 
        }

        $header = (string)$xml->header;
        $footer = (string)$xml->footer;
    }
    else
    {
        exit('Errore apertura file xml');
    }

?>