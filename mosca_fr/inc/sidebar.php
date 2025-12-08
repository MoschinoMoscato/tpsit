<div class="sidebar">

 <?php
  $site = include("parsing.php");

  array_multisort($site["pos"], SORT_ASC, $site["text"], $site["page"]);
  
  foreach($site["text"] as $i => $value)
  {
   echo '<p><a href="?page=' . $site["page"][$i] . '">' . $value . '</a></p>';
  }
 ?>

</div>