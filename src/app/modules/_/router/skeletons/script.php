<?php
  $app = new App;

  $pageinfo = array (
    "canLoad" => true
  );

  if (!$pageinfo['canLoad']) {
    echo 'Cannot load script because the page was not ready to load!';
  } else {
    require_once __DIR__ . '/scripts/' . $page['domain'] . '/' . $page['target'];
  }
?>
