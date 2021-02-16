<?php

use Router\Router;

class PageRouter extends Router {

    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new PageRouter(web_root);
        }
        return self::$instance;
    }

    private $controller;
    private $method;
    private $params;

    public $route_paths = [];

    public function initRoutes() {

        $this->all('', function() {
            return $this->setRoute('index', 'index');
        });

        $this->all('flip', function() {
            return $this->setRoute('flip', 'index');
        });

        
        /**
         * Item List
         */
        $this->all('admin/items', function() {
            return $this->setRoute('items', 'index', ['item' => null, 'page' => 1]);
        });

        $this->all('admin/items/add', function() {
            return $this->setRoute('items', 'add');
        });

        $this->all('admin/items/delete/([0-9]+)', function($id) {
            return $this->setRoute('items', 'delete', ['itemId' => $id]);
        });

        $this->all('admin/items/edit/([0-9]+)', function($id) {
            return $this->setRoute('items', 'edit', ['itemId' => $id]);
        });

        $this->all('admin/items/([0-9]+)', function($page) {
            return $this->setRoute('items', 'index', ['item' => null, 'page' => $page]);
        });

        $this->all('admin/items/([A-Za-z0-9\- ]+)', function($item) {
            return $this->setRoute('items', 'index', ['item' => $item, 'page' => 1]);
        });

        $this->all('admin/items/([A-Za-z0-9\- ]+)/([0-9]+)', function($item, $page) {
            return $this->setRoute('items', 'index', ['item' => $item, 'page' => 1]);
        });


        /**
         * Admin Dashboard / Flip History
         */
        $this->all('admin', function() {
            return $this->setRoute('admin', 'index');
        });

        $this->all('admin/([0-9]+)', function($page) {
            return $this->setRoute('admin', 'index', ['username' => null, 'page' => $page]);
        });

        $this->all('admin/([A-Za-z0-9\- ]+)', function($username) {
            return $this->setRoute('admin', 'index', ['username' => $username, 'page' => 1]);
        });

        $this->all('admin/([A-Za-z0-9\- ]+)/([0-9]+)', function($username, $page) {
            return $this->setRoute('admin', 'index', ['username' => $username, 'page' => $page]);
        });

    }

    public function setRoute($controller, $method, $params = []) {
        $this->controller = $controller;
        $this->method = $method;
        $this->params = $params;

        return [$controller, $method, $params];
    }

    public function getController($formatted = false) {
        return $formatted ? ucfirst($this->controller).'Controller' : $this->controller;
    }

    public function getViewPath() {
        return $this->getController().'/'.$this->getMethod();
    }

    public function getMethod() {
        return $this->method;
    }

    public function getParams() {
        return $this->params;
    }

    public function isSecure() {
        return
          (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }

    public function getUrl() {
        $baseUrl =  'http'.($this->isSecure() ? 's' : '').'://' . $_SERVER['HTTP_HOST'];
        return $baseUrl.web_root;
    }
}