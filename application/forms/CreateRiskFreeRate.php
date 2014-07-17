<?php
class CreateRiskFreeRate extends Zend_Form{
	public function init(){
		$elements = array();
		
		$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		
		$options = array();
		$options[""] = "Please Select";
		foreach($months as $key=>$month){
			$options[$month] = $month;
		}
		
		$month_control = new Zend_Form_Element_Select("month");
		$month_control->addMultiOptions($options);
		$month_control->setRequired(true);
		$month_control->setAttrib("class", "form-control");
			
		$elements[] = $month_control;
		
		$options = array();
		$options[""] = "Please Select";
		for($i=1999;$i<=intval(date("Y"))+4;$i++){
			$options[$i] = $i;
		}
		
		$year_control = new Zend_Form_Element_Select("year");
		$year_control->addMultiOptions($options);
		$year_control->setRequired(true);
		$year_control->setAttrib("class", "form-control");
		
		$elements[] = $year_control;
		
		$value = new Zend_Form_Element_Text("value");
		$value->setRequired(true);
		$value->setAttrib("class", "form-control");
		
		$elements[] = $value;
		
		$this->setElements($elements);
	}
}
