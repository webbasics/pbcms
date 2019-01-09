<?php
  $app = new App;

  $pageitems = $app->jdb('open', 'sys/pages/' . $page['domain'] . '/' . $page['target'] . '.json');
  $defaultpageitems = $app->jdb('open', 'sys/router/defaultObjects.json');
  if (!$pageitems) {
    $pageinfo = array(
      "canLoad" => false,
      "errorDesc" => "could not load page items",
      "errorCode" => "E_PAGE_01"
    );
  } else if (!$defaultpageitems) {
    $pageinfo = array(
      "canLoad" => false,
      "errorDesc" => "could not load default page items",
      "errorCode" => "E_PAGE_02"
    );
  } else {
    $pageinfo = array(
      "canLoad" => true,
      "items" => $pageitems,
      "defaultItems" => $defaultpageitems
    );
  }
?>

<!DOCTYPE html>
<html lang="en" dir="<?php $app->config['details']['root']; ?>">
  <head>
    <title><?php echo $page['title']; ?></title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo ($app->config['details']['ssl'] ? 'https://' : 'http://') . $app->config['details']['domain'] . $app->config['details']['root']; ?>assets/css/rable.css/global">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <script type="text/javascript">
      $.pageinfo = <?php echo $app->json('encode', $pageinfo); ?>
    </script>
  </head>
  <body>
    <?php require_once __DIR__ . '/global/navbar.php'; ?>
    <div class="page-content"></div>
    <?php require_once __DIR__ . '/global/footer.php'; ?>
  </body>

  <script src="<?php echo ($app->config['details']['ssl'] ? 'https://' : 'http://') . $app->config['details']['domain'] . $app->config['details']['root']; ?>assets/js/rable.js/global" type="text/javascript"></script>
</html>
