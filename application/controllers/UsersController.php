<?php

/**
 * Controller for user related action
 * 
 * @author Chris Scrivo
 * 
 */
class UsersController extends Zend_Controller_Action
{
	/**
	 * Render view for signing in. Also responsible for processing log in request URL is /users/signin/
	 * 
	 */
	public function signinAction(){
		$this->_helper->layout->setLayout("html5_plain");
		Zend_Loader::loadClass("UserLogin", array(FORMS_PATH));
		Zend_Loader::loadClass("User", array(MODELS_PATH));	
		$session = new Zend_Session_Namespace("capitalp");	
		$loginForm = new UserLogin();
		
		//if there is a valid POST assume it is a login
		if ($loginForm->isValid($_POST)){
			
			//authenticate process
			 $adapter = new Zend_Auth_Adapter_DbTable(
                Zend_Registry::get("main_db"),
                'users',
                'username',
                'password',
                'MD5(?)'
                );
 
            $adapter->setIdentity($loginForm->getValue('username'));
            $adapter->setCredential($loginForm->getValue('password'));
 
            $auth   = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
 			
			
			// if logged in
            if ($result->isValid()) {
            	
				
				$identity = $auth->getIdentity();
				
				$userTable = new User();
				$user = $userTable->fetchRow($userTable->select()->where("username = ?", $identity));
				$session->manager_id = $user["id"];		
				
				$this->_helper->FlashMessenger('Successful Login');
                $this->_redirect('/portfolio/');
                return;
            }else{
            	$this->_redirect('/users/login?error=2');
            }
		}else{
			$this->_redirect('/users/login?error=1');
            return;
		}
		
	}
	
	/**
	 * Action for logout the user 
	 */
	public function logoutAction(){
		$session = new Zend_Session_Namespace("capitalp");	
		$session->__unset("manager_id");
		$this->_redirect('/users/login/');
	}
	
	/**
	 * Login the user into the system
	 */
	public function loginAction(){
		
		Zend_Loader::loadClass("UserLogin", array(FORMS_PATH));
		$error = $this->getRequest()->getQuery("error");
		$session = new Zend_Session_Namespace("capitalp");	
		if ($session->manager_id){
			header("Location:/portfolio/");
		}
		
		$form = new UserLogin();
		$this->view->form = $form;
		
		if ($error){
			if ($error==1){
				$this->view->error = "Invalid Request. Please try again";
			}else if ($error==2){
				$this->view->error = "Invalid Username/Password. Please try again";
			}
		}else{
			$this->view->error = "";
		}
		$this->view->headTitle("Login");
		$this->view->headScript()->prependFile( "http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.js", $type = 'text/javascript' );		
		$this->view->headScript()->prependFile( "/public/js/users/login.js", $type = 'text/javascript' );
		$this->_helper->layout->setLayout("html5_plain");
	}
}
	