<?php
class User{
	private $_db,
				$_data,
				$_sessionName,
				$_cookieName,
				$_isLoggedIn;		
				
	
	public function __construct($user = null){
		$this->_db = DB::getInstance();
		$this->_sessionName = Config::get('session/session_name');
		$this->_cookieName = Config::get('remember/cookie_name');
		
		if(!$user){
			if(Session::exists($this->_sessionName)){
				$user = Session::get($this->_sessionName);
				
				if($this->find($user)){
					$this->_isLoggedIn = true;
				}else{
					// process logout
				}
			}
		} else {
			$this->find($user);
		}
	}
	
	public function create($fields = array()){
		if(!$this->_db->insert('users', $fields)){
			throw new Exception('There was a problem creating an account');
			
		}
	}
	
	public function register($fields = array()){
		if(!$this->_db->insert('register', $fields)){
			throw new Exception('There was a problem creating an account');
		}
	}
	
	public function find($user = null){
		if($user){
			
			$field = (is_numeric($user)) ? 'id' : 'username';
			
			if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
				$field = 'email';
			}
			
			$data = $this->_db->get('users', array($field, '=', $user));
			
			if($data->count()){
				$this->_data = $data->first();
				return true;
			}
		}
		return false;
	}
	
	public function login($identifier = null, $password = null, $remember = false){
		
		
		if(!$identifier&& !$password && $this->exists()){
			Session::put($this->_sessionName, $this->data()->id);
			
		}else{
			$user = $this->find($identifier);
		
				
			if($user){
			
				if($this->data()->password === Hash::make($password, $this->data()->salt)){
					Session::put($this->_sessionName, $this->data()->id);
					if($remember){
						$hash  = Hash::unique();
						$hashCheck = $this->_db->get('users_session', array('user_id', '=', $this->data()->id));
						
						if(!$hashCheck->count()){
							$this->_db->insert('users_session', array(
								'user_id' => $this->data()->id,
								'hash' => $hash
							));
						} else {
							$hash = $hashCheck->first()->hash;
						}
						
						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
						
					}
					return true;
				}
			}
		}
		
		return false;
	} 
	
	public function update($fields = array(), $id = null){	

		if(!$id && $this->isLoggedIn()){
				$id = $this->data()->id;
		}
		
		if(!$this->_db->update('users', $id, $fields)){
			throw new Exception('There was a problem updating your information');
		}
		
	}
	
	public function hasPermission($key){
		
		$group = $this->_db->get('groups', array('id', '=', $this->data()->group));
		
		if($group->count()){
		
			$permissions = json_decode($group->first()->permissions,true);
			if ($permissions[$key] == true){
				return true;
			}
		
		} 
		return false;
		
	}		
	
	public function exists(){
		return (!empty($this->_data)) ? true : false;
	}
	
	public function data(){
		return $this->_data;
	}
	
	public function isLoggedIn(){
		return $this->_isLoggedIn;
	}
	
	public function logout(){
	
		$this->_db->delete('users_session', array('user_id', '=', $this->data()->id));
		
		Session::delete($this->_sessionName);
		Cookie::delete($this->_cookieName);
	}
	
	
	public function addArticle($id = null){
		
		$this->_db->insert('users_articles', array(
			'user_id' => $this->data()->id,
			'article_id' => $id
		));
		
		return true;
			
	}
	
	public function hasArticle($id = null){
		$articles = $this->_db->query("SELECT  * FROM users_articles WHERE user_id = ? AND article_id = ?", array(
			$this->data()->id,
			$id
		));		
		
		if (!$articles->count()){
			return false;
		}else{
			return true;
		}
	}
}