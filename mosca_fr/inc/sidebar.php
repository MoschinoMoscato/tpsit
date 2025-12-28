<div class="sidebar">

 <?php
  $site = include("parsing.php");

  array_multisort($site["pos"], SORT_ASC, $site["text"], $site["page"]);
  
  foreach($site["text"] as $i => $value)
  {
   $active = ($_GET["page"] == $site["page"][$i]) ? 'active' : '';

   echo '<a class = "' . $active . '" href="?page=' . $site["page"][$i] . '">' . $value . '</a>';
  }
 ?>

 <?php if (isset($_SESSION["logged"]) && $_SESSION["logged"] === true): ?>
   
  <div class="sidebar-user">
   <a href="?page=area_riservata" class="sidebar-user-link">

    <div class="sidebar-avatar"
     style="background-color: <?= htmlspecialchars($_SESSION["user"]["colore"]) ?>">
     <?= strtoupper($_SESSION["user"]["nome"][0] . $_SESSION["user"]["cognome"][0]) // Prendo le iniziali del nome e cognome e le rendo maiuscole ?>
    </div>

    <div class="sidebar-user-info">
     <span class="sidebar-user-name">
      <?= htmlspecialchars($_SESSION["user"]["nome"]) ?>
      <?= htmlspecialchars($_SESSION["user"]["cognome"]) ?>
     </span>
    </div>

   </a>
  </div>

 <?php endif; ?>

</div>