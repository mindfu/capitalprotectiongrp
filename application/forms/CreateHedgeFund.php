<?php
/**
 * Form for creating the hedgefund
 * 
 * @author Chris Scrivo
 * 
 */
class CreateHedgeFund extends Zend_Form{
	public function init(){
		$db = Zend_Registry::get("main_db");
		$documentType = new Zend_View_Helper_Doctype(); 
		$documentType->doctype('HTML5');
		$this->addDecorators(array("ViewHelper"), array("Errors"));
		$elements = array();
		$fund_name = new Zend_Form_Element_Text("fund_name");
		$fund_name->setRequired(true);
		$fund_name->setFilters(array("StripTags", "StringTrim"));
		$elements[] = $fund_name;
		
		$general_partner_fname = new Zend_Form_Element_Text("general_partner_fname");
		
		//$general_partner_fname->setRequired(true);
		$elements[] = $general_partner_fname;
		
		$general_partner_lname = new Zend_Form_Element_Text("general_partner_lname");
		//$general_partner_lname->setRequired(true);
		$elements[] = $general_partner_lname;
		
		$general_partner_title = new Zend_Form_Element_Text("general_partner_title");
		//$general_partner_title->setRequired(true);
		$elements[] = $general_partner_title;
				
		$manager_fname = new Zend_Form_Element_Text("manager_fname");
		$elements[] = $manager_fname;
		
		$manager_lname = new Zend_Form_Element_Text("manager_lname");
		$elements[] = $manager_lname;
		
		$manager_title = new Zend_Form_Element_Text("manager_title");
		$elements[] = $manager_title;
		
		Zend_Loader::loadClass("Category", array(MODELS_PATH));
		$categoryTable = new Category();
		$category_id = new Zend_Form_Element_Select("category_id");
		$category_id->setAttrib("class", "form-control");
		$category_id->setAttrib("id", "selectCategoryId");
		$category_id->setAttrib("required", "required");
		$categories = $categoryTable->fetchAll();
		$options = array(""=>"Please Select");
		foreach($categories as $category){
			$options[$category->id] = $category->name;
		}
		$category_id->setMultiOptions($options);
		$category_id->setRequired(true);
		$elements[] = $category_id;
		
		
		$street1 = new Zend_Form_Element_Text("street_1");
		$street1->setRequired(true);		
		$elements[] = $street1;
		
		$street2 = new Zend_Form_Element_Text("street_2");
		$elements[] = $street2;

		$city = new Zend_Form_Element_Text("city");
		$city->setRequired(true);
		$elements[] = $city;
		
		$state =  new Zend_Form_Element_Text("state");
		$state->setRequired(true);
		$elements[] = $state;
		
		$countries = $db->fetchAll($db->select()->from(array("c"=>"country"))->order("printable_name")->where("numcode IS NOT NULL"));
		$options = array();
		foreach($countries as $country){
			$options[$country["numcode"]] = $country["printable_name"];
		}
		$country_id = new Zend_Form_Element_Select("country_id");
		$country_id->setRequired(true);
		$country_id->setMultiOptions($options);
		
		$elements[] = $country_id;
				
		$continents = $db->fetchAll($db->select()->from(array("c"=>"continents"))->order("name"));
		$options = array();
		foreach($continents as $continent){
			$options[$continent["id"]] = $continent["name"];
		}
		$continent_id = new Zend_Form_Element_Select("continent_id");
		$continent_id->setMultiOptions($options);
		$continent_id->setRequired(true);
		
		$elements[] = $continent_id;
		
		$phone = new Zend_Form_Element_Text("phone");
		$phone->setRequired(true);
		$elements[] = $phone;
		
		$fax = new Zend_Form_Element_Text("fax");
		$fax->setRequired(true);
		$elements[] = $fax;
		
		$email = new Zend_Form_Element_Text("email");
		$email->setRequired(true);
		$email->setAttrib("type", "email");
		$elements[] = $email;
		
		$contact_person_fname = new Zend_Form_Element_Text("contact_person_fname");
		$contact_person_fname->setRequired(true);
		$elements[] = $contact_person_fname;
		
		$contact_person_lname = new Zend_Form_Element_Text("contact_person_lname");
		$contact_person_lname->setRequired(true);
		$elements[] = $contact_person_lname;
		
		$contact_person_title = new Zend_Form_Element_Text("contact_person_title");
		//$contact_person_title->setRequired(true);
		$elements[] = $contact_person_title;
		
		
		$firm_assets = new Zend_Form_Element_Text("firm_assets");
		$firm_assets->setRequired(true);
		$elements[] = $firm_assets;
		
		$fund_assets = new Zend_Form_Element_Text("fund_assets");
		$fund_assets->setRequired(true);
		$elements[] = $fund_assets;
		
		$primary_strategy = new Zend_Form_Element_Text("primary_strategy");
		$elements[] = $primary_strategy;
		
		$secondary_strategy = new Zend_Form_Element_Text("secondary_strategy");
		$elements[] = $secondary_strategy;
		
		$secondary_strategy_2 = new Zend_Form_Element_Text("secondary_strategy_2");
		$elements[] = $secondary_strategy_2;
		
		$options = array("onshore"=>"Onshore", "offshore"=>"Offshore");
		$onshore = new Zend_Form_Element_Radio("onshore_offshore");
		$onshore->setMultiOptions($options);
		$onshore->setAttrib("labelClass", "radio-inline");
		$onshore->setLabel("Onshore/Offshore");
		$elements[] = $onshore;
		
		
		$description = new Zend_Form_Element_Textarea("description");
		$description->setAttrib("rows", "5");
		$elements[] = $description;
		
		
		$return_start = new Zend_Form_Element_Text("return_start");
		$elements[] = $return_start;
		
		$minimum_investment = new Zend_Form_Element_Text("minimum_investment");
		$minimum_investment->setRequired(true);
		$elements[] = $minimum_investment;
		
		$management_fee = new Zend_Form_Element_Text("management_fee");
		$management_fee->setRequired(true);
		$elements[] = $management_fee;
		
		$incentive_fee = new Zend_Form_Element_Text("incentive_fee");
		$incentive_fee->setRequired(true);
		$elements[] = $incentive_fee;
		
		$early_redemption_fee = new Zend_Form_Element_Text("early_redemption_fee");
		$early_redemption_fee->setRequired(true);
		$elements[] = $early_redemption_fee;
		
		$other_fee = new Zend_Form_Element_Text("other_fee");
		$elements[] = $other_fee;
		
		$capital_addition = new Zend_Form_Element_Text("capital_addition");
		$elements[] = $capital_addition;
		
		$capital_redemption = new Zend_Form_Element_Text("capital_redemption");
		$elements[] = $capital_redemption;
		
		$lockup = new Zend_Form_Element_Text("lockup");
		$elements[] = $lockup;
		
		$hurdle_rate = new Zend_Form_Element_Text("hurdle_rate");
		$elements[] = $hurdle_rate;
		
		$options = array();
		$options["1"] = "Yes";
		$options["0"] = "No";
		
		$high_watermark = new Zend_Form_Element_Radio("high_watermark");
		$high_watermark->setMultiOptions($options);
		$high_watermark->setAttrib("labelClass", "radio-inline");
		$elements[] = $high_watermark;
		
		
		$legal_counsel_fname = new Zend_Form_Element_Text("legal_counsel_fname");
		$elements[] = $legal_counsel_fname;
		
		$legal_counsel_lname = new Zend_Form_Element_Text("legal_counsel_lname");
		$elements[] = $legal_counsel_lname;
		
		$legal_counsel_title = new Zend_Form_Element_Text("legal_counsel_title");
		$elements[] = $legal_counsel_title;
		
		$administrator_fname = new Zend_Form_Element_Text("administrator_fname");
		$elements[] = $administrator_fname;
		
		$administrator_lname = new Zend_Form_Element_Text("administrator_lname");
		$elements[] = $administrator_lname;
		
		$administrator_title = new Zend_Form_Element_Text("administrator_title");
		$elements[] = $administrator_title;
		$custodian_fname = new Zend_Form_Element_Text("custodian_fname");
		
		$elements[] = $custodian_fname;
		
		$custodian_lname = new Zend_Form_Element_Text("custodian_lname");
		$elements[] = $custodian_lname;
		
		$custodian_title = new Zend_Form_Element_Text("custodian_title");
		$elements[] = $custodian_title;
		
		$accountant_fname = new Zend_Form_Element_Text("accountant_fname");
		$elements[] = $accountant_fname;
		
		$accountant_lname = new Zend_Form_Element_Text("accountant_lname");
		$elements[] = $accountant_lname;
		
		$accountant_title = new Zend_Form_Element_Text("accountant_title");
		$elements[] = $accountant_title;
		
		$prime_broker_fname = new Zend_Form_Element_Text("prime_broker_fname");
		$elements[] = $prime_broker_fname;
		
		$prime_broker_lname = new Zend_Form_Element_Text("prime_broker_lname");
		$elements[] = $prime_broker_lname;
		
		$prime_broker_title = new Zend_Form_Element_Text("prime_broker_title");
		$elements[] = $prime_broker_title;
		
		$typical_net_exposure_low = new Zend_Form_Element_Text("typical_net_exposure_low");
		$elements[] = $typical_net_exposure_low;
		
		$typical_net_exposure_high = new Zend_Form_Element_Text("typical_net_exposure_high");
		$elements[] = $typical_net_exposure_high;
		
		$typical_percent_long_low = new Zend_Form_Element_Text("typical_percent_long_low");
		$elements[] = $typical_percent_long_low;
		
		$typical_percent_long_high = new Zend_Form_Element_Text("typical_percent_long_high");
		$elements[] = $typical_percent_long_high;
		
		
		$options = array();
		$options["1"] = "Yes";
		$options["0"] = "No";
		
		$open_to_investment = new Zend_Form_Element_Radio("open_to_investment");
		$open_to_investment->setMultiOptions($options);
		$elements[] = $open_to_investment;
		
		$currency_class = new Zend_Form_Element_Text("currency_class");
		$elements[] = $currency_class;
		
		$location_of_components = new Zend_Form_Element_Text("location_of_components");
		$elements[] = $location_of_components;
		
		$assets_in_strategy = new Zend_Form_Element_Text("assets_in_strategy");
		$elements[] = $assets_in_strategy;
		
		$investment_market = new Zend_Form_Element_Text("investment_market");
		$elements[] = $investment_market;
		
		$investment_style = new Zend_Form_Element_Text("investment_style");
		$elements[] = $investment_style;
		
		$investment_geography = new Zend_Form_Element_Text("investment_geography");
		$elements[] = $investment_geography;
		
		$lehman_hfn_index_participant = new Zend_Form_Element_Radio("lehman_hfn_index_participant");
		$lehman_hfn_index_participant->setMultiOptions($options);
		$elements[] = $lehman_hfn_index_participant;
		
		$this->setElements($elements);
		
		
		$elements = $this->getElements();
		foreach($elements as $element){
			//get the name
			
			$label = $element->getLabel();
			if ($label){
				continue;
			}
			
			$name = $element->getName();
			$name = str_replace("_id", "", $name);
			$names = explode("_", $name);
			$label = array();
			foreach($names as $piece){
				$label[] = ucwords(strtolower($piece));
			}
			
			$name = implode(" ", $label);
			$element->setLabel($name);
			
			if ($element instanceof Zend_Form_Element_Text||$element instanceof Zend_Form_Element_Textarea||$element instanceof Zend_Form_Element_Select){
				$element->setAttrib("class", "form-control");
				$element->setFilters(array("StripTags", "StringTrim"));
			}
			if ($element instanceof Zend_Form_Element_Text||$element instanceof Zend_Form_Element_Textarea){
				$element->setAttrib("placeholder", $name);			
			}
			if ($element instanceof Zend_Form_Element_Radio){
				$element->setAttrib("labelClass", "radio-inline");
			}
		}
	}
}
