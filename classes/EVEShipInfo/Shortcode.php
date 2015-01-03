<?php

abstract class EVEShipInfo_Shortcode
{
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	protected $attributes;
	
	protected $content;
	
	protected $id;
	
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
	public function __construct(EVEShipInfo $plugin)	
	{
		$this->plugin = $plugin;
		$this->collection = $this->plugin->createCollection();
		$this->id = $this->plugin->nextJSID();	
	}
	
	public function getDefaultAttributes()
	{
		return array();
	}
	
	public function getID()
	{
		return str_replace('EVEShipInfo_Shortcode_', '', get_class($this));
	}
	
	public function handle_call($attributes, $content=null)
	{
		$this->attributes = shortcode_atts($this->getDefaultAttributes(), $attributes);
		$this->content = $content;
		
		$this->process();
		
		return $this->content;
	}
	
	protected function getAttribute($name, $default=null)
	{
		if(isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		
		return $default;
	}
	
   /**
    * Retrieves the URL to the help page for this shortcode in the administration area.
    * @return string
    */
	public function getAdminHelpURL()
	{
		return admin_url('admin.php?page=eveshipinfo_shortcodes&amp;shortcode='.$this->getID());
	}
	
	abstract protected function process();
	
	abstract public function getName();
	
	abstract public function getTagName();
	
	abstract public function getDescription();

	public function getExamples()
	{
		$examples = $this->_getExamples();
		$tagName = $this->getTagName();
		foreach($examples as $idx => $def) {
			$examples[$idx]['shortcode'] = str_replace('TAGNAME', $tagName, $examples[$idx]['shortcode']);
		}
		
		return $examples;
	}
	
	abstract protected function _getExamples();
	
	abstract protected function _describeAttributes();
	
	public function describeAttributes()
	{
		$defaults = $this->getDefaultAttributes();
		$attribs = $this->_describeAttributes();
		foreach($attribs as $name => $def) {
			$default = null;
			if(isset($defaults[$name])) {
				$default = $defaults[$name];
			}
			
			$attribs[$name]['default'] = $default;
		}
		
		return $attribs;
	}
}