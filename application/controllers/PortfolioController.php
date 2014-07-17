<?php
/**
 * Controller related to Portfolio Management CRUD and other action
 */
class PortfolioController extends Zend_Controller_Action
{
	public function testAction(){
		
		$this->_helper->layout->setLayout("html5");
	}
	
	
	/**
	 * Action for rendering view for creating a new portfolio
	 */
	public function setupAction(){
		        // action body
		$this->__checkAuth();
    	$this->view->headTitle("Capital Protection");    	
		Zend_Loader::loadClass("CreatePortfolio", array(FORMS_PATH));
		$this->view->form = new CreatePortfolio();
		$this->view->headScript()->prependFile( "/public/js/portfolio/setup.js", $type = 'text/javascript' );
    	$this->_helper->layout->setLayout("sb_admin");
	}
	
	/**
	 * Action for rendering the calculated view of a portfolio based on the fund
	 */
	public function calculateAction(){
		$this->__checkAuth();
		Zend_Loader::loadClass("Subset", array(MODELS_PATH));
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		Zend_Loader::loadClass("Template", array(MODELS_PATH));
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		Zend_Loader::loadClass("SubsetCalculator", array(MODELS_PATH));
		Zend_Loader::loadClass("Fund", array(MODELS_PATH));
		Zend_Loader::loadClass("PortfolioCalculator", array(COMPONENTS_PATH));
		
		setlocale(LC_MONETARY, 'en_US');
		
		$id = $this->getRequest()->getQuery("id");
		$calculator = new PortfolioCalculator($id);
		
		$leverageRatioTable = new LeverageRatio();
		$ratios = $leverageRatioTable->fetchAll();
		if ($calculator->isValid()){
			$subsets = $calculator->getSubsets();
			$portfolio = $calculator->getPortfolio();
			$this->view->headTitle($portfolio["name"]." - Capital Protection");  	 	
			$this->view->subsets = $subsets;
			$this->view->portfolio = $portfolio;
			$this->view->ratios = $ratios;
		}
		$this->view->headScript()->appendFile( "/public/js/portfolio/calculate.js", $type = 'text/javascript' );
		$this->_helper->layout->setLayout("sb_admin");
	}
	
	/**
	 * List the portfolios owned by the system user
	 */
	public function indexAction(){
		 // action body
		$this->__checkAuth();
		$session = new Zend_Session_Namespace("capitalp");
    	$this->view->headTitle("Capital Protection");    	
		
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));

		
		$portfolio_table = new Portfolio();
		//$portfolios = $portfolio_table->fetchAll($portfolio_table->select()->where("user_id = ?", $session->manager_id)->where("deleted = 0"));
		
		$this->view->portfolios = $portfolios;
		
		$this->view->headScript()->appendFile( "/public/js/portfolio/index.js", $type = 'text/javascript' );
    	$this->_helper->layout->setLayout("sb_admin");
	}
	
	/**
	 * Ajax call for listing the portfolio
	 */
	public function listAction(){
		
		$this->__checkAuth();
		$session = new Zend_Session_Namespace("capitalp");
    	$db = Zend_Registry::get("main_db");
		
		//get the page requested
		$rows = 100;
		$page = $this->getRequest()->getQuery("page");
		if (!$page){
			$page = 1;
		}
		
    	$portfolios = $db->fetchAll($db->select()->from("portfolios", array(new Zend_Db_Expr("SQL_CALC_FOUND_ROWS id"), "name", "initial_equity", "leverage_bank_cost", "leverage", "additional_leverage_bank_cost", "show_results_net_fund", "fund_of_funds_performance_fee", "date_created", "date_updated"))->where("user_id = ?", $session->manager_id)->where("deleted = 0")->limitPage($page, $rows));
		$count = $db->fetchOne("SELECT FOUND_ROWS() as count");
		
		$totalPage = ceil($count/$rows);
		$this->view->result = array("count"=>$count, "portfolios"=>$portfolios, "page"=>$page, "total_page"=>$totalPage);
		
    	$this->_helper->layout->setLayout("empty");
	} 
	
	/**
	 * Save the updated portfolio information
	 */
	public function saveAction(){
		Zend_Loader::loadClass("UpdatePortfolio", array(FORMS_PATH));
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
		$this->_helper->layout->setLayout("empty");
		$form = new UpdatePortfolio();
		if (!empty($_POST)){	
			$id = $this->getRequest()->getPost("id");
			if ($form->isValid($_POST)){
				$data = $form->getValues($_POST);	
				$portfolioTable = new Portfolio();
				$portfolioTable->update($data, $portfolioTable->getAdapter()->quoteInto("id = ?", $id));
				$this->view->result = array("success"=>true, "id"=>$id);
			}else{
				$this->view->result = array("success"=>false, "errors"=>$form->getErrors());
				
			}
		}
	}
	
	/**
	 * Render view for updating the Portfolio
	 */
	public function updateAction(){
		        // action body
		$this->__checkAuth();
		Zend_Loader::loadClass("UpdatePortfolio", array(FORMS_PATH));
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
		$form = new UpdatePortfolio();
		//load the selected portfolio
		$portfolioTable = new Portfolio();
		$portfolio = $portfolioTable->find($this->getRequest()->getQuery("id"));
		$portfolio = $portfolio->toArray();
		$portfolio = $portfolio[0];
		foreach($portfolio as $key=>$val){
			try{
				if ($form->getElement($key)!=null){
					$form->getElement($key)->setValue($val);									
					
				}
			}catch(Exception $e){
				
			}

		}
		
    	$this->view->headTitle("Capital Protection");  	 	
		$this->view->form = $form;
		$this->view->portfolio = $portfolio;
		$this->view->form->getElement("id")->setValue($this->getRequest()->getQuery("id"));
		$this->view->headScript()->appendFile( "/public/js/portfolio/update.js", $type = 'text/javascript' );
    	$this->_helper->layout->setLayout("sb_admin");			
		

	}

	/**
	 * Get all leverage ratio ajax service
	 */
	public function getLeverageRatioAction(){
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		$ratioTable = new LeverageRatio();
		$ratios = $ratioTable->fetchAll($ratioTable->select());
		$ratios = $ratios->toArray();
		$this->view->result = array("success"=>true, "ratios"=>$ratios);
	}
	
	/**
	 * Delete a certain portfolio using its id
	 */
	public function deleteAction(){
		$id = $this->getRequest()->getQuery("id");
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
		
		if ($id){
			$portfolioTable = new Portfolio();
			$portfolioTable->update(array("deleted"=>1), $portfolioTable->getAdapter()->quoteInto("id = ?", $id));
			$this->view->result = array("success"=>true, "id"=>$id);
		}else{
			$this->view->result = array("success"=>false);
		}
		
	}
	
	/**
	 * Create a new portfolio
	 */
	public function createAction(){
		$this->__checkAuth();
		$session = new Zend_Session_Namespace("capitalp");
		$data = $_POST;
		$this->_helper->layout->setLayout("empty");
		Zend_Loader::loadClass("CreatePortfolio", array(FORMS_PATH));
		$form = new CreatePortfolio();
		if ($form->isValid($data)){
//			$data = $form->getValidValues($data);
			$data["date_created"] = date("Y-m-d H:i:s");
			$data["date_updated"] = date("Y-m-d H:i:s");
			$data["user_id"] = $session->manager_id;
			Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
			$portfolioTable = new Portfolio();
			$portfolioTable->insert($data);
			
			$id = $portfolioTable->getAdapter()->lastInsertId("portfolios");
			$session = new Zend_Session_Namespace("portfolio_session");
			$session->created_portfolio = $id;
			$this->view->result = array("success"=>true, "id"=>$id);
		}else{
			$this->view->result = array("success"=>false, "errors"=>$form->getErrors());
		}
	}
	
	public function getMonthlyFundAnalysisAction(){
		$this->_helper->layout->setLayout("empty");
		$portfolio_id = $this->getRequest()->getQuery("portfolio_id");
		$leverage_ratio_id = $this->getRequest()->getQuery("leverage_ratio_id");
		$selected_subsets =  $this->getRequest()->getQuery("subset_id");
		$db = Zend_Registry::get("main_db");
		Zend_Loader::loadClass("HedgeFund", array(MODELS_PATH));
		Zend_Loader::loadClass("Portfolio", array(MODELS_PATH));
		Zend_Loader::loadClass("Fund", array(MODELS_PATH));
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		Zend_Loader::loadClass("Template", array(MODELS_PATH));
		Zend_Loader::loadClass("TemplateItem", array(MODELS_PATH));
		Zend_Loader::loadClass("PortfolioCalculator", array(COMPONENTS_PATH));
		Zend_Loader::loadClass("FundCalculator", array(COMPONENTS_PATH));
		$portfolio_calculator = new PortfolioCalculator($portfolio_id);
		$subsets = $portfolio_calculator->getSubsets();
		
		$leverage_ratio_table = new LeverageRatio();
		if (!$leverage_ratio_id){
			$leverage_ratio_id = $db->fetchOne($db->select()->from(array("p"=>"portfolios"), array("leverage_ratio_id"))->where("id = ?", $portfolio_id));		
		}
		$hedgeFundTable = new HedgeFund();
		$finalList = array();
		foreach($subsets as $key_subset=>$subset){
			if (!empty($selected_subsets)){
				if (!in_array($subset["id"], $selected_subsets)){
					continue;
				}				
			}
			$hedgefunds = $db->fetchAll($db->select()->from(array("f"=>"funds"), array())->joinLeft(array("hf"=>"hedgefunds"), "hf.id = f.fund_id", array("hf.id AS id", "hf.fund_name"))->where("f.portfolio_id = ?", $portfolio_id)->where("f.subset_id = ?", $subset["id"]));
			$ratio = $leverage_ratio_table->fetchRow($leverage_ratio_table->select()->where("id = ?", $leverage_ratio_id));
			foreach($hedgefunds as $key=>$hedgefund){
				$calculator = new FundCalculator($hedgefund["id"], $portfolio_id);
				
				$portfolios = $db->fetchAll($db->select()->from(array("f"=>"funds"), array("f.portfolio_id"))->joinLeft(array("p"=>"portfolios"), "p.id = f.portfolio_id", array("p.name"))->where("f.fund_id = ?", $hedgefund["id"])->group("portfolio_id"));
				$hedgefunds[$key]["portfolios"] = $portfolios;
				$yearlySharpes = array();
				$year = date("Y")+2;
				for($i=1999;$i<=$year;$i++){
					$sharpe = $calculator->getSharpeRatio($i, $leverage_ratio_id);
					$ratioPerformance = $calculator->getAppliedRatioPerformance($i, $leverage_ratio_id);
					$totalPerformance = 0;
					foreach($ratioPerformance["performance"] as $performance_value){
						$totalPerformance += $performance_value["ratio_performances"]["total_monthly_return"];
					}
					
					$avgPerformance = $totalPerformance/12;
					
					$performances = $db->fetchAll($db->select()->from("fund_percentages")->where("fund_id = ?", $hedgefund["id"])->where("year = ?", $i)->order("month_num"));
					foreach($performances as $key_perf=>$perf){
						$performances[$key_perf]["value"] = number_format($perf["value"], 2);
					}
					
					if (!empty($performances)&&count($performances)<12){
						$missing = 12-count($performances);
						for($j=1;$j<=$missing;$j++){
							$perf_item = array("value"=>"0");
							$performances[] = $perf_item;
						}
					}
					
					if ($ratio->debt==0){
						$label = number_format($ratio->equity, 2)." : ".number_format($ratio->debt, 2)." (Unleveraged)";
					}else{
						$label = number_format($ratio->equity, 2)." : ".number_format($ratio->debt, 2);
					}
					$sharpeData = array("sharpe"=>number_format($sharpe,2), "average_return"=>number_format($avgPerformance, 2), "year"=>$i, "performances"=>$performances, "leverage"=>$label);
					$yearlySharpes[] = $sharpeData;
				}
				$hedgefunds[$key]["sharpes"] = $yearlySharpes;
				$hedgefunds[$key]["sharpes"] = array_reverse($hedgefunds[$key]["sharpes"]);
			}
			$subsets[$key_subset]["hedgefunds"] = $hedgefunds;
		}
		foreach($subsets as $key_subset=>$subset){
			if (!empty($selected_subsets)){
				if (in_array($subset["id"], $selected_subsets)){
					$finalList[] = $subset;
				}				
			}else{
				$finalList[] = $subset;
			}
		}
		
		$this->view->result = $finalList;
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
	