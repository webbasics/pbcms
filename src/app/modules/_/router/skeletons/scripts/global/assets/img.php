<?php
  $app = new App;
  $mng = (new Pbcassets)->img();

  //CHECKING THE REQUEST TYPE

  if ($param[1] == 'v' || $param[1] == 'view') {
    $type = 'view';

    $newparam = array();
    if (isset($param[2])) {
      $newparam[1] = $param[2];
      if (isset($param[3])) {
        $newparam[2] = $param[3];
      }
    }
    $param = $newparam;

    $dlDisallowed = false;
    if (isset($_POST['e'])) {
      if ($_POST['e'] == 'E_ASSET_IMG_DL_01') {
        $dlDisallowed = true;
      }
    }
  } else if ($param[1] == 'dl') {
    $type = 'dl';

    $newparam = array();
    if (isset($param[2])) {
      $newparam[1] = $param[2];
      if (isset($param[3])) {
        $newparam[2] = $param[3];
      }
    }
    $param = $newparam;
  } else {
    $type = false;
  }

  //OBTAINING IMAGE AND CONFIGURING THE CLASS

  $img = $mng->autorun(array(
    "params" => $param
  ));

  //SECURITY CHECKS

  if ($mng->config['assetProtection'] == true) {
    if (!$type) {
      if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'assets/img/' . $mng->get('imgurl'))) {
        header('Location: ' . $app->baseurl . 'assets/img/v/' . $mng->get('imgurl'));
        die('redirecting...');
      }
    }
  }

  //RETURNING RESPONSE

  if (!$type) {
    $mng->print($img);
  } else if ($type == 'view') {
    ?>
      <!DOCTYPE html>
      <html lang="en">
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <title><?php echo $mng->get('imginfo')['name'] . (!$mng->get('imginfo')['author'] ? '' : ' by ' . $mng->get('imginfo')['author'])?></title>

          <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
          <link rel="icon" type="image/png" href="<?php echo $app->baseurl; ?>assets/img/vDV5X4KSPbUJVoWsJAEkD2knuR4skOzDrNmFTbtVyosua2CJBr">
          <style media="screen">
            html, body {
              background-color: #d9d9d9;
              font-family: 'Montserrat', sans-serif;
            }

            .middle {
              position: fixed;
              top: 50%;
              left: 50%;
              transform: translate(-50%, -50%);
              -ms-transform: translate(-50%, -50%);
              max-width: 50%;
            }

            .image-container {
              position: relative;
              max-width: 100%;
            }

            .image {
              transition: all 0.5s;
              display: block;
              max-width: 100%;
              border: 2px solid #e5e5e5;
              border-radius: 6px;
              -webkit-box-shadow: 0px 0px 10px 5px rgba(0,0,0,0.75);
              -moz-box-shadow: 0px 0px 10px 5px rgba(0,0,0,0.75);
              box-shadow: 0px 0px 10px 5px rgba(0,0,0,0.75);
              z-index: 1;
              object-fit: cover;
            }

            .image:hover {
              -webkit-box-shadow: 0px 0px 10px 6px rgba(0,0,0,0.75);
              -moz-box-shadow: 0px 0px 10px 6px rgba(0,0,0,0.75);
              box-shadow: 0px 0px 10px 6px rgba(0,0,0,0.75);
            }

            .image-info {
              opacity: 1;
              position: absolute;
              bottom: 5%;
              left: 3%;
              margin: 10px;
              padding: 1px;
              padding-left: 20px;
              padding-right: 15px;
              border-left: 2px solid black;
              border-bottom: 2px solid black;
              border-bottom-left-radius: 6px;
              z-index: 2;
              font-size: 12px;
              background-color: rgba(229, 229, 229, 0.3);
              -webkit-box-shadow: -1px 1px 10px -6px rgba(229,229,229,1);
              -moz-box-shadow: -1px 1px 10px -6px rgba(229,229,229,1);
              box-shadow: -1px 1px 10px -6px rgba(229,229,229,1);
            }

            .text-1 {
              font-size: 15px;
              font-weight: 700;
            }

            a:link {
              color: #000e6b;
              text-decoration: none;
            }

            a:visited {
              color: #000e6b;
              text-decoration: none;
            }

            a:hover {
              color: #000e6b;
              text-decoration: none;
            }

            a:active {
              color: #000e6b;
              text-decoration: none;
            }

            <?php
              if ($dlDisallowed == true) {
                ?>
                  @import "https://fonts.googleapis.com/css?family=Montserrat";

                  body::after {
                    font-family: 'Montserrat', sans-serif;
                    font-weight: 600;
                    font-size: 17px;
                    content: "NOTICE: You are not allowed to download this image! (E_ASSET_IMG_DL_01)";
                    text-align: center;
                    vertical-align: middle;
                    line-height: 55px;
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 60px;
                    background-color: #e0dc00;
                  }
                <?php
              }
            ?>
          </style>
        </head>
        <body>
          <div class="middle">
            <div class="image-container">
              <img class="image" src="<?php echo $app->baseurl; ?>assets/img/<?php echo $mng->get('imgurl'); ?>">
              <div class="image-info">
                <p class="text-1"><?php echo $mng->get('imginfo')['name'] . (!$mng->get('imginfo')['author'] ? '' : ' by ' . $mng->get('imginfo')['author'])?></p>
                <p class="text-2"><strong>licence:</strong> <?php echo (!$mng->get('imginfo')['licence'] ? "-" : $mng->get('imginfo')['licence']); ?></p>
                <p class="text-3"><strong>download:</strong> <?php echo (!$mng->get('imginfo')['download'] ? "-" : '<a target="_blank" href="' . $app->baseurl . '/assets/img/dl/' . $mng->get('imgurl') . '">' . $mng->get('imginfo')['original'] . '</a>'); ?></p>
              </div>
            </div>
          </div>
        </body>
        <?php
          if ($mng->config['BlockCtrls'] == true) {
            ?>
              <script language="JavaScript">
                /**
                  * Disable right-click of mouse, F12 key, and save key combinations on page
                  * By Arthur Gareginyan (arthurgareginyan@gmail.com)
                  * For full source code, visit https://mycyberuniverse.com
                  */
                  window.onload = function() {
                    document.addEventListener("contextmenu", function(e){
                      e.preventDefault();
                    }, false);
                    document.addEventListener("keydown", function(e) {
                      //document.onkeydown = function(e) {
                      // "I" key
                      if (e.ctrlKey && e.shiftKey && e.keyCode == 73) {
                        disabledEvent(e);
                      }
                      // "J" key
                      if (e.ctrlKey && e.shiftKey && e.keyCode == 74) {
                        disabledEvent(e);
                      }
                      // "S" key + macOS
                      if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
                        disabledEvent(e);
                      }
                      // "U" key
                      if (e.ctrlKey && e.keyCode == 85) {
                        disabledEvent(e);
                      }
                      // "F12" key
                      if (event.keyCode == 123) {
                        disabledEvent(e);
                      }
                    }, false);
                    function disabledEvent(e){
                      if (e.stopPropagation){
                        e.stopPropagation();
                      } else if (window.event){
                        window.event.cancelBubble = true;
                      }
                      e.preventDefault();
                      return false;
                    }
                  };

                  //console protection
                  (function() {
                      try {
                          var $_console$$ = console;
                          Object.defineProperty(window, "console", {
                              get: function() {
                                  if ($_console$$._commandLineAPI)
                                      throw "Sorry, for security reasons, the script console is deactivated on netflix.com";
                                  return $_console$$
                              },
                              set: function($val$$) {
                                  $_console$$ = $val$$
                              }
                          })
                      } catch ($ignore$$) {
                      }
                  })();
                  </script>
            <?php
          }
        ?>
      </html>
    <?php
  } else if ($type == 'dl') {
    if (!$mng->get('imginfo')['download']) {
      $app->redirect($app->baseurl . 'assets/img/v/' . $mng->get('imgurl'), array(
        "method" => "POST",
        "data" => array(
          "e" => "E_ASSET_IMG_DL_01"
        )
      ));
    } else {
      $app->file('dl', 'modules/_/assets/files/img/' . $mng->get('imginfo')['target'] . '.' . $mng->get('imginfo')['ext'], $mng->get('imginfo')['original']);
    }
  }
?>
