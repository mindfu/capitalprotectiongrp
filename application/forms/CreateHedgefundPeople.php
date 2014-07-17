<?php
/**
 * Form for creating the hedgefund
 * 
 * @author Chris Scrivo
 * 
 */
class CreateHedgefundPeople extends Zend_Form{
	public function init(){
		$this->addDecorators(array("ViewHelper"), array("Errors"));
		
		$elements = array();
		
		$first_name = new Zend_Form_Element_Text("first_name");
		$first_name->setFilters(array("StripTags", "StringTrim"));
		$first_name->setAttrib("class", "form-control");
		$first_name->setAttrib("id", "inputFirstName");
		$first_name->setRequired(true);
		$elements[] = $first_name;
		
		$last_name = new Zend_Form_Element_Text("last_name");
		$last_name->setFilters(array("StripTags", "StringTrim"));
		$last_name->setAttrib("class", "form-control");
		$last_name->setAttrib("id", "inputLastName");
		$last_name->setRequired(true);
		$elements[] = $last_name;
		
		$title = new Zend_Form_Element_Text("title");
		$title->setFilters(array("StripTags", "StringTrim"));
		$title->setAttrib("class", "form-control");
		$title->setAttrib("id", "inputLastName");
		$title->setRequired(true);
		$elements[] = $title;
		
		$hedgefund_id = new Zend_Form_Element_Hidden("hedgefund_id");
		$hedgefund_id->setRequired(true);
		$elements[] = $hedgefund_id;
		
		
		$this->addElements($elements);
	}
}
		