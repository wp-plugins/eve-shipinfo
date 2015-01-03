<?php

class EVEShipInfo_Admin_UI
{
   /**
    * @var EVEShipInfo
    */
    protected $plugin;
    
    public function __construct(EVEShipInfo $plugin)
    {
    	$this->plugin = $plugin;
    }
    
   /**
    * Creates a new stuff box UI element that can be used to 
    * create static or collapsible box elements.
    * 
    * @param string $title
    * @return EVEShipInfo_Admin_UI_StuffBox
    */
    public function createStuffBox($title=null)
    {
		$this->plugin->loadClass('EVEShipInfo_Admin_UI_Renderable');
    	$this->plugin->loadClass('EVEShipInfo_Admin_UI_StuffBox');
    	
    	$box = new EVEShipInfo_Admin_UI_StuffBox($this, $this->plugin->nextJSID());
    	
    	if(!empty($title)) {
    		$box->setTitle($title);
    	}
    	
    	return $box;
    }
    
}