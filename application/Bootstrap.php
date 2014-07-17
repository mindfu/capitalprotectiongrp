<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAutoload(){
		define("TEST", true);
		define("FORMS_PATH", APPLICATION_PATH.DIRECTORY_SEPARATOR."forms");
		define("MODELS_PATH", APPLICATION_PATH.DIRECTORY_SEPARATOR."models");
		define("COMPONENTS_PATH", APPLICATION_PATH.DIRECTORY_SEPARATOR."components");
		require_once COMPONENTS_PATH."/CheckAuth.php";
		require_once COMPONENTS_PATH."/FundCalculator.php";
		require_once COMPONENTS_PATH."/PortfolioCalculator.php";
		
		
		$connectionParameters = array("host" => "localhost",
								"username" => "root",
								"password" => "slasherpix01",
								"dbname" => "capitalp");
		//load the database adapter
		$db = Zend_Db::factory("PDO_MYSQL", $connectionParameters);
		Zend_Registry::set("main_db", $db);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);
		Zend_Layout::startMvc();
    		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayoutPath(APPLICATION_PATH.DIRECTORY_SEPARATOR."views/layouts");
		
	}
	
	private function loadLibraries(){
		$views = APPLICATION_PATH.DIRECTORY_SEPARATOR."views";
		$models = APPLICATION_PATH.DIRECTORY_SEPARATOR."models";
		Zend_Loader::loadClass("Converter", array($models));
	}
	
	
	private function defineACL(){
		$acl = new Zend_Acl();
		$acl->addRole(new Zend_Acl_Role("admin"))
			->addRole(new Zend_Acl_Role("agent"))
			->addRole(new Zend_Acl_Role("member"))
			->addRole(new Zend_Acl_Role("owner"));
	
	}
	
	private function _initSetupBaseUrl(){
		$this->bootstrap('frontcontroller');
		$ctrl = Zend_Controller_Front::getInstance();
		$router = $ctrl->getRouter();
		$route = new Zend_Controller_Router_Route_Static("about/*",
					array("controller"=>"index", "action"=>"about"));
		$router->addRoute("about", $route);
	}
	
	private function loadNewClasses(){
		$models = APPLICATION_PATH.DIRECTORY_SEPARATOR."models";
		$forms = APPLICATION_PATH.DIRECTORY_SEPARATOR."forms";
		
	}
	
	protected function _initView(){
		// Initialize view
		$view = new Zend_View();
		$view->addScriptPath(APPLICATION_PATH . '/views/scripts/');
		// Add it to the ViewRenderer
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
		    'ViewRenderer'
		);
		$viewRenderer->setView($view);
		return $view;
	}
	
	protected function _initUser(){

	}
	
	protected function _initAcl(){
		
	}	

}

function dateDiff($startDate, $endDate){
	$startArry = date_parse($startDate);
	$endArry = date_parse($endDate);
	$start_date = gregoriantojd($startArry["month"], $startArry["day"], $startArry["year"]);
	$end_date = gregoriantojd($endArry["month"], $endArry["day"], $endArry["year"]);
	return round(($end_date - $start_date), 0);
}
