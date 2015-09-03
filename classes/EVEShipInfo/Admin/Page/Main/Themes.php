<?php

class EVEShipInfo_Admin_Page_Main_Themes extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('Themes', 'EVEShipInfo');
	}
	
   /**
    * @var EVEShipInfo_Admin_UI_Form
    */
	protected $form;
	
	protected function _render()
	{
		$this->createThemeForm();
		
		if($this->form->isSubmitted() && $this->form->validate()) {
			$values = $this->form->getValues();
			$this->plugin->setThemeID($values['themeID']);
			$this->addSuccessMessage(sprintf(
				__('The frontend theme was successfully set to %1$s at %2$s.', 'EVEShipInfo'),
				$this->plugin->getThemeLabel(),
				date('H:i:s')
			));
		}
		
		$box = $this->ui->createStuffBox(__('Frontend themes', 'EVEShipInfo'))
		->setIcon($this->ui->icon()->theme())
		->setAbstract(__('This lets you choose among one of the bundled frontend themes for the ship popups and EFT fittings.', 'EVEShipInfo'))
		->setContent($this->form->render());
		
		return $box->render();
	}
	
	protected function createThemeForm()
	{
		$themes = $this->plugin->getThemes();
		$theme = $this->plugin->getThemeID();
		
		$form = $this->createForm('themes', array('themeID' => $theme));
		$form->setSubmitLabel(__('Choose theme', 'EVEShipInfo'));
		$form->setSubmitIcon($this->ui->icon()->theme());
		
		$group = $form->addRadioGroup('themeID', __('Frontend theme', 'EVEShipInfo'));
		foreach($themes as $themeID => $def) {
			$group->addItem(
				$themeID, 
				'<b>'.$def['label'].'</b><br/><br/>'.
				'<i>'.$def['description'].'</i><br/>'.
				'<img src="'.$this->plugin->getURL().'/assets/theme-'.$themeID.'.jpg"/>'
			);
		}
		
		$this->form = $form;
	}
}