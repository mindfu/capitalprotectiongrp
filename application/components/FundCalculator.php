<?php
/**
 * Main Component for Fund related calculations
 */

class FundCalculator{
	private $db;
	
	/**
	 * The fund
	 */
	private $fund;
	
	
	/**
	 * The portfolio
	 */
	private $portfolio;
	
	
	public function __construct($fund_id, $portfolio_id){
		$this->db = Zend_Registry::get("main_db");
		$portfolioTable = new Portfolio();
		$fundTable = new Fund();
		$hedgefundTable = new HedgeFund();
		if ($fund_id&&$portfolio_id){
			$this->hedgefund = $hedgefundTable->fetchRow($hedgefundTable->select()->where("id = ?", $fund_id));
			$this->hedgefund = $this->hedgefund->toArray();
			$this->portfolio = $portfolioTable->fetchRow($portfolioTable->select()->where("id = ?", $portfolio_id));		
			$this->fund = $fundTable->fetchRow($fundTable->select()->where("id = ?", $fund_id));
			if ($this->portfolio){
				$this->portfolio = $this->portfolio->toArray();
				$ratioTable = new LeverageRatio();
				$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $this->portfolio["leverage_ratio_id"]));
				if ($ratio){
					if ($ratio->debt>0){
						$this->portfolio["leverage"] = "Yes";
					}else{
						$this->portfolio["leverage"] = "No";
					}
					$ratio = $ratio->toArray();
					$this->portfolio["selected_leverage_ratio"] = $ratio;					
				}
			}
			
			if ($this->fund){
				$this->fund = $this->fund->toArray();
				
				
			}			
		}
	}
	
	public function getSharpeRatio($year, $leverage_ratio_id){
		$db = $this->db;
		$portfolio = $this->portfolio;
		$hedgefund = $this->hedgefund;
		$fund_id = $hedgefund["fund_id"];
		$fund_percentages = $db->fetchAll($db->select()->from("fund_percentages")->where("year = ?", $year)->where("fund_id = ?", $hedgefund["id"])->order("month_num"));
		$risk_free_rates = $db->fetchAll($db->select()->from("risk_free_rates")->where("year = ?", $year));
		
		$performance = array();
		$risk_rates = array();
		$excess_rates = array();
		for($i=0;$i<=12;$i++){
			$fund_percentage = $fund_percentages[$i];
			$risk_free_rate = $risk_free_rates[$i];
			
			$appliedRatio = $this->getMonthlyAppliedRatio($fund_percentage, $leverage_ratio_id);
			
			$performance[] = $appliedRatio["total_monthly_return"];
			$risk_rates[] = $risk_free_rate["value"];
			$excess_rates[] = $appliedRatio["total_monthly_return"] - $risk_free_rate["value"];
		}
		
		$average = array_sum($excess_rates)/count($excess_rates);

		$standard_deviation = $this->stats_standard_deviation($excess_rates);
		return $average/$standard_deviation;
	}
	
	
	
	public function getAppliedRatioPerformance($year, $leverage_ratio_id=null){
		$db = $this->db;
		$portfolio = $this->portfolio;
		$fund = $this->fund;
		//load performance for the fund
		$fund["performance"] = $db->fetchAll($db->select()->from("fund_percentages")->where("year = ?", $year)->where("fund_id = ?", $fund["id"])->order("month_num"));
		$fund["portfolio"] = $this->portfolio;
		$ratioTable = new LeverageRatio();
		if ($leverage_ratio_id){
			$ratios = $ratioTable->fetchAll($ratioTable->select()->where("id = ?", $leverage_ratio_id));
			$ratios = $ratios->toArray();			
		}else{
			$ratios = $ratioTable->fetchAll($ratioTable->select()->where("id = ?", $this->portfolio["leverage_ratio_id"]));
			$ratios = $ratios->toArray();
		}
		
		//load libor data
		
		foreach($fund["performance"] as $key=>$performance){
			
			$ratio_performances = array();
			foreach($ratios as $ratio){
				
				$ratio_performance = array();
				//multiplier
				$selected_libor = $db->fetchOne($db->select()->from("libor", "value")->where("month = ?", $performance["month"])->where("year = ?", $performance["year"])->where("type = ?", $portfolio["bank_leverage"]));
				if (!$selected_libor){
					$selected_libor = 0;
				}
				
				
				$multiplier = $ratio["debt"]+$ratio["equity"];
				$ratio_performance["performance"] = $performance;
				$ratio_performance["debt"] = $ratio["debt"];
				$ratio_performance["equity"] = $ratio["equity"];
				$ratio_performance["multiplier"] = $multiplier;
				$ratio_performance["gross_return"] = $performance["value"]*($multiplier/100);
				$ratio_performance["libor_over_12_months"] = $selected_libor/12;
				
				$ratio_performance["bank_fee"] = ($portfolio["leverage_bank_cost"]/10000)/12;
				$ratio_performance["libor_plus_bank_fee"] = $ratio_performance["libor_over_12_months"] + $ratio_performance["bank_fee"];
				$ratio_performance["libor_plus_bank_fee_debt"] = $ratio_performance["libor_plus_bank_fee_debt"]*($ratio["debt"]/100);
				
				$ratio_performance["additional_bank_fee"] = (($portfolio["additional_leverage_bank_cost"]/10000)/12) * ($ratio_performance["debt"]/100);
				$ratio_performance["total_fee"] = $ratio_performance["libor_plus_bank_fee"] + $ratio_performance["additional_bank_fee"];
				$ratio_performance["return_on_leveraged_amount"] = $ratio_performance["gross_return"] - ($performance["value"]*($ratio_performance["equity"]/100)) - $ratio_performance["total_fee"];
				$ratio_performance["total_monthly_return"] = ($performance["value"]*($ratio_performance["equity"]/100)) + $ratio_performance["return_on_leveraged_amount"];
				
				$ratio_performances = $ratio_performance;
			}
			
			$fund["performance"][$key]["ratio_performances"] = $ratio_performances;
		}

		
		return $fund;
	}
	
	
	public function getMonthlyAppliedRatio($performance, $leverage_ratio_id){
		$ratio_performance = array();
		$portfolio = $this->portfolio;
		$ratioTable = new LeverageRatio();
		$ratio = $ratioTable->fetchRow($ratioTable->select()->where("id = ?", $leverage_ratio_id));
		$ratio = $ratio->toArray();			
		$db = Zend_Registry::get("main_db");
		if (!$performance){
			return array();
		}
		//multiplier
		$selected_libor = $db->fetchOne($db->select()->from("libor", "value")->where("month = ?", $performance["month"])->where("year = ?", $performance["year"])->where("type = ?", $portfolio["bank_leverage"]));
		if (!$selected_libor){
			$selected_libor = 0;
		}
		
		
		$multiplier = $ratio["debt"]+$ratio["equity"];
		$ratio_performance["performance"] = $performance;
		$ratio_performance["debt"] = $ratio["debt"];
		$ratio_performance["equity"] = $ratio["equity"];
		$ratio_performance["multiplier"] = $multiplier;
		$ratio_performance["gross_return"] = $performance["value"]*($multiplier/100);
		$ratio_performance["libor_over_12_months"] = $selected_libor/12;
		
		$ratio_performance["bank_fee"] = ($portfolio["leverage_bank_cost"]/10000)/12;
		$ratio_performance["libor_plus_bank_fee"] = $ratio_performance["libor_over_12_months"] + $ratio_performance["bank_fee"];
		$ratio_performance["libor_plus_bank_fee_debt"] = $ratio_performance["libor_plus_bank_fee_debt"]*($ratio["debt"]/100);
		
		$ratio_performance["additional_bank_fee"] = (($portfolio["additional_leverage_bank_cost"]/10000)/12) * ($ratio_performance["debt"]/100);
		$ratio_performance["total_fee"] = $ratio_performance["libor_plus_bank_fee"] + $ratio_performance["additional_bank_fee"];
		$ratio_performance["return_on_leveraged_amount"] = $ratio_performance["gross_return"] - ($performance["value"]*($ratio_performance["equity"]/100)) - $ratio_performance["total_fee"];
		$ratio_performance["total_monthly_return"] = ($performance["value"]*($ratio_performance["equity"]/100)) + $ratio_performance["return_on_leveraged_amount"];
		
		return $ratio_performance;
	}
	
	 /**
     * This user-land implementation follows the implementation quite strictly;
     * it does not attempt to improve the code or algorithm in any way. It will
     * raise a warning if you have fewer than 2 values in your array, just like
     * the extension does (although as an E_USER_WARNING, not E_WARNING).
     * 
     * @param array $a 
     * @param bool $sample [optional] Defaults to false
     * @return float|bool The standard deviation or false on error.
     */
    function stats_standard_deviation(array $a, $sample = false) {
        $n = count($a);
        if ($n === 0) {
            trigger_error("The array has zero elements", E_USER_WARNING);
            return false;
        }
        if ($sample && $n === 1) {
            trigger_error("The array has only 1 element", E_USER_WARNING);
            return false;
        }
        $mean = array_sum($a) / $n;
        $carry = 0.0;
        foreach ($a as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        };
        if ($sample) {
           --$n;
        }
        return sqrt($carry / $n);
    }
}