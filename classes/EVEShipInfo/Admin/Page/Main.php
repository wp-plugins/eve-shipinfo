<?php

class EVEShipInfo_Admin_Page_Main extends EVEShipInfo_Admin_Page
{
	public function getTabs()
	{
		$tabs = array(
			'Dashboard' => __('Dashboard', 'EVEShipInfo'),
        	'Help' => __('Help', 'EVEShipInfo'),
        	'Database' => __('Database reference', 'EVEShipInfo'),
        	'Shortcodes' => __('Shortcordes reference', 'EVEShipInfo'),
			'EFTImport' => __('EFT import', 'EVEShipInfo'),
		    'EFTFittings' => __('EFT fittings', 'EVEShipInfo')
        );
		
		return $tabs;
	}
	
	public function getTitle()
	{
		return __('EVE ShipInfo', 'EVEShipInfo');
	}
	
	protected function isTabEnabled($tabID)
	{
	    if($tabID == 'EFTFittings') {
	        $eft = $this->plugin->createEFTManager();
	        return $eft->hasFittings(); 
	    }
	    
		return true;
	}
}