<?php

class EVEShipInfo_Admin_Page_Main_Database extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('Database reference', 'EVEShipInfo');
	}
	
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
   /**
    * @var EVEShipInfo_Collection_Filter
    */
	protected $filter;
	
	protected function _render()
	{
		$this->collection = $this->plugin->createCollection();
		$this->filter = $this->collection->createFilter();
		
		$html = 
		'<p>'.
			__('The following is a <b>reference for items</b> you can use in combination with the plugin\'s shortcodes.', 'EVEShipInfo').' '.
			__('Whenever you need to specify names of things like races or ship groups, you can look them up here.', 'EVEShipInfo').' '.
			__('Note:', 'EVEShipInfo').' '.__('These lists are generated dynamically from the integrated ships database, so they will always be accurate for the version of the plugin you have installed.', 'EVEShipInfo').
		'</p>'.
		$this->renderRaces().
		$this->renderShipGroups().
		$this->renderShips();
		
		return $html;
	}
	
	protected function renderRaces()
	{
		$races = $this->collection->getRaces();
		
		$boxHTML = 
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
					$boxHTML .=
					'<tr>'.
						'<td>'.$id.'</td>'.
						'<td>'.$name.'</td>'.
						'<td><code>'.strtolower($name).'</code></td>'.
					'</tr>';
				}
				$boxHTML .=
			'</tbody>'.
		'</table>';
				
		return $this->ui->createStuffBox(__('Races', 'EVEShipInfo'))
			->setContent($boxHTML)
			->setCollapsed()
			->render();
	}
	
	protected function renderShipGroups()
	{
		$groups = $this->filter->getGroups();
		
		$html =
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
					$virtual = $this->filter->getVirtualGroupGroupNames($id);
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
				
		return $this->ui->createStuffBox(__('Ship groups', 'EVEShipInfo'))
			->setAbstract(
			    __('These are all available ship groups in the database.', 'EVEShipInfo').' '.
		    	__('Note:', 'EVEShipInfo').' '.__('The first groups in the list are special convenience groups that automatically select all ship groups of the same hull size.', 'EVEShipInfo')
		    )
			->setContent($html)
			->setCollapsed()
			->render();
	}
	
	protected function renderShips()
	{
		$ships = $this->filter->getShips();
		
		$html =
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
						
		return $this->ui->createStuffBox(__('Ships', 'EVEShipInfo'))
			->setAbstract(__('These are all available ships in the database.', 'EVEShipInfo'))
			->setCollapsed()
			->setContent($html)
			->render();
	}
}