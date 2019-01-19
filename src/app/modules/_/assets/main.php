<?php
  class Pbcassets {
    public $app;
    public $assets;
    public $config;

    public function __construct() {
      $this->app = new App;
      $this->assets = $this->app->jdb('open', 'sys/assets/list');
      $this->config = $this->app->jdb('open', 'sys/assets/config');
    }

    public function css($masterInput = false) {
      return new class($masterInput) extends Pbcassets {
        public $app;
        public $assets;
        public $config;
        public $assetlist;
        public $lib;
        public $libname;
        public $assetinfo;

        public function __construct($input = false) {
          $this->app = new App;
          $this->assets = $this->app->jdb('open', 'sys/assets/list');
          $this->config = $this->app->jdb('open', 'sys/assets/config');
          $this->assetlist = $this->assets['css'];
          $this->set('lib', 'sys');
          $this->set('libname', 'err_01');

          if (!is_array($input)) {
            $this->autorun($input);
          }
        }

        public function autorun($options) {

          $cssReady = false;
          if (!is_array($options)) {
            return false;
          } else {
            if (isset($options['lib']) && isset($options['libname'])) {
              $params = array(
                "lib" => $options['lib'],
                "libname" => $options['libname']
              );
            } else {
              if (!isset($options['params'])) {
                return false;
              } else {
                $params = $options['params'];
              }
            }

            if (isset($options['options'])) {
              $ops = $options['options'];
            } else {
              $ops = $options;
            }

            if (isset($ops['cssReady'])) {
              $cssReady = $ops['cssReady'];
            }
          }

          $this->init($params);

          $css = $this->get('css');

          if(!$css) {
            $this->set('lib', 'sys');
            $this->set('libname', 'err_02');
            $css = $this->get('css');
          }

          if ($cssReady == 'print' || $cssReady == 'return') {
            if ($cssReady == 'print') {
              $this->print($css);
              return true;
            } else {
              return $css;
            }
          } else {
            if ($this->config['autorun']['cssReady'] == 'print') {
              $this->print($css);
              return true;
            } else {
              return $css;
            }
          }
        }

        public function init($params) {
          if (!$params || count($params) < 2) {
            if (is_array($params) && count($params) == 1) {
              $this->set('lib', 'sys');
              $this->set('libname', 'err_03');
            }

            return false;
          } else {
            $this->set('lib', $params[1]);
            $this->set('libname', $params[2]);

            return true;
          }
        }

        public function print($css) {
          $this->app->setheader('css');
          print_r($css);
        }

        public function exists($type, $input = false) {
          if ($type == 'lib') {
            if (!$input) { $input = $this->lib; }
            if (isset($this->assetlist[$input])) {
              return true;
            } else {
              return false;
            }
          } else if ($type == 'libname') {
            if (!$input) { $input = $this->libname; }
            if (isset($this->assetlist[$this->lib][$input])) {
              return true;
            } else {
              return false;
            }
          } else if ($type == 'defaultLibname') {
            if (!$input) { $input = $this->lib; }
            if (isset($this->assetlist[$input]['default'])) {
              return true;
            } else {
              return false;
            }
          } else {
            return false;
          }
        }

        public function set($type, $input = false) {
          if ($type == 'lib') {
            $this->lib = $input;
          } else if ($type == 'libname') {
            $this->libname = $input;
          } else if ($type == 'assetinfo') {
            $this->assetinfo = $input;
          }
        }

        public function get($type) {
          if ($type == 'lib') {
            return $this->lib;
          } else if ($type == 'libname') {
            return $this->libname;
          } else if ($type == 'assetinfo') {
            if (!$this->exists('lib')) {
              $this->set('lib', 'sys');
              $this->set('libname', 'err_04');
            } else {
              if (!$this->exists('libname')) {
                $this->set('lib', 'sys');
                $this->set('libname', 'err_05');
              }
            }

            return $this->assetlist[$this->lib][$this->libname];
          } else if ($type == 'css') {
            $assetinfo = $this->get('assetinfo');
            if (!isset($assetinfo['target'])) {
              $this->set('lib', 'sys');
              $this->set('libname', 'err_06');
            }
            $css = $this->app->file('content', 'modules/_/assets/files/css/' . $assetinfo['target']);
            return $css;
          } else {
            return false;
          }
        }
      };
    }

    public function js($masterInput = false) {
      return new class($masterInput) extends Pbcassets {
        public $app;
        public $assets;
        public $config;
        public $assetlist;
        public $lib;
        public $libname;
        public $assetinfo;

        public function __construct($input = false) {
          $this->app = new App;
          $this->assets = $this->app->jdb('open', 'sys/assets/list');
          $this->config = $this->app->jdb('open', 'sys/assets/config');
          $this->assetlist = $this->assets['js'];
          $this->set('lib', 'sys');
          $this->set('libname', 'err_01');

          if (!is_array($input)) {
            $this->autorun($input);
          }
        }

        public function autorun($options) {

          $jsReady = false;
          if (!is_array($options)) {
            return false;
          } else {
            if (isset($options['lib']) && isset($options['libname'])) {
              $params = array(
                "lib" => $options['lib'],
                "libname" => $options['libname']
              );
            } else {
              if (!isset($options['params'])) {
                return false;
              } else {
                $params = $options['params'];
              }
            }

            if (isset($options['options'])) {
              $ops = $options['options'];
            } else {
              $ops = $options;
            }

            if (isset($ops['jsReady'])) {
              $jsReady = $ops['jsReady'];
            }
          }

          $this->init($params);

          $js = $this->get('js');

          if(!$js) {
            $this->set('lib', 'sys');
            $this->set('libname', 'err_02');
            $js = $this->get('js');
          }

          if ($jsReady == 'print' || $jsReady == 'return') {
            if ($jsReady == 'print') {
              $this->print($js);
              return true;
            } else {
              return $js;
            }
          } else {
            if ($this->config['autorun']['jsReady'] == 'print') {
              $this->print($js);
              return true;
            } else {
              return $js;
            }
          }
        }

        public function init($params) {
          if (!$params || count($params) < 2) {
            if (is_array($params) && count($params) == 1) {
              $this->set('lib', 'sys');
              $this->set('libname', 'err_03');
            }

            return false;
          } else {
            $this->set('lib', $params[1]);
            $this->set('libname', $params[2]);

            return true;
          }
        }

        public function print($js) {
          $this->app->setheader('js');
          print_r($js);
        }

        public function exists($type, $input = false) {
          if ($type == 'lib') {
            if (!$input) { $input = $this->lib; }
            if (isset($this->assetlist[$input])) {
              return true;
            } else {
              return false;
            }
          } else if ($type == 'libname') {
            if (!$input) { $input = $this->libname; }
            if (isset($this->assetlist[$this->lib][$input])) {
              return true;
            } else {
              return false;
            }
          } else if ($type == 'defaultLibname') {
            if (!$input) { $input = $this->lib; }
            if (isset($this->assetlist[$input]['default'])) {
              return true;
            } else {
              return false;
            }
          } else {
            return false;
          }
        }

        public function set($type, $input) {
          if ($type == 'lib') {
            $this->lib = $input;
          } else if ($type == 'libname') {
            $this->libname = $input;
          } else if ($type == 'assetinfo') {
            $this->assetinfo = $input;
          }
        }

        public function get($type) {
          if ($type == 'lib') {
            return $this->lib;
          } else if ($type == 'libname') {
            return $this->libname;
          } else if ($type == 'assetinfo') {
            if (!$this->exists('lib')) {
              $this->set('lib', 'sys');
              $this->set('libname', 'err_04');
            } else {
              if (!$this->exists('libname')) {
                $this->set('lib', 'sys');
                $this->set('libname', 'err_05');
              }
            }

            return $this->assetlist[$this->lib][$this->libname];
          } else if ($type == 'js') {
            $assetinfo = $this->get('assetinfo');
            if (!isset($assetinfo['target'])) {
              $this->set('lib', 'sys');
              $this->set('libname', 'err_06');
            }

            $js = $this->app->file('content', 'modules/_/assets/files/js/' . $assetinfo['target']);
            return $js;
          } else {
            return false;
          }
        }
      };
    }

    public function img($masterInput = false) {
      return new class($masterInput) extends Pbcassets {
        public $app;
        public $config;
        public $assets;
        public $imglist;
        public $imgurl;
        public $imginfo;

        public function __construct($options) {
          $this->app = new App;
          $this->config = $this->app->jdb('open', 'sys/assets/config');
          $this->assets = $this->app->jdb('open', 'sys/assets/list');
          $this->imglist = $this->assets['img'];
          $this->set('imgurl', false);

          if (!is_array($options)) {
            $this->autorun($options);
          }
        }

        public function autorun($options) {

          $imgReady = false;
          if (!is_array($options)) {
            return false;
          } else {
            if (isset($options['imgurl'])) {
              $params = $options['imgurl'];
            } else {
              if (!isset($options['params'])) {
                return false;
              } else {
                $params = $options['params'];
              }
            }
          }

          if (isset($options['options'])) {
            $ops = $options[$options];
          } else {
            $ops = $options;
          }

          if (isset($ops['imgReady'])) {
            $imgReady = $ops['imgReady'];
          }

          $this->init($params);

          $img = $this->get('img');

          if (!$img) {
            //error: 01
            $this->set('imgurl', 'E_N6RZwM74IRU69bhBV9EZAy9n9vGg0hMPeGivNdXx51wkPo21Wv');
            $img = $this->get('img');
          }

          if ($imgReady == 'print' || $imgReady == 'return') {
            if ($imgReady == 'print') {
              $this->print($img);
              return true;
            } else {
              return $img;
            }
          } else {
            if ($this->config['autorun']['imgReady'] == 'print') {
              $this->print($img);
              return true;
            } else {
              return $img;
            }
          }
        }

        public function init($params) {
          if (!$params) {
            //error: 02
            $this->set('imgurl', 'E_1oJ8pBxQvQQFEa1N4L80g1opM7CisVeSEhLklOQ11WBuH4SEld');
            return false;
          } else {
            $this->set('imgurl', $params[1]);

            return true;
          }
        }

        public function print($img) {
          $this->app->setheader($this->get('imgExt'));
          print_r($img);
        }

        public function exists($type, $input = false) {
          if ($type == 'imgurl') {
            if (!$input) { $input = $this->imgurl; }
            if (isset($this->imglist[$input])) {
              return true;
            } else {
              return false;
            }
          }
        }

        public function set($type, $input = false) {
          if ($type == 'imgurl') {
            $this->imgurl = (substr($this->imgurl, 0, 2) == 'E_' ? $this->get('imgurl') : $input);
          } else if ($type == 'imgExt') {
            $this->imgExt = $input;
          } else if ($type == 'imginfo') {
            $this->imginfo = $input;
          }
        }

        public function get($type) {
          if ($type == 'imgurl') {
            return $this->imgurl;
          } else if ($type == 'imgExt') {
            return $this->get('imginfo')['ext'];
          } else if ($type == 'imginfo') {
            if (!$this->exists('imgurl')) {
              //error: 03
              $this->set('imgurl', 'E_0mdf3NfxAG41wq2PbTjWrn7KVNtgbUCC5mkAk4473jIhc82nuV');
            }

            return $this->imglist[$this->get('imgurl')];
          } else if ($type == 'img') {
            if (!$this->get('imginfo')) {
              //error: 04
              $this->set('imgurl', 'E_ZDt0kBlnETgzvkY4ZB3h6s6vgdxDMRkSNYkrD5DFKmgMEj6Red');
            }
            $imginfo = $this->get('imginfo');

            $img = $this->app->file('content', 'modules/_/assets/files/img/' . $imginfo['target'] . '.' . $imginfo['ext']);
            return $img;
          } else {
            return false;
          }
        }

      };
    }

  }
?>
