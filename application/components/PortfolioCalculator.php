<?php
/**
 * Main component for the calculation of portfolio's based on the assigned funds and weights
 * 
 * @author Chris Scrivo
 * 
 */

class PortfolioCalculator{
		
	private $db;	
	/**
	 * The portfolio
	 */
	private $portfolio;	
	
	private $valid = false;
	
	
	/**
	 * The subsets
	 */
	private $subsets = array();
		
	public function __construct($portfolio_id){
		$this->db = Zend_Registry::get("main_db");
		$portfolioTable = new Portfolio();
		if ($portfolio_id){
			$this->portfolio = $portfolioTable->fetchRow($portfolioTable->select()->where("id = ?", $portfolio_id));	
			if ($this->portfolio){
				$this->portfolio = $this->portfolio->toArray();
				$ratioTable = new LeverageRatio();
				$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $this->portfolio["leverage_ratio_id"]));
				if ($ratio->debt>0){
					$this->portfolio["leverage"] = "Yes";
				}else{
					$this->portfolio["leverage"] = "No";
				}
				
				
				$this->valid = true;
				$this->calculate();
			}	
		}
	}
	/**
	 * get calculated subsets
	 */
	public function getSubsets(){
		return $this->subsets;
	}
	
	/**
	 * Return check if portfolio is valid
	 */
	public function isValid(){
		return $this->valid;
	}
	/**
	 * Get the portfolio
	 */
	public function getPortfolio(){
		
		return $this->portfolio;
	}
	
	/**
	 * Private method to calculate portfolio information
	 */
	private function calculate(){
		$db = $this->db;
		$portfolio = $this->portfolio;	
		$ratioTable = new LeverageRatio();
		//get the selected ratio
		$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $portfolio["leverage_ratio_id"]));
		
		$portfolio["ratio_debt_initial_investment"] = $ratio->debt*$portfolio["initial_equity"];
		$portfolio["ratio_equity_initial_investment"] = $ratio->equity*$portfolio["initial_equity"];
		$portfolio["total_leveraged_initial_investment"] = $portfolio["ratio_equity_initial_investment"]+$portfolio["ratio_debt_initial_investment"];
		
		$this->portfolio = $portfolio;
		
		
		//get all fund record then collect unique subset ids
		$subset_ids = $db->fetchAll($db->select()->from(array("f"=>"funds"), "f.subset_id")->where("portfolio_id = ?", $portfolio["id"])->group(array("subset_id")));
		$templateItemTable = new TemplateItem();
		$templateTable = new Template();
		
		$subsets = array();
		foreach($subset_ids as $item){
			$subset_id = $item["subset_id"];
			$subset = $templateItemTable->fetchRow($templateItemTable->select()->where("id = ?", $subset_id));
			if ($subset){
				$subset = $subset->toArray();
				$template = $templateTable->fetchRow($templateTable->select()->where("id = ?", $subset["template_id"]));
				if ($template){
					$selected_ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $template->leverage_ratio_id));
					if ($selected_ratio){
						$selected_ratio = $selected_ratio->toArray();
						$subset["debt"] = $selected_ratio["debt"];
						$subset["equity"] = $selected_ratio["equity"];
					}
				}
				$subsets[] = $subset;
			}
		}
		
		//get all subset within the selected ratio
		/*
		$subsetTable = new Subset();
		$subsets = $subsetTable->fetchAll($subsetTable->select()->where("debt = ?", $ratio->debt)->where("equity = ?", $ratio->equity));
		$subsets = $subsets->toArray();
		*/
		
		//calculate basic subset information
		$subsets_debt_total = 0;
		$subsets_equity_total = 0;
		$subsets_total = 0;
		foreach($subsets as $key=>$subset){
			$fund_weight = $subset["debt"] + $subset["equity"];
			$subsets[$key]["subset_name"] = $subset["name"];
			$subsets[$key]["number_of_funds"] = $db->fetchOne($db->select()->from(array("f"=>"funds"), array(new Zend_Db_Expr("COUNT(*) AS count_funds")))->where("subset_id = ?", $subset["id"]));
			$subsets[$key]["number_of_funds_units"] = $subset["number_of_fund_units"];
			$subset["number_of_funds_units"] = $subset["number_of_fund_units"];
			$subsets[$key]["fund_weight"] = $fund_weight;
			$subsets[$key]["group_weight"] = $fund_weight * $subset["number_of_funds_units"];
			$subsets[$key]["weight"] = $subset["debt"] + $subset["equity"];
			$subsets[$key]["debt_total"] = $subset["debt"] * $subset["number_of_funds_units"];
			$subsets[$key]["equity_total"] = $subset["equity"] * $subset["number_of_funds_units"];
			$subsets[$key]["total"] = $subsets[$key]["debt_total"] + $subsets[$key]["equity_total"];		
			$subsets_equity_total += $subsets[$key]["equity_total"];
			$subsets_debt_total += $subsets[$key]["debt_total"];
			$subsets_total += $subsets[$key]["total"];
		}
		
		//calculate subset other information
		foreach($subsets as $key=>$subset){
			if ($subsets_total>0){
				$subsets[$key]["group_percentage_of_portfolio"] = $subset["total"]/$subsets_total;			
			}else{
				$subsets[$key]["group_percentage_of_portfolio"] = 0;
			}
			if ($subsets_equity_total>0){
				$subsets[$key]["group_percentage_of_equity"] = $subset["total"]/$subsets_equity_total;
			}else{
				$subsets[$key]["group_percentage_of_equity"] = 0;
			}
			$subsets[$key]["each_fund_percentage_of_portfolio"] = $subset["total"]/$subsets_total;
			
			if ($subsets_debt_total!=0){
				$subsets[$key]["calculated_debt"] = ($subset["debt_total"]/$subsets_debt_total)*$portfolio["ratio_debt_initial_investment"];
			}else{
				$subsets[$key]["calculated_debt"] = 0;
			}
			if ($subsets_equity_total!=0){
				$subsets[$key]["calculated_equity"] = ($subset["equity_total"]/$subsets_equity_total)*$portfolio["ratio_equity_initial_investment"];
				$subsets[$key]["each_fund_average_total_exposure"] = $subset["weight"]/$subsets_equity_total;
			}else{
				$subsets[$key]["calculated_equity"] = 0;
				$subsets[$key]["each_fund_average_total_exposure"] = 0;
			}
			$subsets[$key]["calculated_total"] = $subsets[$key]["calculated_debt"]+$subsets[$key]["calculated_equity"];
			
			$subsets[$key]["equity_percentage"] = $subsets[$key]["calculated_equity"]/$portfolio["ratio_equity_initial_investment"];
			
		}
		
		
		$fundTable = new Fund();
		foreach($subsets as $key=>$subset){
			//get all funds under the current subset
			$funds = $fundTable->fetchAll($fundTable->select()->where("subset_id = ?", $subset["id"])->where("portfolio_id = ?", $portfolio["id"])->where("deleted = 0"));
			$funds = $funds->toArray();				
			$totalFund = 0;
			//calculate total number of funds
			foreach($funds as $fund){
				$totalFund += $fund["number_of_fund"];
			}				
			$subsets[$key]["total_fund"] = $totalFund;	
			if ($subset["weight"] > 0){
				$subsets[$key]["average_initial_fund_investment"] = $subsets[$key]["calculated_total"]/$subsets[$key]["total_fund"];
			}else{
				$subsets[$key]["average_initial_fund_investement"] = 0;
			}
			
			//calculate fund specific details
			$totalUnits = 0;
			foreach($funds as $key_fund=>$fund){
				
				//check if is weight variable or weight fixed
				$funds[$key_fund]["unit"] = ($fund["weight_variable"]/$fund["number_of_fund"])*100;
				$funds[$key_fund]["temp_weight"] = $fund["number_of_fund"]*$fund["weight_variable"];
				$totalUnits += $funds[$key_fund]["unit"];
			}
			
			
			foreach($funds as $key_fund=>$fund){
				if ($fund["fund_id"]!=0){
					$funds[$key_fund]["name"] = $db->fetchOne($db->select()->from("hedgefunds", array("fund_name"))->where("id = ?", $fund["fund_id"]));
					$funds[$key_fund]["category"] = $db->fetchOne($db->select()->from(array("h"=>"hedgefunds"), array())->joinInner(array("c"=>"categories"), "c.id = h.category_id", array("c.name"))->where("h.id = ?",  $fund["fund_id"]));
				
				}
				
				
				if ($subset["equity_percentage"] > 0){
					$funds[$key_fund]["percentage_initial_equity"] = (($subset["equity_percentage"]*100)/$totalUnits)*$fund["unit"];
				}else{
					$funds[$key_fund]["percentage_initial_equity"] = 0;
				}
				$funds[$key_fund]["initial_investment_in_equity"] = ($funds[$key_fund]["percentage_initial_equity"]/100)*$portfolio["initial_equity"];
				
				//check if debt is leveraged
				if ($portfolio["ratio_debt_initial_investment"] > 0){
					$funds[$key_fund]["initial_investment_in_debt"] = ($fund["unit"]/$totalUnits)*$portfolio["ratio_debt_initial_investment"];
					$funds[$key_fund]["percentage_initial_debt"] = ($funds[$key_fund]["initial_investment_in_debt"]/$portfolio["ratio_debt_initial_investment"])*100;
					
				}else{
					$funds[$key_fund]["initial_investment_in_debt"] = 0;
					$funds[$key_fund]["percentage_initial_debt"] = 0;
					
				}
				
				$funds[$key_fund]["initial_investment"] = $funds[$key_fund]["initial_investment_in_debt"]+$funds[$key_fund]["initial_investment_in_equity"];
				$funds[$key_fund]["each_fund_percentage_of_portfolio"] = $funds[$key_fund]["initial_investment"]/$portfolio["total_leveraged_initial_investment"];
				$funds[$key_fund]["total_debt_equity_exposure"] = $funds[$key_fund]["initial_investment"]/$portfolio["ratio_equity_initial_investment"];
			}
			$subsets[$key]["funds"] = $funds;
			
		}
		$this->subsets = $subsets;
		
	}
}
