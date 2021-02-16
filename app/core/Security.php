<?php
use Fox\Cookies;
use Fox\Request;
use Router\Route;
use Router\Router;

class Security {

    private static $instance;

    /**
     * @param $controller
     * @return Security
     */
    public static function getInstance($controller) {
        if (!self::$instance) {
            self::$instance = new Security($controller);
        }
        return self::$instance;
    }

    private $is_root;
    private $user;
    private $controller;
    private $cookies;
    private $router;
    private $request;
    private $bridge;

    /**
     * Security constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller) {
        $this->controller = $controller;
        $this->cookies    = $controller->getCookies();
        $this->router     = $controller->getRouter();
        $this->request    = $controller->getRequest();
        $this->bridge     = $controller->getBridge();
    }

    /**
     * Verifies if a user has access to certain pages.
     * @return bool
     */
    public function checkAccess() {
        $controller = $this->router->getController();
        $action     = $this->router->getMethod();
        $member     = $this->bridge->getCachedMember();

        if ($this->controller->requireLogin()) {
            if ($member == null) {
                if ($action != "login") { 
            		$this->controller->redirect("https://sohanscape.com/community/index.php/login", false);
            		return false; // If we aren't logged in, we need to redirect to login
                }
            }

            if ($this->controller->isAdminPanel()) {
                if (!$member->isAdmin() && !$member->isOwner() && !$member->isDeveloper()) {
                    $this->controller->redirect("");
                    return false;
                }
            }

            $this->controller->setMember($member);
            $this->controller->set("member", $member);
        }
        return true;
    }

    public function getUser() {
        return $this->user;
    }

    public function isRoot() {
        return $this->is_root;
    }
}