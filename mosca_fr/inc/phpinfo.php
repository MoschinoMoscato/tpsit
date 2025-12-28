<?php
 ob_start();
 phpinfo();
 $phpinfo = ob_get_clean();

 $override_css = <<<CSS
 <style>
 body { background:#F5EEDC; color:#3B2A1A; }

 h1, h2 { color:#3B2A1A; }

 table, table th, table td 
 {
  border-color: #00A86B !important;
 }

 th { background:#6E4F2B; color:#F5EEDC; }

 td { background:#F5EEDC; color:#3B2A1A; }

 .e { background:#6E4F2B; color:#F5EEDC; }
 .v { background:#F5EEDC; color:#3B2A1A; }
 .p { background:#F5EEDC; color:#3B2A1A; }

 a { color:#00A86B; }
 </style>
 CSS;

 echo str_replace('</head>', $override_css . '</head>', $phpinfo);
?>