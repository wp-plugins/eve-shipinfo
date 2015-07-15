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
    
   /**
    * @var EVEShipInfo_Admin_UI
    */
    protected $ui;
    
    public function __construct(EVEShipInfo $plugin, $slug)
    {
    	$this->screen = get_current_screen();
        $this->plugin = $plugin;
        $this->tabs = $this->getTabs();
        $this->slug = $slug;
        $this->ui = $plugin->getAdminUI();
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
    	
    	$content = $this->activeTab->render();
    	
        $html =
        '<div class="wrap">'.
            '<h2>'.$this->getTitle().'</h2>'.
            '<br/>'.
        	'<div id="poststuff">';
        
		        if(!empty($this->errorMessages)) {
		        	foreach($this->errorMessages as $message) {
		        		$html .=
		        		$this->ui->renderAlertError(
		        			'<span class="dashicons dashicons-info error-message"></span> '.
		        			'<b>'.__('Error:', 'EVEShipInfo').'</b> '.
		        			$message
		        		);
		        	}
		        }
		        
		        if(!empty($this->successMessages)) {
		        	foreach($this->successMessages as $message) {
		        		$html .=
		        		$this->ui->renderAlertUpdated(
		        			'<span class="dashicons dashicons-yes"></span> '.
		        			$message
		        		);
		        	}
		        }
		        
	        	if(count($this->tabs) > 1) {
		        	$html .=
		            '<table class="wp-list-table widefat">'.
		            	'<tbody>'.
		            		'<tr>'.
		            			'<td>'.
						            '<ul class="subsubsub" style="margin-top:0;">';
		        						$tabs = $this->getEnabledTabs();
						        		foreach($tabs as $tabID => $tabLabel) {
						        			$active = '';
						        			if($tabID==$this->activeTab->getID()) {
						        				$active = ' class="current"';
						        			}
						        			
						        			$html .=
						        			'<li>'.
						        				'<a href="'.$this->getURL($tabID).'"'.$active.'>'.
						        					$tabLabel.
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
					$actionLinks = array();
            		$actions = $this->activeTab->getActions();
            		foreach($actions as $alias => $def) {
            			$actionLinks[] = 
             			'<a href="'.$this->activeTab->getActionURL($alias).'" class="button">'.
             				'<span class="dashicons dashicons-'.$def['icon'].'"></span> '.
             				$def['label'].
            			'</a>';
            		}
					$html .=
	            	'<div class="shipinfo-page-heading">'. 
		            	'<h3>'.
		            		$this->activeTab->getTitle().
		            	'</h3>';
	            		if(!empty($actionLinks)) {
	            			$html .= 
	            			'<div class="shipinfo-action-links">'. 
	            				implode(' ', $actionLinks).
	            			'</div>';
	            		}
	            		$html .=
	            	'</div>';
				}
				$html .=
	            $content.
            '</div>'.
        '</div>';
        
        return $html;
    }
    
    protected function getEnabledTabs()
    {
        $total = count($this->tabs);
        $count = 0;
        $enabled = array();
        foreach($this->tabs as $tabID => $tabLabel) {
        	if(!$this->isTabEnabled($tabID)) {
        		continue;
        	}
        	
        	$enabled[$tabID] = $tabLabel;
        }
        
        return $enabled;
    }
    
    protected function isTabEnabled($tabID)
    {
    	return true;
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
  
    	$params['page'] = $page;
    	
    	return admin_url('admin.php?'.http_build_query($params));
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
    
   /**
    * @return EVEShipInfo_Admin_UI
    */
    public function getUI()
    {
    	return $this->ui;
    }

	protected $errorMessages = array();
	
	protected $successMessages = array();
	
	public function addErrorMessage($message)
	{
		$this->errorMessages[] = $message;
	}
	
	public function addSuccessMessage($message)
	{
		$this->successMessages[] = $message;
	}
}