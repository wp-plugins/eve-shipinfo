<?php

class EVEShipInfo_Admin_UI_Form extends EVEShipInfo_Admin_UI_Renderable
{
	protected $submitLabel;
	
	protected $submittedVar;
	
	public function __construct(EVEShipInfo_Admin_UI $ui, $id)
	{
		parent::__construct($ui, $id);
		
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element');
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_ElementInput');
		
		$this->submitLabel = __('Save', 'EVEShipInfo');
		$this->submittedVar = $this->id.'_submitted';
	}
	
	protected $buttons = array();
	
   /**
    * Adds a button to the end of the form, next to the submit button.
    * Multiple buttons can be added this way.
    * 
    * @param EVEShipInfo_Admin_UI_Button $button
    * @return EVEShipInfo_Admin_UI_Form
    */
	public function addButton(EVEShipInfo_Admin_UI_Button $button)
	{
		$this->buttons[] = $button;
		return $this;
	}
	
	public function isSubmitted()
	{
		if(isset($_REQUEST[$this->submittedVar]) && $_REQUEST[$this->submittedVar] == 'yes') {
			return true;
		}
		
		return false;
	}
	
	public function setSubmitLabel($label)
	{
		$this->submitLabel = $label;
		return $this;
	}
	
	public function render()
	{
		$this->addHiddenVar($this->submittedVar, 'yes');
		
		$buttons = $this->buttons;
		array_unshift(
			$buttons, 
			$this->ui->button($this->submitLabel)
			->makeSubmit()
			->setName('save')
			->makePrimary()
		);
		
		$html = 
		'<form method="post" id="'.$this->getID().'">';
			foreach($this->hiddens as $name => $value) {
				$html .=
				'<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
			}
			$html .=
			'<table class="form-table">'.
				'<tbody>';
					foreach($this->elements as $element) {
						$html .= $element->render();
					}
					$html .=
				'</tbody>'.
				'<tfoot>'.
					'<td></td>'.
					'<td>'.
						'<p class="submit">';
							foreach($buttons as $button) {
								$html .= $button->render();
							}
							$html .=
						'</p>'.
					'</td>'.		
				'</tfoot>'.
			'</table>'.
		'</form>';
		
		// focus on the default element if it has been specified 
		if(isset($this->defaultElement)) {
			$html .= sprintf(
				'<script type="text/javascript">'.
					"jQuery(document).ready(function() {".
						"setTimeout(function() {jQuery('#%s').focus();}, 500);".
					"})".
				'</script>',
				$this->defaultElement->getID()
			);
		}
							
		return $html;
	}
	
	protected $hiddens = array();
	
   /**
    * Adds a hidden form variable.
    * @param string $name
    * @param string $value
    * @return EVEShipInfo_Admin_UI_Form
    */
	public function addHiddenVar($name, $value)
	{
		$this->hiddens[$name] = $value;
		return $this;
	}
	
   /**
    * Adds a regular text element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Text
    */
	public function addText($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Text');
		
		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Text($this, $name, $label));
	}
	
   /**
    * Adds a textarea element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Textarea
    */
	public function addTextarea($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Textarea');
		
		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Textarea($this, $name, $label));
	}
	
   /**
    * Adds a select element.
    * @param string $name
    * @param string $label
    * @return EVEShipInfo_Admin_UI_Form_Element_Select
    */
	public function addSelect($name, $label)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Form_Element_Select');

		return $this->addElement(new EVEShipInfo_Admin_UI_Form_Element_Select($this, $name, $label));
	}
	
   /**
    * @var EVEShipInfo_Admin_UI_Form_Element[]
    */
	protected $elements;
	
	protected function addElement(EVEShipInfo_Admin_UI_Form_Element $element)
	{
		$this->elements[] = $element;
		return $element;
	}
	
	protected $defaultValues = array();
	
	public function setDefaultValues($values)
	{
		$this->defaultValues = $values;
		return $this;
	}
	
	public function getDefaultValue($name)
	{
		if(isset($this->defaultValues[$name])) {
			return $this->defaultValues[$name];
		}
		
		return null;
	}
	
	protected $defaultElement;
	
   /**
    * Selects which element in the form will get the focus on page load.
    * 
    * @param EVEShipInfo_Admin_UI_Form_Element $element
    * @return EVEShipInfo_Admin_UI_Form
    */
	public function setDefaultElement(EVEShipInfo_Admin_UI_Form_Element $element)
	{
		$this->defaultElement = $element;
		return $this;
	}
	
   /**
    * Validates the form if it has been submitted. Returns
    * whether it is valid, and automatically marks elements
    * as erroneous that have errors. 
    * 
    * @return boolean
    */
	public function validate()
	{
		if(!$this->isSubmitted()) {
			return false;
		}
		
		$valid = true;
		foreach($this->elements as $element) {
			if(!$element->validate()) {
				$valid = false;
			}
		}
		
		return $valid;
	}
}
