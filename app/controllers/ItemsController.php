<?php
use Fox\CSRF;
use Fox\Paginator;

class ItemsController extends Controller {

    public function index($item_name = null, $page = 1) {
        if ($this->request->isPost() && CSRF::post()) {
            $item_name = $this->request->getPost("item_name", "string");
            $item_name = str_replace(" ", "+", $item_name);
            $this->redirect("admin/items/$item_name");
            return;
        }

        if ($item_name != null) {
            $items = FlipItems::getItemsByName($item_name);
            $this->set("item_name", $item_name);
        } else {
            $items = FlipItems::getItems();
        }

        $paginator = (new Paginator($items, $page, 15))->paginate();
        $results   = $paginator->getResults();

        $this->set("flip_items", $results);
        $this->set("csrf_token", CSRF::token());
    }

    public function edit($itemId) {
        $item = FlipItems::getItem($itemId);

        if (!$item) {
            $this->setView("errors/show404");
            return;
        }

        if ($this->request->hasQuery("success")) {
            $this->set("success", "Your changes has been saved successfully.");
        }

        if ($this->request->isPost() && CSRF::post()) {
            $name   = $this->request->getPost("item_name", "string");
            $id     = $this->request->getPost("item_id", "int");
            $qty    = $this->request->getPost("quantity", "int");
            $rarity = $this->request->getPost("rarity", "int");

            $updated = FlipItems::update($item['id'], [
                'item_name' => $name,
                'item_id'   => (int) $id,
                'quantity'  => (int) $qty,
                'rarity'    => (int) $rarity
            ]);
            
            if ($updated) {
                $this->redirect("admin/items/edit/".$item['id']."/?success");
                return;
            }

            $this->set("error", "Unable to update item. Sumthin dun broked.");
        }

        $this->set("item", $item);
        $this->set("csrf_token", CSRF::token());
    }

    public function add() {
        if ($this->request->hasQuery("success")) {
            $this->set("success", "Your item was added succesfully.");
        }

        if ($this->request->isPost() && CSRF::post()) {
            $name   = $this->request->getPost("item_name", "string");
            $id     = $this->request->getPost("item_id", "int");
            $qty    = $this->request->getPost("quantity", "int");
            $rarity = $this->request->getPost("rarity", "int");

            if (strlen($name) < 3 || strlen($name) > 30) {
                $this->set("error", "Invalid item name");
            } else if ($id < 0 || $id > 65535) {
                $this->set("error", "invalid item id (Min: 0, Max: 65535)");
            } else if ($quantity < 0) {
                $this->set("error", "Invalid quantity. (Min: 1, Max: 2,147,483,647)");
            } else if ($rarity < 0 || $rarity > 9) {
                $this->set("error", "Invalid rarity. (Min: 0, Max: 9)");
            } else {
                $updated = FlipItems::add([
                    'item_name' => $name,
                    'item_id'   => (int) $id,
                    'quantity'  => (int) $qty,
                    'rarity'    => (int) $rarity
                ]);
                
                if ($updated) {
                    $this->redirect("admin/items/add/?success");
                    return;
                }

                $this->set("error", "Unable to add item. Sumthin dun broked.");
            }
        }

        $this->set("csrf_token", CSRF::token());
    }

    public function delete($itemId) {
        $item = FlipItems::getItem($itemId);

        if (!$item) {
            $this->setView("errors/show404");
            return;
        }

        if ($this->request->isPost() && CSRF::post()) {
            FlipItems::delete($item['id']);
            $this->redirect("admin/items/");
            return;
        }

        $this->set("item", $item);
        $this->set("csrf_token", CSRF::token());
    }

    

    public function requireLogin() {
        return true;
    }

    public function isAdminPanel() {
        return true;
    }

}