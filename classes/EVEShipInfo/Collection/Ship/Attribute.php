<?php

class EVEShipInfo_Collection_Ship_Attribute
{
    protected $rawData;
    
    protected $name;
    
   /**
    * @var EVEShipInfo_Collection
    */
    protected $collection;
    
   /**
    * @var EVEShipInfo_Collection_Ship
    */
    protected $ship;
    
    public function __construct(EVEShipInfo_Collection_Ship $ship, $name, $rawData)
    {
        $this->collection = $ship->getCollection();
        $this->rawData = $rawData;
        $this->ship = $ship;
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }

    protected static $cypher; 
    
    protected function loadCypher()
    {
        if(!isset(self::$cypher)) {
            self::$cypher = $this->collection->getCypher('attributes');
        }
    }
    
    protected function getProperty($name)
    {
    	if(!isset(self::$cypher[$name])) {
    		return null;
    	}
    	
    	$decyphered = self::$cypher[$name];
    	
    	if(isset($this->rawData[$decyphered])) {
    		return $this->rawData[$decyphered];
    	}
    	
    	return null;
    }
    
    protected $unitName;
    
    public function getUnitName()
    {
    	if(!isset($this->unitName)) {
    		// unit names are in english, so to support translations
    		// we translate the native strings locally.
    		$this->unitName = $this->translateNativeString(
    			$this->getProperty('displayName')
    		);
    	}
    	
    	return $this->unitName;
    }
    
    public function getCategoryName()
    {
    	return $this->getProperty('categoryName');
    }
    
    public function getIconID()
    {
    	return $this->getProperty('iconID');
    }
    
    public function getValue($pretty=false)
    {
    	$this->loadCypher();
    	
    	$int = $this->getProperty('valueInt');
    	$float = $this->getProperty('valueFloat');
    	$value = $int;

    	if(strlen($value) < 1) {
    		$value = $float;
    	}
    	
    	if($pretty) {
    		$tokens = explode('.', $value);
    		$thousands = $tokens[0];
    		if(isset($tokens[1])) {
    			return number_format($value, 2);
    		}
    		
    		return number_format($value);
    	}
    	
    	return $value;
    }
    
    public function __toString()
    {
    	$value = $this->getValue();
    	return $value;
    }
    
    protected static $stringTranslations;
    
    protected function translateNativeString($string)
    {
    	if(!isset(self::$stringTranslations)) {
    		self::$stringTranslations = array(
    			'HP' => __('HP', 'EVEShipInfo'),
    			'MW' => __('MW', 'EVEShipInfo'),
    			'm/sec' => __('M/Sec', 'EVEShipInfo'),
    			'tf' => __('TF', 'EVEShipInfo'),
    			'm' => __('M', 'EVEShipInfo'),
    			's' => __('S', 'EVEShipInfo'),
    			'GJ' => __('GJ', 'EVEShipInfo'),
    			'mm' => __('MM', 'EVEShipInfo'),
    			'm3' => __('M3', 'EVEShipInfo'),
    			'Mbit/sec' => __('MB/S', 'EVEShipInfo')
    		);
    	}
    	
    	if(isset(self::$stringTranslations[$string])) {
    		return self::$stringTranslations[$string];
    	}
    	
    	return $string;
    }
}