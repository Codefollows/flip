<?php
use Fox\CSRF;

class FlipController extends Controller {

    const LOW = 0;
    const LOW_MEDIUM = 1;
    const LOW_HIGH = 2;
    const MEDIUM_LOW = 3;
    const MEDIUM = 4;
    const MEDIUM_HIGH = 5;
    const HIGH_LOW = 6;
    const HIGH = 7;
    const HIGH_HIGH = 8;
    const EXTREME = 9;

    private static $disabled = false;

    public function index() {
        if (self::$disabled) {
            return [
                'token'   => CSRF::token(),
                'success' => false, 
                'message' => 'Temporarily disabled for maintenence. Check back later.'
            ];
        }

        if (!$this->request->isPost() || !$this->getMember()) {
            return [
                'token'   => CSRF::token(),
                'success' => false, 
                'message' => 'This page can not be accessed directly.'
            ];
        }

        // validates if the post request was legitimate
        if (!CSRF::post()) {
            return [
                'success' => false, 
                'message' => 'CSRF Token failed. Refresh the page and try again. If this persists contact the site owner.'
            ];
        }

        // check for page refresh spam. 
        if ($this->cookies->has("last_flip")) {
            $last_flip  = $this->cookies->get("last_flip");
            $difference = time() - $last_flip;
            
            // use the card limit here cause it takes 1 second to flip each card.
            if ($difference < CARD_LIMIT) {
                return [
                    'success' => false, 
                    'message' => 'Maybe try not spamming. Kthx.',
                    'token'   => CSRF::token()
                ];
            }
        }

        // get users flip count, and type from post request
        $avail = $this->getBridge()->getFlips();
        $type  = $this->request->getPost("type", "string");

        $cost  = $type == "regular" ? REG_COST : ENH_COST;

        if ($type != "regular" && $type != "enhanced") {
            return [
                'success' => false, 
                'message' => 'Invalid type. What are you doing?'
            ];
        }

        // if we don't have enough flips then cancel.
        if ($avail < $cost) {
            return [
                'success' => false, 
                'message' => 'You do not have enough flips for this.',
                'token'   => CSRF::token()
            ];
        }

        // base array
        $items = [
            'cards' => [],
        ];

        // roll for a set amount of cards defined by CARD_LIMIT
        for ($i = 0; $i < CARD_LIMIT; $i++) {
            $rarity = $this->getRarity($type);
            $cards  = FlipItems::getItemsByRarity($rarity);
            $items['cards'][] = $cards[mt_rand(0, count($cards) - 1)];
        }

        // save the reward to the DB for claiming in-game.
        $saved = FlipRewards::saveReward([
            'user_id'    => $this->getMember()->getId(),
            'username'   => $this->getMember()->getName(),
            'items'      => json_encode($items),
            'date_added' => time()
        ]);

        // if the reward was saved, then update the users flips. if this fails, welp they get
        // free flips, so just make sure your shit is right lol.
        if ($saved) {
            $avail -= $cost;
            $this->getBridge()->updateFlips($avail);
            $items['flips'] = $avail;
        } else {
            return [
                'success' => false,
                'message' => 'Unable to save rewards. Contact an admin if this persists.'
            ];
        }
        
        // set the time of the flip to prevent page refresh spam.
        $this->cookies->set("last_flip", time());

        // return items to be displayed and a new csrf token.
        return [
            'token'   => CSRF::token(),
            'success' => true,
            'message' => $items
        ];
    }

    /**
     * Gets a psuedo-random rarity that adjusts based on the type of flip.
     * Enhanced flips will have a reduced second number to increase chances of rarer items.
     */
    private function getRarity($type = 'regular') {
        if (mt_rand(0, $type == "regular" ? 800 : 500) == 1) {
            return self::EXTREME;
        } else if (mt_rand(0, $type == "regular" ? 300 : 180) == 1) {
            return self::HIGH_HIGH;
        } else if (mt_rand(0, $type == "regular" ? 170 : 120) == 1) {
            return self::HIGH;
        } else if (mt_rand(0, $type == "regular" ? 135 : 75) == 1) {
            return self::HIGH_LOW;
        } else if (mt_rand(0, $type == "regular" ? 72 : 35) == 1) {
            return self::MEDIUM_HIGH;
        } else if (mt_rand(0, $type == "regular" ? 46 : 20) == 1) {
            return self::MEDIUM;
        } else if (mt_rand(0, $type == "regular" ? 22 : 10) == 1) {
            return self::MEDIUM_LOW;
        } else if (mt_rand(0, $type == "regular" ? 10 : 5) == 1) {
            return self::LOW_HIGH;
        } else if (mt_rand(0, $type == "regular" ? 4 : 2) == 1) {
            return self::LOW_MEDIUM;
        }
        return self::LOW;
    }

    /**
     * is called before the action is. Disables the view and sets the output as JSON. 
     */
    public function beforeExecute() {
        parent::beforeExecute();
        $this->disableView(true);
    }

    /**
     * This page requires login, so this returns true
     */
    public function requireLogin(){
        return true;
    }

}