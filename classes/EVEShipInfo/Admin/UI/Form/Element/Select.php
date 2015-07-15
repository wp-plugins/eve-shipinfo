<?php

class EVEShipInfo_Admin_UI_Form_Element_Select extends EVEShipInfo_Admin_UI_Form_Element
{
	protected function _renderElement()
	{
		if(isset($this->pleaseSelect)) {
			array_unshift($this->options, new EVEShipInfo_Admin_UI_Form_Element_Select_Option($this->pleaseSelect, ''));
		}
		
		$html = 
		'<select'.$this->renderAttributes().'>';
			foreach($this->options as $option) {
				$html .= $option->render();
			}
			$html .=
		'</select>';
			
		return $html;
	}
	
	protected $options = array();
	
   /**
    * Adds an option to the select.
    * @param string $label
    * @param string $value
    * @return EVEShipInfo_Admin_UI_Form_Element_Select
    */
	public function addOption($label, $value=null)
	{
		$option = new EVEShipInfo_Admin_UI_Form_Element_Select_Option($label, $value);
		$this->options[] = $option;
		return $this;
	}
	
   /**
    * Adds an option group to the select.
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Select_OptionGroup
    */
	public function addOptionGroup($label)
	{
		$group = new EVEShipInfo_Admin_UI_Form_Element_Select_OptionGroup($label);
		$this->options[] = $group;
		return $group;
	}
	
	public function addPleaseSelect($label=null)
	{
		if(empty($label)) {
			$label = __('Please select...', 'EVEShipInfo');
		}
		
		$this->pleaseSelect = $label;
		return $this;
	}
	
	protected $pleaseSelect;
}

class EVEShipInfo_Admin_UI_Form_Element_Select_Option
{
	protected $value;
	
	protected $label;
	
	public function __construct($label, $value=null)
	{
		$this->value = $value;
		$this->label = $label;
	}
	
	public function render()
	{
		$atts = array(
			'value' => $this->value
		);
		
		return '<option'.EVEShipInfo::getInstance()->compileAttributes($atts).'>'.$this->label.'</option>';
	}
}

class EVEShipInfo_Admin_UI_Form_Element_Select_OptionGroup
{
	protected $label;
	
	protected $options = array();
	
	public function __construct($label)
	{
		$this->label = $label;
	}
	
	public function addOption($label, $value=null)
	{
		$this->options[] = new EVEShipInfo_Admin_UI_Form_Element_Select_Option($label, $value);
		return $this;
	}
	
	public function render()
	{
		$atts = array(
			'label' => $this->label
		);
		
		$html = 
		'<optgroup'.EVEShipInfo::getInstance()->compileAttributes($atts).'>';
			foreach($this->options as $option) {
				$html .= $option->render();
			}
			$html .=
		'</optgroup>';
			
		return $html;
	}
}