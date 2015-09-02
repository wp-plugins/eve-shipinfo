<?php

class EVEShipInfo_Admin_Page_Main_EFTFittings extends EVEShipInfo_Admin_Page_Tab
{
	const ERROR_INVALID_FITTING_FORM_ACTION = 1601;
	
	public function getTitle()
	{
		return __('EFT fittings', 'EVEShipInfo');
	}
	
   /**
    * Only present if the fid request parameter is present.
    * @var EVEShipInfo_EFTManager_Fit
    */
	protected $fit;
	
	protected function configure()
	{
		$this->eft = $this->plugin->createEFTManager();
		
		if(isset($_REQUEST['fid'])) {
			$this->fit = $this->eft->getFittingByID($_REQUEST['fid']);
		}
		
		$this->registerAction(
			'edit', 
			__('Edit fitting', 'EVEShipInfo'),
			$this->ui->icon()->edit(),
			false
		);
		
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
		
		if(isset($_REQUEST['fits'])) {
			$this->handleActions();
		}
		
		return $this->renderList();
	}
	
	protected function renderList()
	{
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
							'<td>'.$filters->renderVisibilitySelect('list_visibility').'</td>'.
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
						'<th style="width:30px;padding-left:3px;">'.
							'<input type="checkbox" onclick="FittingsList.ToggleAll()" class="fits-toggler" title="'.__('Select / deselect all', 'EVEShipInfo').'"/>'.
						'</th>'.
						'<th>'.__('Fit name', 'EVEShipInfo').'</th>'.
						'<th>'.__('Ship', 'EVEShipInfo').'</th>'.
						'<th>'.__('Visibility', 'EVEShipInfo').'</th>'.
						'<th>'.__('Modified', 'EVEShipInfo').'</th>'.
						'<th style="width:8%">'.__('Fit ID', 'EVEShipInfo').'</th>'.
						'<th style="text-align:center;">'.__('Protected', 'EVEShipInfo').'</th>'.
					'</tr>'.
				'</thead>'.
				'<tbody>';
					if(empty($fits)) {
						$boxHTML .=
						'<tr>'.
							'<td colspan="7" class="text-info">'.
								'<span class="dashicons dashicons-info"></span> '.
								'<b>'.__('No fittings found matching these criteria.', 'EVEShipInfo').'</b>'.
							'</td>'.
						'</tr>';	
					} else {
						foreach($fits as $fit) {
							$public = $this->ui->icon()->visibilityPrivate()->makeDangerous().' '.__('Private', 'EVEShipInfo');
							if($fit->isPublic()) {
								$public = $this->ui->icon()->visibilityPublic()->makeSuccess().' '.__('Public', 'EVEShipInfo');
							}
							
							$invalid = '';
							if($fit->hasInvalidSlots()) {
								$invalid = $this->ui->icon()->warning()
								->makeDangerous()
								->cursorHelp()
								->setTitle(__('This fitting has some invalid slots.', 'EVEShipInfo'));
							}
							
							$boxHTML .=
							'<tr>'.
								'<td>'.
									'<input type="checkbox" name="fits[]" class="fit-checkbox" value="'.$fit->getID().'"/>'.
								'</td>'.
								'<td>'.
									'<a href="'.$fit->getAdminEditURL().'">'.
										$fit->getName().
									'</a> '.
									$invalid.
								'</td>'.
								'<td>'.$fit->getShipName().'</td>'.
								'<td>'.$public.'</td>'.
								'<td>'.$fit->getDateUpdatedPretty().'</td>'.
								'<td>'.$fit->getID().'</td>'.
								'<td style="text-align:center;">'.$fit->isProtectedPretty().'</td>'.
							'</tr>';
						}
					}
					$boxHTML .=
				'</tbody>'.
			'</table>'.
			'<br>'.
			__('With selected:', 'EVEShipInfo').'<br/>'.
			'<ul class="list-toolbar">'.
				'<li>'.
					$this->ui->button(__('Delete', 'EVEShipInfo'))
					->makeDangerous()
					->setIcon($this->ui->icon()->delete())
					->setName('action')
					->makeSubmit('delete').
				'</li>'.
				'<li class="list-toolbar-separator"></li>'.
				'<li>'.
					$this->ui->button(__('Make private', 'EVEShipInfo'))
					->setIcon($this->ui->icon()->visibilityPrivate())
					->setName('action')
					->makeSubmit('makePrivate').
				'</li>'.
				'<li>'.
					$this->ui->button(__('Make public', 'EVEShipInfo'))
					->setIcon($this->ui->icon()->visibilityPublic())
					->setName('action')
					->makeSubmit('makePublic').
				'</li>'.
				'<li class="list-toolbar-separator"></li>'.
				'<li>'.
					$this->ui->button(__('Protect', 'EVEShipInfo'))
					->setIcon($this->ui->icon()->protect())
					->setName('action')
					->makeSubmit('protect').
				'</li>'.
				'<li>'.
					$this->ui->button(__('Unprotect', 'EVEShipInfo'))
					->setIcon($this->ui->icon()->unprotect())
					->setName('action')
					->makeSubmit('unprotect').
				'</li>'.
			'</ul>'.
			'<div style="clear:both"></div>'.
		'</form>';
		
		$html = $this->ui->createStuffBox(__('Available fittings', 'EVEShipInfo'))
		->setIcon($this->ui->icon()->listView())
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
		
		$method = 'handleListAction_'.$_REQUEST['action'];
		if(method_exists($this, $method)) {
			$this->$method($selected);
		}
	}
	
   /**
    * Handles deleting a collection of fits.
    * @param EVEShipInfo_EFTManager_Fit[] $selected
    */
	protected function handleListAction_delete($selected)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($this->eft->deleteFitting($fit)) {
				$total++;
			}
		}
		
		if($total > 0) {
			$this->eft->save();
		}
		
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
	protected function handleListAction_makePrivate($selected)
	{
		$this->handleListAction_visibility($selected, EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE);
	}

	/**
	 * Handles making a collection of fits protected from import.
	 * @param EVEShipInfo_EFTManager_Fit[] $selected
	 */
	protected function handleListAction_protect($selected)
	{
		$this->handleListAction_protection($selected, true);
	}

	/**
	 * Handles making a collection of fits not protected from import.
	 * @param EVEShipInfo_EFTManager_Fit[] $selected
	 */
	protected function handleListAction_unprotect($selected)
	{
		$this->handleListAction_protection($selected, false);
	}
	
	protected function handleListAction_protection($selected, $protect)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($fit->setProtection($protect)) {
				$total++;
			}
		}
		
		$label = __('protected', 'EVEShipInfo');
		if(!$protect) {
			$label = __('not protected', 'EVEShipInfo');
		}
	
		if($total > 0) {
			$this->eft->save();
		}
	
		if($total==1) {
			return $this->addSuccessMessage(sprintf(
				__('The fitting %1$s was successfully marked as %2$s at %3$s.', 'EVEShipInfo'),
				$selected[0]->getName(),
				$label,
				date('H:i:s')
			));
		}
	
		if($total==0) {
			return $this->addErrorMessage(sprintf(
				__('All the selected fittings were already marked as %1$s.', 'EVEShipInfo'),
				$label
			));
		}
	
		$this->addSuccessMessage(sprintf(
			__('%1$s fittings were successfully marked as %2$s at %3$s.', 'EVEShipInfo'),
			count($selected),
			$label,
			date('H:i:s')
		));
	}
	
   /**
    * Handles making a collection of fits public.
    * @param EVEShipInfo_EFTManager_Fit[] $selected
    */
	protected function handleListAction_makePublic($selected)
	{
		$this->handleListAction_visibility($selected, EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC);
	}
	
	protected function handleListAction_visibility($selected, $visibility)
	{
		$total = 0;
		foreach($selected as $fit) {
			if($fit->setVisibility($visibility)) {
				$total++;
			}
		}		
		
		if($total > 0) {
			$this->eft->save();
		}
		
		$label = __('public', 'EVEShipInfo');
		if($visibility == EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE) {
			$label = __('private', 'EVEShipInfo');
		}
		
		if($total==1) {
			return $this->addSuccessMessage(sprintf(
				__('The fitting %1$s was successfully marked as %2$s at %3$s.', 'EVEShipInfo'),
				$selected[0]->getName(),
				$label,
				date('H:i:s')
			));
		} 
		
		if($total==0) {
			return $this->addErrorMessage(sprintf(
				__('All the selected fittings were already marked as %1$s.', 'EVEShipInfo'),
				$label	
			));
		} 
		
		$this->addSuccessMessage(sprintf(
			__('%1$s fittings were successfully marked as %2$s at %3$s.', 'EVEShipInfo'),
			count($selected),
			$label,
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
		
		if(isset($_REQUEST['list_visibility']) && $filters->visibilityExists($_REQUEST['list_visibility'])) {
			$filters->setVisibility($_REQUEST['list_visibility']);
		}

		return $filters;
	}
	
	public function renderAction_add()
	{
		$html = $this->renderFittingForm('add');
		return $html;
	}
	
	public function renderAction_edit()
	{
		$html = $this->renderFittingForm('edit');
		
		if($this->fit->hasInvalidSlots()) {
			$message = 
			'<p>'.
				'<b>'.__('Some module slots in this fit could not be recognized.', 'EVEShipInfo').'</b> '.
				__('This can happen for example when a fit uses old modules that have been renamed or removed from the game.', 'EVEShipInfo').' '.
				__('The following modules could not be recognized:', 'EVEShipInfo').
			'</p>'.
			'<ul>';
				$invalid = $this->fit->getInvalidSlots();
				foreach($invalid as $item) {
					$message .= '<li>'.$item['moduleName'].'</li>';
				} 
				$message .=
			'</ul>'.
			'<p>'.
				__('These modules have already been stripped from the fit below.', 'EVEShipInfo').' '.
				'<b>'.__('To remove this notice, simply save the fit as is to confirm removing the obsolete modules.', 'EVEShipInfo').'</b>'.
			'</p>';
				
			$sect = $this->ui->createStuffBox(__('Invalid slots detected', 'EVEShipInfo'));
			$sect->makeError();
			$sect->setContent($message);
			
			$html = $sect->render().$html;
		}
		
		return $html;
	}
	
	protected function createFittingForm($action, $defaultValues=array())
	{
		$form = $this->createForm('fitting', $defaultValues)
		->addButton(
			$this->ui->button(__('Cancel', 'EVEShipInfo'))
			->link($this->getURL())
		)
		->setSubmitLabel(__('Add now', 'EVEShipInfo'))
		->setSubmitIcon($this->ui->icon()->add());
		
		if($action=='edit') {
			$form->setSubmitLabel(__('Save now', 'EVEShipInfo'));
			$form->setSubmitIcon($this->ui->icon()->edit());
			$form->addStatic(__('Fit ID', 'EVEShipInfo'), '<code>'.$this->fit->getID().'</code>');
			$form->addStatic(__('Date added', 'EVEShipInfo'), $this->fit->getDateAddedPretty());
			$form->addStatic(__('Last modified', 'EVEShipInfo'), $this->fit->getDateUpdatedPretty());
			$form->addStatic(__('Shortcode', 'EVEShipInfo'), '<code>'.$this->fit->getShortcode().'</code>');
			$form->addStatic(__('Shortcode (custom name)', 'EVEShipInfo'), '<code>'.$this->fit->getShortcode(__('Custom name', 'EVEShipInfo')).'</code>');
		}
		
		$fitting = $form->addTextarea('fitting', __('EFT fitting', 'EVEShipInfo'))
		->addFilter('trim')
		->addCallbackRule(array($this, 'validateFit'), __('Could not recognize the format as an EFT fitting.'))
		->setRows(15)
		->matchRows()
		->setRequired()
		->setDescription(
			'<b>'.__('Howto:', 'EVEShipInfo').'</b> '.
			sprintf(
				__('Open the target fit in EFT, in the ship menu choose %1$s, and paste it here (press %2$s in the field).', 'EVEShipInfo'),
				'<code>Copy to clipboard</code>',
				'<code>CTRL+V</code>'
			).' '.
			__('All information, from the ship to the fit label will be retrieved automatically from the fit.', 'EVEShipInfo').
			'<br/>'.
			'<br/>'.
			__('When manually adding modules, ensure you write the name exactly as used ingame, including capitalization.').' '.
			__('The order of modules is irrelevant: they are are sorted automatically.').' '.
			__('The available slots on the ship are not checked, so you can add too many modules here.')
		);
		
		$labelEl = $form->addText('label', __('Label', 'EVEShipInfo'))
		->addFilter('trim')
		->addFilter('strip_tags')
		->addRegexRule('/\A[^,]+\z/', __('May not contain commas.'));
		
		if($action=='add') {
			$labelEl->setDescription(
				__('Optional:', 'EVEShipInfo').' '.
				__('Specify this if you wish to overwrite the label that comes with the fit.', 'EVEShipInfo')
			);
		} else {
			$labelEl->setRequired();
		}
		
		$form->addSelect('visibility', __('Visibility', 'EVEShipInfo'))
		->addOption(__('Public', 'EVEShipInfo'), EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC)
		->addOption(__('Private', 'EVEShipInfo'), EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE);
		
		$form->addCheckbox('protection', __('Protection', 'EVEShipInfo'))
		->setInlineLabel(__('Protect fit from import', 'EVEShipInfo'))
		->setDescription(__('If checked, this fit will be protected from any changes when importing fits from EFT.', 'EVEShipInfo'));
		
		$form->setDefaultElement($fitting);
		
		return $form;
	}		
		
	public function validateFit($value, EVEShipInfo_Admin_UI_Form_ValidationRule_Callback $rule, EVEShipInfo_Admin_UI_Form_Element $element)
	{
		$manager = $this->plugin->createEFTManager();
		$fit = $manager->parseFit($value);
		if($fit) {
			return true;
		}
		
		return false;
	}
	
	protected function renderFittingForm($action)
	{
		$defaultValues = array(
			'fitting' => '',
			'label' => '',
			'visibility' => EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC,
			'protection' => 'no'
		);
		
		$boxTitle = __('Add a new fit', 'EVEShipInfo');
		$boxIcon = $this->ui->icon()->add();
		
		if($action == 'edit') {
			$boxTitle = sprintf(
				__('Edit the %1$s fitting %2$s', 'EVEShipInfo'), 
				$this->fit->getShipName(), 
				'<b>"'.$this->fit->getName().'"</b>'
			);
			
			$boxIcon = $this->ui->icon()->edit();
			$defaultValues['fitting'] = $this->fit->toEFTString();
			$defaultValues['label'] = $this->fit->getName();
			$defaultValues['visibility'] = $this->fit->getVisibility();
			
			if($this->fit->isProtected()) {
				$defaultValues['protection'] = 'yes';
			}
		}
		
		$form = $this->createFittingForm($action, $defaultValues);

		if($action == 'edit') {
			$form->addHiddenVar('fid', $this->fit->getID());
		}
		
		if($form->validate()) {
			$values = $form->getValues();
			$method = 'handleFormAction_'.$action;
			if(!method_exists($this, $method)) {
				throw new EVEShipInfo_Exception(
					'Invalid fitting form action',
					sprintf(
						'The form hanlding method [%s] does not exist in the class [%s].',
						$method,
						get_class($this)
					),
					self::ERROR_INVALID_FITTING_FORM_ACTION	
				);
			}
			return $this->$method($form, $values);
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
		
		$html = $this->ui->createStuffBox($boxTitle)
		->setIcon($boxIcon)
		->setContent($boxHTML)
		->render();
		
		return $html;
	}
	
	protected function handleFormAction_add($form, $values)
	{
		$fit = $this->eft->addFromFitString(
			$values['fitting'], 
			$values['label'], 
			$values['visibility'], 
			true
		);
		
		$this->eft->save();
		
		$message = sprintf(
			__('The fitting %1$s was added successfully at %2$s.', 'EVEShipInfo'),
			'<i>'.$fit->getName().'</i>',
			date('H:i:s')
		);
		
		return $this->renderRedirect(
			$this->getURL(), 
			__('Back to the list', 'EVEShipInfo'), 
			__('Add a new fit', 'EVEShipInfo'), 
			$message
		);
	}
	
	protected function handleFormAction_edit($form, $values)
	{
		$this->fit->updateFromFitString($values['fitting']);
		$this->fit->setVisibility($values['visibility']);
		$this->fit->setName($values['label']);
		$this->fit->setProtection($values['protection']);
				
		if($this->fit->isModified()) 
		{
			$this->eft->save();
			
			$message = sprintf(
				__('The fitting %1$s was updated successfully at %2$s.', 'EVEShipInfo'),
				'<i>'.$this->fit->getName().'</i>',
				date('H:i:s')
			);
		} else {
			$message = sprintf(
				__('The fitting %1$s had no edits, and was not modified.', 'EVEShipInfo'),
				'<i>'.$this->fit->getName().'</i>'
			);
		}
		
		return $this->renderRedirect(
			$this->getURL(), 
			__('Back to the list', 'EVEShipInfo'),
			sprintf(
				__('Edit the %1$s fitting %2$s', 'EVEShipInfo'), 
				$this->fit->getShipName(), 
				'<b>"'.$this->fit->getName().'"</b>'
			),
			$message
		);
	}
}