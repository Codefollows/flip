<?php
use Fox\CSRF;

class LoginController extends Controller {

    public function index() {
        if ($this->getMember()) {
            return $this->redirect("");
        }
        
        if ($this->request->isPost()) {
            $username = $this->request->getPost("username", "string");
            $password = $this->request->getPost("password");

            $login = $this->getBridge()->login($username, $password, true);

            if (!$login) {
                $this->set("error", "Invalid username or password.");
            } else {
                return $this->redirect("");
            }
        }
        
        $this->set("csrf_token", CSRF::token());
    }

}