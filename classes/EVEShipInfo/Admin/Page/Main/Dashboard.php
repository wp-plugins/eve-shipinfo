<?php

class EVEShipInfo_Admin_Page_Main_Dashboard extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('EVE ShipInfo Dashboard', 'EVEShipInfo');
	}
	
	public function render()
	{
		$this->checkSystem();
		$html = '';
		
		$content = ''; 
		if(!empty($this->messages)) {
			$status = '<b style="color:#cc0000;">'.__('Warning', 'EVEShipInfo').'</b>';
			$content .=
			'<ul>';
				foreach($this->messages as $message) {
					$content .= 
					'<li>'.
						$message.
					'</li>';
				}
				$content .=
			'</ul>';
		} else {
			$status = '<b style="color:#00cc00">'.__('OK', 'EVEShipInfo').'</b>';
			$content .= __('Congratulations, everything seems to be in order.', 'EVEShipInfo');
		}
		
		$html .= $this->ui->createStuffBox(__('System health status:', 'EVEShipInfo').' '.$status)
			->setContent($content)
			->render();
		
		return $html;
	}
	
	protected $messages = array();
	
	protected function checkSystem()
	{
		if(!$this->plugin->isBlogURLRewritingEnabled()) {
			$this->messages[] = __('Permalinks are not enabled, virtual pages will not work even if you have enabled them.', 'EVEShipInfo');
		}
		
		if(!$this->plugin->getDummyPage()) {
			$this->messages[] = 
				__('Could not find any pages.', 'EVEShipInfo').' '.
				__('For virtual pages to work, you have to create at least one page.').' '.
				__('It does not need to have any content, just create an empty page.');
		}
	}
}