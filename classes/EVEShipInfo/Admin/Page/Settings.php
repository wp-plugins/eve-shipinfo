<?php

class EVEShipInfo_Admin_Page_Settings extends EVEShipInfo_Admin_Page
{
	public function getTabs()
	{
		return array(
        	'Basic' => __('Basic settings', 'EVEShipInfo'),
        	//'Help' => __('Help', 'EVEShipInfo'),
        	//'Info' => __('Database reference', 'EVEShipInfo'),
        	//'Shortcodes' => __('Shortcordes reference', 'EVEShipInfo')
        );
	}
	
	public function getTitle()
	{
		return __('EVE ShipInfo Settings', 'EVEShipInfo');
	}
}