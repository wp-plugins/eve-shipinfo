<?php

class EVEShipInfo_Admin_Page_Main_Dashboard extends EVEShipInfo_Admin_Page_Tab
{
	public function getTitle()
	{
		return __('EVE ShipInfo Dashboard', 'EVEShipInfo');
	}
	
	protected function _render()
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
		
		$html .= $this->ui->createStuffBox(__('Ship screenshots bundle', 'EVEShipInfo'))
			->setContent($this->renderScreenshotsBundle())
			->render();
		
		return $html;
	}
	
	protected function renderScreenshotsBundle()
	{
		$folder = $this->plugin->getGalleryPath();
		if(!is_dir($folder)) {
			return
			'<p>'.
				__('No screenshots bundle is installed.').
			'</p>'.
			'<p>'.
				sprintf(
					__('To download a screenshots bundle, go to the %1$splugin download page%2$s.', 'EVEShipInfo'),
					'<a href="'.$this->plugin->getHomepageDownloadURL().'">',
					'</a>'
				).
			'</p>';
		}
		
		$versionFile = $folder.'/version.txt';
		$html = '';
		if(file_exists($versionFile)) {
			$html .=
			'<p>'.
				sprintf(
					__('The screenshot bundle %1$s is installed.', 'EVEShipInfo'),
					'<b>v'.file_get_contents($versionFile).'</b>'
				).
			'</p>';
		} else {
			$html .= 
			'<p>'.
				__('An older screenshot bundle seems to be installed.', 'EVEShipInfo').
			'</p>';
		}
		
		$html .=
		'<p>'.
			sprintf(
				__('To check for updates, view the %1$splugin download page%2$s.', 'EVEShipInfo'),
				'<a href="'.$this->plugin->getHomepageDownloadURL().'">',
				'</a>'
			).
		'</p>';
		
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