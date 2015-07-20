<?php

abstract class EVEShipInfo_Admin_Page_Tab
{
   /**
    * @var WP_Screen
    */
    protected $screen;
    
   /**
    * @var EVEShipInfo
    */
    protected $plugin;
    
   /**
    * @var EVEShipInfo_Admin_Page
    */
    protected $page;
    
   /**
    * @var EVEShipInfo_Admin_UI
    */
    protected $ui;
    
    protected $activeAction = null;
    
    public function render()
    {
    	if(isset($_REQUEST['action']) && isset($this->actions[$_REQUEST['action']])) {
    		$this->activeAction = $_REQUEST['action'];
    		$method = 'renderAction_'.$this->activeAction;
    		if(method_exists($this, $method)) {
    			return $this->$method();
    		}
    	}
    	
    	return $this->_render();
    }
    
	abstract protected function _render();
	
	public function __construct(EVEShipInfo_Admin_Page $page)
	{
		$this->page = $page;
		$this->ui = $page->getUI();
		$this->plugin = EVEShipInfo::getInstance();
		$this->screen = get_current_screen();
		
		$this->configure();
	}
	
	protected $actions = array();
	
	protected function configure()
	{
		// extensible to configure the tab
	}
	
	protected function registerAction($name, $label, EVEShipInfo_Admin_UI_Icon $icon=null)
	{
		$this->actions[$name] = array(
			'label' => $label,
			'icon' => $icon
		);
		
		return $this;
	}
	
	public function getID()
	{
		return str_replace('EVEShipInfo_Admin_Page_'.$this->page->getID().'_', '', get_class($this));
	}
	
	public function getURL($params=array())
	{
		return $this->page->getURL($this->getID(), $params);
	}
	
	public function getSlug()
	{
		return $this->page->getSlug().'_'.strtolower($this->getID());
	}
	
   /**
    * Creates a new settings manager that can be used to manage
    * a set of configuration settings for the plugin.
    * 
    * @param string $id
    * @return EVEShipInfo_Admin_SettingsManager
    */
	protected function createSettings($id)
	{
		$this->plugin->loadClass('EVEShipInfo_Admin_SettingsManager');
		return new EVEShipInfo_Admin_SettingsManager($id);
	}
	
	abstract public function getTitle();

	public function getActionURL($action, $params=array())
	{
		$params['action'] = $action;
		return $this->getURL($params);
	}
	
	public function renderAlertSuccess($message)
	{
		return $this->renderAlert('updated', $message);
	}
	
	public function renderAlertError($message)
	{
		return $this->renderAlert('error', $message);
	}
	
	protected function renderAlert($type, $message)
	{
		return 
		'<div class="'.$type.'">'.
			$message.
		'</div>';
	}

	protected function addErrorMessage($message)
	{
		$this->page->addErrorMessage($message);
	}
	
	protected function addSuccessMessage($message)
	{
		$this->page->addSuccessMessage($message);
	}
	
	public function getActions()
	{
		return $this->actions;
	}
	
	protected function createForm($name, $defaultValues=array())
	{
		$form = $this->ui->createForm($name, $defaultValues);
		$form->addHiddenVar('page', $this->getSlug());

		if(isset($this->activeAction)) {
			$form->addHiddenVar('action', $this->activeAction);
		}
		
		return $form;
	}
}