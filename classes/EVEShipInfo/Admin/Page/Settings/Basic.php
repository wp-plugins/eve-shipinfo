<?php

class EVEShipInfo_Admin_Page_Settings_Basic extends EVEShipInfo_Admin_Page_Tab
{
   /**
    * @var EVEShipInfo_Admin_SettingsManager
    */
	protected $form;
	
	public function __construct($page)
	{
		parent::__construct($page);
		
		$this->form = $this->createSettings('eveshipinfo_settings');
		
		$section = $this->form->addSection('basic', __('Basic settings'));
		$section->addRadioGroup('enable_virtual_pages', __('Enable virtual pages?'))
			->addItem('yes', __('Yes, allow both virtual pages and popups'))
			->addItem('no', __('No, only use info popups'))
			->setDescription(__('When disabled, ship links will only point to popups, and the ship pages will show the blog\'s homepage.'));
	}
	
	public function getTitle()
	{
		return '';
	}
	
	protected function _render()
	{
		return $this->form->render();
	}
	
	protected $formSection = 'eveshipinfo_settings_section';
	
	protected $formPage = 'eveshipinfo_settings';
	
	public function initSettings()
	{
		$this->form->initSettings();
	}
}