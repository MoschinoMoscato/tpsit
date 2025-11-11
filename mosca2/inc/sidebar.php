<div class="col-md-2 site-sidebar p-3">
        <?php

    $i = 0;

    foreach($ref as $rif)
    {
        echo '<h5 class="nav flex-column gap-1"><a href="' . $rif . '" class="link-light text-decoration-none">' . "$parti[$i]" . '</a></h5>';
        $i++;
    }

    ?>
</div>

    