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
		return file_exists($this->getDataPath());
	}
	
   /**
    * Retrieves all available fittings.
    * @return multitype:EVEShipInfo_EFTManager_Fit
    */
	public function getFittings()
	{
		$this->load();
		return $this->fittings;
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
		$date->setTimestamp(filemtime($this->getDataPath()));
		return $date;
	}
	
	protected $fittings = array();
	
	protected $loaded = false;
	
	protected function load()
	{
		if($this->loaded) {
			return;
		}
		
		$this->loaded = true;
		
		if(!$this->hasFittings()) {
			return;
		}
		
		$this->plugin->loadClass('EVEShipInfo_EFTManager_Fit');
		
		$root = simplexml_load_file($this->getDataPath());
		$encoded = json_encode($root);
		$data = json_decode($encoded, true);
		
		if(!isset($data['fitting'])) {
			return;
		}
		
		foreach($data['fitting'] as $fit) {
			$this->loadFit($fit);
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
	
	protected function loadFit($fit)
	{
		$ship = $fit['shipType']['@attributes']['value'];
		$name = str_replace($ship.' - ', '', $fit['@attributes']['name']);
		
		// fits without modules
		if(!isset($fit['hardware'])) {
			$fit['hardware'] = array();
		}
		
		// fits with a single module
		if(isset($fit['hardware']['@attributes'])) {
			$new = array(array('@attributes' => $fit['hardware']['@attributes']));
			$fit['hardware'] = $new;
		}
		
		$hardware = array();
		foreach($fit['hardware'] as $item) {
			$slot = $item['@attributes']['slot'];
			$type = $item['@attributes']['type'];
			
			$tokens = explode(' ', $slot);
			$slotType = $tokens[0];
			if(!isset($hardware[$slotType])) {
				$hardware[$slotType] = array();
			}

			if(isset($item['@attributes']['qty'])) {
				$type .= ' x '.$item['@attributes']['qty'];
			}
				
			$hardware[$slotType][] = $type;
		}
		
		// ensure all keys are present
		$keys = array('low', 'med', 'hi', 'rig', 'drone');
		foreach($keys as $key) {
			if(!isset($hardware[$key])) {
				$hardware[$key] = array();
			}
		}
		
		$this->fittings[] = new EVEShipInfo_EFTManager_Fit($this, $name, $ship, $hardware);
	}
}