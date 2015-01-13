<?php

class EVEShipInfo_Admin_Page_Main_EFTFittings extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('EFT fittings', 'EVEShipInfo');
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
		
		$html = '';
		
		if($this->eft->hasFittings()) {
			$fits = $this->eft->getFittings();
			
			$boxHTML = 
			'<ul>';
				foreach($fits as $fit) {
					$boxHTML .=
					'<li>'.$fit->getShipName().' - '.$fit->getName().'</li>';
				}
				$boxHTML .=
			'</ul>';
			
			$html .= $this->ui->createStuffBox(sprintf(
				__('%s available fittings, uploaded on %s', 'EVEShipInfo'), 
				$this->eft->countFittings(),
				$this->eft->getLastModified()->format('d.m.Y H:i:s')
			))
				->setContent($boxHTML)
				->setCollapsible()
				->setCollapsed()
				->render();
		}
		
		if(isset($this->uploadError)) {
			$html .= 
			$this->ui->renderAlertError(
				'<span class="dashicons dashicons-info error-message"></span> '.
				'<b>'.__('Error:', 'EVEShipInfo').'</b> '.
				$this->uploadError
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
								'<b>'.__('Howto:').'</b> '.
								__('Open EFT, select File &gt; Import/Export to EVE &gt; Save all setups to one XML file...', 'EVEShipInfo').' '.
								__('Save the file where you like, then upload it here.', 'EVEShipInfo').
							'</p>'.
						'</td>'.
					'</tr>'.
					'<tr>'.
						'<td></td>'.
						'<td>'.
							get_submit_button(__( 'Upload now', 'EVEShipInfo'), 'primary', 'eft-submit', false ).
						'</td>'.
					'</tr>'.
				'</tbody>'.
			'</table>'.
		'</form>';
		
		return $this->ui->createStuffBox(__('Upload EFT export'))
			->setAbstract(
				__('To easily share EFT fits with your readers in your posts, you can upload an EFT export here.', 'EVEShipInfo').' '.
				__('Once you have uploaded your fits, you can use the dedicated shortcodes to display them.', 'EVEShipInfo')
			)
			->setCollapsible()
			->setContent($html)
			->render();
	}
	
	protected $uploadError;
	
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
		
		$content = trim(file_get_contents($_FILES['eft_xml_file']['tmp_name']));
		if(empty($content)) {
			$this->uploadError = __('The uploaded file was empty.', 'EVEShipInfo');
			return;
		}

		$doc = new DOMDocument();
		if(!$doc->loadXML($content)) {
			$this->uploadError = __('The uploaded XML file could not be read, it is possibly malformed or not an XML file.', 'EVEShipInfo');
			return;
		}
		
		$target = $this->plugin->getDir().'/data/eft.xml';
		if(!@file_put_contents($target, $content)) {
			$this->uploadError = sprintf(
				__('The uploaded file could not be saved, the plugin\'s %s folder may not be writable.', 'EVEShipInfo'),
				'<code>data</code>'
			);
		}
	}
}