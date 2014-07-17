<?php

class LiborController extends Zend_Controller_Action{
	/**
	 * Check authentication
	 */
	private function __checkAuth(){
		Zend_Loader::loadClass("CheckAuth", array(COMPONENTS_PATH));
		CheckAuth::__checkAuth();
		$session = new Zend_Session_Namespace("capitalp");
		Zend_Loader::loadClass("User", array(MODELS_PATH));
		//get authenticated user
		$userTable = new User();
		$manager = $userTable->find($session->manager_id);
		$manager = $manager->toArray();
		$manager = $manager[0];
		$this->view->manager = $manager;
	}
	
	/**
	 * Index action
	 *
	 * Landing page for libor management. URL is /libor/
	 * 
	 */
	public function indexAction(){
		$this->__checkAuth();
		$db = Zend_Registry::get("main_db");
		
		$this->view->three_month_libor = $db->fetchAll($db->select()->from("libor")->where("type = ?", "3 Month LIBOR"));
		$this->view->six_month_libor = $db->fetchAll($db->select()->from("libor")->where("type = ?", "6 Month LIBOR"));
		$this->view->headTitle("Capital Protection - Libor");
		$this->view->headScript()->appendFile( "/public/js/libor/index.js", $type = 'text/javascript' );
		$this->_helper->layout->setLayout("sb_admin");
				
	}
	
	/**
	 * Save action via ajax web service call
	 * 
	 * Web service API for saving a libor. URL is /libor/save/. Method is POST
	 * 
	 * @param string year The year of the libor
	 * @param string month The month of the libor
	 * @param string type 3 month or 6 month libor
	 * 
	 */
	public function saveAction(){
		$db = Zend_Registry::get("main_db");
		$this->__checkAuth();
		$data = array();
		Zend_Loader::loadClass("CreateLibor", array(FORMS_PATH));
		$form = new CreateLibor();
		$this->_helper->layout->setLayout("empty");
		if ($form->isValid($_POST)){
			$data = $form->getValues($_POST);
			Zend_Loader::loadClass("Libor", array(MODELS_PATH));
			$liborTable = new Libor();
			$foundLibor = $liborTable->fetchRow($liborTable->select()->where("year = ?", $data["year"])->where("month = ?", $data["month"])->where("type = ?", $data["type"]));
			if ($foundLibor){
				$liborTable->update($data, $liborTable->getAdapter()->quoteInto("id = ?", $foundLibor->id));
			}else{
				$liborTable->insert($data);
			}
		}
	}
	
	
}
