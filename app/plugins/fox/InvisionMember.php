<?php

class InvisionMember {

    public static $mods    = [ 6, 8, 10, 12, 38, 41, 40 ];
    public static $admins  = [ 9, 15, 34 ];
    public static $devs    = [ 7, 36 ];
    public static $owners  = [ 4 ];
    public static $web_dev = [ 43 ];

    public $raw;
    public $id;
    public $name;
    public $password;
    public $email;
    public $joined;
    public $mfaKey;
    public $group_id;
    public $groups;
    public $seo_name;
    public $avatar;

    public function __construct($member) {
        $this->raw      = $member;
        $this->id       = $member->member_id;
        $this->name     = $member->name;
        $this->joined   = $member->joined;
        $this->password = $member->members_pass_hash;
        $this->email    = $member->email;
        $this->group_id = $member->member_group_id;
        $this->groups   = $member->mgroup_others;
        $this->seo_name = $member->members_seo_name;
        $this->avatar   = $member->pp_main_photo;
    }

    /**
     * @return mixed the user's id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed the users name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed the users hashed passwoed
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @return mixed the users email address
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @return mixed the users primary group id
     */
    public function getGroupId() {
        return $this->group_id;
    }

    /**
     * Returns true if user is in an owner group
     * @return bool
     */
    public function isOwner() {
        return  in_array($this->group_id, self::$owners)
            || $this->isInSecondary(self::$owners);
    }

    /**
     * Returns true if user is in a developer group
     * @return bool
     */
    public function isDeveloper() {
        return in_array($this->group_id, self::$devs)
            || $this->isInSecondary(self::$devs);
    }

    /**
     * Returns true if user is in a developer group
     * @return bool
     */
    public function isWebDev() {
        return in_array($this->group_id, self::$web_dev)
            || $this->isInSecondary(self::$web_dev);
    }

    /**
     * Returns true if user is in an admin group
     * @return bool
     */
    public function isAdmin() {
        return $this->isOwner() || $this->isDeveloper()
            || in_array($this->group_id, self::$admins)
            || $this->isInSecondary(self::$admins);
    }

    /**
     * Returns true if user is in a moderator group
     * @return bool
     */
    public function isModerator() {
        return $this->isAdmin() || in_array($this->group_id, self::$mods)
            || $this->isInSecondary(self::$mods);
    }

    /**
     * Returnns true if user is in any of the staff groups.
     * @return bool
     */
    public function isStaff() {
        return $this->isOwner()
            || $this->isDeveloper()
            || $this->isWebDev()
            || $this->isAdmin()
            || $this->isModerator();
    }

    /**
     * Checks if the user has a specific rank in secondary groups
     * @param $groupIds
     * @return bool
     */
    public function isInSecondary($groupIds) {
        $otherGroups = $this->getOtherGroups();
        foreach ($otherGroups as $group) {
            if (in_array($group, $groupIds)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if user is in a members group
     * @return bool
     */
    public function isMember() {
        if ($this->isStaff()) {
            return false;
        }
        return true;
    }

    public function isAuthor() {
        return $this->isStaff();
    }

    /**
     * Returns the users secondary group ids
     * @return mixed
     */
    public function getOtherGroups() {
        return explode(",", $this->groups);
    }

    /**
     * Returns the raw IPS member object.
     * @return mixed
     */
    public function getRaw() {
        return $this->raw;
    }

    /**
     * Returns te SEO friendly username
     * @return mixed
     */
    public function getSeoName() {
        return $this->seo_name;
    }

    /**
     * Returns the users avatar url.
     * @return mixed
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * Gets the profile url of the user, with seo support
     * @return string
     */
    public function getAvatarUrl() {
        return FORUM_URL."/uploads/{$this->getAvatar()}";
    }

    /**
     * Returns the users join date.
     * @return mixed
     */
    public function getJoined() {
        return $this->joined;
    }

    /**
     * Gets the profile url of the user, with seo support
     * @return string
     */
    public function getProfileUrl() {
        if (ENABLE_SEO) {
            return FORUM_URL."/profile/$this->id-$this->seo_name/";
        }
        return FORUM_URL."/index.php?/profile/$this->id-$this->seo_name/";
    }

    public function setGroupId($id) {
        $this->raw->member_group_id = $id;
        $this->raw->save();
    }

    public function updateOtherGroups($groups) {
        $this->raw->mgroup_others = $groups;
        $this->raw->save();
    }
}