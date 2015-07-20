<?php

abstract class EVEShipInfo_Admin_UI_Form_Element extends EVEShipInfo_Admin_UI_Renderable
{
	protected $form;
	
	protected $name;
	
	protected $label;
	
	protected $description = '';
	
	protected $attributes = array();
	
	public function __construct(EVEShipInfo_Admin_UI_Form $form, $name, $label)
	{
		parent::__construct($form->getUI(), $form->getPlugin()->nextJSID());
		
		$this->form = $form;
		$this->name = $name;
		$this->label = $label;
	}
	
   /**
    * Sets the help text to describe the element.
    * @param string $description
    * @return EVEShipInfo_Admin_UI_Form_Element
    */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	
	public function render()
	{
		if(!empty($this->description)) {
			$this->setAttribute('aria-describedby', 'tagline-'.$this->id);
		}
		
		$rowClasses = array('form-field');
		if($this->required) {
			$rowClasses[] = 'form-required';
		}
		
		$description = $this->description;
		
		if($this->validated && !$this->valid) {
			$rowClasses[] = 'form-invalid';
			$description = 
			'<b class="validation-message">'.
				__('Note:', 'EVEShipInfo').' '.
				$this->validationMessage.
			'</b>'.
			'<br/>'.
			$description;
		}
		
		$html =
		'<tr class="'.implode(' ', $rowClasses).'">'.
			'<th scope="row">'.
				'<label for="'.$this->id.'">'.
					$this->label;
					if($this->required) {
						$html .= ' <span class="description">('.__('required', 'EVEShipInfo').')</span>';
					}
					$html .=
				'</label>'.
			'</th>'.
			'<td>'.
				$this->_renderElement();
				if(!empty($description)) {
					$html .=
					'<p class="description" id="tagline-'.$this->id.'">'.
						$description.
					'</p>';
				}
				$html .=
			'</td>'.
		'</tr>';
		
		return $html;
	}
	
	protected $rules = array();
	
	protected $validated = false;

	protected $valid = true;
	
	protected $validationMessage;
	
	public function validate()
	{
		if($this->validated) {
			return $this->valid;
		}

		$this->validated = true;
		
		$value = $this->getValue();
		
		// special case: empty value
		if($value===null || $value==='') {
			if($this->required) {
				$this->valid = false;
				$this->validationMessage = __('This element is required.', 'EVEShipInfo');
			}
		} else {
			foreach($this->rules as $rule) {
				if($rule->validate($value)) {
					continue;
				}
				
				$this->valid = false;
				$this->validationMessage = $rule->getMessage();
				break;
			}
		}
		
		return $this->valid;
	}
	
	protected $classes = array();
	
	public function addClass($className)
	{
		if(!in_array($className, $this->classes)) {
			$this->classes[] = $className;
		}
		
		return $this;
	}
	
	protected $styles = array();
	
	public function setStyle($name, $value)
	{
		$this->styles[$name] = $value;
		return $this;
	}
	
	protected $required = false;
	
	public function setRequired($required=true)
	{
		$this->required = $required;
		return $this;
	}
	
	protected $defaultValue = null;
	
	public function setDefaultValue($value)
	{
		$this->defaultValue = $value;
		return $this;
	}
	
	protected function renderAttributes()
	{
		$atts = $this->attributes;
		$atts['id'] = $this->id;
		$atts['name'] = $this->name;
		$atts['class'] = implode(' ', $this->classes);
		$atts['style'] = $this->plugin->compileStyles($this->styles);		
		
		return $this->plugin->compileAttributes($atts);	
	}
	
   /**
    * Sets an attribute of the element.
    * @param string $name
    * @param string $value
    * @return EVEShipInfo_Admin_UI_Form_Element
    */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
		return $this;
	}
	
	abstract protected function _renderElement();
	
	protected $value;
	
	public function getValue()
	{
		if(isset($this->value)) {
			return $this->filter($this->value);
		}
		
		if(isset($_REQUEST[$this->name])) {
			return $this->filter($_REQUEST[$this->name]);
		}
		
		return $this->filter($this->form->getDefaultValue($this->name));
	}
	
   /**
    * Adds a filtering function that will be applied to
    * the value before it is validated.
    * 
    * @param function $filter
    * @return EVEShipInfo_Admin_UI_Form_Element
    */
	public function addFilter($filter)
	{
		$this->filters[] = $filter;
		return $this;
	}
	
	protected function filter($value)
	{
		if($value===null || $value==='') {
			return $value;
		}
		
		foreach($this->filters as $filter) {
			$value = call_user_func($filter, $value);
		}
		
		return $value;
	}
}