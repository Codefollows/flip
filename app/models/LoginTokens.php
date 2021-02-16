<?php
class LoginTokens extends Model {

    /**
     * Grabs a login from db based on IP and Token. If not
     * found should probably boot the user.
     */
    public static function getLogin($ip, $token) {
        return self::getDb()->select(true)
            ->columns(['*'])
            ->from("login_tokens")
            ->where(['ip_address = :ip', 'token = :token'])
            ->bind([
                ':ip'    => $ip,
                ':token' => $token
            ])->execute();
    }

    /**
     * Stores login info.
     */
    public static function storeLogin($ip, $token, $expires) {
        return self::getDb()->insert()
            ->into('login_tokens')
            ->columns(['ip_address', 'token', 'expires'])
            ->values([[$ip, $token, $expires]])
            ->execute();
    }

    /**
     * Update token and expire date. 
     */
    public static function updateLogin($ip, $token, $expires) {
        return self::getDb()->update()
            ->table("login_tokens")
            ->columns([
                'token'      => $token,
                'expires'    => $expires
            ])
            ->where(['ip_address = :ip'])
            ->bind([
                ':ip' => $ip
            ])->execute();
    }


}