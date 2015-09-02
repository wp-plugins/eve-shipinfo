<?php

class EVEShipInfo_Admin_Page_Main_EFTImport extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('EFT import', 'EVEShipInfo');
	}
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $eft;
	
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
	protected function _render()
	{
		/* @var $fit EVEShipInfo_EFTManager_Fit */
		
		$this->eft = $this->plugin->createEFTManager();
		$this->collection = $this->plugin->createCollection();
		
		$this->createImportForm();
		
	    if($this->form->isSubmitted() && $this->form->validate()) {
			$this->processUpload();
		}
		
		if(isset($_REQUEST['confirmDelete']) && $_REQUEST['confirmDelete']) {
			$this->processDelete();
		}
		
		$html = 
		$this->renderForm().
		$this->renderMaintenance();
		
		return $html;
	}
	
	protected function renderMaintenance()
	{
	    if(!$this->eft->hasFittings()) {
	    	return '';
	    }
	    
	    $confirmText =
	    __('All existing fits will be deleted permanently.', 'EVEShipInfo').' '.
	    __('This cannot be undone, are you sure?', 'EVEShipInfo');
	    
	    $box = $this->ui->createStuffBox(__('Fittings maintenance', 'EVEShipInfo'));
	    $box->setCollapsed();
	    $box->setContent(
	    	'<script>'.
	    		'function ConfirmDeleteFits()'.
    			'{'.
    				"if(confirm('".$confirmText."')) {".
    					"document.location = '?page=eveshipinfo_eftimport&confirmDelete=yes'".
    				'}'.
    			'}'.
    		'</script>'.
	    	'<a href="javascript:void(0)" onclick="ConfirmDeleteFits()" class="button" title="'.__('Displays a confirmation dialog.', 'EVEShipInfo').'">'.
	   		 	__('Delete all fits...', 'EVEShipInfo').
	    	'</a>'
	    );
	    
	    return $box->render();
	}
	
   /**
    * @var EVEShipInfo_Admin_UI_Form
    */
	protected $form;
	
	protected function createImportForm()
	{
		$form = $this->createForm(
			'import',
			array(
				'mode' => 'merge',
				'visibility' => EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC,
				'ignore_protected' => 'yes'
			)
		);
		
		$form->setSubmitLabel(__('Upload and import', 'EVEShipInfo'));
		$form->setSubmitIcon($this->ui->icon()->upload());
		
		$form->addUpload('file', __('EFT Export XML file', 'EVEShipInfo'))
		->setRequired()
		->setAccept('text/xml')
		->setDescription(
			'<b>'.__('Howto:', 'EVEShipInfo').'</b> '.
			__('Open EFT, select File &gt; Import/Export to EVE &gt; Save all setups to one XML file...', 'EVEShipInfo').' '.
			__('Save the file where you like, then upload it here.', 'EVEShipInfo')
		);
		
		$form->addRadioGroup('mode', __('Import mode', 'EVEShipInfo'))
		->addItem('fresh', '<b>'.__('Clean:', 'EVEShipInfo').'</b> '.__('Delete all existing fits before the import', 'EVEShipInfo'))
		->addItem('merge', '<b>'.__('Merge:', 'EVEShipInfo').'</b> '.__('Add new fits, replace existing ones and keep all others', 'EVEShipInfo'))
		->addItem('new', '<b>'.__('New only:', 'EVEShipInfo').'</b> '.__('Only add new fits, leave existing untouched', 'EVEShipInfo'))
		->setDescription(
			__('Specifies what to do with already existing fits and those you are importing.', 'EVEShipInfo').' '.
			'<b>'.__('Note:', 'EVEShipInfo').'</b> '.
			__('The fitting names are used to match existing fittings.', 'EVEShipInfo').' '.
			__('If you changed some names in EFT, it is best to use the merge option.', 'EVEShipInfo')
		);
		
		$form->addSelect('visibility', __('Visibility'))
		->addOption(__('Public', 'EVEShipInfo'), EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC)
		->addOption(__('Private', 'EVEShipInfo'), EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE)
		->setDescription(__('The default visibility to use for all imported fits.', 'EVEShipInfo'));
		
		$form->addCheckbox('ignore_protected', __('Protection', 'EVEShipInfo'))
		->setInlineLabel(__('Ignore protected fittings', 'EVEShipInfo'))
		->setDescription(
			__('If checked, any fittings that are set as protected will be left entirely untouched by the import.', 'EVEShipInfo').' '.
			__('If a fitting to import has the same name as a protected one, it will not be imported.', 'EVEShipInfo').' '.
			__('If unchecked, protected fittings will be deleted and updated like any other.', 'EVEShipInfo')
		);
		
		$this->form = $form;
	}
	
	protected function renderForm()
	{
		return $this->ui->createStuffBox(
			'<span class="dashicons dashicons-upload"></span> '.
			__('Upload EFT export', 'EVEShipInfo')
		)
		->setAbstract(
			__('To easily share EFT fits with your readers in your posts, you can upload an EFT export here.', 'EVEShipInfo').' '.
			__('Once you have uploaded your fits, you can use the dedicated shortcodes to display them.', 'EVEShipInfo').' '.
			'<b>'.__('Note:', 'EVEShipInfo').'</b> '.
			__('The EFT export format is somewhat limited.', 'EVEShipInfo').' '.
			__('It does not include any implants or turret/launcher charges you may have used.', 'EVEShipInfo').' '.
			__('Alternatively, you can add single fittings manually or edit them after the import.')
		)
		->setContent($this->form->render())
		->render();
	}
	
	protected function processDelete()
	{
		$this->plugin->clearOption('fittings');

		$this->eft->reload();
		
		$this->addSuccessMessage(
			sprintf(
			    __('All fittings have been deleted successfully at %1$s','EVEShipInfo'), 
			    date('H:i:s')
		    )    
	    );
	}
	
	protected $nameHashes;
	
   /**
    * Processes the upload of an EFT fittings XML file: 
    * parses the XML, and stores the new data according
    * to the selected import mode.
    */
	protected function processUpload()
	{
		$values = $this->form->getValues();

		$xml = $this->form->getElementByName('file')->getContent();
		
		$root = @simplexml_load_string($xml);
		if(!$root) {
			$this->addErrorMessage(__('The uploaded XML file could not be read, it is possibly malformed or not an XML file.', 'EVEShipInfo'));
			return;
		}
		
		// to read the xml, we use the json encode + decode trick,
		// which magically creates an array with everything in it.
		$encoded = json_encode($root);
		$data = json_decode($encoded, true);
		if(!isset($data['fitting'])) {
			$this->addErrorMessage(__('The fitting data could not be found in the XML file.', 'EVEShipInfo'));
			return false;
		}
		
		$fits = array();
		foreach($data['fitting'] as $fit) {
			$def = $this->parseFit($fit);
			if($def===false) {
				continue;
			}
			
			$fits[] = $def;
		}
		
		if(empty($fits)) {
			$this->addErrorMessage(__('No fittings found in the XML file.', 'EVEShipInfo'));
			return false;
		}
		
		$ignore = false;
		if($values['ignore_protected']=='yes') {
			$ignore = true;
		}
		
		$this->processFits($fits, $values['visibility'], $ignore, $values['mode']);
		
		$this->eft->save();
	}
	
	protected function processFits($fits, $visibility, $ignoreProtected, $mode)
	{
		if($mode=='fresh') {
			$this->eft->clear($ignoreProtected);
		}
		
		$existing = $this->eft->countFittings();
		$new = 0;
		$updated = 0;
		$errors = 0;
		$protected = 0;
		
		foreach($fits as $def) {
			// check if a fit with the same name already exists
			$fit = $this->eft->getFittingByName($def['name'], $def['ship']);
			
			if(!$fit) {
				$fit = $this->eft->addFromFitString($def['fitString'], null, $visibility);
				if(!$fit) {
					$errors++;
					continue;
				}
				$new++;
			}
			// in new mode there are no updates, only new fits,
			// and in fresh mode only protected fits are there, 
			// and they should not be updated (in fresh mode with
			// the igore mode off, they will all have been deleted) 
			else if($mode != 'new') 
			{
				// if we are not ignoring protection and the fit is protected, 
				// do not modify it.
				if($ignoreProtected && $fit->isProtected()) {
					$protected++;
					continue;
				}
				
				// this fit must be a duplicate of an already imported
				// fit during this import session - can happen :)
				if($mode=='fresh') {
					$errors++;
					continue;
				}
				
				if($fit->updateFromFitString($def['fitString'])) {
					$updated++;
				}
			}
		}
		
		$kept = $existing - $updated;
		
		if($new==0) { $new = __('none', 'EVEShipInfo');	}
		if($updated==0) { $updated = __('none', 'EVEShipInfo');	}
		if($kept==0) { $kept = __('none', 'EVEShipInfo');	}
		if($protected==0) { $protected = __('none', 'EVEShipInfo');	}
		if($errors==0) { $protected = __('none', 'EVEShipInfo');	}
		
		$ignoreLabel = __('No, protected fits are overwritten', 'EVEShipInfo');
		if($ignoreProtected) {
			$ignoreLabel = __('Yes, protected fits are left unchanged', 'EVEShipInfo');
		}
		
		switch($mode) {
			case 'new':
				$modeLabel = __('New only', 'EVEShipInfo');
				break;
				
			case 'merge':
				$modeLabel = __('Merge', 'EVEShipInfo');
				break;
				
			case 'fresh':
				$modeLabel = __('Clean', 'EVEShipInfo');
				break;
		}
		
		$this->addSuccessMessage(
			sprintf(
				__('The file was imported successfully at %1$s.', 'EVEShipInfo'),
				date('H:i:s')
			).' '.
			'<br>'.
			'<br>'.
			'<b>'.__('Import summary:', 'EVEShipInfo').'</b>'.
			'<ul>'.
				'<li>'.__('Import mode:', 'EVEShipInfo').' <b>'.$modeLabel.'</b></li>'.
				'<li>'.__('Ignore protected fittings:', 'EVEShipInfo').' '.$ignoreLabel.'</li>'.
				'<li>'.__('Fittings in imported file:', 'EVEShipInfo').' '.count($fits).'</li>'.
				'<li>'.__('New:', 'EVEShipInfo').' '.$new.'</li>'.
				'<li>'.__('Updated:', 'EVEShipInfo').' '.$updated.'</li>'.
				'<li>'.__('Unchanged:', 'EVEShipInfo').' '.$kept.'</li>'.
				'<li>'.__('Protected:', 'EVEShipInfo').' '.$protected.'</li>'.
				'<li>'.__('Invalid:', 'EVEShipInfo').' '.$errors.' <span class="text-muted">('.__('Unknown ships, duplicates, etc.', 'EVEShipInfo').')</span></li>'.
			'</ul>'
		);
	}
	
	/**
	 * Goes through the raw imported data of a fit from the imported
	 * XML document and converts it to the internal storage format.
	 * 
	 * Returns an array with the following structure:
	 * 
	 * <pre>
	 * array(
	 *     'name' => 'Full rack Tachyons',
	 *     'ship' => 'Abaddon',
	 *     'hardware' => array(
	 *         'low' => array(
     *     	       'Item One',
     *             'Item Two',
     *             ...
     *         ),
	 *         'med' => array(
     *     	       'Item One',
     *             'Item Two',
     *             ...
     *         ),
	 *         'hi' => array(
     *     	       'Item One',
     *             'Item Two',
     *             ...
     *         ),
	 *         'rig' => array(
     *     	       'Item One',
     *             'Item Two',
     *             ...
     *         ),
	 *         'drone' => array(
     *             'Item One',
     *             'Item Two',
     *             ...
     *         )
	 *     )
	 * )
	 * </pre>
	 * 
	 * @param array $fit
	 * @return array
	 */
	protected function parseFit($fit)
	{
		$ship = $fit['shipType']['@attributes']['value'];
		if(!$this->collection->shipNameExists($ship)) {
			return false;
		}
		
		$name = str_replace($ship.' - ', '', $fit['@attributes']['name']);
		
		// fits without modules
		if(!isset($fit['hardware'])) {
			$fit['hardware'] = array();
		}
		
		// fits with a single module
		if(isset($fit['hardware']['@attributes'])) {
			$new = array(array('@attributes' => $fit['hardware']['@attributes']));
			$fit['hardware'] = $new;
		}
		
		$hardware = array();
		foreach($fit['hardware'] as $item) {
			$slot = $item['@attributes']['slot'];
			$type = $item['@attributes']['type'];
			
			$tokens = explode(' ', $slot);
			$slotType = $tokens[0];
			if(!isset($hardware[$slotType])) {
				$hardware[$slotType] = array();
			}

			if(isset($item['@attributes']['qty'])) {
				$type .= ' x '.$item['@attributes']['qty'];
			}
				
			$hardware[$slotType][] = $type;
		}
		
		// ensure all keys are present
		$keys = array('low', 'med', 'hi', 'rig', 'drone');
		foreach($keys as $key) {
			if(!isset($hardware[$key])) {
				$hardware[$key] = array();
			}
		}
		
		$fitString = 
		'['.$ship.', '.$name.']'.PHP_EOL;
		
		foreach($hardware as $section => $items) {
			foreach($items as $item) {
				$fitString .= $item.PHP_EOL;
			}
			$fitString .= PHP_EOL;
		}
		
		return array(
			'name' => $name,
			'ship' => $ship,
			'hardware' => $hardware,
			'fitString' => $fitString
		);
	}
}