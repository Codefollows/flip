<?php
use Fox\CSRF;

class IndexController extends Controller {

    private const rarities = [
        'common'   => ['low'],
        'uncommon' => ['low medium', 'low high', 'medium low'],
        'rare'     => ['medium', 'medium high'],
        'epic'     => ['high low', 'high'],
        'legendary'=> ['high high', 'extreme'],
    ];

    public function index() {
        if ($this->request->hasQuery("logout")) {
            $this->getBridge()->logout();
            $this->redirect("https://sohanscape.com/community/index.php?/login/", false);
            return;
        }

        $this->set("flips", $this->getBridge()->getFlips());
        $this->set("rarities", self::rarities);
        $this->set("csrf_token", CSRF::token());
    }

    public function requireLogin(){
        return true;
    }

}