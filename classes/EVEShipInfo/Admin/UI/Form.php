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
						'<p class="submit">'.
							'<button type="submit" name="save" class="button button-primary">'.
								$this->submitLabel.
							'</button>'.
						'</p>'.
					'</td>'.		
				'</tfoot>'.
			'</table>'.
		'</form>';
		
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
}
