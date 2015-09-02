<?php
/**
 * File containing the {@link EVEShipInfo_Collection} class.
 * 
 * @package EVEShipInfo
 * @subpackage Collection
 * @link EVEShipInfo_Collection
 */

/**
 * Handles the entire ship collection: offers an easy to use API
 * to retrieve ships and other data like races and the like.
 * 
 * @package EVEShipInfo
 * @subpackage Collection
 * @author Sebastian Mordziol <eve@aeonoftime.com>
 */
class EVEShipInfo_Collection
{
    const ERROR_CANNOT_OPEN_DATAFILE = 1201;
    
    const ERROR_CANNOT_UNSERIALIZE_DATAFILE = 1202;
    
    protected $plugin;
    
    protected $races = array(
        1 => 'Caldari',
        2 => 'Minmatar',
        4 => 'Amarr',
        8 => 'Gallente',
        16 => 'Jove',
        32 => 'Pirate',
        64 => 'Sleepers',
        128 => 'Ore'
    );
    
    public function __construct(EVEShipInfo_Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
    
   /**
    * @return EVEShipInfo_Plugin
    */
    public function getPlugin()
    {
    	return $this->plugin;
    }
    
   /**
    * Checks whether a race with the specified ID exists.
    * @param integer $raceID
    */
    public function raceIDExists($raceID)
    {
    	return isset($this->races[$raceID]);
    }
    
   /**
    * Returns an indexed array with all known race names.
    * @return multitype:string
    */
    public function getRaceNames()
    {
    	return array_values($this->races);
    }
    
   /**
    * Retrieves a race name by its ID. Returns an empty string
    * if it does not exist.
    * 
    * @param integer $raceID
    * @return string
    */
    public function getRaceName($raceID)
    {
        if(isset($this->races[$raceID])) {
            return $this->races[$raceID];
        }
        
        return '';
    }
    
   /**
    * Tries to find the race ID matching the specified race name.
    * The check is done in a case insensitive way. Returns null
    * if no match is found.
    * 
    * @param string $raceName
    * @return integer|NULL
    */
    public function getRaceIDByName($raceName)
    {
    	$raceName = strtolower($raceName);
    	foreach($this->races as $id => $name) {
    		if(strtolower($name) == $raceName) {
    			return $id;
    		}
    	}
    	
    	return null;
    }
    
   /**
    * Retrieves an associative array with race ID => race name
    * pairs of all known ship races.
    * 
    * @return multitype:<integer,string>
    */
    public function getRaces()
    {
        return $this->races;
    }
    
    public function getShipByID($shipID)
    {
        if(!$this->shipIDExists($shipID)) {
            return null;
        }
        
        $this->plugin->loadClass('EVEShipInfo_Collection_Ship');
        return EVEShipInfo_Collection_Ship::create($this->plugin, $this, $shipID);
    }
    
    public function getShipByName($shipName)
    {
        $id = $this->getShipIDByName($shipName);
        if($id) {
            return $this->getShipByID($id);
        }
        
        return null;
    }
    
    public function shipIDExists($shipID)
    {
        $this->loadIndex();
        return isset($this->data['lookuptable']['ids'][$shipID]);
    }
    
    public function shipNameExists($shipName)
    {
        $id = $this->getShipIDByName($shipName);
        if($id) {
        	return true;
        }
        
        return false;
    }

   /**
    * Retrieves a ship's ID by its name. The check is done case insensitively,
    * and spaces or hyphens are ignored to make it easier to match names.
    * 
    * For example all these variants will find the ship:
    * 
    * getShipIDByName('Stabber Nefantar Edition');
    * getShipIDByName('Stabber-Nefantar-Edition');
    * getShipIDByName('StabberNefantarEdition');
    * 
    * @param string $shipName
    * @return integer|NULL
    */
    public function getShipIDByName($shipName)
    {
        $this->loadIndex();
        
        $shipName = str_replace(array('-', ' '), array('', ''), strtolower($shipName));
        
        foreach($this->data['lookuptable']['names'] as $name => $id) {
        	$name = str_replace(array("'", '-', ' '), array('', '', ''), strtolower($name));
        	if($name == $shipName) {
        		return $id;
        	}
        }
        
        return null;
    }
    
    public function getShipNameByID($shipID)
    {
        $this->loadIndex();
        
        if(isset($this->data['lookuptable']['ids'][$shipID])) {
            return $this->data['lookuptable']['ids'][$shipID];
        }
        
        return '';
    }
    
    public function getShipNames()
    {
        $this->loadIndex();
        return array_values($this->data['lookuptable']['names']);
    }
    
    public function getShipIDs()
    {
    	$this->loadIndex();
    	return array_keys($this->data['lookuptable']['ids']);
    }
    
    protected $data = array();
    
   /**
    * Loads the serialized data from one of the bundled data files
    * and stores it internally.
    * 
    * @param string $name
    */
    protected function loadDataFile($name)
    {
        if(isset($this->data[$name])) {
            return;
        }
        
        $this->data[$name] = array();
        
        $path = $this->plugin->getDir().'/data/'.$name.'.ser';
        
        $raw = @file_get_contents($path);
        if(!$raw) {
            $this->plugin->registerError(
                sprintf(__('Cannot open a the data file %1$s.', 'EVEShipInfo'), '['.$name.']'),
                self::ERROR_CANNOT_OPEN_DATAFILE
            );
            return;
        }
        
        $data = @unserialize($raw);
        if(!$data) {
            $this->plugin->registerError(
                sprintf(__('Cannot decode the data file %1$s.', 'EVEShipInfo'), '['.$name.']'),
                self::ERROR_CANNOT_UNSERIALIZE_DATAFILE
            );
            return;
        }
        
        $this->data[$name] = $data;
    }
    
    protected function loadIndex()
    {
        $this->loadDataFile('lookuptable');
    }
    
    protected function loadCollection()
    {
        $this->loadDataFile('collection');
    }
    
    protected function loadCypher()
    {
        $this->loadDataFile('cypher');
    }
    
    public function getRawShipData(EVEShipInfo_Collection_Ship $ship)
    {
        $this->loadCollection();
        
        $shipID = $ship->getID();
        if(isset($this->data['collection'][$shipID])) {
            return $this->data['collection'][$shipID];
        }
        
        return array();
    }
    
    public function getCypher($part)
    {
        $this->loadCypher();
        
        if(isset($this->data['cypher'][$part])) {
            return $this->data['cypher'][$part];
        }
        
        return array();
    }
    
   /**
    * Creates a new filter instance that can be used to 
    * access the ships collection by setting a number of
    * filters to apply, to be able to retrieve only a 
    * subset of matching ships.
    * 
    * @return EVEShipInfo_Collection_Filter
    */
    public function createFilter()
    {
    	$this->plugin->loadClass('EVEShipInfo_Collection_Filter');
    	return new EVEShipInfo_Collection_Filter($this);
    }
    
   /**
    * Retrieves a list of all available ships, as an indexed array
    * with ship object instances. Note that this list is not ordered
    * in any particular way. You may want to consider using the 
    * filter, see {@link createFilter()}.
    * 
    * @return multitype:<EVEShipInfo_Collection_Ship>
    * @see createFilter()
    */
    public function getShips()
    {
    	if(isset($this->ships)) {
    		return $this->ships;
    	}
    	
    	$this->ships = array();

    	$ids = $this->getShipIDs();
    	foreach($ids as $id) {
    		$this->ships[] = $this->getShipByID($id);
    	}
    	
    	return $this->ships;
    }
    
   /**
    * Creates a new list object that can be used to build ship lists
    * using a previously configured ship collection filter. The list
    * itself can be configured further to specify columns and the like.
    * 
    * @param EVEShipInfo_Collection_Filter $filter
    * @return EVEShipInfo_Collection_List
    */
    public function createList(EVEShipInfo_Collection_Filter $filter)
    {
    	$this->plugin->loadClass('EVEShipInfo_Collection_List');
    	return new EVEShipInfo_Collection_List($filter);
    }
    
   /**
    * Creates a new gallery object that can be used to build ship galleries
    * using a previously configured ship collection filter. The list
    * itself can be configured further to specify the amount of columns
    * or rows and the like.
    * 
    * @param EVEShipInfo_Collection_Filter $filter
    * @return EVEShipInfo_Collection_List
    */
    public function createGallery(EVEShipInfo_Collection_Filter $filter)
    {
    	$this->plugin->loadClass('EVEShipInfo_Collection_Gallery');
    	return new EVEShipInfo_Collection_Gallery($filter);
    }
    
    protected static $views;
    
   /**
    * Retrieves an associative array with ship view names and
    * their translated labels.
    * 
    * @return multitype:<string, string>
    */
    public function getViews()
    {
    	if(!isset(self::$views)) {
    		self::$views = array(
    			'Front' => __('Front', 'EVEShipInfo'),
    			'Side' => __('Side', 'EVEShipInfo')
    		);
    	}
    	
    	return self::$views;
    }
    
    public function getGroups()
    {
    	$this->loadGroups();
    	
    	return $this->groups;
    }
    
	protected $groups;
    
    public function loadGroups()
    {
    	if(isset($this->groups)) {
    		return;
    	}
    	
    	/* @var $ship EVEShipInfo_Collection_Ship */
    	
    	$this->groups = array();
    	$ships = $this->getShips();
    	foreach($ships as $ship) {
    		$groupID = $ship->getGroupID();
    		if(!isset($this->groups[$groupID])) {
    			$this->groups[$groupID] = $ship->getGroupName();
    		}
    	}
    }
    
    public function getGroupNameByID($groupID)
    {
    	$this->loadGroups();
    	
    	if(isset($this->groups[$groupID])) {
    		return $this->groups[$groupID];
    	}
    	
    	return '';
    }
}