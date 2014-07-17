<?php
/**
 * Controller class for Fund related actions
 * 
 * Controller class for CRUD operations related to Fund.
 * 
 * @author Chris Scrivo
 * 
 */ 
class FundController extends Zend_Controller_Action
{
	/**
	 * 
	 * Assign Fund to Portfolio Landing page
	 *
	 * Landing page to assign fund to portfolio. URL is /fund/assign/
	 * 
	 * @param string id The id of the portfolio
	 * 
	 */
	public function assignAction(){
		$this->__checkAuth();
		//load dependencies
		Zend_Loader::loadClass("CreateFund", array(FORMS_PATH));	
		Zend_Loader::loadClass("UpdateFund", array(FORMS_PATH));	
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
		Zend_Loader::loadClass("Fund", array(MODELS_PATH));
		Zend_Loader::loadClass("Category", array(MODELS_PATH));
		Zend_Loader::loadClass("Subset", array(MODELS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		Zend_Loader::loadClass("Template", array(MODELS_PATH));
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		Zend_Loader::loadClass("HedgeFund", array(MODELS_PATH));
		$db = Zend_Registry::get("main_db");
		
		
		$session = new Zend_Session_Namespace("portfolio_session");
		
		//initialize the forms
		$form = new CreateFund();
		$updateForm = new UpdateFund();
		
		$this->view->form = $form;
		$this->view->update_form = $updateForm;
		
		//get portfolio details
		$portfolioTable = new Portfolio();
		if ($this->getRequest()->getQuery("id")){
			$id = $this->getRequest()->getQuery("id");			
		}else{
			$id = $session->created_portfolio;
		}
		
		if (!$id){
			header("Location:/portfolio/");				
		}

		//load portfolio details
		$portfolioTable = new Portfolio();
		$portfolio = $portfolioTable->fetchRow($portfolioTable->select()->where("id = ?", $id));
		if ($portfolio){
			
			$portfolio = $portfolio->toArray();
			
			//get all funds under the portfolio
			$fundTable = new Fund();
			$funds = $fundTable->fetchAll($fundTable->select()->where("portfolio_id = ?", $portfolio["id"])->where("deleted = 0"));
			$funds = $funds->toArray();
			foreach($funds as $key=>$fund){
				//load hedgefund details
				$hedgefundTable = new HedgeFund();
				$hedgefund = $hedgefundTable->fetchRow($hedgefundTable->select()->where("id = ?", $fund["fund_id"]));
				
				
				$categoryTable = new Category();
				$category = $categoryTable->fetchRow($categoryTable->select()->where("id = ?", $hedgefund->category_id));
				$category = $category->toArray();
				if ($fund["fund_id"]!=0){
					$funds[$key]["name"] = $db->fetchOne($db->select()->from("hedgefunds", array("fund_name"))->where("id = ?", $fund["fund_id"]));
				}
				
				$funds[$key]["category"] = $category;
				
				//load subset
				$templateItemTable = new TemplateItem();
				$subset = $templateItemTable->fetchRow($templateItemTable->select()->where("id = ?", $fund["subset_id"]));
				$funds[$key]["subset"] = $subset->name;
				
			}
			$portfolio["funds"] = $funds;
			
			
			
			$templateTable = new Template();
			$templateItemTable = new TemplateItem();
			
			//auto select template based on leveraged ratio
			
			
			$templates = $templateTable->fetchAll($templateTable->select()->where("leverage_ratio_id = ?", $portfolio["leverage_ratio_id"]));
			$templates = $templates->toArray();
			foreach($templates as $key=>$template){
				$template_items = $templateItemTable->fetchAll($templateItemTable->select()->where("template_id = ?", $template["id"]));
				$template_items = $template_items->toArray();
				$templates[$key]["template_items"] = $template_items;
			}
			
			$ratioTable = new LeverageRatio();
			$this->view->subsets = array();
			$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $portfolio["leverage_ratio_id"]));
			if ($ratio){
				
				$templateTable = new Template();
				$templates = $templateTable->fetchAll($templateTable->select()->where("leverage_ratio_id = ?", $ratio->id));						
				$templates = $templates->toArray();
				$options = array();
				$options[""] = "Please Select";
				foreach($templates as $key=>$template){
					$options[$template["id"]] = $template["name"];
				}
				
				$form->getElement("template_id")->setMultiOptions($options);
				$updateForm->getElement("template_id")->setMultiOptions($options);
			}
			$form->getElement("portfolio_id")->setValue($id);
			$this->view->portfolio = $portfolio;
		}
		$this->view->headScript()->appendFile("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.js", $type="text/javascript");
		$this->view->headScript()->appendFile( "/public/js/fund/assign.js", $type = 'text/javascript' );
		$this->view->headTitle("Assign funds to ".$portfolio["name"]);
		$this->_helper->layout->setLayout("sb_admin");			
	}

	/**
	 * Add Fund to Portfolio Action via Ajax web service call
	 * 
	 * Web service API for assigning a fund to a portfolio. URL is /fund/add/. Method is POST
	 * 
	 * @param string portfolio_id The id of the portfolio where the fund is being added to
	 * @param string subset_id The id of the subset where the fund is being assigned
	 * @param string template_id  The id of the template where the fund is being assigned
	 * @param string fund_id The id of the fund
	 * 
	 */
	public function addAction(){
		Zend_Loader::loadClass("CreateFund", array(FORMS_PATH));	
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
		Zend_Loader::loadClass("Fund", array(MODELS_PATH));
		Zend_Loader::loadClass("Category", array(MODELS_PATH));
		Zend_Loader::loadClass("Subset", array(MODELS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		Zend_Loader::loadClass("Template", array(MODELS_PATH));
		
		
		$db = Zend_Registry::get('main_db');
		//get portfolio id
		$portfolio_id = $this->getRequest()->getPost("portfolio_id");
		$portfolioTable = new Portfolio();
		$ratioTable = new LeverageRatio();
		
		$fund_id = $this->getRequest()->getPost("fund_id");
		$subset_id = $this->getRequest()->getPost("subset_id");
		$hedgefund_id = $this->getRequest()->getPost("fund_id");
		
		//load portfolio details
		$portfolio = $portfolioTable->fetchRow($portfolioTable->select()->where("id = ?", $portfolio_id));
		if ($portfolio){
			$portfolio = $portfolio->toArray();
			if ($portfolio["leverage_ratio_id"]){
				$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $portfolio["leverage_ratio_id"]));
				if ($ratio){
					
					//create the fund validator
					$form = new CreateFund();							
					
					//initially load all templates
					$templateTable = new Template();
					$templates = $templateTable->fetchAll();
					$options = array();
					$options[""] = "Please Select";
					foreach($templates as $template){
						$options[$template->id] = $template->name;				
					}	
					$form->getElement("template_id")->setMultiOptions($options);
					
					
					$templateItemTable = new TemplateItem();		
					$template_items = $templateItemTable->fetchAll($templateItemTable->select()->where("template_id = ?", $_REQUEST["template_id"]));

					$options = array();
					$options[""] = "Please Select";
					foreach($template_items as $template){
						$options[$template->id] = $template->name;				
					}	
					$form->getElement("subset_id")->setMultiOptions($options);
					
					
					//if $_POST passed validation
					if ($form->isValid($_POST)&&$fund_id){
						
						$data = $form->getValues();
						$fundTable = new Fund();
						
						$countFunds = $fundTable->fetchAll($fundTable->select()->where("subset_id = ?", $data["subset_id"]));
						$countFunds = $countFunds->toArray();
						
						
						$subset = $templateItemTable->fetchRow($templateItemTable->select()->where('id = ?', $subset_id));
						$number_of_fund_units = $subset->number_of_fund_units;
						
						
						$fundsInPortfolio = $fundTable->fetchAll($fundTable->select()->where("subset_id = ?", $subset_id)->where("portfolio_id = ?", $portfolio_id));
						$fundsInPortfolio = $fundsInPortfolio->toArray();
						
						if (count($fundsInPortfolio)>=$number_of_fund_units){
							$this->view->result = array("success"=>false, "error"=>"Maximum fund has been added to this subset");
						}
						
						
						//if not existing create a new fund assignment
						$existingFund = $fundTable->fetchRow($fundTable->select()->where("subset_id = ?", $subset_id)->where("fund_id = ?", $hedgefund_id)->where("portfolio_id = ?", $portfolio_id));
						if (!$existingFund){
							$data = $form->getValues($_POST);
							$data["fund_id"] = $fund_id;
							
							//depracate fund_name field
							unset($data["name"]);
							
							$fundTable->insert($data);
							$this->view->result = array("success"=>true, "id"=>$id);
						}else{
							//notify for error
							$this->view->result = array("success"=>false, "error"=>"Fund is already added to the subset.");
						}		

						
						$this->_helper->layout->setLayout("empty");
					}else{
						$this->view->result = array("success"=>false, "errors"=>$form->getErrors());
						
						$this->_helper->layout->setLayout("empty");
					}
				}else{
					$this->view->result = array("success"=>false, "error"=>"Non Existing Leverage Ratio");
					
					$this->_helper->layout->setLayout("empty");
				}
			}
			
		}else{
			$this->view->result = array("success"=>false, "error"=>"Non Existing Portfolio");
			
			$this->_helper->layout->setLayout("empty");
		}
	}
	
	/*
	 * Get Information about the fund via Ajax web service call
	 * 
	 * Web service API for getting information about the fund. URL is /fund/get-info/. Method is GET
	 * 
	 * @param string id The id of the fund
	 * 
	 */
	public function getInfoAction(){
		Zend_Loader::loadClass("Fund", array(MODELS_PATH));
		Zend_Loader::loadClass("Template", array(MODELS_PATH));
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		
		$db = Zend_Registry::get("main_db");
		$id = $this->getRequest()->getQuery("id");
		if ($id){
			$fundTable = new Fund();
			//get the fund based on the id
			$fund = $fundTable->fetchRow($fundTable->select()->where("id = ?", $this->getRequest()->getQuery("id")));
			$fund = $fund->toArray();
			if ($fund["fund_id"]!=0){
				//get the name of the hedgefund
				$fund["name"] = $db->fetchOne($db->select()->from("hedgefunds", array("fund_name"))->where("id = ?", $fund["fund_id"]));
			}
			
			$templateItemTable = new TemplateItem();
			$templateTable = new Template();
			$template_items = $templateItemTable->fetchAll($templateItemTable->select()->where("template_id = ?", $fund["template_id"]));
			$templates = $templateTable->fetchAll();
			$this->view->result = array("success"=>true, "fund"=>$fund, "template_items"=>$template_items->toArray(), "templates"=>$templates->toArray());
		}else{
			$this->view->result = array("success"=>false);			
		}
		$this->_helper->layout->setLayout("empty");		
	}
	/**
	 * List all fund under a certain portfolio via Ajax web service call
	 * 
	 * Web service API for Listing all fund under a certain portfolio Method is GET
	 * 
	 * @param string portfolio_id The id of the portfolio
	 * 
	 */
	public function listByPortfolioAction(){
		Zend_Loader::loadClass("Fund", array(MODELS_PATH));
		$fundTable = new Fund();
		$funds = $fundTable->fetchAll($fundTable->select()->where("portfolio_id = ?", $this->getRequest()->getQuery("portfolio_id")));
		$funds = $funds->toArray();
		$this->view->result = array("success"=>true, "funds"=>$funds);
		$this->_helper->layout->setLayout("empty");		
	}

	/**
	 * Delete a fund via Ajax web service call
	 * 
	 * Web service API for deleting a fund
	 * 
	 * @param string id The id of the fund
	 * 
	 */
	public function deleteAction(){
		Zend_Loader::loadClass("Fund", array(MODELS_PATH));
		$fundTable = new Fund();
		$id = $this->getRequest()->getQuery("id");
		$fundTable->update(array("deleted"=>1), $fundTable->getAdapter()->quoteInto("id = ?", $id));
		$this->view->result = array("success"=>true, "id"=>$id);
		$this->_helper->layout->setLayout("empty");	
	}
	
	/**
	 * Updating Fund Assignment to Portfolio Action via Ajax web service call
	 * 
	 * Web service API for updating a fund assigment to a portfolio. URL is /fund/update/. Method is POST
	 * 
	 * @param string portfolio_id The id of the portfolio where the fund is being added to
	 * @param string subset_id The id of the subset where the fund is being assigned
	 * @param string template_id  The id of the template where the fund is being assigned
	 * @param string fund_id The id of the fund
	 * 
	 */
	public function updateAction(){
		Zend_Loader::loadClass("CreateFund", array(FORMS_PATH));	
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
		Zend_Loader::loadClass("Fund", array(MODELS_PATH));
		Zend_Loader::loadClass("Category", array(MODELS_PATH));
		Zend_Loader::loadClass("Subset", array(MODELS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		Zend_Loader::loadClass("Template", array(MODELS_PATH));
		
		//get portfolio id
		$fund_id = $this->getRequest()->getPost("id");
		$hedgefund_id = $this->getRequest()->getPost("fund_id");
		$subset_id = $this->getRequest()->getPost("subset_id");
		
		if ($fund_id){
			//get all subset
			$fundTable = new Fund();
			$portfolioTable = new Portfolio();
			$subsetTable = new Subset();
			$ratioTable = new LeverageRatio();
			$templateTable = new Template();
			$templateItemTable = new TemplateItem();
			
			$fund = $fundTable->fetchRow($fundTable->select()->where("id = ?", $fund_id));
			if ($fund){
				$portfolio = $portfolioTable->fetchRow($portfolioTable->select()->where("id = ?", $this->getRequest()->getPost("portfolio_id")));
				if ($portfolio){
					$portfolio = $portfolio->toArray();
					$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $portfolio["leverage_ratio_id"]));
					if ($ratio){
						$form = new CreateFund();
						$templateTable = new Template();
						$templates = $templateTable->fetchAll();
						$options = array();
						$options[""] = "Please Select";
						foreach($templates as $template){
							$options[$template->id] = $template->name;				
						}	
						$form->getElement("template_id")->setMultiOptions($options);
						
						
						$templateItemTable = new TemplateItem();		
						$template_items = $templateItemTable->fetchAll($templateItemTable->select()->where("template_id = ?", $_REQUEST["template_id"]));
	
						$options = array();
						$options[""] = "Please Select";
						foreach($template_items as $template){
							$options[$template->id] = $template->name;				
						}	
						$form->getElement("subset_id")->setMultiOptions($options);
						
						if ($form->isValid($_POST)&&$hedgefund_id){
							$data = $form->getValues($_POST);
							unset($data["name"]);
							unset($data["id"]);
							$data["fund_id"] = $hedgefund_id;
							$fundTable->update($data, $fundTable->getAdapter()->quoteInto("id = ?", $_REQUEST["id"]));
							$this->view->result = array("success"=>true);
							
						}else{
							$this->view->result = array("success"=>false, "errors"=>$form->getErrors());
						}
					}else{
						$this->view->result = array("success"=>false, "error"=>"Leverage Ratio not Found");
						
					}
				}else{
					$this->view->result = array("success"=>false, "error"=>"Portfolio Does Not Exist");
				}			
			}else{
				$this->view->result = array("success"=>false, "error"=>"Fund does not exist");			
			}

		}else{
			$this->view->result = array("success"=>false);
		}
		$this->_helper->layout->setLayout("empty");
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
	