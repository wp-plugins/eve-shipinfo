<?php

class EVEShipInfo_Shortcode_EFTFit extends EVEShipInfo_Shortcode
{
	public function getTagName()
	{
		return 'shipinfo_fit';
	}
	
	public function getDescription()
	{
		return 
		__('Links a text to show details of one of your EFT fits.', 'EVEShipInfo').' '.
		'<b>'.__('Note:', 'EVEShipInfo').'</b> '.
		sprintf(
		    __('You have to %simport%s your EFT fits before you can use this.', 'EVEShipInfo'),
		    '<a href="?page=eveshipinfo_eftimport">',
		    '</a>'
		);
	}
	
	public function getName()
	{
		return __('EFT fit link', 'EVEShipInfo');
	}
	
	public function process()
	{
		$id = $this->getAttribute('id');
		if(empty($id)) {
			return;
		}
		
		/* @var $ship EVEShipInfo_Collection_Ship */
		
		$eft = $this->plugin->createEFTManager();
		$fit = $eft->getFittingByID($id);
		if(!$fit) {
			return;
		}
		
		$classes = array(
			$this->plugin->getCSSName('fitlink')
		);
		
		$attribs = array(
			'href' => 'javascript_void(0)',
		    'onclick' => sprintf("EVEShipInfo.InfoPopup('%s')", $id)
		);
		
		$attribs['class'] = implode(' ', $classes);
		
		$this->content =
		'<a'.$this->plugin->compileAttributes($attribs).'>'.
			$this->content.
		'</a>';
		
		$this->registerFit($fit);
	}
	
	protected static $registeredFits = array();
	
	protected function registerFit(EVEShipInfo_EFTManager_Fit $fit)
	{
		$fitID = $fit->getID();
		if(isset(self::$registeredFits[$fitID])) {
			return;
		}
		
		self::$registeredFits[$fitID] = true; 
		
		$this->content .= 
		'<script>'.
			sprintf(
			    "EVEShipInfo.AddFit(%s);",
			    json_encode($fit->exportData())
			).
		'</script>';
	}
	
	public function getDefaultAttributes()
	{
		return array(
			'id' => '',
		);
	}
	
	protected function _describeAttributes()
	{
		return array(
	        'settings' => array(
	            'group' => __('Settings'),
	            'abstract' => __('Configuration settings for the link.'),
		        'attribs' => array(
					'id' => array(
						'descr' => __('The ID of the fit to link to.', 'EVEShipInfo').' '.
					    		   sprintf(
					    		       __('Have a look at the %sfittings list%s to find out which IDs you can use.'),
					    		       '<a href="?page=eveshipinfo_eftfittings">',
					    		       '</a>'
				    		       ),
						'optional' => true,
						'type' => 'text'
					),
	            )
            )
		);
	}
	
	protected function _getExamples()
	{
		return array(
			array(
				'shortcode' => '[TAGNAME id="2"]',
				'descr' => __('Insert a link to the target fit.', 'EVEShipInfo').' '.
					       __('Since the shortcode is empty, the name of the fit will be used for the link.', 'EVEShipInfo')
			),
			array(
				'shortcode' => '[TAGNAME id="2"]'.__('Custom link text', 'EVEShipInfo').'[/TAGNAME]',
				'descr' => __('For a custom link title, simply put the text within the shortcode.', 'EVEShipInfo')
			),
		);
	}
}