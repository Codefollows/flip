<?php
use Fox\CSRF;
use Fox\Paginator;

class AdminController extends Controller {

    public function index($username = null, $page = 1) {
        if ($this->request->isPost() && CSRF::post()) {
            $username = $this->request->getPost("username", "string");
            $username = str_replace(" ", "+", $username);
            $this->redirect("admin/$username");
            return;
        }

        if ($username != null) {
            $rewards  = FlipRewards::getPlayerRewards($username);
            $this->set("username", $username);
        } else {
            $rewards = FlipRewards::getRewards();
        }

        $paginator = (new Paginator($rewards, $page, 6))->paginate();

        $this->set("rewards", $paginator->getResults());
        $this->set("csrf_token", CSRF::token());
    }
    
    public function requireLogin() {
        return true;
    }

    public function isAdminPanel() {
        return true;
    }

}