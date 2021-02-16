<?php
define('site_title', 'Sohanscape | Card Game');
define('web_root', '/flips/');

/**
 * Database
 */
define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_DATABASE', '');
define('MYSQL_USERNAME', 'username');
define('MYSQL_PASSWORD', 'password');

/**
 * Forum Integration. 
 */
define("FORUM_PATH", "../community/");
define("FORUM_URL", "https://sohanscape.com/community/");

 // the max number of cards. Supports no more than 4. the more you add the more
 // squooshed the cards will become because of flex box. so keep that in mind.
define("CARD_LIMIT", 3);

// the cost of both types of flips. Min 1, max whatever
define("REG_COST", 1);
define("ENH_COST", 3);
