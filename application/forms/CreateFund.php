<?php
/**
 * Form for creating the fund
 * 
 * @author Chris Scrivo
 * 
 */
class CreateFund extends Zend_Form{
	public function init(){
		$this->addDecorators(array("ViewHelper"), array("Errors"));
		
		$elements = array();
				
		$portfolio_id = new Zend_Form_Element_Hidden("portfolio_id");
		$portfolio_id->setRequired(true);
		
		$elements[] = $portfolio_id;
		
		$name = new Zend_Form_Element_Text("name");
		$name->setFilters(array("StripTags", "StringTrim"));
		$name->setAttrib("class", "form-control");
		$name->setAttrib("id", "inputName");
		$name->setAttrib("placeholder", "Enter the name of fund then search");

		$elements[] = $name;
		$subset_id = new Zend_Form_Element_Hidden("subset_id");
		$subset_id->setRequired(true);
		$elements[] = $subset_id;
		
		$options = array();
		$options["1"] = "Yes";
		$options["0"] = "No";
		$number_of_fund = new Zend_Form_Element_Radio("number_of_fund");
		$number_of_fund->setRequired(true);
		$number_of_fund->setMultiOptions($options);
		$number_of_fund->setAttrib("labelClass", "radio-inline");
		
		$elements[] = $number_of_fund;

		$weight_variable = new Zend_Form_Element_Text("weight_variable");
		$weight_variable->setAttrib("class", "form-control");
		$weight_variable->setAttrib("id", "inputWeightVariable");
		$weight_variable->setFilters(array("LocalizedToNormalized"));
		
		
		$elements[] = $weight_variable;
		$weight_fixed = new Zend_Form_Element_Text("weight_fixed");
		$weight_fixed->setFilters(array("LocalizedToNormalized"));
		$weight_fixed->setAttrib("class", "form-control");
		$weight_fixed->setAttrib("id", "inputWeightFixed");
		
		$elements[] = $weight_fixed;
		
		$subset_id = new Zend_Form_Element_Select("subset_id");
		$subset_id->setAttrib("class","form-control");
		$subset_id->setAttrib("id","selectSubsetId");
		$subset_id->setAttrib("required", "required");
		
		$elements[] = $subset_id;
		
		$template_id = new Zend_Form_Element_Select("template_id");
		$template_id->setAttrib("class","form-control");
		$template_id->setAttrib("id","selectTemplateId");
		$template_id->setAttrib("required", "required");
		
		$elements[] = $template_id;
		
		$this->setElements($elements);
	}
}
