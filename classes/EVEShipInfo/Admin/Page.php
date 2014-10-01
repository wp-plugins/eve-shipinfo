<?php

abstract class EVEShipInfo_Admin_Page
{
   /**
    * @var EVEShipInfo
    */
    protected $plugin;

   /**
    * @var EVEShipInfo_Admin_Page_Tab
    */
    protected $activeTab;
    
    protected $tabs;
    
   /**
    * @var WP_Screen
    */
    protected $screen;
    
    public function __construct(EVEShipInfo $plugin, $slug)
    {
    	$this->screen = get_current_screen();
        $this->plugin = $plugin;
        $this->tabs = $this->getTabs();
        $this->slug = $slug;
    }
    
    public function getSlug()
    {
    	return $this->slug;
    }
    
    public function selectTab($tabID)
    {
    	if(isset($this->tabs[$tabID])) {
    		$this->activeTab = $this->createTab($tabID);
    	}
    	
    	return $this;
    }
    
   /**
    * @return EVEShipInfo_Admin_Page_Tab
    */
    public function getActiveTab()
    {
    	return $this->activeTab;
    }
    
    public function getID()
    {
    	return str_replace('EVEShipInfo_Admin_Page_', '', get_class($this));
    }
    
    abstract protected function getTabs();
    
    abstract protected function getTitle();
    
   /**
    * Creates a new tab handling class instance for the specified tab ID.
    * 
    * @param string $tabID
    * @return EVEShipInfo_Admin_Page_Tab
    */
    protected function createTab($tabID)
    {
    	$this->plugin->loadClass('EVEShipInfo_Admin_Page_Tab');
    	
    	$className = 'EVEShipInfo_Admin_Page_'.$this->getID().'_'.$tabID;
    	$this->plugin->loadClass($className);
    	 
    	$tab = new $className($this);
    	return $tab;
    }
    
    public function render()
    {
    	if(!isset($this->activeTab)) {
    		$this->activeTab = $this->createTab(key($this->tabs));
    	}
    	
        $html =
        '<div class="wrap">'.
            '<h2>'.$this->getTitle().'</h2>'.
            '<br/>'.
        	'<div id="poststuff">';
	        	if(count($this->tabs) > 1) {
		        	$html .=
		            '<table class="wp-list-table widefat">'.
		            	'<tbody>'.
		            		'<tr>'.
		            			'<td>'.
						            '<ul class="subsubsub" style="margin-top:0;">';
						        		$total = count($this->tabs);
						        		$count = 0;
						        		foreach($this->tabs as $tabID => $tabLabel) {
						        			$count++;
						        			$active = '';
						        			if($tabID==$this->activeTab->getID()) {
						        				$active = ' class="current"';
						        			}
						        			
						        			$separator = ' | ';
						        			if($count>=$total) {
						        				$separator = '';
						        			}
						        			
						        			$html .=
						        			'<li>'.
						        				'<a href="'.$this->getURL($tabID).'"'.$active.'>'.
						        					$tabLabel.
						        					$separator.
						        				'</a>'.
						        			'</li>';
						        		}
						            	$html .=
						            '</ul>'.
						            '<div class="clear"></div>'.
					            '</td>'.
				          	'</tr>'.
			          	'</tbody>'.
		          	'</table>'.
		          	'<br/>';
	        	}
				$title = $this->activeTab->getTitle();
				if(!empty($title)) {
	            	$html .= '<h3>'.$this->activeTab->getTitle().'</h3><br/>';
				}
				$html .=
	            $this->activeTab->render().
            '</div>'.
        '</div>';
        
        return $html;
    }
    
    public function display()
    {
        echo $this->render();
    }   
    
    public function getURL($tabID=null, $params=array())
    {
    	$page = $this->getSlug();
    	if(!empty($tabID) && !$this->isDefaultTab($tabID)) {
    		$page .= '_'.strtolower($tabID);
    	}
    	
    	return admin_url('admin.php?page='.$page);
    }
    
    public function isDefaultTab($tabID)
    {
    	reset($this->tabs);
    	$default = key($this->tabs);
    	if($default == $tabID) {
    		return true;
    	}
    	
    	return false;
    }
}