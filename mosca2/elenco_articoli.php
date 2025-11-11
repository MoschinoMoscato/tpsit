<?php
$xmlFile = __DIR__ . '/esempio_fattura.xml';
$xsdFile = __DIR__ . '/fattura_xsd.xsd';
$messaggio = '';
$errore = '';

// Gestione aggiunta articolo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi') {
    $codiceArticolo = isset($_POST['codice_articolo']) ? trim($_POST['codice_articolo']) : '';
    $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : '';
    $quantita = isset($_POST['quantita']) ? trim($_POST['quantita']) : '';
    $prezzoUnitario = isset($_POST['prezzo_unitario']) ? trim($_POST['prezzo_unitario']) : '';
    
    if (empty($codiceArticolo) || empty($descrizione) || empty($quantita) || empty($prezzoUnitario)) {
        $errore = 'Tutti i campi sono obbligatori';
    } else {
        // Carica il file XML
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        
        if (file_exists($xmlFile)) {
            if (!$dom->load($xmlFile)) {
                $errore = 'Errore nel caricamento del file XML';
            } else {
                if (!$dom->documentElement || $dom->documentElement->nodeName !== 'FatturaXML') {
                    $errore = 'Struttura XML non valida';
                }
            }
        } else {
            $errore = 'File XML non trovato';
        }
        
        // Se non ci sono errori, procedi con l'aggiunta
        if (empty($errore)) {
            // Trova il nodo Articoli
            $articoliNode = $dom->getElementsByTagName('Articoli')->item(0);
            if (!$articoliNode) {
                $fatturaNode = $dom->getElementsByTagName('FatturaXML')->item(0);
                $articoliNode = $dom->createElement('Articoli');
                $fatturaNode->appendChild($articoliNode);
            }
            
            // Calcola PrezzoTotale
            $prezzoTotale = floatval($quantita) * floatval($prezzoUnitario);
            
            // Crea nuovo articolo
            $articolo = $dom->createElement('Articolo');
            $articolo->appendChild($dom->createElement('CodiceArticolo', htmlspecialchars($codiceArticolo)));
            $articolo->appendChild($dom->createElement('Descrizione', htmlspecialchars($descrizione)));
            $articolo->appendChild($dom->createElement('Quantita', htmlspecialchars($quantita)));
            $articolo->appendChild($dom->createElement('PrezzoUnitario', htmlspecialchars($prezzoUnitario)));
            $articolo->appendChild($dom->createElement('PrezzoTotale', number_format($prezzoTotale, 2, '.', '')));
            $articoliNode->appendChild($articolo);
            
            // Valida il XML
            libxml_use_internal_errors(true);
            if ($dom->schemaValidate($xsdFile)) {
                // Salva il file
                if ($dom->save($xmlFile)) {
                    // Imposta permessi corretti dopo il salvataggio
                    @chmod($xmlFile, 0664);
                    $messaggio = 'Articolo aggiunto con successo';
                } else {
                    $errore = 'Errore nel salvataggio del file. Verificare i permessi.';
                }
            } else {
                $errors = libxml_get_errors();
                $errore = 'Errore di validazione XML';
                if (!empty($errors)) {
                    $errore .= ': ' . $errors[0]->message;
                }
                libxml_clear_errors();
            }
        }
    }
}

// Carica articoli dal file XML
$articoli = [];
if (file_exists($xmlFile)) {
    $xml = @simplexml_load_file($xmlFile);
    if ($xml !== false && isset($xml->Articoli->Articolo)) {
        foreach ($xml->Articoli->Articolo as $articolo) {
            $articoli[] = [
                'codice' => (string)$articolo->CodiceArticolo,
                'descrizione' => (string)$articolo->Descrizione,
                'quantita' => (string)$articolo->Quantita,
                'prezzo_unitario' => (string)$articolo->PrezzoUnitario,
                'prezzo_totale' => (string)$articolo->PrezzoTotale
            ];
        }
    }
}
?>
<h1>Elenco Articoli</h1>

<?php if ($messaggio): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($messaggio); ?></div>
<?php endif; ?>

<?php if ($errore): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errore); ?></div>
<?php endif; ?>

<h2 class="mt-4">Aggiungi Articolo</h2>
<form method="POST" action="?page=elenco_articoli">
    <input type="hidden" name="azione" value="aggiungi">
    <div class="mb-3">
        <label for="codice_articolo" class="form-label">Codice Articolo</label>
        <input type="text" class="form-control" id="codice_articolo" name="codice_articolo" required>
    </div>
    <div class="mb-3">
        <label for="descrizione" class="form-label">Descrizione Articolo</label>
        <input type="text" class="form-control" id="descrizione" name="descrizione" required>
    </div>
    <div class="mb-3">
        <label for="quantita" class="form-label">Quantità</label>
        <input type="number" class="form-control" id="quantita" name="quantita" required>
    </div>
    <div class="mb-3">
        <label for="prezzo_unitario" class="form-label">Prezzo Unitario</label>
        <input type="number" step="0.01" class="form-control" id="prezzo_unitario" name="prezzo_unitario" required>
    </div>
    <button type="submit" class="btn btn-primary">Aggiungi</button>
</form>

<h2 class="mt-4">Articoli</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Codice Articolo</th>
            <th>Descrizione</th>
            <th>Quantità</th>
            <th>Prezzo Unitario</th>
            <th>Prezzo Totale</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($articoli)): ?>
            <tr>
                <td colspan="5" class="text-center">Nessun articolo presente</td>
            </tr>
        <?php else: ?>
            <?php foreach ($articoli as $articolo): ?>
                <tr>
                    <td><?php echo htmlspecialchars($articolo['codice']); ?></td>
                    <td><?php echo htmlspecialchars($articolo['descrizione']); ?></td>
                    <td><?php echo htmlspecialchars($articolo['quantita']); ?></td>
                    <td><?php echo htmlspecialchars($articolo['prezzo_unitario']); ?></td>
                    <td><?php echo htmlspecialchars($articolo['prezzo_totale']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
