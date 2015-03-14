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
	
	public function render()
	{
		/* @var $fit EVEShipInfo_EFTManager_Fit */
		
		$this->eft = $this->plugin->createEFTManager();
		
	    if(isset($_POST['process_upload']) && $_POST['process_upload'] == 'yes') {
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
	
	protected function renderForm()
	{
		$html =
		'<form method="post" enctype="multipart/form-data" class="wp-upload-form">'.
			'<input type="hidden" name="process_upload" value="yes"/>'.
			'<table class="form-table">'.
				'<tbody>'.
					'<tr>'.
						'<th scope="row">'.
							__('EFT Export XML file', 'EVEShipInfo').
						'</th>'.
						'<td>'.
							'<input type="file" id="eft_xml_file" name="eft_xml_file" accept="text/xml"/>'.
							'<p class="description">'.
								'<b>'.__('Howto:', 'EVEShipInfo').'</b> '.
								__('Open EFT, select File &gt; Import/Export to EVE &gt; Save all setups to one XML file...', 'EVEShipInfo').' '.
								__('Save the file where you like, then upload it here.', 'EVEShipInfo').
							'</p>'.
						'</td>'.
					'</tr>';
					if($this->eft->hasFittings()) {
						$html .=
						'<tr>'.
							'<th scope="row">'.
								__('Import mode', 'EVEShipInfo').
							'</th>'.
							'<td>'.
								'<label><input type="radio" name="eft_import_mode" value="fresh" checked="checked"/> <b>'.__('Clean:', 'EVEShipInfo').'</b> '.__('Delete all existing fits before the import', 'EVEShipInfo').'</label><br/>'.
								'<label><input type="radio" name="eft_import_mode" value="merge"/> <b>'.__('Merge:', 'EVEShipInfo').'</b> '.__('Add new fits, replace existing ones and keep all others', 'EVEShipInfo').'</label><br/>'.
								'<label><input type="radio" name="eft_import_mode" value="new"/> <b>'.__('New only:', 'EVEShipInfo').'</b> '.__('Only add new fits, leave existing untouched', 'EVEShipInfo').'</label><br/>'.
								'<p class="description">'.
									__('Specifies what to do with already existing fits and those you are importing.', 'EVEShipInfo').' '.
									'<b>'.__('Warning:', 'EVEShipInfo').'</b> '.
									__('The fit IDs depend on the fit names, so if you changed names it is best to use the merge option so any existing fits do not get lost.').
								'</p>'.
							'</td>'.
						'</tr>';
					}
					$html .=
					'<tr>'.
						'<td></td>'.
						'<td>'.
							get_submit_button(__( 'Upload now', 'EVEShipInfo'), 'primary', 'eft-submit', false ).
						'</td>'.
					'</tr>'.
				'</tbody>'.
			'</table>'.
		'</form>';
		
		return $this->ui->createStuffBox('<span class="dashicons dashicons-upload"></span> '.__('Upload EFT export'))
			->setAbstract(
				__('To easily share EFT fits with your readers in your posts, you can upload an EFT export here.', 'EVEShipInfo').' '.
				__('Once you have uploaded your fits, you can use the dedicated shortcodes to display them.', 'EVEShipInfo').' '.
				'<b>'.__('Note:', 'EVEShipInfo').'</b> '.
				__('The EFT export format is somewhat limited.').' '.
				__('It does not include any implants or turret/launcher charges you may have used.')
			)
			->setContent($html)
			->render();
	}
	
	protected function processDelete()
	{
		delete_option('eveshipinfo_name_hashes');
		delete_option('eveshipinfo_fittings');
		
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
    * Reads the contents of the uploaded EFT fittings XML file, and
    * tries to read it. Returns an array with all fits that were found,
    * or false if an error occurred. In case of an error, an error message
    * with details on what went wrong is automatically added and displayed.
    * 
    * @return boolean|array
    */
	protected function parseXML()
	{
		if(!isset($_FILES['eft_xml_file'])) {
			$this->addErrorMessage(__('No file has been uploaded.', 'EVEShipInfo'));
			return false;
		}
		
		$ext = strtolower(pathinfo($_FILES['eft_xml_file']['name'], PATHINFO_EXTENSION));
		if($ext != 'xml') {
		    $this->addErrorMessage(__('The uploaded file was not an XML file.', 'EVEShipInfo'));
			return false;
		}
		
		$tmpFile = $_FILES['eft_xml_file']['tmp_name'];
		$content = trim(file_get_contents($tmpFile));
		if(empty($content)) {
			$this->addErrorMessage(__('The uploaded file was empty.', 'EVEShipInfo'));
			return false;
		}

		$root = @simplexml_load_file($tmpFile);
		if(!$root) {
			$this->addErrorMessage(__('The uploaded XML file could not be read, it is possibly malformed or not an XML file.', 'EVEShipInfo'));
			return false;
		}
		
		// to read the xml, we use the json encode + decode trick,
		// which magically creates an array with everything in it.
		$encoded = json_encode($root);
		$data = json_decode($encoded, true);
		if(!isset($data['fitting'])) {
			$this->addErrorMessage(__('The fitting data could not be found in the XML file.', 'EVEShipInfo'));
			return false;
		}
		
		// the name hashes help recognize wich fittings already exist.
		$this->nameHashes = get_option('eveshipinfo_name_hashes');
		if(is_string($this->nameHashes) && !empty($this->nameHashes)) {
			$this->nameHashes = unserialize($this->nameHashes);
		} else {
			$this->nameHashes = array();
		}
		
		// to clear the name hashes if needed
		//if($mode=='fresh') {$this->nameHashes = array();}

		$fits = array();
		foreach($data['fitting'] as $fit) {
			$def = $this->loadFit($fit);
			$fits[$def['id']] = $def;
		}
		
		if(empty($fits)) {
			$this->addErrorMessage(__('No fittings found in the XML file.', 'EVEShipInfo'));
			return false;
		}
		
		return $fits;
	}
	
   /**
    * Processes the upload of an EFT fittings XML file: 
    * parses the XML, and stores the new data according
    * to the selected import mode.
    */
	protected function processUpload()
	{
		$imported = $this->parseXML();
		if(!$imported) {
			return;
		}

		$existing = get_option('eveshipinfo_fittings', null);
		if( !$existing) {
			$existing = array();
			add_option('eveshipinfo_fittings', serialize($existing));
		} else {
		    $existing = unserialize($existing);
		}

		$validModes = array('fresh', 'merge', 'new');
		$mode = 'fresh';
		if(isset($_REQUEST['eft_import_mode']) && in_array($_REQUEST['eft_import_mode'], $validModes)) {
			$mode = $_REQUEST['eft_import_mode'];
		}
		
		$method = 'processUpload_'.$mode;
		$optionData = array(
			'updated' => time(),
			'fits' => $this->$method($imported, $existing['fits'])
		);
		
		update_option('eveshipinfo_fittings', serialize($optionData));
		update_option('eveshipinfo_name_hashes', serialize($this->nameHashes));
		
		$this->eft->reload();
	}
	
	protected function processUpload_merge($imported, $existing)
	{
		$new = 0;
		foreach($imported as $id => $fit) {
			if(!isset($existing[$id])) {
				$new++;
			}
		}
		
		$updated = 0;
		$kept = 0;
		foreach($existing as $id => $fit) {
			if(isset($imported[$id])) {
				$imported[$id]['added'] = $fit['added'];
				$imported[$id]['visibility'] = $fit['visibility'];
				$updated++;
			} else {
				$imported[$id] = $fit;
				$kept++;
			}
		}
		
		$total = count($imported);
		 
		$this->addSuccessMessage(
			sprintf(
				__('The file was imported successfully at %1$s.', 'EVEShipInfo'),
				date('H:i:s')
			).' '.
			sprintf(
				__('Found a total of %1$s fittings.', 'EVEShipInfo'),
				$total
			).' '.
			sprintf(
				__('Of these, %1$s were new, %2$s were updated and %3$s were unchanged.', 'EVEShipInfo'),
				$new,
				$updated,
				$kept	
			)
		);
		
		return $imported;
	}
	
	protected function processUpload_fresh($imported, $existing)
	{
		$this->addSuccessMessage(
			sprintf(
				__('The file was imported successfully at %1$s.', 'EVEShipInfo'),
				date('H:i:s')
			).sprintf(
				__('Found a total of %1$s fittings.', 'EVEShipInfo'),
				count($imported)
			)
		);
		
		return $imported;
	}
	
	protected function processUpload_new($imported, $existing)
	{
		$new = 0;
		$keep = $existing;
		foreach($imported as $id => $fit) {
			if(!isset($existing[$id])) {
				$keep[$id] = $fit;
				$new++;
			}
		}
		
		$imported = $keep;
		
		$total = count($imported);
		$this->addSuccessMessage(
			sprintf(
				__('The file was imported successfully at %1$s.', 'EVEShipInfo'),
				date('H:i:s')
			).sprintf(
				__('Added %1$s new fittings, for a total of %2$s.', 'EVEShipInfo'),
				$new,
				$total
			)
		);
		
		return  $imported;
	}
	
	/**
	 * Goes through the raw imported data of a fit from the imported
	 * XML document and converts it to the internal storage format.
	 * 
	 * Returns an array with the following structure:
	 * 
	 * <pre>
	 * array(
	 *     'id' => '[generated id]',
	 *     'visibility' => 'public',
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
	protected function loadFit($fit)
	{
		$ship = $fit['shipType']['@attributes']['value'];
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
		
		return array(
		    'id' => $this->generateID($name),
			'visibility' => EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC,
			'added' => time(),
			'name' => $name,
			'ship' => $ship,
			'hardware' => $hardware
		);
	}
	
   /**
    * Generates a unique ID for each fit. This uses the name hashes
    * collection to keep track of known fittings and attribute them IDs.
    * 
    * @param string $name
    * @return integer
    */
	protected function generateID($name)
	{
		$key = md5($name);
		if(isset($this->nameHashes[$key])) {
			return $this->nameHashes[$key];
		}
		
		$id = 0;
		foreach($this->nameHashes as $hash => $hashID) {
			if($hashID > $id) {
				$id = $hashID;
			}
		}
		
		$id++;
		
		$this->nameHashes[$key] = $id;
		return $id;
	}
}