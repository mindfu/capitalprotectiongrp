<?php
/**
 * Controller class for Template related actions
 * 
 * Controller class for CRUD operations related to Templates and TemplateItems.
 * 
 * @author Chris Scrivo
 * 
 */
class TemplatesController extends Zend_Controller_Action{
	
	/**
	 * 
	 * Index action 
	 * 
	 * Landing page for the templates manager. Url is /templates/
	 * 
	 */
	public function indexAction(){
		$this->__checkAuth();
		$db = Zend_Registry::get("main_db");
		Zend_Loader::loadClass("Template", array(MODELS_PATH));
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		
		
		//load all templates with items
		$templateTable = new Template();
		$templates = $templateTable->fetchAll();
		$templates = $templates->toArray();
		
		$templateItemTable = new TemplateItem();
		$ratioTable = new LeverageRatio();
		foreach($templates as $key=>$template){
			
			//collect template items per template
			$template_items = $templateItemTable->fetchAll($templateItemTable->select()->where("template_id = ?", $template["id"]));
			$template_items = $template_items->toArray();
			
			//assign leverage ratio
			foreach($template_items as $key_item=>$template_item){
				$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $template["leverage_ratio_id"]));
				$ratio = $ratio->toArray();
				$template_items[$key_item]["leverage"] = $ratio;
			}
			
			
			$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $template["leverage_ratio_id"]));
			$ratio = $ratio->toArray();
			$templates[$key]["leverage"] = $ratio;
			$templates[$key]["items"] = $template_items;
		}
		
		$this->view->headTitle("Capital Protection - Templates");
		$this->view->templates = $templates;
		$this->_helper->layout->setLayout("sb_admin");
	}
	
	
	/**
	 * 
	 * Setup New Template landing Page
	 * 
	 * Landing page for setting up a new Template. Url is /templates/setup/
	 * 
	 */
	public function setupAction(){
		$this->__checkAuth();
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		$ratioTable = new LeverageRatio();
		$ratios = $ratioTable->fetchAll();
		$this->view->ratios = $ratios;
		$this->view->headTitle("Capital Protection - Setup New Template");
		$this->view->headScript()->prependFile( "/public/js/templates/setup.js", $type = 'text/javascript' );
		$this->_helper->layout->setLayout("sb_admin");
	}
	
	/**
	 * 
	 * Update Template Landing Page
	 * 
	 * Landing page for updating an existing template. Url is /templates/update/
	 * 
	 * @param string id The id of the template. Accessible via $_REQUEST
	 * 
	 */
	public function updateAction(){
		$this->__checkAuth();
		Zend_Loader::loadClass("Template", array(MODELS_PATH));
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		
		$id = $_REQUEST["id"];
		if (!$id){
			header("Location:/templates/");
		}
		
		//load template details
		$templateTable = new Template();
		$template = $templateTable->fetchRow($templateTable->select()->where("id = ?", $id));
		$template = $template->toArray();
		$templateItemTable = new TemplateItem();
		$ratioTable = new LeverageRatio();
		
		//load template items and put ratio
		$template_items = $templateItemTable->fetchAll($templateItemTable->select()->where("template_id = ?", $template["id"]));
		$template_items = $template_items->toArray();
		foreach($template_items as $key_item=>$template_item){
			$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $template_item["leverage_ratio_id"]));
			$ratio = $ratio->toArray();
			$template_items[$key_item]["leverage"] = $ratio;
		}
		$template["items"] = $template_items;
		$this->view->template = $template;
		
		$ratioTable = new LeverageRatio();
		$ratios = $ratioTable->fetchAll();
		$this->view->ratios = $ratios;
		$this->view->headTitle("Capital Protection - Update Template");
		$this->view->headScript()->prependFile( "/public/js/templates/update.js", $type = 'text/javascript' );
		$this->_helper->layout->setLayout("sb_admin");
	}
	
	/**
	 * 
	 * Create Action via Ajax web service call
	 * 
	 * Web service API for creating a template. URL is /templates/create/. Method is POST
	 * 
	 * @param string subset_name The subset name. Accessible via $_POST. Required
	 * @param array number_of_fund_units Number of Fund Units allowable for a subset. Accessible via $_POST. Required
	 * @param array fund_weight Fund Weight of the Subset
	 * @param string selected_leverage_ratio_id The selected Leverage Ratio for the template
	 * 
	 */
	public function createAction(){
		$this->_helper->layout->setLayout("empty");
		$db = Zend_Registry::get("main_db");
		$name = $this->getRequest()->getPost("subset_name");
		//if name is set
		if ($name){
			
			//create new template
			$db->insert("templates", array("name"=>$_REQUEST["subset_name"], "leverage_ratio_id"=>$_REQUEST["selected_leverage_ratio_id"], "date_created"=>date("Y-m-d H:i:s")));
			$template_id = $db->lastInsertId("templates");	
			
			if (!empty($_REQUEST["number_of_fund_units"])){
				
				//create template items for the new template
				foreach($_REQUEST["number_of_fund_units"] as $key=>$val){
					if ($_REQUEST["number_of_fund_units"][$key]&&$_REQUEST["fund_weight"][$key]){
						$data = array(
						"number_of_fund_units"=>$_REQUEST["number_of_fund_units"][$key],
						"leverage_ratio_id"=>$_REQUEST["leverage_ratio_id"][$key],
						"fund_weight"=>$_REQUEST["fund_weight"][$key],
						"name"=>$_REQUEST["name"][$key],
						"date_created"=>date("Y-m-d H:i:s"),
						"template_id"=>$template_id
						);
						$db->insert("template_items", $data);
					}
				}
			}	
			
			$this->view->result = array("success"=>true, "id"=>$id);
		}else{
			$this->view->result = array("success"=>false, "error"=>"Enter a name for the template.");
		}
	}

	/**
	 * 
	 * Save Action via Ajax web service call
	 * 
	 * Web service API for updating a template. URL is /templates/save/. Method is POST
	 * 
	 * @param string id The id number of Template
	 * @param string subset_name The subset name. Accessible via $_POST. Required
	 * @param array number_of_fund_units Number of Fund Units allowable for a subset. Accessible via $_POST. Required
	 * @param array fund_weight Fund Weight of the Subset
	 * @param string selected_leverage_ratio_id The selected Leverage Ratio for the template
	 * 
	 */
	public function saveAction(){
		$this->_helper->layout->setLayout("empty");
		$db = Zend_Registry::get("main_db");
		$name = $this->getRequest()->getPost("subset_name");
		$validationSuccess = true;
		if (!$_REQUEST["subset_name"]){
			$this->view->result = array("success"=>false, "error"=>"Invalid Request.");
			$validationSuccess =  false;
		}
		
		
		$template_id = $this->getRequest()->getPost("id");
		if ($template_id&&$validationSuccess){
			$db->update("templates", array("name"=>$_REQUEST["subset_name"], "leverage_ratio_id"=>$_REQUEST["selected_leverage_ratio_id"]), $db->quoteInto("id = ?", $template_id));
			
			$template_items = $db->fetchAll($db->select()->from(array("ti"=>"template_items"))->where("ti.template_id = ?", $template_id));

			if (!empty($template_items)){
				foreach($template_items as $template_item){
					foreach($_REQUEST["number_of_fund_units"] as $key=>$val){
						if ($_REQUEST["number_of_fund_units"][$key]&&$_REQUEST["fund_weight"][$key]){
							$leverage_ratio_id = $_REQUEST["leverage_ratio_id"][$key];

							if ($_REQUEST["template_id"][$key]==$template_item["id"]){
								
								
								$db->update("template_items", 
								array("number_of_fund_units"=>$_REQUEST["number_of_fund_units"][$key],
									"fund_weight"=>$_REQUEST["fund_weight"][$key]
								), $db->quoteInto("id = ?", $_REQUEST["template_id"][$key]));
							}
						}
						
					}
				}
			}
			
			/*
			$db->delete("template_items", $db->quoteInto("template_id = ?", $template_id));
			if (!empty($_REQUEST["number_of_fund_units"])){
				foreach($_REQUEST["number_of_fund_units"] as $key=>$val){
					if ($_REQUEST["number_of_fund_units"][$key]&&$_REQUEST["fund_weight"][$key]){
						$data = array(
						"number_of_fund_units"=>$_REQUEST["number_of_fund_units"][$key],
						"leverage_ratio_id"=>$_REQUEST["leverage_ratio_id"][$key],
						"fund_weight"=>$_REQUEST["fund_weight"][$key],
						"name"=>$_REQUEST["name"][$key],
						"date_created"=>date("Y-m-d H:i:s"),
						"template_id"=>$template_id
						);
						$db->insert("template_items", $data);
					}
				}
			}	
			*/
			$this->view->result = array("success"=>true, "id"=>$template_id);
		}else{
			$this->view->result = array("success"=>false, "error"=>"Invalid Request.");
		}
	}
	
	/**
	 * 
	 * Get Template Items of a given Template
	 * 
	 * Web service API for updating a template. URL is /templates/get/. Method is GET
	 * 
	 * @param string id The id number of Template
	 * 
	 */
	public function getTemplateItemsAction(){
		$this->_helper->layout->setLayout("empty");
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		$templateItemTable = new TemplateItem();
		$id = $_REQUEST["id"];
		if ($id){
			$templates = $templateItemTable->fetchAll($templateItemTable->select()->where("template_id = ?", $id));
			$this->view->result = array("success"=>true, "templates"=>$templates->toArray());
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