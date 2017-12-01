<?php
session_start();

$GLOBALS['config'] = array(
	'mysql' => array(
		'host' => 'localhost',
		'username' => 'root',
		'password' => '',
		'db' => 'planetdev'
	),
	'remember' => array(
		'cookie_name' => 'hash',
		'cookie_expiry' => 604800
	),
	'session' => array(
		'session_name' => 'user',
		'token_name' => 'token'
	)
);

spl_autoload_register(function($class){
	require_once 'classes/' . $class . '.php';
});

define('BASE_URL', 'http://danijelberkesh.local');
define('FILE_EXTENSION', '.php');

if(Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))){
	$hash = Cookie::get(Config::get('remember/cookie_name'));
	$hashCheck = DB::getInstance()->get('users_session', array('hash', '=', $hash));

	if($hashCheck->count()){
		$user = new User($hashCheck->first()->user_id);
		$user->login();
	}
}

require_once 'functions/timeElapsed.php';
require_once 'functions/slug.php';
