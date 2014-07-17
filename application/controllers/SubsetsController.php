<?php

class SubsetsController extends Zend_Controller_Action
{
	public function indexAction(){
		$this->__checkAuth();
		
		$db = Zend_Registry::get("main_db");
		
		$rows = 50;
		$page = $this->getRequest()->getQuery("page");
		if (!$page){
			$page = 1;
		}
		$query = array();
		foreach($_GET as $key=>$val){
			if ($key=="page"){
				continue;
			}
			$query[] = $key."=".$val;
		}
		$this->view->query = $query;
		$subsets = $db->fetchAll($db->select()->from("subsets", array(new Zend_Db_Expr("SQL_CALC_FOUND_ROWS *")))->limitPage($page, $rows));
		$count = $db->fetchOne("SELECT FOUND_ROWS()");
		
		$this->view->subsets = $subsets;
		$this->view->count = $count;
		$this->view->current_page = $page;
		
		Zend_Loader::loadClass("CreateSubset", array(FORMS_PATH));
		Zend_Loader::loadClass("UpdateSubset", array(FORMS_PATH));

		$this->view->form = new CreateSubset();
		$this->view->update_form = new UpdateSubset();
		
		$this->view->headTitle("Capital Protection - Subsets");
		$this->view->headScript()->appendFile( "/public/js/subsets/index.js", $type = 'text/javascript' );
    	$this->_helper->layout->setLayout("sb_admin");
		
	}
	
	/**
	 * Create a new subset
	 */
	public function createAction(){
		$this->__checkAuth();
		$this->_helper->layout->setLayout("empty");
		Zend_Loader::loadClass("Subset", array(MODELS_PATH));
		Zend_Loader::loadClass("CreateSubset", array(FORMS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		$ratioTable = new LeverageRatio();
		$create_subset = new CreateSubset();
		if ($create_subset->isValid($_POST)){
			$data = $create_subset->getValues($_POST);
			$leverage_ratio_id = $data["leverage_ratio_id"];
			$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $leverage_ratio_id));
			if ($ratio){
				unset($data["leverage_ratio_id"]);
				$data["debt"] = $ratio->debt;
				$data["equity"] = $ratio->equity;
				
				$subsetTable = new Subset();
				$subsetTable->insert($data);			
				$this->view->result = array("success"=>true);
			}else{
				$this->view->result = array("success"=>false);
			}
			
			
				
		}else{
			$this->view->result = array("success"=>false, "errors"=>$form->getErrors());
		}
		
	}
	
	/**
	 * Updates an existing subset
	 */
	public function updateAction(){
		$this->__checkAuth();
		$this->_helper->layout->setLayout("empty");
		Zend_Loader::loadClass("Subset", array(MODELS_PATH));
		Zend_Loader::loadClass("UpdateSubset", array(FORMS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		$ratioTable = new LeverageRatio();
		$update_subset = new UpdateSubset();
		if ($update_subset->isValid($_POST)){
			$data = $update_subset->getValues($_POST);
			
			$id = $data["id"];
			$leverage_ratio_id = $data["leverage_ratio_id"];
			$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $leverage_ratio_id));
			if ($ratio){
				unset($data["leverage_ratio_id"]);
				$data["debt"] = $ratio->debt;
				$data["equity"] = $ratio->equity;
				$subsetTable = new Subset();
				$subsetTable->update($data, $subsetTable->getAdapter()->quoteInto("id = ?", $id));			
				$this->view->result = array("success"=>true);
			}else{
				$this->view->result = array("success"=>false);
			}
		}else{
			$this->view->result = array("success"=>false, "errors"=>$form->getErrors());
		}
		
	}
	
	
	public function getAction(){
		$this->_helper->layout->setLayout("empty");
		$db = Zend_Registry::get("main_db");
		$id = $this->getRequest()->getQuery("id");
		if ($id){
			$result = $db->fetchRow($db->select()->from("subsets")->where("id = ?", $id));
			if ($result){
				$result["number_of_funds_units"] = number_format($result["number_of_funds_units"], 2);
				$result["debt"] = number_format($result["debt"], 2);
				$result["equity"] = number_format($result["equity"], 2);
				$result["fund_weight"] = number_format($result["fund_weight"], 2);
				
				
				$ratio = $db->fetchRow($db->select()->from("leverage_ratios")->where("debt = ?", $result["debt"])->where("equity = ?", $result["equity"]));
				if ($ratio){
					$result["leverage_ratio_id"] = $ratio["id"];
				}else{
					$result["leverage_ratio_id"] = 0;
				}
				
				$this->view->result = array("success"=>true, "result"=>$result);			
			}else{
				
				$this->view->result = array("success"=>false);						
			}
		}else{
			$this->view->result = array("success"=>false);
		}

	}
	
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
}
	