<?php
/**
 * Perform Subset Related Calculation
 * 
 * @author Chris Scrivo
 * @depracated
 * 
 */

class SubsetCalculator{
	private $portfolio;
	private $subset;
	private $db;
	
	public function __construct($db){
		$this->db = $db;
	}
	
	public function setPortfolio($portfolio){
		$this->portfolio = $portfolio;
	}
	
	public function getPortfolio(){
		return $this->portfolio;
	}
	
	public function setSubset($subset){	
		$this->subset = $subset->toArray();
	}
	
	public function getSubset(){
		return $this->subset;
	}
	
	public function calculate(){
		$portfolio = $this->portfolio;
		$subset = $this->subset;
		
	
		$portfolio["subset"]["debt"] = $this->portfolioSubsetDebt();
		
		$portfolio["subset"]["equity"] = $this->portfolioSubsetEquity();
		$portfolio["subset"]["equity_percentage"] = $this->getEquityPercentage();
		$portfolio["subset"]["total_investment"] = $this->portfolioSubsetTotalInvestment($portfolio["subset"]["debt"], $portfolio["subset"]["equity"]);
		$portfolio["subset"]["average_initial_investment_fund"] = $this->getAverageInitialInvestmentFund();
		return $portfolio;
	}
	
	private function portfolioSubsetDebt(){
		//get total of subset first
		$db = $this->db;
		$sum_debt_total = $db->fetchOne("SELECT SUM(debt_total) FROM subsets");
		$sql = $db->select()->from("leverage_ratios", array("debt"))->where("id = ?", $this->portfolio["leverage_ratio_id"]);
		//get ratio
		$debt_ratio = $db->fetchOne($sql);
		
		return (floatval($this->subset["debt_total"])/$sum_debt_total)*(floatval($this->portfolio["initial_equity"])*$debt_ratio);
	}
	
	private function getEquityPercentage(){
		//get ratio
		$db = $this->db;
		$equity_ratio = $db->fetchOne($db->select()->from("leverage_ratios", "equity")->where("id = ?", $this->portfolio["leverage_ratio_id"]));
		return $this->portfolioSubsetEquity()/($this->portfolio["initial_equity"]*$equity_ratio);
	}
	
	private function getAverageInitialInvestmentFund(){
		$db = $this->db;
		$subset = $this->subset;
		
		$total = $db->fetchOne("SELECT SUM(number_of_fund) FROM funds WHERE subset_id = ".$subset["id"]);
		if ($total>0){
			return $this->portfolioSubsetTotalInvestment($this->portfolioSubsetDebt(), $this->portfolioSubsetEquity())/$total;
		}else{
			return 0;
		}
		
	}
	
	private function portfolioSubsetEquity(){
		//get total of subset first
		$db = $this->db;
		$sum_equity_total = $db->fetchOne("SELECT SUM(equity_total) FROM subsets");
		
		//get ratio
		$equity_ratio = $db->fetchOne($db->select()->from("leverage_ratios", "equity")->where("id = ?", $this->portfolio["leverage_ratio_id"]));
		return ($this->subset["equity_total"]/$sum_equity_total)*($this->portfolio["initial_equity"]*$equity_ratio);
	}
	
	public function getNumberOfFunds($subset_id){
		$db = $this->db;	
		$result = $db->fetchOne("SELECT SUM(number_of_fund) AS total FROM funds WHERE subset_id = ".$subset_id);
		if ($result){
			return $result;
		}else{
			return 0;
		}
	}
	
	private function portfolioSubsetTotalInvestment($debt, $equity){
		return $debt+$equity;
	}
}