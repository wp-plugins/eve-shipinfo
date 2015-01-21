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
		
		$html = '';
		
		if($this->eft->hasFittings()) {
			$fits = $this->eft->getFittings();
			
			$boxHTML = 
			'<p>'.
				__('The following is a list of all known fits, and the name to use with the shortcode to insert it in your posts.', 'EVEShipInfo').' '.
				sprintf(
					__('Have a look at the %sshortcode reference%s for examples on how to use this.', 'EVEShipInfo'),
					'<a href="admin.php?page=eveshipinfo_shortcodes&shortcode=EFTFit">',
					'</a>'
				).
			'</p>'.
			'<table class="wp-list-table widefat fixed">'.
				'<thead>'.
					'<tr>'.
						'<th>'.__('Fit ID', 'EVEShipInfo').'</th>'.
						'<th>'.__('Fit name', 'EVEShipInfo').'</th>'.
						'<th>'.__('Ship', 'EVEShipInfo').'</th>'.
					'</tr>'.
				'</thead>'.
				'<tbody>';
					foreach($fits as $fit) {
						$boxHTML .=
						'<tr>'.
							'<td>'.$fit->getID().'</td>'.
							'<td>'.$fit->getName().'</td>'.
							'<td>'.$fit->getShipName().'</td>'.
						'</tr>';
					}
					$boxHTML .=
				'</tbody>'.
			'</table>';
			
			$html .= $this->ui->createStuffBox(sprintf(
				'<span class="dashicons dashicons-list-view"></span> '.
				__('%s available fittings, uploaded on %s', 'EVEShipInfo'), 
				$this->eft->countFittings(),
				$this->eft->getLastModified()->format('d.m.Y H:i:s')
			))
			->setContent($boxHTML)
			->render();
		}
		
		return $html;
	}
}