<?php

class EVEShipInfo_Admin_Page_Main_Database extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('Database reference', 'EVEShipInfo');
	}
	
	public function render()
	{
		$collection = $this->plugin->createCollection();
		
		$races = $collection->getRaces();
		$html = 
		'<p>'.
			__('The following is a <b>reference for items</b> you can use in combination with the plugin\'s shortcodes.', 'EVEShipInfo').' '.
			__('Whenever you need to specify names of things like races or ship groups, you can look them up here.', 'EVEShipInfo').' '.
			__('Note:', 'EVEShipInfo').' '.__('These lists are generated dynamically from the integrated ships database, so they will always be accurate for the version of the plugin you have installed.', 'EVEShipInfo').
		'</p>'.
		'<h3>'.__('Races', 'EVEShipInfo').'</h3>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.	
				'<tr>'.
					'<th>'.__('ID', 'EVEShipInfo').'</th>'.
					'<th>'.__('Name', 'EVEShipInfo').'</th>'.
					'<th>'.__('Shortcode name', 'EVEShipInfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($races as $id => $name) {
					$html .=
					'<tr>'.
						'<td>'.$id.'</td>'.
						'<td>'.$name.'</td>'.
						'<td><code>'.strtolower($name).'</code></td>'.
					'</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
				
		$filter = $collection->createFilter();
		$groups = $filter->getGroups();
		$html .=
		'<h3>'.__('Groups', 'EVEShipInfo').'</h3>'.
		'<p>'.
			__('These are all available ship groups in the database.', 'EVEShipInfo').' '.
			__('Note:', 'EVEShipInfo').' '.__('The first groups in the list are special convenience groups that automatically select all ship groups of the same hull size.', 'EVEShipInfo').
		'</p>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.
				'<tr>'.
					'<th>'.__('ID', 'EVEShipInfo').'</th>'.
					'<th>'.__('Name', 'EVEShipInfo').'</th>'.
					'<th>'.__('Shortcode name', 'EVEShipInfo').'</th>'.
					'<th>'.__('Special', 'EVEShipInfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($groups as $id => $name) {
					$special = '';
					$virtual = $filter->getVirtualGroupGroupNames($id);
					if($virtual) {
						$special = implode(', ', $virtual);
					}
					
				    $html .=
				    '<tr>'.
					    '<td>'.$id.'</td>'.
					    '<td>'.$name.'</td>'.
					    '<td><code>'.strtolower($name).'</code></td>'.
					    '<td>'.$special.'</td>'.
				    '</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
		
		$ships = $filter->getShips();
		$html .=
		'<h3>'.__('Ships', 'EVEShipInfo').'</h3>'.
		'<p>'.
			__('These are all available ships in the database.', 'EVEShipInfo').' '.
		'</p>'.
		'<table class="wp-list-table widefat">'.
			'<thead>'.
				'<tr>'.
					'<th>'.__('ID', 'EVEShipInfo').'</th>'.
					'<th>'.__('Name', 'EVEShipInfo').'</th>'.
				'</tr>'.
			'</thead>'.
			'<tbody>';
				foreach($ships as $ship) {
				    $html .=
				    '<tr>'.
					    '<td>'.$ship->getID().'</td>'.
					    '<td>'.$ship->getName().'</td>'.
				    '</tr>';
				}
				$html .=
			'</tbody>'.
		'</table>';
						
		return $html;
	}
}