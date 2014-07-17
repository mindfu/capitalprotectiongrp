<?php
/**
 * Form for creating a subset
 * 
 * @author Chris Scrivo
 * @depracated
 */
class UpdateSubset extends Zend_Form{
	public function init(){
		$this->addDecorators(array("ViewHelper"), array("Errors"));
		$elements = array();
		
		$id = new Zend_Form_Element_Hidden("id");
		$id->setAttrib("id", "updateSubsetId");
		$id->setRequired(true);
		$elements[] = $id;
			
		$subset_name = new Zend_Form_Element_Text("subset_name");
		$subset_name->setRequired(true);
		$subset_name->setAttrib("class", "form-control");
		$subset_name->setAttrib("id", "updateSubsetName");
		
		$elements[] = $subset_name;
		
		/*
		$number_of_fund_units = new Zend_Form_Element_Text("number_of_funds_units");
		$number_of_fund_units->setRequired(true);
		$number_of_fund_units->setAttrib("class", "form-control");
		$number_of_fund_units->setAttrib("id", "updateNumberOfFundsUnits");
		
		//$elements[] = $number_of_fund_units;
		
		$fund_weight = new Zend_Form_Element_Text("fund_weight");
		$fund_weight->setRequired(true);
		$fund_weight->setAttrib("class", "form-control");
		$fund_weight->setAttrib("id", "updateFundWeight");
		
		//$elements[] = $fund_weight;
		
		$debt = new Zend_Form_Element_Text("debt");
		$debt->setRequired(true);
		$debt->setAttrib("class", "form-control");
		$debt->setAttrib("id", "updateDebt");
		
		//$elements[] = $debt;
		
		$equity = new Zend_Form_Element_Text("equity");
		$equity->setRequired(true);
		$equity->setAttrib("class", "form-control");
		$equity->setAttrib("id", "updateEquity");
		
		//$elements[] = $equity;
		*/
		$options = array();
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
		$leverage_ratio_id->setAttrib("id", "updateLeverageRatioId");
		$leverage_ratio_id->setMultiOptions($options);
		$leverage_ratio_id->setRequired(true);
		$leverage_ratio_id->setAttrib("class", "form-control");
		$elements[] = $leverage_ratio_id;
		
		
		
		$this->addElements($elements);
		
	}
}
