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
}