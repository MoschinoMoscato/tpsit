<div class="col-md-10 content-area p-4">
    <?php 
        if (isset($_GET["page"]) && $_GET["page"] === "phpinfo") {
            echo '<iframe src="inc/phpinfo.php" class="w-100 border-0" style="height:80vh;"></iframe>';
        } elseif (isset($_GET["page"]) && $_GET["page"] === "elenco_articoli") {
            require('elenco_articoli.php');
        } else {
            echo '';
        }
    ?>  
</div>