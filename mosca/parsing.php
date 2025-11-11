<?php

    $parti = [];
    $nparti = 0;
    $ref = [];
    $nref = 0;
    $posi = [];
    $npos = 0;
    $header = '';
    $footer = '';

    if(file_exists(filename: 'config.xml'))
    {
        $xml = simplexml_load_file(filename:'config.xml');
        
        if($xml === false) {
            exit('Errore XML');
        }
        
        if(isset($xml->sidebar->part)) {
            foreach($xml->sidebar->part as $parte)
            {
                $parti[$nparti] = $parte;
                $nparti++;
            }

            foreach($xml->sidebar->rif as $href)
            {
                $ref[$nref] = $href;
                $nref++;
            }

            foreach($xml->sidebar->pos as $pos)
            {
                $posi[$npos] = (int)$pos;
                $npos++;
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