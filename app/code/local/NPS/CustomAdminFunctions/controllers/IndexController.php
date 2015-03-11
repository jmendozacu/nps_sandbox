<?php

class NPS_CustomAdminFunctions_IndexController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {

		//run all pre head commands
		$this->requestFunctions();

		//check for bthom function controlling the page output for primary content
		$this->btf = 0; //default welcome page
		if (isset($_GET['btf'])) {
			$this->btf = $_GET['btf'];
		}

		//function array to control output of primary content
		$displayModes = array(
			'npsWelcomePage',
			'createAddOptionsAttributeForm',
			'customAttributeOptions',
		);

		$primaryContent = '<style>' . file_get_contents(Mage::getBaseDir('base') . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR . 'NPS' . DIRECTORY_SEPARATOR . 'CustomAdminFunctions' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'adminStyle.css') . '</style>';
		$primaryContent .= '<div id="nps-custom-attr-manager-container">' . call_user_func(array($this, $displayModes[$this->btf])) . '</div>';

		//load the layout
		$this->loadLayout();

		//set the menu item active
		$this->_setActiveMenu('catalog/nps_attribute_manager_menu');

		//set left block
		$leftBlock = $this->getLayout()
		                  ->createBlock('core/text')
		                  ->setText($this->leftColumnHtml());

		//compile the lyout
		$block = $this->getLayout()
		              ->createBlock('core/text', 'attr-mass-addition')
		              ->setText($primaryContent);

		//add content block to layout
		$this->_addLeft($leftBlock);
		$this->_addContent($block);

		//render the layout
		$this->renderLayout();
	}
/**
PAGE LOAD FUNCTIONS THAT CONTROL UPDATES
 */

	public function requestFunctions() {

		//check if form is subitted
		if (isset($_POST['nps_function'])) {
			$refresh = false;
			$append_url = null;

			//if mass attribute addition
			if ($_POST['nps_function'] == 'mass_attr_option_add') {

				//append the btf number to the url
				$append_url = 'btf=1';

				//set the attribute name
				$attr_code = $_POST['nps_attr_select'];

				//$start sorting
				$sort_start = $_POST['nps_attr_start_number'];

				//set the options
				$attr_options = $_POST['nps_attr_new_options'];
				$attr_options = explode(',', $_POST['nps_attr_new_options']);

				//process and remove any blanks prior to processing
				foreach ($attr_options as $key => $option) {if ($option == '' || empty($option)) {unset($attr_options[$key]);}}

				//process new options
				$this->addAttributeOptions($attr_code, $attr_options, $sort_start);

				//trigger page refresh
				$refresh = true;
			} elseif ($_POST['nps_function'] == 'nps_attr_option_settings') {

				//set the refresh url
				$append_url = 'btf=2&attr=' . $_POST['attribute_id'];

				//trigger page refresh
				$refresh = true;
			}

			//if refresh is true then reload the page to prevent duplicate posting
			if ($refresh) {
				Mage::app()->getFrontController()->getResponse()->setRedirect($_SERVER['REQUEST_URI'] . '?' . $append_url);
			}
		}
	}

/**
HTML OUTPUT MEHTODS
 */
	private function leftColumnHtml() {

		//set url parts
		$url = explode('?', $_SERVER['PHP_SELF']);
		$url_base = $url[0];
		if (!empty($url[1])) {
			$params = explode('&', $url[1]);
		} else {
			$params = array();
		}

		//title and list start
		$html = '<h2 style="border-bottom: 1px dotted #d9d9d9;font-size:15px;">NPS Custom Attribute Tools</h2>';
		$html .= '<ul id="nps-admin-custom-attr-nav">';

		//attribute controls
		$html .= '<a href="' . $url_base . '?btf=2" title="Attribute Controls"><li class="' . $this->active(2, $this->btf) . '">Attribute Custom Controls</li></a>';

		//mass add options
		$html .= '<a href="' . $url_base . '?btf=1" title="Mass Attribute Option Addition"><li class="' . $this->active(1, $this->btf) . '">Mass Attribute Option Addition</li></a>';

		//close the list
		$html .= '</ul>';

		return $html;
	}

	private function npsWelcomePage() {
		$html = '<h1>NPS Custom Attribute Tools</h1>';
		$html .= '<p>Please select a function from the left</p>';
		return $html;
	}

	private function createAddOptionsAttributeForm() {

		//start html output
		$html = '<h1>Mass Attribute Option Addition</h1>';

		//explanation
		$html .= '<p class="page-head-note">This area will allow you to insert a massive amount of attribute options for a selected attribute. Enter a comma separated list of values below to add them to the attribute you select. It is imperative that you <span style="color:red;font-weight:bold;">DO NOT</span> insert values that have comma in them or you will confuse the system and cause undesired results.</p>';
		$html .= '<p class="page-head-note">Options should not be duplicated and so any from your list that exist in the database will not be inserted. It should be noted that options that are inserted will be given sort ordering starting with 0 and progressing through the group of options. If you would prefer that the ordering start at a different number select it below.</p>';
		$html .= '<p class="page-head-note">You may only select options that are either drop down or multiselect</p>';

		//start form
		$html .= '<form id="nps_mass_attr_option_add" name="nps_mass_attr_option_add" method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';

		//include hidden form key and function command
		$html .= '<input type="hidden" name="nps_function" value="mass_attr_option_add">';
		$html .= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';

		//start select box
		$html .= '<div class="half-block">';
		$html .= '<label for="nps_attr_select">Select Attribute</label>';
		$html .= '<select name="nps_attr_select" required value=""><option>SELECT ATTRIBUTE</option>';

		//get the list of attribute that can have options selected
		$attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
		foreach ($attributes as $attribute) {

			//make sure type is multiselect
			if ($attribute->getFrontendInput() == 'multiselect') {
				$html .= '<option data-attr-type="" value="' . $attribute->getAttributecode() . '">' . $attribute->getFrontendLabel() . '</option>';
			}

		}

		//close select box
		$html .= '</select>';
		$html .= '</div>';

		//start number
		$html .= '<div class="half-block">';
		$html .= '<label for="nps_attr_start_number">Start Ordering From</label>';
		$html .= '<input type="number" name="nps_attr_start_number" value="0"><br>';
		$html .= '</div><div class="clearer"></div>';

		//add text area for adding comma separated values
		$html .= '<label for="nps_attr_new_options" class="full-width" style="display: block;">Comma Separated Values</label>';
		$html .= '<textarea id="nps_attr_new_options" name="nps_attr_new_options" class="full-width" required></textarea><br>';

		//submit button
		$html .= '<input type="submit" value="Update Attribute">';

		//close form
		$html .= '</form>';

		return $html;
	}

	private function customAttributeOptions() {
		//start boody
		$html = '<h1>Custom Attribute Options</h1>';

		//check for attribute being set
		if (!isset($_GET['attr'])) {

			$html .= '<form id="nps_attr_option_select_attr" name="nps_attr_option_select_attr" method="get" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';
			$html .= '<input type="hidden" name="btf" value="2">';

			$html .= '<label for="nps_attr_select">Select Attribute</label>';
			$html .= '<select name="attr" required value=""><option>SELECT ATTRIBUTE</option>';

			//get the list of attribute that can have options selected
			$attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
			foreach ($attributes as $attribute) {
				if ($attribute->getFrontendLabel() !== '' && !empty($attribute->getFrontendLabel())) {
					$html .= '<option value="' . $attribute->getAttributecode() . '">' . $attribute->getFrontendLabel() . '</option>';
				}
			}

			//close select box
			$html .= '</select><div class="clearer big"></div>';

			$submit_buttom = 'Configure Attribute';

		} else {
			$attribute_id = $_GET['attr'];

			//start form & include hidden form key and function command
			$html .= '<form id="nps_attr_options" name="nps_attr_options" method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';
			$html .= '<input type="hidden" name="nps_function" value="nps_attr_option_settings">';
			$html .= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';
			$html .= '<input type="hidden" name="attribute_id" value="' . $attribute_id . '">';

			//show the attribute ID
			$html .= '<div class="half-block">';
			$html .= '<label for="nps_attr_select">Attribute Code</label>';
			$html .= '<input type="text" disabled name="attribute_code_display" value="' . $attribute_id . '">';
			$html .= '</div>';

			if ($attributeRaw = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $attribute_id, 'attribute_id')) {
				//var_dump($attributeRaw);
				$html .= '<div class="half-block">';
				$html .= '<label for="nps_attr_select">Attribute ID</label>';
				$html .= '<input type="text" disabled name="attribute_id_display" value="' . $attributeRaw->getId() . '">';
				$html .= '</div>';

				$html .= '<div class="clearer small"></div>';

				//carry child up to container products
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_carry_parent">Carry Over to Parent Container Product</label>';
				$html .= '<input type="checkbox" name="attr_option_carry_parent" checked><br>';
				$html .= '</div>';

				//how to handle duplicates
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_duplicate_handling">Duplicate Handling</label>';
				$html .= '<select name="attr_option_duplicate_handling"><option value=""></option>';
				$html .= '<option value="override">Override (newest products value is used)</option>';
				$html .= '<option value="append">Override (creates a comma separated list)</option>';
				$html .= '<option value="hide">Override (Hides all values if they differ)</option>';
				$html .= '</select>';
				$html .= '</div>';

				$html .= '<div class="clearer small"></div>';

				//add to product description section
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_add_prd_desc">Add to Product Description Section</label>';
				$html .= '<input type="checkbox" name="attr_option_add_prd_desc" checked>';
				$html .= '</div>';

				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_desc_location">Description Location</label>';
				$html .= '<select name="attr_option_desc_location"><option value=""></option>';
				$html .= '<option value="techspecs">Technical Specifications</option>';
				$html .= '<option value="features">Product Features</option>';
				$html .= '<option value="manufacturer_info">Manufacturer Specific Features</option>';
				$html .= '</select>';
				$html .= '</div>';

			} else {
				//cant find the attribute
				$html .= '<h2 style="color: red; text-transform:uppercase;">we\'re sorry but there was an error. please go back and try again by selecting another attirbute.</h2>';
			}

			$html .= '<div class="clearer big"></div>';

			//submit button value
			$submit_buttom = 'Save Attribute';
		}

		//submit button
		$html .= '<input type="submit" value="' . $submit_buttom . '">';

		//close form
		$html .= '</form>';

		return $html;
	}

/**
DATABASE AND OTHER UPDATE METHODS CALLED BY  $this->requestFunctions()
 */

	protected function addAttributeOptions($attribute_code, array $optionsArray, $sort_start) {

		//database read adapter
		$this->setConnection();

		//make sure we can find the attribute
		if ($attributeRaw = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $attribute_code, 'attribute_id')) {

			//get and clean existing options
			$attribute_existing = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attribute_code);
			if ($attribute_existing->usesSource()) {

				//compile the options
				$options = $attribute_existing->getSource()->getAllOptions(false);

				//loop through and remove any duplicates that exist from previous
				foreach ($options as $key => $val) {
					if ($new_key = array_search($val['label'], $optionsArray)) {
						unset($optionsArray[$new_key]);
					}
				}
			}

			$attributeData = $attributeRaw->getData();
			$attributeId = $attributeData['attribute_id'];

			foreach ($optionsArray as $sortOrder => $label) {
				// add option
				$data = array(
					'attribute_id' => $attributeId,
					'sort_order' => $sortOrder + $sort_start,
				);
				$this->sqlwrite->insert('eav_attribute_option', $data);

				// add option label
				$optionId = (int) $this->sqlread->lastInsertId('eav_attribute_option', 'option_id');
				$data = array(
					'option_id' => $optionId,
					'store_id' => 0,
					'value' => $label,
				);
				$this->sqlwrite->insert('eav_attribute_option_value', $data);
			}
		}
	}

/**
INFASTRUCTURE METHODS
 */

	private function setConnection() {

		//database read adapter
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		//database table prefix
		$this->tablePrefix = (string) Mage::getConfig()->getTablePrefix();
	}

	public function checked($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' checked ';
			}
		} else {
			return false;
		}
	}
	public function selected($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' selected ';
			}
		} else {
			return false;
		}
	}
	public function active($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' active ';
			}
		} else {
			return false;
		}
	}

}

?>