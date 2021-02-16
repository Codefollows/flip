<?php
class FlipItems extends Model {


    public static function getItem($id) {
        return self::getDb()->select(true)
            ->columns(['*'])
            ->from("flip_items")
            ->where(['id = :id'])
            ->bind([
                'id' => $id
            ])->execute();
    }

    public static function getItems() {
        return self::getDb()->select()
            ->columns(['*'])
            ->from("flip_items")
            ->order("id DESC")
            ->execute();
    }

    public static function getItemsByName($name) {
        return self::getDb()->select()
            ->columns(['*'])
            ->from("flip_items")
            ->where(['item_name LIKE :name'])
            ->order("id DESC")
            ->bind([
                ':name' => '%'.$name.'%'
            ])->execute();
    }

    public static function getItemsByRarity($rarity) {
        return self::getDb()->select()
            ->columns(['*'])
            ->from("flip_items")
            ->where(['rarity = :rarity'])
            ->bind([
                ':rarity' => $rarity
            ])->execute();
    }

    public static function add($data) {
        return self::getDb()->insert()
            ->into("flip_items")
            ->columns(array_keys($data))
            ->values([array_values($data)])
            ->execute();
    }

    public static function delete($id) {
        return self::getDb()->delete()
            ->from("flip_items")
            ->where(['id = :id'])
            ->bind([
                ':id' => $id
            ])->execute();
    }

    /**
     * Update token and expire date. 
     */
    public static function update($id, $data) {
        return self::getDb()->update()
            ->table("flip_items")
            ->columns($data)
            ->where(['id = :id'])
            ->bind([
                ':id' => $id
            ])->execute();
    }

}