<?php
/**
 * File containing the {@link EVEShipInfo_Collection_Ship} class.
 * 
 * @package EVEShipInfo
 * @subpackage Collection
 * @see EVEShipInfo_Collection_Ship
 */

/**
 * Container for a single ship in the collection. This is the
 * main hub to retrieve information about individual ships.  
 * 
 * @package EVEShipInfo
 * @subpackage Collection
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 */
class EVEShipInfo_Collection_Ship
{
   /**
    * @var multitype:<EVEShipInfo_Collection_Ship>
    */
    protected static $instances = array();
    
   /**
    * Factory for creating a specific ship. Note: make sure
    * that the specified ship ID exists before calling this,
    * as it will throw an exception if the ship does not exist.
    * 
    * Each ship instance is only created once to avoid any
    * overhead.
    * 
    * @param integer $shipID
    * @return EVEShipInfo_Collection_Ship
    */
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
    
    const ERROR_SHIP_DOES_NOT_EXIST = 45872001;
    
    protected function __construct($shipID)
    {
        $this->plugin = EVEShipInfo::getInstance();
        $this->collection = $this->plugin->createCollection();
        $this->id = $shipID;
        $this->name = $this->collection->getShipNameByID($shipID);
        
        if(empty($this->name)) {
        	throw new Exception(
        		'Unknown ship',
        		self::ERROR_SHIP_DOES_NOT_EXIST	
        	);
        }
    }
    
   /**
    * The ship ID. This is the EVE typeID from the invtypes table.
    * @return integer
    */
    public function getID()
    {
        return $this->id;
    }
    
   /**
    * @return string
    */
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
    
    public function getPowerLoad()
    {
    	return $this->getAttributeValue('powerLoad');
    }
    
    public function getPowerToSpeed()
    {
    	return $this->getAttribute('powerToSpeed');
    }
    
    public function getStructureKineticResistance($units=false)
    {
    	return $this->getXYResistance('structure', 'kinetic', $units);
    }
    
    public function getStructureEmResistance($units=false)
    {
    	return $this->getXYResistance('structure', 'em', $units);
    }
    
    public function getStructureThermalResistance($units=false)
    {
    	return $this->getXYResistance('structure', 'thermal', $units);
    }
    
    public function getStructureExplosiveResistance($units=false)
    {
    	return $this->getXYResistance('structure', 'explosive', $units);
    }
    
    public function getArmorKineticResistance($units=false)
    {
    	return $this->getXYResistance('armor', 'kinetic', $units);
    }

    public function getArmorEmResistance($units=false)
    {
    	return $this->getXYResistance('armor', 'em', $units);
    }
    
    public function getArmorThermalResistance($units=false)
    {
    	return $this->getXYResistance('armor', 'thermal', $units);
    }
    
    public function getArmorExplosiveResistance($units=false)
    {
    	return $this->getXYResistance('armor', 'explosive', $units);
    }
    
    public function getShieldKineticResistance($units=false)
    {
    	return $this->getXYResistance('shield', 'kinetic', $units);
    }
    
    public function getShieldEmResistance($units=false)
    {
    	return $this->getXYResistance('shield', 'em', $units);
    }
    
    public function getShieldThermalResistance($units=false)
    {
    	return $this->getXYResistance('shield', 'thermal', $units);
    }
    
    public function getShieldExplosiveResistance($units=false)
    {
    	return $this->getXYResistance('shield', 'explosive', $units);
    }
    
   /**
    * Retrieves the resistance value for the specified ship area
    * and the specified damage type.
    * 
    * @param string $area The ship area: armor, shield, structure
    * @param string $type The damage type: kinetic, em, thermal, explosive
    * @param string $units
    * @return EVEShipInfo_Collection_Ship_Attribute
    */
    public function getXYResistance($area, $type, $units=false)
    {
    	$area = strtolower($area);
    	$type = ucfirst($type);
    	
    	if($area=='structure') {
    		$area = '';
    		$type = strtolower($type);
    	}
    	 
    	$name = $area.$type.'DamageResonance';
    	
    	return $this->getAttribute($name, $units);
    }
    
    public function getMeanKineticResistance($units=false)
    {
    	return $this->getMeanXResistance('kinetic', $units);
    }
    
    public function getMeanEmResistance($units=false)
    {
    	return $this->getMeanXResistance('em', $units);
    }
    
    public function getMeanThermalResistance($units=false)
    {
    	return $this->getMeanXResistance('thermal', $units);
    }
    
    public function getMeanExplosiveResistance($units=false)
    {
    	return $this->getMeanXResistance('explosive', $units);
    }
    
   /**
    * Retrieves the average resistance value for the specified
    * damage type.
    * 
    * @param string $type The damage type: kinetic, em, thermal, explosive
    * @param boolean $units
    * @return string
    */
    public function getMeanXResistance($type, $units=false)
    {
    	$total =
    	$this->getXYResistance('structure', $type)+
    	$this->getXYResistance('armor', $type)+
    	$this->getXYResistance('shield', $type);
    	 
    	$average = number_format($total/3, 2);
    	 
    	if($units) {
    		$average .= ' %';
    	}
    	 
    	return $average;
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
    		'powerLoad' => $this->getPowerLoad(),
    		'powerToSpeed' => $this->getPowerToSpeed(),
    		'structureKineticResistance' => $this->getStructureKineticResistance(true),
    		'structureEmResistance' => $this->getStructureEmResistance(true),
    		'structureThermalResistance' => $this->getStructureThermalResistance(true),
    		'structureExplosiveResistance' => $this->getStructureExplosiveResistance(true),
    		'armorKineticResistance' => $this->getArmorKineticResistance(true),
    		'armorEmResistance' => $this->getArmorEmResistance(true),
    		'armorThermalResistance' => $this->getArmorThermalResistance(true),
    		'armorExplosiveResistance' => $this->getArmorExplosiveResistance(true),
    		'shieldKineticResistance' => $this->getShieldKineticResistance(true),
    		'shieldEmResistance' => $this->getShieldEmResistance(true),
    		'shieldThermalResistance' => $this->getShieldThermalResistance(true),
    		'shieldExplosiveResistance' => $this->getShieldExplosiveResistance(true),
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