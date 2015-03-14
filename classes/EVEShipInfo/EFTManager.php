<?php

class EVEShipInfo_EFTManager
{
   /**
    * @var EVEShipInfo
    */
	protected $plugin;
	
	public function __construct(EVEShipInfo $plugin)
	{
		$this->plugin = $plugin;
	}
	
	public function getDataPath()
	{
		return $this->plugin->getDir().'/data/eft.xml';
	}
	
   /**
    * Checks whether the fittings data file exists. 
    * @return boolean
    */
	public function hasFittings()
	{
		$this->load();
		return !empty($this->fittings);
	}
	
   /**
    * Retrieves all available fittings.
    * @return multitype:EVEShipInfo_EFTManager_Fit
    */
	public function getFittings()
	{
		$this->load();
		return array_values($this->fittings);
	}
	
   /**
    * Retrieves a ship fit by its ID.
    * @param integer $id
    * @return EVEShipInfo_EFTManager_Fit|NULL
    */
	public function getFittingByID($id)
	{
		$this->load();
		if(isset($this->fittings[$id])) {
			return $this->fittings[$id];
		}
		
		return null;
	}
	
   /**
    * Retrieves the last modification date of the EFT export XML file.
    * Note: returns null if the file does not exist, so always check the
    * return value or use the {@link hasFittings()} method first.
    * 
    * @return NULL|DateTime
    */
	public function getLastModified()
	{
		if(!$this->hasFittings()) {
			return null;
		}
		
		$date = new DateTime();
		$date->setTimestamp($this->modtime);
		return $date;
	}
	
	protected $fittings = array();
	
	protected $loaded = false;
	
	protected $modtime;
	
	protected function load()
	{
		if($this->loaded) {
			return;
		}
		
		$this->loaded = true;
		$this->plugin->loadClass('EVEShipInfo_EFTManager_Fit');
		
		$data = get_option('eveshipinfo_fittings', null);
		if(!$data) {
			return;
		}
		
		$data = unserialize($data);
		
		$this->modtime = $data['updated'];
		foreach($data['fits'] as $item) {
			$this->fittings[$item['id']] = new EVEShipInfo_EFTManager_Fit(
				$this, 
			    $item['id'],
				$item['visibility'],
				$item['added'],
				$item['name'], 
				$item['ship'], 
				$item['hardware']
			);
		}
	}
	
   /**
    * Counts the amount of available fittings.
    * @return integer
    */
	public function countFittings()
	{
		$this->load();
		return count($this->fittings);
	}
	
   /**
    * Tells the manager it should refresh its data collection next
    * time information is accessed. This is used by the import function
    * to clear the internal cache.
    * 
    * @return EVEShipInfo_EFTManager
    */
	public function reload()
	{
		$this->loaded = false;
		$this->fittings = array();
		return $this;
	}
	
   /**
    * Deletes a fitting from the collection. Note that the
    * change does not get saved automatically: the {@link save()}
    * method has to be called to commit the changes.
    * 
    * @param EVEShipInfo_EFTManager_Fit $fit
    * @return boolean
    */
	public function deleteFitting(EVEShipInfo_EFTManager_Fit $fit)
	{
		$this->load();
		
		$id = $fit->getID();
		
		if(isset($this->fittings[$id])) {
			unset($this->fittings[$id]);
			return true;
		}
		
		return false;
	}

   /**
    * Checks whether the specified fit ID exists.
    * @param integer $id
    * @return boolean
    */
	public function idExists($id)
	{
		$this->load();
		return isset($this->fittings[$id]);
	}
	
   /**
    * Saves all existing fits and any changes that may have
    * been made to fits into the database.
    * 
    * @return boolean
    */
	public function save()
	{
		if(!$this->loaded) {
			return false;
		}
		
		$data = array(
			'updated' => $this->modtime,
			'fits' => array()
		);
		
		foreach($this->fittings as $fit) {
			$data['fits'][] = $fit->toArray();
		} 
		
		update_option('eveshipinfo_fittings', serialize($data));
		
		return true;
	}
	
   /**
    * Creates an returns an instance of the helper class that can be
    * used to retrieve a list of fittings matching a number of criteria.
    * Also supports ordering the list.
    * 
    * @return EVEShipInfo_EFTManager_Filters
    */
	public function getFilters()
	{
		$this->plugin->loadClass('EVEShipInfo_EFTManager_Filters');
		return new EVEShipInfo_EFTManager_Filters($this);
	}
}