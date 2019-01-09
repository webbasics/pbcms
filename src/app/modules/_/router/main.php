<?php
  class Pbcrouter {
    public $url;
    public $controller;
    public $handler;
    public $app;
    public $pages;
    public $skltns;
    public $redirect;
    public $config;
    public $domain;

    public function __construct() {
      $this->app = new App;
      $this->config = $this->app->jdb('open', 'sys/router/config');
      $this->pages = $this->app->jdb('open', 'sys/router/pages');
      $this->skltns = $this->app->jdb('open', 'sys/router/skeletons');
      $this->redirect = (!isset($_POST['app_redirect']) ? false : $_POST['app_redirect']);
      $this->url = (!isset($_GET['app_url']) ? (!$this->exists('defaultUrl') ? 'home/index' : $this->get('defaultUrl')) : $_GET['app_url']);
      $this->url = (substr($this->url, 0, 1) == '/' ? substr($this->url, 1) : $this->url);
      $this->url = (substr($this->url, -1) == '/' ? substr($this->url, 0, -1) : $this->url);
      $this->url = (substr($this->url, 0, 1) == '\\' ? substr($this->url, 1) : $this->url);
      $this->url = (substr($this->url, -1) == '\\' ? substr($this->url, 0, -1) : $this->url);

      if (!in_array($this->app->get('hostname'), $this->app->config['details']['allowedDomains'])) {
        $this->app->redirect($this->app->baseurl . $this->url, array(
          "method" => "post",
          "data" => array (
            "redirect" => $this->redirect,
            "badhost" => $this->app->get('hostname'),
            "oldurl" => $this->url,
            "noticeCode" => "N_PBRTR_INIT_01"
          )
        ));
      } else {
        $this->domain = $this->app->get('hostname');
      }

      $this->controller = $this->urlpart(0);
      $this->handler = $this->urlpart(1);

    }

    protected function urlpart($part = 0) {
      $url = $this->url;
      $url = (strpos($url, '/') ? explode('/', $url)[$part] : ($part == 0 ? $url : false));
      return $url;
    }

    protected function exists($function, $name = false) {
      if ($function == 'controller') {
        $name = (!$name ? $this->controller : $name);
        if (isset($this->pages[$this->domain][$name])) {
          return true;
        } else {
          if (isset($this->pages['global'][$name])) {
            return true;
          } else {
            return false;
          }
        }
      } else if ($function == 'handler') {
        $name = (!$name ? $this->handler : $name);
        if (isset($this->pages[$this->domain][$this->controller][$name])) {
          return true;
        } else {
          if (isset($this->pages['global'][$this->controller][$name])) {
            return true;
          } else {
            return false;
          }
        }
      } else if ($function == 'defaultUrl') {
        if (isset($this->pages[$this->domain]['default'])) {
          return true;
        } else {
          if (isset($this->pages['global']['default'])) {
            return true;
          } else {
            return false;
          }
        }
      } else if ($function == 'defaultHandler') {
        if (isset($this->pages[$this->domain][$this->controller]['default'])) {
          return true;
        } else {
          if (isset($this->pages['global'][$this->controller]['default'])) {
            return true;
          } else {
            return false;
          }
        }
      }
    }

    public function get($type) {
      if ($type == 'controller') {
        return $this->controller;
      } else if ($type == 'handler') {
        return $this->handler;
      } else if ($type == 'defaultUrl') {
        if (isset($this->pages[$this->domain]['default'])) {
          return $this->pages[$this->domain]['default'];
        } else {
          if (isset($this->pages['global']['default'])) {
            return $this->pages['global']['default'];
          } else {
            return false;
          }
        }
      } else if ($type == 'defaultHandler') {
        if (isset($this->pages[$this->domain][$this->controller]['default'])) {
          return $this->pages[$this->domain][$this->controller]['default'];
        } else {
          if (isset($this->pages['global'][$this->controller]['default'])) {
            return $this->pages['global'][$this->controller]['default'];
          } else {
            return false;
          }
        }
      } else if ($type == 'page') {
        $domain = $this->domain;
        if (!$this->exists('controller')) {
          http_response_code('404');
          die;
        }

        if (!$this->exists('handler')) {
          if ($this->exists('defaultHandler')) {
            $defaultHandler = $this->get('defaultHandler');
            $this->set('handler', $defaultHandler);
          } else {
            http_response_code(404);
            die;
          }
        }

        if (!isset($this->pages[$domain][$this->controller][$this->handler])) {
          $domain = 'global';

          if (!isset($this->pages[$domain][$this->controller][$this->handler])) {
            $this->set('controller', $this->config['defaults']['404']['controller']);
            $this->set('handler', $this->config['defaults']['404']['handler']);
          }
        }

        $page = $this->pages[$domain][$this->controller][$this->handler];

        if ($page['maintenance']) {
          $domain = 'global';

          $this->set('controller', $this->config['defaults']['maintenance']['controller']);
          $this->set('handler', $this->config['defaults']['maintenance']['handler']);
        }

        $page = $this->pages[$domain][$this->controller][$this->handler];
        $page['domain'] = $domain;

        return $page;
      } else if ($type == 'params') {
        $url = $this->url;
        $amount = count(explode('/', $url)) - 2;
        if ($amount > 0) {
          for ($i = $amount; $i > 0; $i--) {
            $param[$i] = $this->urlpart($i + 1);
          }
        } else {
          $param = false;
        }
        return $param;
      }
    }

    public function set($type, $input) {
      if ($type == 'controller') {
        $this->controller = $input;
      } else if ($type == 'handler') {
        $this->handler = $input;
      } else if ($type == 'url') {
        $this->url = $input;
      } else if ($type == 'domain') {
        $this->domain = $input;
      }
    }

    public function load($url = false) {
      $this->url = (!$url ? $this->url : $url);
      $page = $this->get('page');
      $param = $this->get('params');
      $redirect = $this->redirect;

      $skltn = false;
      if (!isset($page['type'])) {
        $page['type'] = 'pb_undefined';
      }
      foreach ($this->skltns as $item) {
        if ($page['type'] === $item['Type']) {
          $skltn = $item;
        }
      }

      if (!$skltn) {
        require_once __DIR__ . '/skeletons/undefined.php';
      } else {
        require_once __DIR__ . '/skeletons/' . $skltn['Target'];
      }
    }

    public function setup() {
      $pages = array(
        "default" => "home",
        "home" => array(
          "default" => "index",
          "index" => array(
            "title" => "home",
            "target" => "home.php",
            "css" => "rable.css/home",
            "js" => "rable.js/home",
            "maintenance" => false
          )
        )
      );
      $config = array(
        "defaults" => array(
          "404" => array(
            "controller" => "err",
            "handler" => "404",
            "url" => "err/404"
          ),
          "403" => array(
            "controller" => "err",
            "handler" => "403",
            "url" => "err/403"
          ),
          "maintenance" => array(
            "controller" => "err",
            "handler" => "maintenance",
            "url" => "err/maintenance"
          )
        )
      );
      $navbar = array();
      $footer = array();

      $this->app->jdb('new', 'sys/router/pages');
      $this->app->jdb('new', 'sys/router/config');
      $this->app->jdb('new', 'sys/router/navbar');
      $this->app->jdb('new', 'sys/router/footer');

      $this->app->jdb('update', 'sys/router/pages', $pages);
      $this->app->jdb('update', 'sys/router/config', $config);
      $this->app->jdb('update', 'sys/router/navbar', $navbar);
      $this->app->jdb('update', 'sys/router/footer', $footer);
    }
  }
?>
