<?php

class EVEShipInfo_Admin_UI_Form_Element_Upload extends EVEShipInfo_Admin_UI_Form_ElementInput
{
	public function getType()
	{
		return 'file';
	}
	
   /**
    * Sets the mime type this element accepts, e.g. "text/html".
    * 
    * @param string $mime
    * @return EVEShipInfo_Admin_UI_Form_Element
    */
	public function setAccept($mime)
	{
		return $this->setAttribute('accept', $mime);
	}
	
	public function validate()
	{
		if($this->validated) {
			return $this->valid;
		}

		$this->validated = true;
		$this->valid = false;
		
		$this->clearSetting('name');
		$this->clearSetting('content');
		
		if(!isset($_FILES[$this->name])) {
			$this->validationMessage = __('No uploaded file found.', 'EVEShipInfo');
			return false;
		}
		
		switch($_FILES[$this->name]['error']) {
			case UPLOAD_ERR_FORM_SIZE:
			case UPLOAD_ERR_INI_SIZE:
				$this->validationMessage = __('The uploaded file is too big.', 'EVEShipInfo');
				return false;
				
			case UPLOAD_ERR_PARTIAL:
				$this->validationMessage = __('The file was only partially uploaded.', 'EVEShipInfo');
				return false; 

			case UPLOAD_ERR_NO_FILE:
				$this->validationMessage = __('No file uploaded.', 'EVEShipInfo');
				return false;

			case UPLOAD_ERR_EXTENSION:
			case UPLOAD_ERR_CANT_WRITE:
			case UPLOAD_ERR_NO_TMP_DIR:
				$this->validationMessage = __('Could not write uploaded file to disk, server configuration error.', 'EVEShipInfo');
				return false;
		}
		
		$content = @file_get_contents($_FILES[$this->name]['tmp_name']);
		if(!$content) {
			$this->validationMessage = __('Could not open uploaded file.', 'EVEShipInfo');
			return false;	
		}
		
		$content = trim($content);
		if(empty($content)) {
			$this->validationMessage = __('The uploaded file was empty.', 'EVEShipInfo');
			return false;
		}
		
		$this->valid = true;
		
		$this->setSetting('name', $_FILES[$this->name]['name']);
		$this->setSetting('content', $content);
		
		return true;
	}
	
	public function getValue()
	{
		if(!$this->form->isSubmitted()) {
			return '';
		}
		
		return $this->getSetting('name');
	}
	
	public function getContent()
	{
		if(!$this->form->isSubmitted()) {
			return '';
		}
		
		return $this->getSetting('content');
	}
}