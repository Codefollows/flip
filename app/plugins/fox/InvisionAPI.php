<?php
/**
 * Class InvisionMemberAPI
 * @author King Fox & atomicint
 */
class InvisionAPI {

    private static $instance;

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new InvisionAPI();
        }
        return self::$instance;
    }

    private $settings;
    private $database;
    private $session;

    public function __construct() {
        $this->refreshSession();

        $this->session  = \IPS\Session::i();
        $this->database = \IPS\Db::i();
        $this->settings = \IPS\Settings::i();
    }

    /**
     * Refreshes our IPS session, if we ever had one.
     * Required if for some reason our session has timed out and we have yet to revisit the suite.
     */
    public function refreshSession() {
        $this->requireIPS();
        \IPS\Session\Front::i();
    }

    public function isGuest($member) {
        return $member->member_group_id == $this->settings->guest_group;
    }

    /**
     * Returns the current logged in user
     * @return null
     */
    public function getCachedMember() {
        $this->refreshSession();
        $member = \IPS\Member::loggedIn();

        if ($this->isGuest($member)) {
            return null;
        }

        return new InvisionMember($member);
    }

    /**
     * Finds a member by username
     * @param $username
     * @return InvisionMember|null
     */
    public function loadMember($username) {
        $member = \IPS\Member::load($username, 'name');

        if ($this->isGuest($member)) {
            return null;
        }

        return new InvisionMember($member);
    }

    /**
     * @param int $member_id
     * @return InvisionMember|null
     */
    public function loadMemberById($member_id) {
        try {
            $member = \IPS\Member::load($member_id, 'member_id');

            if ($this->isGuest($member)) {
                return null;
            }

            return new InvisionMember($member);
        } catch (Exception $e) {
            return null;
        }
    }

    public function login($username, $password, $rememberMe = false) {
        $member = $this->loadMember($username);
        if ($member == null) {
            return null;
        }

        if (!$this->verifyPassword($member->getRaw(), $password)) {
            return false;
        }

        $this->setSession($member->getRaw(), $rememberMe);
        return true;
    }

    public function logout() {
        $member = $this->getCachedMember();

        if ($member == null) {
            return; // We are already logged out
        }

        session_destroy();

        \IPS\Request::i()->clearLoginCookies();
        $member->getRaw()->memberSync('onLogout', array(\IPS\Http\Url::internal('')));
    }

    /**
     * Sets the user session after use has been verified.
     * @param $member
     * @param $rememberMe
     */
    public function setSession($member, $rememberMe) {
        $this->session->setMember($member);

        $device = \IPS\Member\Device::loadOrCreate($member);

        $member->last_visit = $member->last_activity;
        $member->save();

        $device->anonymous = false;
        $device->updateAfterAuthentication($rememberMe, null);

        $member->memberSync('onLogin');
        $member->profileSync();
    }

    /**
     * Finds and returns the latest topics in the specified board.
     * @param $boards array The board to find topics in
     * @param int $maximum The maximum amount of topics to find (default 100)
     * @return array Found topics, empty array if criteria is not matched
     */
    public function findLatestTopics($boards, $maximum = 100) {

        $query = "(forum_id =".implode(' OR forum_id = ', $boards).")";

        $select = $this->database->select("*",
            'forums_topics',
            "approved = 1 AND $query",
            "start_date DESC",
            array(0, $maximum)
        );

        $topics = array();
        foreach ($select as $topic) {
            $topics[] = $topic;
        }
        return $topics;
    }

    /**
     * Finds and returns the latest posts in the specified topic.
     * @param $postId int The post id
     * @return array Found posts, empty array if criteria is not matched
     */
    public function findPost($postId) {
        $select = $this->database->select("*",
            'forums_posts',
            array("pid = ?", $postId),
            "post_date ASC"
        );
        return $select->first();
    }

    /**
     * Finds and returns the latest posts in the specified topic.
     * @param $topic int The topic to find posts in
     * @param int $maximum The maximum amount of posts to find (default 100)
     * @return array Found posts, empty array if criteria is not matched
     */
    public function findPosts($topic, $maximum = 100) {
        $select = $this->database->select("*",
            'forums_posts',
            array("topic_id = ?", $topic),
            "post_date ASC",
            array(0, $maximum)
        );

        $posts = array();
        foreach($select as $post) {
            $posts[] = $post;
        }
        return $posts;
    }

    /**
     * Verifies that the password entered is correct
     * @param $member
     * @param $password
     * @return bool
     */
    public function verifyPassword($member, $password) {
        return password_verify($password, $member->members_pass_hash) === true;
    }

    /**
     * Finds multi-factor authentication information of the specified type for the specified member.
     * @param $member \IPS\Member The member.
     * @param $type String The type. [google, authy, etc...]
     * @return String The MFA information, if it exists, otherwise null.
     */
    public function findMfa($member, $type) {
        $mfaDetails = $member->get_mfa_details();

        if (count($mfaDetails) == 0) {
            return null;
        }

        $mfaToken = $mfaDetails[$type];

        if (isset($mfaToken)) {
            return $mfaToken;
        }

        return null;
    }

    /**
     * Gets the amount of flips for a given member id.
     * @param $member_id
     * @return array
     */
    public function getFlips() {
        $member = $this->getCachedMember();

        if (!$member) {
            return 0;
        }

        $select = $this->database->select("field_2",
            'core_pfields_content',
            array("member_id = ?", $member->getId())
        );
        return $select->first();
    }

    /**
     * Updates the amount of credits a user has.
     * @param $member_id
     * @param $amount
     * @return mixed
     */
    public function updateFlips($amount) {
        $member = $this->getCachedMember();

        if (!$member) {
            return false;
        }

        $this->database->update("core_pfields_content",
            "field_2 = $amount",
            "member_id = {$member->getId()}"
        );
        return true;
    }
    
    /**
     * Includes the required file from the forum
     */
    private function requireIPS() {
        require_once FORUM_PATH . 'init.php';
    }

}
