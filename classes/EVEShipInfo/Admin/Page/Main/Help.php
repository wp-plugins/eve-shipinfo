<?php

class EVEShipInfo_Admin_Page_Main_Help extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('Help and documentation', 'EVEShipInfo');
	}
	
	public function render()
	{
		return 
		'<b>'.__('Welcome to the EVE ShipInfo documentation.').'</b> '.
		__('Please choose a destination in the navigation above.');
	}
}