<?php
/**
 * Form for creating the portfolio
 * 
 * @author Chris Scrivo
 */
class CreatePortfolio extends Zend_Form{
	public function init(){
		$this->addDecorators(array("ViewHelper"), array("Errors"));
		
		//add elements to create portfolio
		$elements = array();
		$name = new Zend_Form_Element_Text("name");
		$name->setRequired(true);
		$name->setAttrib("class", "form-control");
		$name->setAttrib("id", "inputName");
		$name->setAttrib("placeholder", "Name of the Portfolio");
		
		$elements[] = $name;
		
		$initial_equity = new Zend_Form_Element_Text("initial_equity");
		$initial_equity->setRequired(true);
		$initial_equity->setAttrib("class", "form-control");
		$initial_equity->setAttrib("id", "inputEquity");
		$initial_equity->setAttrib("placeholder", "Amount in Dollars");
		$elements[] = $initial_equity;
		
		$leverage = new Zend_Form_Element_Radio("leverage");
		$leverage->setRequired(true);
		$leverage->addMultiOptions(array("Yes"=>"Yes", "No"=>"No"));
		$leverage->setAttrib('label_class', 'radio-inline');
		$leverage->setSeparator("");
		$elements[] = $leverage;
		
		$options = array(""=>"Please Select");
		for($i=50;$i<=200;$i+=5){
			$options["$i"] = $i;
		}
		
		$leverage_bank_cost = new Zend_Form_Element_Select("leverage_bank_cost");
		$leverage_bank_cost->setRequired(true);
		$leverage_bank_cost->setAttrib("class", "form-control");
		$leverage_bank_cost->setMultiOptions($options);
		$elements[] = $leverage_bank_cost;
		
		$options = array(""=>"Please Select");
		for($i=0;$i<=50;$i++){
			$options["$i"] = $i;
		}
		$additional_leverage_bank_cost = new Zend_Form_Element_Select("additional_leverage_bank_cost");
		$additional_leverage_bank_cost->setRequired(true);
		$additional_leverage_bank_cost->setAttrib("class", "form-control");
		$additional_leverage_bank_cost->setMultiOptions($options);
		$elements[] = $additional_leverage_bank_cost;
		
		$options = array(""=>"Please Select", "3 Month LIBOR"=>"3 Month LIBOR", "6 Month LIBOR"=>"6 Month LIBOR");
		$bank_leverage = new Zend_Form_Element_Select("bank_leverage");
		$bank_leverage->setRequired(true);
		$bank_leverage->setAttrib("class", "form-control");
		$bank_leverage->setMultiOptions($options);
		$elements[] = $bank_leverage;
		
		$show_results_net_fund = new Zend_Form_Element_Radio("show_results_net_fund");
		$show_results_net_fund->setRequired(true);
		$show_results_net_fund->addMultiOptions(array("Yes"=>"Yes", "No"=>"No"));
		$show_results_net_fund->setAttrib('label_class', 'radio-inline');
		$show_results_net_fund->setSeparator("");
		$elements[] = $show_results_net_fund;
		
		$options = array(""=>"Please Select");
		for($i=0;$i<=3;$i+=.5){
			$options["$i"] = $i."%";
		}
		$fund_of_funds_management_fee = new Zend_Form_Element_Select("fund_of_funds_management_fee");
		$fund_of_funds_management_fee->setRequired(true);
		$fund_of_funds_management_fee->setAttrib("class", "form-control");
		$fund_of_funds_management_fee->setMultiOptions($options);
		$elements[] = $fund_of_funds_management_fee;
		
		$options = array(""=>"Please Select");
		for($i=0;$i<=30;$i+=2.5){
			$options["$i"] = $i."%";
		}
		$fund_of_funds_performance_fee = new Zend_Form_Element_Select("fund_of_funds_performance_fee");
		$fund_of_funds_performance_fee->setRequired(true);
		$fund_of_funds_performance_fee->setAttrib("class", "form-control");
		$fund_of_funds_performance_fee->setMultiOptions($options);
		$elements[] = $fund_of_funds_performance_fee;
		
		
		Zend_Loader::loadClass("LeverageRatio", array(MODELS_PATH));
		$ratioTable = new LeverageRatio();
		$ratios = $ratioTable->fetchAll($ratioTable->select());
		$options = array(""=>"Please Select");
		foreach($ratios as $ratio){
			if ($ratio->debt==0){
				$options[$ratio->id] = $ratio->debt." : ".$ratio->equity." unleveraged";
			}else{
				$options[$ratio->id] = $ratio->debt." : ".$ratio->equity;	
				
			}
		}
		$leverage_ratio_id = new Zend_Form_Element_Select("leverage_ratio_id");
		$leverage_ratio_id->setMultiOptions($options);
		$leverage_ratio_id->setRequired(true);
		$leverage_ratio_id->setAttrib("class", "form-control");
		$elements[] = $leverage_ratio_id;
		
		
		$this->addElements($elements);
		
	}
	
}
