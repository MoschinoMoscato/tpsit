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

</div>