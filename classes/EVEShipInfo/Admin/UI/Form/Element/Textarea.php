<?php

class EVEShipInfo_Admin_UI_Form_Element_Textarea extends EVEShipInfo_Admin_UI_Form_Element
{
	protected function _renderElement()
	{
		$html = 
		'<textarea'.$this->renderAttributes().'>'.$this->getValue().'</textarea>';
			
		return $html;
	}
	
	public function setRows($rows)
	{
		return $this->setAttribute('rows', $rows);
	}
}
