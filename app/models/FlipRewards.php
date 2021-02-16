<?php
class FlipRewards extends Model {

    public static function saveReward($data) {
        return self::getDb()->insert()
            ->into("flip_rewards")
            ->columns(array_keys($data))
            ->values([array_values($data)])
            ->execute();
    }

    public static function getRewards() {
        return self::getDb()->select()
            ->columns(['*'])
            ->from("flip_rewards")
            ->execute();
    }

    public static function getPlayerRewards($username) {
        return self::getDb()->select()
            ->columns(['*'])
            ->from("flip_rewards")
            ->where(['username = :username'])
            ->bind([
                ':username' => $username
            ])
            ->execute();
    }
}