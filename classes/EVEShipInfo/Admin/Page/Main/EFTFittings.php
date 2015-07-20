<?php

class EVEShipInfo_Admin_Page_Main_EFTFittings extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('EFT fittings', 'EVEShipInfo');
	}
	
	protected function configure()
	{
		$this->registerAction(
			'add', 
			__('Add new', 'EVEShipInfo'), 
			$this->ui->icon()->add()
		);
	}
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $eft;
	
	protected function _render()
	{
		/* @var $fit EVEShipInfo_EFTManager_Fit */
		
		$this->eft = $this->plugin->createEFTManager();
		
		if(isset($_REQUEST['fits'])) {
			$this->handleActions();
		}
		
		if(!$this->eft->hasFittings()) {
			return '';
		}
		
		$filters = $this->configureFilters();
		$fits = $filters->getFittings();
		
		$boxHTML = 
		'<p>'.
			__('The following is a list of all known fits, and the name to use with the shortcode to insert it in your posts.', 'EVEShipInfo').' '.
			sprintf(
				__('Have a look at the %sshortcode reference%s for examples on how to use this.', 'EVEShipInfo'),
				'<a href="admin.php?page=eveshipinfo_shortcodes&shortcode=EFTFit">',
				'</a>'
			).' '.
			__('If you mark fits as private, they will not be shown in the fittings tab in the ship info windows.', 'EVEShipInfo').' '.
			__('You can still link them manually with the fitting shortcodes though.', 'EVEShipInfo').
		'</p>'.
		'<form method="post" id="form_fittings">'.
			'<div class="shipinfo-table-controls">'.
				'<table class="shipinfo-form-table shipinfo-list-ordering">'.
					'<tbody>'.
						'<tr>'.
							'<td>'.__('Order by:', 'EVEShipInfo').'</td>'.
							'<td>'.$filters->renderOrderBySelect().'</td>'.
							'<td>'.$filters->renderOrderDirSelect().'</td>'.
							'<td>'.
								'<button type="submit" name="apply_sort" class="button">'.
									__('Apply', 'EVEShipInfo').
								'</button>'.
							'</td>'.
						'</tr>'.
					'</tbody>'.
				'</table>'.
				'<table class="shipinfo-form-table shipinfo-list-filtering">'.
					'<tbody>'.
						'<tr>'.
							'<td><input type="text" name="filter" id="field_filter" value="'.$filters->getSearch().'" placeholder="'.__('Search, e.g. Abaddon', 'EVEShipInfo').'"/></td>'.
							'<td>'.$filters->renderVisibilitySelect().'</td>'.
							'<td>'.
								'<button type="submit" name="apply_filter" value="yes" class="button"/>'.
									'<span class="dashicons dashicons-update"></span> '.
									__('Apply', 'EVEShipInfo').
								'</button>'.
							'</td>'.
							'<td>'.
								'<button type="button" class="button" onclick="jQuery(\'#field_filter\').val(\'\');jQuery(\'#form_fittings\').submit();">'.
									'<span class="dashicons dashicons-no-alt"></span> '.
									__('Reset', 'EVEShipInfo').
								'</button>'.
							'</td>'.
						'</tr>'.
					'</tbody>'.
				'</table>'.
			'</div>'.
			'<table class="wp-list-table widefat fixed">'.
				'<thead>'.
					'<tr>'.
						'<th>'.
							'<input type="checkbox" onclick="FittingsList.ToggleAll()" class="fits-toggler" title="'.__('Select / deselect all', 'EVEShipInfo').'"/>'.
						'</th>'.
						'<th>'.__('Fit name', 'EVEShipInfo').'</th>'.
						'<th>'.__('Ship', 'EVEShipInfo').'</th>'.
						'<th>'.__('Visibility', 'EVEShipInfo').'</th>'.
						'<th>'.__('Date added', 'EVEShipInfo').'</th>'.
						'<th>'.__('Fit ID', 'EVEShipInfo').'</th>'.
					'</tr>'.
				'</thead>'.
				'<tbody>';
					if(empty($fits)) {
						$boxHTML .=
						'<tr>'.
							'<td colspan="6" class="text-info">'.
								'<span class="dashicons dashicons-info"></span> '.
								'<b>'.__('No fittings found matching these criteria.', 'EVEShipInfo').'</b>'.
							'</td>'.
						'</tr>';	
					} else {
						foreach($fits as $fit) {
							$public = '<span class="dashicons dashicons-lock icon-private"></span> '.__('Private', 'EVEShipInfo');
							if($fit->isPublic()) {
								$public = '<span class="dashicons dashicons-visibility icon-public"></span> '.__('Public', 'EVEShipInfo');
							}
							
							$boxHTML .=
							'<tr>'.
								'<td>'.
									'<input type="checkbox" name="fits[]" class="fit-checkbox" value="'.$fit->getID().'"/>'.
								'</td>'.
								'<td>'.$fit->getName().'</td>'.
								'<td>'.$fit->getShipName().'</td>'.
								'<td>'.$public.'</td>'.
								'<td>'.$fit->getDateAddedPretty().'</td>'.
								'<td>'.$fit->getID().'</td>'.
							'</tr>';
						}
					}
					$boxHTML .=
				'</tbody>'.
			'</table>'.
			'<p>'.
				__('With selected:', 'EVEShipInfo').'<br/>'.
				'<button type="submit" class="button" name="action" value="delete">'.
					'<span class="dashicons dashicons-no-alt"></span> '.
					__('Delete', 'EVEShipInfo').
				'</button> '.
				'<button type="submit" class="button" name="action" value="makePrivate">'.
					'<span class="dashicons dashicons-lock"></span> '.
					__('Make private', 'EVEShipInfo').
				'</button> '.
				'<button type="submit" class="button" name="action" value="makePublic">'.
					'<span class="dashicons dashicons-visibility"></span> '.
					__('Make public', 'EVEShipInfo').
				'</button> '.
			'</p>'.
		'</form>';
		
		$html = $this->ui->createStuffBox(sprintf(
			'<span class="dashicons dashicons-list-view"></span> '.
			__('%s available fittings, uploaded on %s', 'EVEShipInfo'), 
			$this->eft->countFittings(),
			$this->eft->getLastModified()->format('d.m.Y H:i:s')
		))
		->setContent($boxHTML)
		->render();
		
		return $html;
	}
	
   /**
    * Handles the list being submitted. Collects selected fits and dispatches 
    * to the method according to the selected list action.
    */
	protected function handleActions()
	{
		$selected = array();
		foreach($_REQUEST['fits'] as $fitID) {
			if($this->eft->idExists($fitID)) {
				$selected[] = $this->eft->getFittingByID($fitID);
			}
		}
		
		if(empty($selected)) {
			$this->addErrorMessage(__('No valid ships were selected, no changes made.', 'EVEShipInfo'));
			return;
		}
		
		$method = 'handleActions_'.$_REQUEST['action'];
		if(method_exists($this, $method)) {
			$this->$method($selected);
		}
	}
	
   /**
    * Handles deleting a collection of fits.
    * @param EVEShipInfo_EFTManager_Fit[] $selected
    */
	protected function handleActions_delete($selected)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($this->eft->deleteFitting($fit)) {
				$total++;
			}
		}
		
		$this->eft->save();
		
		if($total==1) {
			return $this->addSuccessMessage(sprintf(
				__('The fitting %1$s was deleted successfully at %2$s.', 'EVEShipInfo'),
				$selected[0]->getName(),
				date('H:i:s')
			));
		}

		if($total==0) {
			return $this->addErrorMessage(
				__('All the selected fittings were already deleted.', 'EVEShipInfo')
			);
		}
		
		$this->addSuccessMessage(sprintf(
			__('%1$s fittings were deleted successfully at %2$s.', 'EVEShipInfo'),
			count($selected),
			date('H:i:s')
		));
	}
	
   /**
    * Handles making a collection of fits private.
    * @param EVEShipInfo_EFTManager_Fit[] $selected
    */
	protected function handleActions_makePrivate($selected)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($fit->makePrivate()) {
				$total++;
			}
		}		
		
		$this->eft->save();
		
		$total = count($selected);
		if($total==1) {
			return $this->addSuccessMessage(sprintf(
				__('The fitting %1$s was successfully marked as private at %2$s.', 'EVEShipInfo'),
				$selected[0]->getName(),
				date('H:i:s')
			));
		} 
		
		if($total==0) {
			return $this->addErrorMessage(
				__('All the selected fittings were already marked as private.', 'EVEShipInfo')	
			);
		} 
		
		$this->addSuccessMessage(sprintf(
			__('%1$s fittings were successfully marked as private at %2$s.', 'EVEShipInfo'),
			count($selected),
			date('H:i:s')
		));
	}

   /**
    * Handles making a collection of fits public.
    * @param EVEShipInfo_EFTManager_Fit[] $selected
    */
	protected function handleActions_makePublic($selected)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($fit->makePublic()) {
				$total++;
			}
		}		
		
		$this->eft->save();
		
		$total = count($selected);
		if($total==1) {
			return $this->addSuccessMessage(sprintf(
				__('The fitting %1$s was successfully marked as private at %2$s.', 'EVEShipInfo'),
				$selected[0]->getName(),
				date('H:i:s')
			));
		} 
		
		if($total==0) {
			return $this->addErrorMessage(
				__('All the selected fittings were already marked as private.', 'EVEShipInfo')	
			);
		} 
		
		$this->addSuccessMessage(sprintf(
			__('%1$s fittings were successfully marked as private at %2$s.', 'EVEShipInfo'),
			count($selected),
			date('H:i:s')
		));
	}
	
   /**
    * Creates and configures the filters used for the fittings list.
    * @return EVEShipInfo_EFTManager_Filters
    */
	protected function configureFilters()
	{
		$filters = $this->eft->getFilters();
		
		$filter = '';
		if(isset($_REQUEST['filter'])) {
			$filter = htmlspecialchars(trim(strip_tags($_REQUEST['filter'])), ENT_QUOTES, 'UTF-8');
			if(!empty($filter)) {
				$filters->setSearch($filter);
			}
		}
		
		if(isset($_REQUEST['order_by']) && $filters->orderFieldExists($_REQUEST['order_by'])) {
			$filters->setOrderBy($_REQUEST['order_by']);
		}
		
		if(isset($_REQUEST['order_dir']) && $filters->orderDirExists($_REQUEST['order_dir'])) {
			$filters->setOrderDir($_REQUEST['order_dir']);
		}
		
		if(isset($_REQUEST['visibility']) && $filters->visibilityExists($_REQUEST['visibility'])) {
			$filters->setVisibility($_REQUEST['visibility']);
		}

		return $filters;
	}
	
	public function renderAction_add()
	{
		$html = $this->renderFittingForm('add');
		return $html;
	}
	
	protected function createFittingForm()
	{
		$form = $this->createForm('fitting')
		->addButton(
			$this->ui->button(__('Cancel', 'EVEShipInfo'))
			->link($this->getURL())
		)
		->setSubmitLabel(
			$this->ui->icon()->add() . ' ' .
			__('Add now', 'EVEShipInfo')
		);
		
		$fitting = $form->addTextarea('fitting', __('EFT fitting'))
		->addFilter('trim')
		->setRows(15)
		->setRequired()
		->setDescription(
			'<b>'.__('Howto:', 'EVEShipInfo').'</b> '.
			sprintf(
				__('Open the target fit in EFT, in the ship menu choose %1$s, and paste it here (press %2$s in the field).', 'EVEShipInfo'),
				'<code>Copy to clipboard</code>',
				'<code>CTRL+V</code>'
			).' '.
			__('All information, from the ship to the fit label will be retrieved automatically from the fit.', 'EVEShipInfo')
		);
		
		$form->addText('label', __('Label', 'EVEShipInfo'))
		->addFilter('trim')
		->setDescription(
			__('Optional:', 'EVEShipInfo').' '.
			__('Specify this if you wish to overwrite the label of the fit.', 'EVEShipInfo')
		);
		
		$form->addSelect('visibility', __('Visibility'))
		->addOption(__('Public', 'EVEShipInfo'))
		->addOption(__('Private', 'EVEShipInfo'));
		
		$form->setDefaultElement($fitting);
		
		return $form;
	}		
		
	protected function renderFittingForm($action)
	{
		$form = $this->createFittingForm();

		if($form->validate()) {
			
		}
		
		$boxHTML = '';
		
		switch($action) {
			case 'add':
				$boxHTML .=
				'<p>'.
					__('The following lets you manually add a new fit to the EFT fittings collection.', 'EVEShipInfo').
				'</p>';
				break;
		}
		
		$boxHTML .= $form->render();
		
		$html = $this->ui->createStuffBox(__('Add a new fit', 'EVEShipInfo'))
		->setIcon($this->ui->icon()->add())
		->setContent($boxHTML)
		->render();
		
		return $html;
	}
}

/*
[Exequror Navy Issue, COSMOS]
Imperial Navy Medium Armor Repairer
Damage Control II
Energized Adaptive Nano Membrane II
Energized Adaptive Nano Membrane II
Energized Explosive Membrane II
Magnetic Field Stabilizer II

Tracking Computer II, Optimal Range Script
Tracking Computer II, Optimal Range Script
Cap Recharger II
Republic Fleet 10MN Afterburner

Heavy Neutron Blaster II, Void M
Heavy Neutron Blaster II, Void M
Heavy Neutron Blaster II, Void M
Heavy Neutron Blaster II, Void M
Small Tractor Beam II

Medium Capacitor Control Circuit I
Medium Capacitor Control Circuit I
Medium Capacitor Control Circuit I

Salvage Drone I x5




[Legion, Complex Specialist]
Centum A-Type Medium Armor Repairer
Armor Thermic Hardener II
Armor EM Hardener II
Tairei's Modified Energized Adaptive Nano Membrane
Imperial Navy Heat Sink

Federation Navy Stasis Webifier
Republic Fleet 10MN Afterburner
Data Analyzer II
Relic Analyzer II

Heavy Pulse Laser II, Conflagration M
Heavy Pulse Laser II, Conflagration M
Heavy Pulse Laser II, Conflagration M
Improved Cloaking Device II
Salvager II
Core Probe Launcher II, Core Scanner Probe I
Small Tractor Beam II

Medium Capacitor Control Circuit I
Medium Energy Burst Aerator I
Medium Nanobot Accelerator I

Legion Defensive - Adaptive Augmenter
Legion Electronics - Emergent Locus Analyzer
Legion Engineering - Capacitor Regeneration Matrix
Legion Propulsion - Fuel Catalyst
Legion Offensive - Drone Synthesis Projector

Valkyrie II x5
Hammerhead II x5
*/
	
	
	
	
	