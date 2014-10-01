<?php

class EVEShipInfo_Collection_Ship
{
   /**
    * @var multitype:<EVEShipInfo_Collection_Ship>
    */
    protected static $instances = array();
    
    public static function create($shipID)
    {
        if(!isset(self::$instances[$shipID])) {
            self::$instances[$shipID] = new EVEShipInfo_Collection_Ship($shipID);
        }
        
        return self::$instances[$shipID];
    }
    
    protected $id;
    
    protected $name;
    
   /**
    * @var EVEShipInfo
    */
    protected $plugin;
    
   /**
    * @var EVEShipInfo_Collection
    */
    protected $collection;
    
    protected function __construct($shipID)
    {
        $this->plugin = EVEShipInfo::getInstance();
        $this->collection = $this->plugin->createCollection();
        $this->id = $shipID;
        $this->name = $this->collection->getShipNameByID($shipID);
    }
    
    public function getID()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getDescription()
    {
        return $this->getProperty('description');
    }
    
    public function getMass($units=false)
    {
        $value = $this->getProperty('mass');
        if(!$units) {
        	return $value;
        }
        
        return number_format($value).' '.__('KG', 'EVEShipInfo');
    }
    
    public function getVolume($units=false)
    {
        $value = $this->getProperty('volume');
        if(!$units) {
        	return $value;
        }
        
        return number_format($value).' '.__('M3', 'EVEShipInfo');
    }
    
    public function getCargobaySize($units=false)
    {
    	$value = $this->getProperty('capacity');
    	if(!$units) {
    		return $value;
    	}
    	
    	return number_format($value).' '.__('M3', 'EVEShipInfo');
    }
    
    public function getRaceID()
    {
        return $this->getProperty('raceID');
    }
    
    public function getRaceName()
    {
        $id = $this->getRaceID();
        if($id) {
            return $this->collection->getRaceName($id);
        }
        
        return null;
    }
    
    public function getGroupName()
    {
        return $this->getProperty('group_groupName');
    }
    
    public function getGroupID()
    {
        return $this->getProperty('groupID');
    }
    
    public function getLowSlots()
    {
        return $this->getAttributeValue('lowSlots');
    }
    
    public function getMedSlots()
    {
        return $this->getAttributeValue('medSlots');
    }
    
    public function getHighSlots()
    {
        return $this->getAttributeValue('hiSlots');
    }
    
    public function getStructureHitpoints($units=false)
    {
    	return $this->getAttributeValue('hp', $units);
    }
    
    public function getPowerOutput($units=false)
    {
    	return $this->getAttributeValue('powerOutput', $units);
    }
    
    protected $attributes = array();
    
    protected $attributesInitialized = false;
    
   /**
    * @param string $name
    * @return EVEShipInfo_Collection_Ship_Attribute
    */
    protected function getAttributeValue($name, $units=false)
    {
        $attr = $this->getAttribute($name);
        if(!$attr) {
        	return '';
        }
        
        if($units) {
            return $attr->getValue(true).' '.$attr->getUnitName();
        }
         
        return $attr->getValue();
    }
    
    protected function getAttribute($name)
    {
    	if(isset($this->attributes[$name])) {
    		return $this->attributes[$name];
    	}
    	
   	    if(!$this->attributesInitialized) {
   	        $this->loadData();
   	        $this->loadCypher('attributeNames');
   	        $this->attributesInitialized = true;
   	    }
    	     
   	    if(!isset($this->data['_'])) {
   	        return null;
   	    }
    	     
   	    if(!isset(self::$cypher['attributeNames'][$name])) {
   	        return null;
   	    }
    	     
   	    $decyphered = self::$cypher['attributeNames'][$name];
   	    if(!isset($this->data['_'][$decyphered])) {
   	        return null;
   	    }
    	     
   	    $attr = $this->createAttribute($decyphered, $this->data['_'][$decyphered]);
   	    $this->attributes[$name] = $attr;
    	
    	return $attr;
    }
    
    protected function createAttribute($name, $rawData)
    {
        $this->plugin->loadClass('EVEShipInfo_Collection_Ship_Attribute');
        return new EVEShipInfo_Collection_Ship_Attribute($this, $name, $rawData);
    }
    
    protected $propertiesInitialized = false;
    
    protected function getProperty($name)
    {
        if(!$this->propertiesInitialized) {
            $this->loadData();
            $this->loadCypher('properties');
            $this->propertiesInitialized = true;
        }

        if(isset(self::$cypher['properties'][$name])) {
            $decyphered = self::$cypher['properties'][$name];
            if(isset($this->data[$decyphered])) {
                return $this->data[$decyphered];
            }
        }
        
        return null;
    }
    
    protected $hasScreenshot = array();
    
    public function hasScreenshot($view='Front')
    {
    	if(!isset($this->hasScreenshot[$view])) {
    		$this->hasScreenshot[$view] = file_exists($this->getScreenshotPath($view));
    	}
    	
    	return $this->hasScreenshot[$view];
    }
    
    protected $screenshotPath = array();
    
    public function getScreenshotPath($view='Front')
    {
    	if(!isset($this->screenshotPath[$view])) {
    		$this->screenshotPath[$view] = $this->plugin->getGalleryPath().'/'.$this->getName().' '.$view.'.jpg';
    	}
    	
    	return $this->screenshotPath[$view];
    }
    
    public function getScreenshotSize($view='Front')
    {
    	$path = $this->getScreenshotPath($view);
    	if(file_exists($path)) {
    		$size = getimagesize($path);
    		return array($size[0], $size[1]);
    	}
    	
    	return array(0,0);
    }
    
    protected $screenshotURL = array();
    
    public function getScreenshotURL($view='Front')
    {
    	if(!isset($this->screenshotURL[$view])) {
    	    $this->screenshotURL[$view] = $this->plugin->getGalleryURL().'/'.$this->getName().' '.$view.'.jpg';
    	}
    	 
    	return $this->screenshotURL[$view];
    }
    
    protected $data;
    
    protected function loadData()
    {
        if(!isset($this->data)) {
            $this->data = $this->collection->getRawShipData($this);
        }
    }
    
    protected static $cypher = array(); 
    
    protected function loadCypher($part)
    {
        if(!isset(self::$cypher[$part])) {
            self::$cypher[$part] = $this->collection->getCypher($part);
        }
    }
    
   /**
    * @return EVEShipInfo_Collection
    */
    public function getCollection()
    {
        return $this->collection;
    }
    
    public function getViewURL()
    {
    	$name = str_replace(array(' ', "'"), array('-', ''), $this->getName());
    	return rtrim(get_site_url(), '/').'/eve/ship/'.$name;
    }
    
    public function getMaxVelocity($units=false)
    {
    	return $this->getAttributeValue('maxVelocity', $units);
    }
    
    public function getCPUOutput($units=false)
    {
    	return $this->getAttributeValue('cpuOutput', $units);
    }
    
    public function getCapacitorRechargeRate($units=false)
    {
    	return $this->getAttributeValue('rechargeRate', $units);
    }
    
    public function getAgility($units=false)
    {
    	return $this->getAttributeValue('agility', $units);
    }
    
    public function getMaxTargetingRange($units=false)
    {
    	return $this->getAttributeValue('maxTargetRange', $units);
    }
    
    public function getScanSpeed($units=false)
    {
    	return $this->getAttributeValue('scanSpeed', $units);
    }
    
    public function getLauncherHardpoints()
    {
    	return $this->getAttributeValue('launcherSlotsLeft');
    }
    
    public function getTurretHardpoints()
    {
    	return $this->getAttributeValue('turretSlotsLeft');
    }
    
    public function getMaxLockedTargets()
    {
    	return $this->getAttributeValue('maxLockedTargets');
    }
    
    public function getShieldHitpoints($units=false)
    {
    	return $this->getAttributeValue('shieldCapacity', $units);
    }
    
    public function getArmorHitpoints($units=false)
    {
    	return $this->getAttributeValue('armorHP', $units);
    }
    
    public function getDronebaySize($units=false)
    {
    	return $this->getAttributeValue('droneCapacity', $units);
    }
    
    public function getTechLevel()
    {
    	return $this->getAttributeValue('techLevel');
    }
    
    public function getShieldRechargeRate($units=false)
    {
    	return $this->getAttributeValue('shieldRechargeRate', $units);
    }
    
    public function getCapacitorCapacity($units=false)
    {
    	return $this->getAttributeValue('capacitorCapacity', $units);
    }
    
    public function getSignatureRadius($units=false)
    {
    	return $this->getAttributeValue('signatureRadius', $units);
    } 
    
    public function getScanResolution($units=false)
    {
    	return $this->getAttributeValue('scanResolution', $units);
    }
    
    public function getTotalHitpoints($units=false)
    {
    	$total = 
    	$this->getStructureHitpoints() + 
    	$this->getShieldHitpoints() + 
    	$this->getArmorHitpoints();
    	
    	if($units) {
    		return number_format($total).' '.$this->getAttribute('armorHP')->getUnitName();
    	}
    	
    	return $total;
    }
    
    public function getWarpSpeed($units=false)
    {
    	$value = $this->getAttributeValue('baseWarpSpeed') * $this->getAttributeValue('warpSpeedMultiplier');
    	
    	if($units) {
    		return number_format($value, 2).' '.__('AU/S', 'EVEShipInfo');
    	}
    	
    	return $value;
    }
    
    public function getDroneBandwidth($units=false)
    {
    	return $this->getAttributeValue('droneBandwidth', $units);
    }
    
    public function exportData()
    {
    	$data = array(
    		'id' => $this->getID(),
    		'name' => $this->getName(),
    		'description' => nl2br($this->getDescription()),
    		'mass' => $this->getMass(true),
    		'volume' => $this->getVolume(true),
    		'cargobaySize' => $this->getCargobaySize(true),
    		'raceID' => $this->getRaceID(),
    		'raceName' => $this->getRaceName(),
    		'groupID' => $this->getGroupID(),
    		'groupName' => $this->getGroupName(),
    		'lowSlots' => $this->getLowSlots(),
    		'hiSlots' => $this->getHighSlots(),
    		'medSlots' => $this->getMedSlots(),
    		'maxVelocity' => $this->getMaxVelocity(true),
    		'powerOutput' => $this->getPowerOutput(true),
    		'capacitorRechargeRate' => $this->getCapacitorRechargeRate(true),
    		'capacitorCapacity' => $this->getCapacitorCapacity(true),
    		'shieldRechargeRate' => $this->getShieldRechargeRate(true),
    		'shieldHitpoints' => $this->getShieldHitpoints(true),
    		'agility' => $this->getAgility(true),
    		'maxTargetRange' => $this->getMaxTargetingRange(true),
    		'scanSpeed' => $this->getScanSpeed(true),
    		'launcherHardpoints' => $this->getLauncherHardpoints(),
    		'turretHardpoints' => $this->getTurretHardpoints(),
    		'maxLockedTargets' => $this->getMaxLockedTargets(),
    		'structureHitpoints' => $this->getStructureHitpoints(true),
    		'armorHitpoints' => $this->getArmorHitpoints(true),
    		'totalHitpoints' => $this->getTotalHitpoints(true),
    		'techLevel' => $this->getTechLevel(),
    		'signatureRadius' => $this->getSignatureRadius(true),
    		'scanResolution' => $this->getScanResolution(true),
    		'warpSpeed' => $this->getWarpSpeed(true),
    		'dronebaySize' => $this->getDronebaySize(true),
    		'droneBandwidth' => $this->getDroneBandwidth(true),
    		'screenshots' => array()
    	);
    	
    	$views = $this->collection->getViews();
    	foreach($views as $view => $label) {
    		$data['screenshots'][$view] = array(
    			'exists' => $this->screenshotExists($view),
    			'url' => $this->getScreenshotURL($view),
    			'size' => $this->getScreenshotSize($view)
    		);
       	}
    	
    	return $data;
    }
    
   /**
    * Checks whether a screenshot exists for this ship and the specified view.
    * @param string $view
    * @return boolean
    */
    public function screenshotExists($view='Front')
    {
    	return file_exists($this->getScreenshotPath($view));
    }
    
    public function getDecypheredData()
    {
    	$this->loadCypher('properties');
    	$this->loadCypher('attributes');
    	$this->loadCypher('attributeNames');
    	$this->loadData();
    	 
    	$data = array();
    	foreach(self::$cypher['properties'] as $name => $cypher) {
    	    $data[$name] = $this->data[$cypher];
    	}
    	 
    	$data['attributes'] = array();
    	foreach(self::$cypher['attributeNames'] as $name => $cypher) {
    		if(!isset($this->data['_'][$cypher])) {
    			continue;
    		}
    	    $raw = $this->data['_'][$cypher];
    	    $attribData = array();
    	    foreach(self::$cypher['attributes'] as $aName => $aCypher) {
    	        $attribData[$aName] = $raw[$aCypher];
    	    }
    	    $data['attributes'][$name] = $attribData;
    	}
    	 
    	return $data;
    }
    
    public function search($terms)
    {
    	if(stristr($this->getName(), $terms)) {
    		return true;
    	}
    	
    	if(stristr($this->getDescription(), $terms)) {
    		return true;
    	}
    	
    	return false;
    }
}