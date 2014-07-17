<?php
/**
 * Authentication object to check if the user is logged in`
 * 
 * @author Chris Scrivo
 */
class CheckAuth{
	public static function __checkAuth(){
		$session = new Zend_Session_Namespace("capitalp");
		Zend_Loader::loadClass("User", array(MODELS_PATH));
		if (!$session->manager_id){
			header("Location:/users/login");
			die;
		}
	}
	
}
