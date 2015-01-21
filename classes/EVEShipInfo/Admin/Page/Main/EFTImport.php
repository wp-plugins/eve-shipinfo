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
	
	protected $uploadSuccess = false;
	
	public function render()
	{
		/* @var $fit EVEShipInfo_EFTManager_Fit */
		
		$this->eft = $this->plugin->createEFTManager();
		
		if(isset($_POST['process_upload']) && $_POST['process_upload'] == 'yes') {
			$this->processUpload();
		}
		
		$html = '';
		
		if(isset($this->uploadError)) {
			$html .= 
			$this->ui->renderAlertError(
				'<span class="dashicons dashicons-info error-message"></span> '.
				'<b>'.__('Error:', 'EVEShipInfo').'</b> '.
				$this->uploadError
			);
		} else if($this->uploadSuccess) {
			$html .=
			$this->ui->renderAlertUpdated(
				'<span class="dashicons dashicons-yes"></span> '.
				sprintf(
					__('The file was imported successfully at %1$s, %2$s fits found.'),
					date('H:i:s'), 
					$this->eft->countFittings()
				)
			);
		}
		
		$html .= $this->renderForm();
		
		return $html;
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
	
	protected $uploadError;
	
	protected $nameHashes;
	
	protected function processUpload()
	{
		if(!isset($_FILES['eft_xml_file'])) {
			return;
		}
		
		$ext = strtolower(pathinfo($_FILES['eft_xml_file']['name'], PATHINFO_EXTENSION));
		if($ext != 'xml') {
			$this->uploadError = __('The uploaded file was not an XML file.', 'EVEShipFile');
			return;
		}
		
		$tmpFile = $_FILES['eft_xml_file']['tmp_name'];
		$content = trim(file_get_contents($tmpFile));
		if(empty($content)) {
			$this->uploadError = __('The uploaded file was empty.', 'EVEShipInfo');
			return;
		}

		$root = @simplexml_load_file($tmpFile);
		if(!$root) {
			$this->uploadError = __('The uploaded XML file could not be read, it is possibly malformed or not an XML file.', 'EVEShipInfo');
			return;
		}
		
		$encoded = json_encode($root);
		$data = json_decode($encoded, true);
		$mode = $_REQUEST['eft_import_mode'];
		
		if(!isset($data['fitting'])) {
			$this->uploadError = __('The fitting data could not be found in the XML file.', 'EVEShipInfo');
			return;
		}

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
			$this->uploadError = __('No fittings found in the XML file.', 'EVEShipInfo');
			return;
		}
		
		$this->uploadSuccess = true;
		
		$optionData = array(
			'updated' => time(),
			'fits' => $fits
		);

		$existing = get_option('eveshipinfo_fittings', null);
		if(!$existing) {
			add_option('eveshipinfo_fittings', serialize($optionData));
			return;
		}

		$existing = unserialize($existing);
		
		$mode = $_REQUEST['eft_import_mode'];
		switch($mode) {
			case 'merge':
			    foreach($existing['fits'] as $id => $fit) {
			    	if(!isset($optionData['fits'][$id])) {
			    		$optionData['fits'][$id] = $fit;	
			    	}
			    }
				break;
				
			case 'fresh':
				// nothing to do, we just use the imported data
				break;
				
			case 'new':
			    $keep = $existing['fits'];
				foreach($optionData['fits'] as $id => $fit) {
					if(!isset($existing['fits'][$id])) {
						$keep[$id] = $fit;
					}
				}
				$optionData['fits'] = $keep;
				break;
		}
		
		update_option('eveshipinfo_fittings', serialize($optionData));
		update_option('eveshipinfo_name_hashes', serialize($this->nameHashes));
		
		$this->eft->reload();
	}
	
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
			'name' => $name,
			'ship' => $ship,
			'hardware' => $hardware
		);
	}
	
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