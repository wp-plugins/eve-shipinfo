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