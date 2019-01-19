<?php
  class App {
    public $config;
    public $baseurl;

    public function __construct() {
      $this->config = (file_exists(__DIR__ . '/internal/jdb/sys/config.json') ? json_decode(file_get_contents(__DIR__ . '/internal/jdb/sys/config.json'), true) : false);
      $this->baseurl = ($this->config['details']['ssl'] ? 'https://' : 'http://') . $this->config['details']['domain'] . $this->config['details']['root'];
    }

    public function get($type, $input = false) {
      if ($type == 'rand') {
        $length = (!$input ? 10 : $input);
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
      } else if ($type == "hostname") {
        $host = '';
        $hostSources = array('HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR');
        $sourceTransformations = array(
          "HTTP_X_FORWARDED_HOST" => function($val) {
            $elements = explode(',', $val);
            return trim(end($elements));
          }
        );

        foreach ($hostSources as $src) {
          if (!empty($host)) break;
          if (empty($_SERVER[$src])) continue;

          $host = $_SERVER[$src];

          if (array_key_exists($src, $sourceTransformations)) {
            $host = $sourceTransformations[$src]($host);
          }
        }

        $host = preg_replace('/:\d+$/', '', $host);

        return trim($host);
      } else if ($type == 'hostip') {
        return file_get_contents("http://ipecho.net/plain");
      } else if ($type == 'userip' || $type == 'uip') {
        $ip = '';
        $ipSources = array('HTTP_CLIENT_HOST', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        $sourceTransformations = array(
          "HTTP_X_FORWARDED_FOR" => function($val) {
            $elements = explode(',', $val);
            return trim(end($elements));
          }
        );

        foreach ($ipSources as $src) {
          if (!empty($ip)) break;
          if (empty($_SERVER[$src])) continue;

          $ip = $_SERVER[$src];

          if (array_key_exists($src, $sourceTransformations)) {
            $ip = $sourceTransformations[$src]($ip);
          }
        }

        $ip = trim($ip);

        if ($this->check('privateip', $ip)) {
          $ip = $this->get('hostip');
        }

        return $ip;
      } else if ($type == 'userlocation' || $type == 'userloc' || $type == 'ulocation' || $type == 'uloc') {
        //$ip = (!$input ? $this->get('uip') : $input);
        $ip = '192.168.178.255';
        $this->json('decode', file_get_contents('http://www.geoplugin.net/json.gp?ip=' . $ip));
      } else {
        return false;
      }
    }

    public function redirect($url, $options = false) {
      if (!$options) {
        header("Location: " . $url);
      } else {
        if (!isset($options['data']) || !isset($options['method'])) {
          return false;
        } else {

          $formid = 'redirect_form_' . $this->get('rand');
          ?>
            <form id="<?php echo $formid; ?>" action="<?php echo $url; ?>" method="<?php echo $options['method']; ?>">
            <?php
                foreach ($options['data'] as $name => $value) {
                    echo '<input type="hidden" name="'.htmlentities($name).'" value="'.htmlentities($value).'">';
                }
            ?>
            </form>
            <script type="text/javascript">
                document.getElementById('<?php echo $formid; ?>').submit();
            </script>
          <?php

          return true;
        }
      }
    }

    public function check($type, $input = false) {
      if ($type == 'privateip' || $type == 'pip') {
        $ip = (!$input ? $this->get('uip') : $input);
        $pri_addrs = array (
          '10.0.0.0|10.255.255.255',
          '172.16.0.0|172.31.255.255',
          '192.168.0.0|192.168.255.255',
          '169.254.0.0|169.254.255.255',
          '127.0.0.0|127.255.255.255'
        );

        $long_ip = ip2long($ip);
        if ($long_ip != -1) {
          foreach ($pri_addrs AS $pri_addr) {
            list ($start, $end) = explode('|', $pri_addr);

            // IF IS PRIVATE
            if ($long_ip >= ip2long ($start) && $long_ip <= ip2long ($end)) {
              return true;
            }
          }
        }

        return false;
      } else if ($type == "mobile") {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
      } else {
        return false;
      }
    }

    public function file($function, $file, $content = false) {
      $file = (substr($file, 0, 1) == '/' ? substr($file, 1) : $file);
      if (strpos($file, '/')) {
        $parts = explode('/', $file);
        if (strpos(end($parts), '.')) {
          $dir = implode('/', array_slice($parts, 0, -1)) . '/';
          $filename = array_pop($parts);
          $type = 'file';
        } else {
          $dir = $file . '/';
          $filename = '';
          $type = 'dir';
        }
      } else {
        if (strpos($file, '.')) {
          $type = 'file';
          $filename = $file;
          $dir = '';
        } else {
          $type = 'dir';
          $filename = '';
          $dir = '';
        }
      }

      if ($function == 'exists') {
        if ($type == 'file') {
          if (file_exists(__DIR__ . '/' . $file)) {
            return true;
          } else {
            return false;
          }
        } else {
          if (is_dir(__DIR__ . '/' . $dir)) {
            return true;
          } else {
            return false;
          }
        }
      } else if ($function == 'content') {
        if ($type == 'dir') {
          return false;
        } else {
          if ($this->file('exists', $file)) {
            $path = __DIR__ . '/' . $file;
            $file = fopen($path, "r");
            if (filesize($path) > 0) {
              $content = fread($file, filesize($path));
            } else {
              $content = '';
            }
            return $content;
            fclose($file);
          } else {
            return false;
          }
        }
      } else if ($function == 'print') {
        if ($type == 'dir') {
          return false;
        } else {
          if (!$this->file('content', $file)) {
            return false;
          } else {
            echo htmlentities($this->file('content', $file));
            return true;
          }
        }
      } else if ($function == 'require') {
        if ($type == 'dir') {
          return false;
        } else {
          if (!$this->file('exists', $file)) {
            return false;
          } else {
            require_once(__DIR__ . '/' . $file);
            return true;
          }
        }
      } else if ($function == 'make') {
        if ($type == 'dir') {
          if (!$this->file('exists', $dir)) {
            mkdir(__DIR__ . '/' . $dir, 0774);
          } else {
            return false;
          }
        } else {
          if (!$this->file('exists', $file)) {
            if (!$this->file('exists', $dir)) {
              $this->file('make', $dir);
            }
            chmod(__DIR__ . '/' . $dir, 755);
            $file = fopen(__DIR__ . '/' . $file, "wb");
            fclose($file);
            return true;
          } else {
            return false;
          }
        }
      } else if ($function == 'setcontent') {
        if ($type == 'dir') {
          return false;
        } else {
          if (!$this->file('exists', $file)) {
            return false;
          } else {
            if (!$content) {
              return false;
            } else {
              $this->file('empty', $file);
              $this->file('addcontent', $file, $content);
              $file = fopen(__DIR__ . '/' . $file, "wb");
              fwrite($file, $content);
              fclose($file);
            }
          }
        }
      } else if ($function == 'empty') {
        if ($type == 'dir') {
          return false;
        } else {
          if (!$this->file('exists', $file)) {
            return false;
          } else {
            $file = fopen(__DIR__ . '/' . $file, "r+");
            ftruncate($file, 0);
            fclose($file);
          }
        }
      } else if ($function == 'delete') {
        if ($type == 'dir') {
          if (!$this->file('exists', $dir)) {
            return false;
          } else {
            $files = glob(__DIR__ . '/' . $dir . '*', GLOB_MARK);
            foreach ($files as $f) {
              $l = strlen(__DIR__);
              $path = substr($f, $l);
              echo $path;
              $this->file('delete', $path);
            }
            rmdir(__DIR__ . '/' . $file);
          }
        } else {
          if (!$this->file('exists', $file)) {
            return false;
          } else {
            if (is_writable(__DIR__ . '/' . $file)) {
              unlink(__DIR__ . '/' . $file);
              return true;
            } else {
              return false;
            }
          }
        }
      } else if ($function == 'dl' || $function == 'download') {
        if (!$content) {
          $filename = basename($file);
        } else {
          $filename = $content;
        }

        if (!$this->file('exists', $file)) {
          return false;
        } else {
          header('Content-Description: File Transfer');
          header('Content-Type: ' . mime_content_type(__DIR__ . '/' . $file));
          header('Content-Disposition: attachment; filename="' . $filename . '"');
          header('Expires: 0');
          header('Cache-Control: must-revalidate');
          header('Pragma: public');
          header('Content-Length: ' . filesize( __DIR__ . '/' . $file));
          flush();
          readfile(__DIR__ . '/' . $file);
          return true;
        }
      } else {
        return false;
      }
    }

    public function json($function, $data) {
      if ($function == 'decode') {
        return json_decode($data, true);
      } else if ($function == 'encode') {
        return json_encode($data, JSON_PRETTY_PRINT);
      } else if ($function == 'print') {
        $this->setheader('json');
        print_r($data);
      }
    }

    public function jdb($function, $file, $data = false) {
      $jdbp = $this->config['jdb']['path'];
      $jdbp = (substr($jdbp, -1) !== '/' ? $jdbp . '/' : $jdbp);
      $file = (substr($file, -5) !== '.json' ? $file . '.json' : $file);
      $file = (substr($file, 0, 1) == '/' ? substr($file, 1) : $file);
      if (strpos($file, '/')) {
        $parts = explode('/', $file);
        $dir = implode('/', array_slice($parts, 0, -1)) . '/';
        $filename = array_pop($parts);
      } else {
        $dir = '';
        $filename = $file;
      }

      if ($function == 'exists') {
        if (!$this->file('exists', $jdbp . $file)) {
          return false;
        } else {
          return true;
        }
      } else if ($function == 'open') {
        if (!$this->jdb('exists', $file)) {
          return false;
        } else {
          return $this->json('decode', $this->file('content', $jdbp . $file));
        }
      } else if ($function == 'new') {
        if (!$this->jdb('exists', $file)) {
          return $this->file('make', $jdbp . $file);
        } else {
          return false;
        }
      } else if ($function == 'update') {
        if (!$this->jdb('exists', $file)) {
          return false;
        } else {
          if (!$data) {
            return false;
          } else {
            return $this->file('setcontent', $jdbp . $file, $this->json('encode', $data));
          }
        }
      } else if ($function == 'empty') {
        if (!$this->jdb('exists', $file)) {
          return false;
        } else {
          return $this->file('empty', $jdbp . $file);
        }
      } else if ($function == 'delete') {
        if (!$this->jdb('exists', $file)) {
          return false;
        } else {
          return $this->file('delete', $jdbp . $file);
        }
      } else {
        return false;
      }
    }

    public function setheader($header) {
      if (!isset($this->config['headers'][$header])) {
        header($header);
      } else {
        header($this->config['headers'][$header]);
      }
    }

    public function module($function, $file, $option = false) {
      $mdl = $this->jdb('open', 'sys/modules');
      $mdp = $this->config['modules']['path'];
      $mdp = (substr($mdp, -1) !== '/' ? $mdp . '/' : $mdp);
      $file = (substr($file, 0, 1) == '/' ? substr($file, 1) : $file);
      $file = (substr($file, -1) == '/' ? substr($file, 0, -1) : $file);
      if (strpos($file, '/')) {
        $parts = explode('/', $file);
        if (strpos(end($parts), '.')) {
          $dir = implode('/', array_slice($parts, 0, -1)) . '/';
          $filename = array_pop($parts);
          $type = 'file';
        } else {
          $dir = $file . '/';
          $filename = '';
          $type = 'dir';
        }
      } else {
        if (strpos($file, '.')) {
          $filename = $file;
          $dir = '';
          $type = 'file';
        } else {
          $type = 'dir';
          $filename = '';
          $dir = '';
        }
      }

      if ($function == 'exists') {
        if ($type == 'dir') {
          if (!$option) {
            return $this->module('exists', $file, 'n');
          } else if ($option == 'n') {
            $result = false;
            foreach($mdl as $i) {
              if ($i['name'] == $file) {
                $result = true;
              }
            }
            return $result;
          } else if ($option == 'p') {
            $result = false;
            foreach($mdl as $i) {
              $path = (substr($i['dir'], -1) == '/' ? substr($i['dir'], 0, -1) : $i['dir']);
              if ($path == $file) {
                $result = true;
              }
            }
            return $result;
          } else if ($option == 'd') {
            if (!$this->file('exists', $mdp . $dir)) {
              return false;
            } else {
              return true;
            }
          } else {
            return false;
          }
        } else {
          return false;
        }
      } else if ($function == 'info') {
        if (!$this->module('exists', $file)) {
          return false;
        } else {
          $m = $file;
          $mdli = false;
          foreach($mdl as $i) { if ($i['name'] == $m) { $mdli = $i; }}
          if (!$mdli) {
            return false;
          } else {
            if ($this->file('exists', $mdp . $i['dir'] . '/info.json')) {
              return $this->json('decode', $this->file('content', $mdp . $i['dir'] . '/info.json'));
            } else {
              return false;
            }
          }
        }
      } else if ($function == 'load') {
        if (!$file || $file == 'all' || $file = '*') {
          foreach($mdl as $i) {
            $loaded = $this->file('require', $mdp . $i['dir'] . '/main.php');
            if (!$loaded) {
              echo 'module "' . $i['name'] . '" could not be loaded!';
            }
          }
        } else {
          $m = $this->module('info', $file);
          if (!$m) {
            return false;
          } else {
            return $this->file('require', $mdp . $m['dir'] . '/main.php');
          }
        }
      } else if ($function == 'register') {
        if (!$this->module('exists', $file, 'p')) {
          $i = $mdp . $file . '/info.json';
          if (!$this->file('exists', $i)) {
            return false;
          } else {
            $i = $this->json('decode', $this->file('content', $i));
            if (!isset($i['name']) || !isset($i['dir']) || !isset($i['class'])) {
              return false;
            } else {
              $id = count($mdl) + 1;
              $i['id'] = $id;
              $md['id'] = $id;
              $md['name'] = $i['name'];
              $md['dir'] = $i['dir'];
              $md['class'] = $i['class'];
              $md['enabled'] = true;
              array_push($mdl, $md);
              $this->jdb('update', 'sys/modules', $mdl);
              $this->file('setcontent', $mdp . $file . '/info.json', $this->json('encode', $i));
              $this->module('load', $md['name']);
              $module = new $md['class'];
              if (method_exists($module, 'setup')) {
                $module->setup();
              }
              return true;
            }
          }
        } else {
          return false;
        }
      }
    }
  }
?>
