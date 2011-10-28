<?php

define('USER_LEVEL_BANNED',     10);
define('USER_LEVEL_NONE',       20);
define('USER_LEVEL_USER',       30);
define('USER_LEVEL_ADMIN',      40);
define('USER_LEVEL_OWNER',      50);

class userLevels{
    private $levels = array();

    /**
     * Returns the level of a user
     *
     * @param string $user is the nickname
     */
	public function getLevel($user){
        if(isset($this->levels[$user])){
            return $this->levels[$user];
        }else{
            return USER_LEVEL_NONE;
        }
    }
    
    /**
     * Changes the level of a user
     *
     * @param string $user Is the user's nickname
     * @param integer $level is the new level
     */
    public function setLevel($user, $level){
        $this->levels[$user] = $level;
    }
}

$this->user_levels = new userLevels;
$this->user_levels->setLevel($this->master, USER_LEVEL_OWNER);