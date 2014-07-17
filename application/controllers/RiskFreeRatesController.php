<?php
class RiskFreeRatesController extends Zend_Controller_Action{
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
	
	public function indexAction(){
		$this->__checkAuth();
		$db = Zend_Registry::get("main_db");
		$this->view->risk_free_rates = $db->fetchAll($db->select()->from("risk_free_rates"));
		$this->view->headTitle("Capital Protection - Risk Free Rates");
		$this->view->headScript()->appendFile( "/public/js/risk-free-rates/index.js", $type = 'text/javascript' );
		$this->_helper->layout->setLayout("sb_admin");
	}
	
	/**
	 * Save action via ajax web service call
	 * 
	 * Web service API for saving a risk free rate. URL is /risk-free-rates/save/. Method is POST
	 * 
	 * @param string year The year of the libor
	 * @param string month The month of the libor
	 * 
	 */
	public function saveAction(){
		$this->__checkAuth();
		$this->_helper->layout->setLayout("empty");
		$db = Zend_Registry::get("main_db");
		$data = array();
		Zend_Loader::loadClass("CreateRiskFreeRate", array(FORMS_PATH));
		$form = new CreateRiskFreeRate();
		$this->_helper->layout->setLayout("empty");
		if ($form->isValid($_POST)){
			$data = $form->getValues($_POST);
			Zend_Loader::loadClass("RiskFreeRate", array(MODELS_PATH));
			$riskFreeRateTable = new RiskFreeRate();
			$foundRiskFreeRate = $riskFreeRateTable->fetchRow($riskFreeRateTable->select()->where("year = ?", $data["year"])->where("month = ?", $data["month"]));
			if ($foundRiskFreeRate){
				$riskFreeRateTable->update($data, $riskFreeRateTable->getAdapter()->quoteInto("id = ?", $foundRiskFreeRate->id));
			}else{
				$riskFreeRateTable->insert($data);
			}
		}
	}
}
