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
    	
    	try
    	{
    		$content = $this->activeTab->render();
    	} 
    	catch(Exception $e)
    	{
    		$content = $this->renderErrorBox($e);
    	}
    	
        $html =
        '<div class="wrap">'.
            '<h2>'.$this->getTitle().'</h2>'.
            '<br/>'.
        	'<div id="poststuff">';
        
		        if(!empty($this->errorMessages)) {
		        	foreach($this->errorMessages as $message) {
		        		$html .=
		        		$this->ui->renderAlertError(
		        			$this->ui->icon()->warning()->makeDangerous().' '.
		        			'<b>'.__('Error:', 'EVEShipInfo').'</b> '.
		        			$message
		        		);
		        	}
		        }
		        
		        if(!empty($this->successMessages)) {
		        	foreach($this->successMessages as $message) {
		        		$html .=
		        		$this->ui->renderAlertUpdated(
		        			$this->ui->icon()->yes().' '.
		        			$message
		        		);
		        	}
		        }

		        if(!empty($this->warningMessages)) {
		        	foreach($this->warningMessages as $message) {
		        		$html .=
		        		$this->ui->renderAlertWarning(
		        			$this->ui->icon()->warning().' '.
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
            			if(!$def['showButton']) {
            				continue;
            			}
            			$actionLinks[] = 
             			'<a href="'.$this->activeTab->getActionURL($alias).'" class="button">'.
             				$def['icon'] . ' '.
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
	
	protected $warningMessages = array();
	
	public function addErrorMessage($message)
	{
		$this->errorMessages[] = $message;
	}
	
	public function addSuccessMessage($message)
	{
		$this->successMessages[] = $message;
	}
	
	public function addWarningMessage($message)
	{
		$this->warningMessages[] = $message;
	}
	
	protected function renderErrorBox(Exception $e)
	{
		$info = $e->getMessage();
		if($e instanceof EVEShipInfo_Exception) {
			$info = $e->getDetails();
		}
		
		$details =
		'<p>'.__('Error message:', 'EVEShipInfo').' <b>'.$e->getMessage().'</b></p>'.
		'<p>'.
		sprintf(
			__('If you feel this error is absolutely out of place, feel free to send a bug report to %1$s.', 'EVEShipInfo'),
				'<a href="mailto:eve@aeonoftime.com?subject=EVEShipInfo '.$this->plugin->getVersion().' Bug Report - Error '.$e->getCode().'&amp;body=Error details: '.$info.PHP_EOL.PHP_EOL.'">eve@aeonoftime.com</a>'
			).' '.
			__('Ideally this should include the last few things you did before you got this message.', 'EVEShipInfo').' '.
			__('Also interesting would be to know if you can reproduce the error by doing the same thing again.', 'EVEShipInfo').
		'</p>';
		
		if(!WP_DEBUG) {
			$details .= 
			'<p>'.
				__('Note:', 'EVEShipInfo').' '.
				sprintf(
					__('Turning on the WordPress debugging mode will display more detailed information about any %1$s errors.', 'EVEShipInfo'),
					'EVEShipInfo'
				).
			'</p>';
		}
		
		if(WP_DEBUG && $e instanceof EVEShipInfo_Exception) {
			$details .=
			'<hr>'.
			'<p>'.__('Error details:', 'EVEShipInfo').' '.$e->getDetails().'</p>'.
			'<hr>'.
			'<p>'.__('Full trace:', 'EVEShipInfo').'</p>'.
			'<table class="trace-table">'.
				'<thead>'.
					'<tr>'.
						'<th style="text-align:right;">'.__('File', 'EVEShipInfo').'</th>'.
						'<th style="text-align:left;">'.__('Line', 'EVEShipInfo').'</th>'.
						'<th>'.__('Function call', 'EVEShipInfo').'</th>'.
					'</tr>'.
				'</thead>'.
				'<tbody>';
					$trace = array_reverse($e->getTrace());
					foreach($trace as $entry) {
						
						/*unset($entry['args']);
						echo '<pre>'.print_r($entry, true).'</pre>';*/
						
						$file = '<span class="text-muted">-</span>';
						if(isset($entry['file'])) {
							$file = $this->plugin->relativizePath($entry['file']);
						}
						
						if(!isset($entry['line'])) {
							$entry['line'] = '<span class="text-muted">-</span>';
						}
						
						$params = array();
						if(isset($entry['args'])) {
							foreach($entry['args'] as $arg) {
								switch(gettype($arg)) {
									case 'string':
										$params[] = '<span style="color:#29992f">"'.$arg.'"</span>';
										break;
										
									case 'boolean':
										$val = '<span class="text-danger">true</span>';
										if($arg===true) {
											$val = '<span class="text-success">true</span>';
										}
										$params[] = $val;
										break;
										
									case 'NULL':
										$params[] = '<span class="text-muted">null</span>';
										break;
										
									case 'integer':
									case 'double':
										$params[] = '<span style="color:#0000dd">'.$arg.'</span>';
										break;
										
									case 'array':
										$params[] = '<span style="color:#306eb4">array(</span>'.json_encode($arg).'<span style="color:#306eb4">)</span>';
										break;
										
									case 'object':
										$params[] = '<span style="color:#e36203">'.get_class($arg).'</span>'; 
										break;
										
									case 'resource':
										$params[] = '<span style="color:#e36203">resource</span>';
										break;
								}
							}
						}
						
						$details .=
						'<tr>'.
							'<td style="text-align:right;">'.$file.'</td>'.
							'<td style="text-align:left;">'.$entry['line'].'</td>'.
							'<td>'.$entry['function'].'('.implode(', ', $params).')</td>'.
						'</tr>';
					}
					$details .=
				'</tbody>'.
			'</table>';
		}
		
		$box = $this->ui->createStuffBox(__('Error', 'EVEShipInfo'));
		$box->makeError();
		$box->setAbstract(sprintf(
			__('An exception with the code <code>%1$s</code> occurred.', 'EVEShipInfo'),
			$e->getCode()
		));
		$box->setContent($details);
		return $box->render();
	}
}