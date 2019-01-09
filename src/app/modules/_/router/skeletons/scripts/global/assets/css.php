<?php
  $app = new App;
  $mng = (new Pbcassets)->css();
  $css = $mng->autorun(array(
    "params" => $param
  ));

  if (!isset($_SERVER['HTTP_REFERER'])) {
    echo "ERROR: You are not allowed to watch this library raw!";
  } else {
    $mng->print($css);
  }
?>
