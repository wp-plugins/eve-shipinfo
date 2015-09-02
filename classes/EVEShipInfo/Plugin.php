<?php

abstract class EVEShipInfo_Plugin implements EVEShipInfo_PluginInterface
{
    protected $dir;
    
    protected $url;
    
	public function getURL()
	{
		return $this->url;
	}
	
	public function getHomepageURL()
	{
		return 'http://www.aeonoftime.com/EVE_Online_Tools/EVE-ShipInfo-WordPress-Plugin/';
	}
	
	public function getHomepageDownloadURL()
	{
		return $this->getHomepageURL().'/download.php';
	}
	
	public function getGalleryURL()
	{
		return $this->url.'/gallery';
	}
	
	public function getGalleryPath()
	{
		return $this->dir.'/gallery';
	}
    
    public function getDir()
	{
	    return $this->dir;
	}
	
    public function loadClass($className)
    {
    	if(class_exists($className)) {
    		return;
    	}
    
    	$file = $this->getDir().'/classes/'.str_replace('_', '/', $className).'.php';
    	require_once $file;
    }
    
    public function registerError($errorMessage, $errorCode)
    {
    
    }
    
    protected $collection;
    
    /**
     * Returns the ships collection instance that can be used to
     * access the entire ships collection and retrieve information
     * about ships.
     *
     * @return EVEShipInfo_Collection
     */
    public function createCollection()
    {
    	if(!isset($this->collection)) {
    		$this->loadClass('EVEShipInfo_Collection');
    		$this->collection = new EVEShipInfo_Collection($this);
    	}
    		
    	return $this->collection;
    }
    
    public function getImageWidth()
    {
    	return 750;
    }
    
    public function getCSSName($part)
    {
    	return 'shipinfo-'.$part;
    }
    
    public function compileAttributes($attributes)
    {
    	$tokens = array();
    	foreach($attributes as $name => $value) {
    		if($value===null) {
    			continue;
    		}
    			
    		$value = str_replace('&#039;', "'", htmlspecialchars($value, ENT_QUOTES));
    			
    		$tokens[] = $name.'="'.$value.'"';
    	}
    
    	if(!empty($tokens)) {
    		return ' '.implode(' ', $tokens).' ';
    	}
    	
    	return '';
    }
    
    public function compileStyles($styles)
    {
    	$tokens = array();
    	foreach($styles as $name => $value) {
    		if($value===null) {
    			continue;
    		}
    		$tokens[] = $name . ':' . $value;	
    	}
    	
    	if(!empty($tokens)) {
	    	return ' '.implode(';', $tokens).' ';
    	}
    	
    	return '';
    }
    
    public function getOption($name, $default='')
    {
    	$internalName = $this->resolveInternalOptionName($name);
    	
    	$data = get_option($internalName, false);
    	if($data===false) {
    		add_option($internalName, $default);
    		$data = $default;
    	}
    	
    	return $data;
    }
    
   /**
    * Sets a plugin option that is persisted in the database, using
    * the wordpress options table.
    *  
    * @param string $name
    * @param string $value
    * @throws EVEShipInfo_Exception
    */
    public function setOption($name, $value)
    {
    	$internalName = $this->resolveInternalOptionName($name);
    	
    	$this->getOption($internalName);
    	update_option($internalName, $value);
    }
    
   /**
    * Clears a plugin option.
    * @param string $name
    */
    public function clearOption($name)
    {
    	$internalName = $this->resolveInternalOptionName($name);
    	delete_option($internalName);
    }
    
    protected function resolveInternalOptionName($name)
    {
    	$internalName = 'eveshipinfo_'.$name;
    	 
    	// automatically use an md5 hash for option names that are too long
    	// for the available name length
    	if(strlen($internalName) > 64) {
    		$internalName = 'eveshipinfo_'.md5($name);
    	}
    	
    	return $internalName;
    }
    
    public function relativizePath($path)
    {
    	$path = str_replace('\\', '/', $path);
    	$root = str_replace('\\', '/', get_home_path());
    	
    	return str_replace($root, '', $path);
    }
    
    public function getVersion()
    {
    	$data = get_plugin_data($this->getDir().'/eve-shipinfo.php', false);
    	return $data['Version'];		
    }
}

interface EVEShipInfo_PluginInterface
{
	public function getDir();
	
	public function loadClass($className);
	
	public function registerError($errorMessage, $errorCode);
	
	public function createCollection();
	
	public function getImageWidth();
	
	public function getCSSName($part);
	
	public function getGalleryPath();

	public function getGalleryURL();
	
	public function compileAttributes($attributes);
}