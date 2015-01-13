<?php

class EVEShipInfo_Admin_Page_Main extends EVEShipInfo_Admin_Page
{
	public function getTabs()
	{
		return array(
			'Dashboard' => __('Dashboard', 'EVEShipInfo'),
        	'Help' => __('Help', 'EVEShipInfo'),
        	'Database' => __('Database reference', 'EVEShipInfo'),
        	'Shortcodes' => __('Shortcordes reference', 'EVEShipInfo'),
			'EFTFittings' => __('EFT fittings', 'EVEShipInfo')
        );
	}
	
	public function getTitle()
	{
		return __('EVE ShipInfo', 'EVEShipInfo');
	}
}