<?php

class EVEShipInfo_EFTManager_Fit
{
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $manager;
	
   /**
    * The name of the fit as specified by the user in EFT.
    * @var string
    */
	protected $name;
	
   /**
    * The name of the ship, e.g. <code>Abaddon</code>.
    * @var string
    */
	protected $shipName;
	
   /**
    * Array with the following structure:
    * 
    * <pre>
    * array(
    *     'low' => array(
    *     	  'Item One',
    *         'Item Two',
    *         ...
    *     ),
    *     'med' => array(
    *     	  'Item One',
    *         'Item Two',
    *         ...
    *     ),
    *     'hi' => array(
    *     	  'Item One',
    *         'Item Two',
    *         ...
    *     ),
    *     'rig' => array(
    *     	  'Item One',
    *         'Item Two',
    *         ...
    *     ),
    *     'drone' => array(
    *     	  'Item One',
    *         'Item Two',
    *         ...
    *     )
    * )
    * </pre>
    * 
    * @var unknown
    */
	protected $hardware;
	
	protected $id;
	
	public function __construct(EVEShipInfo_EFTManager $manager, $id, $name, $ship, $hardware)
	{
		$this->manager = $manager;
		$this->id = $id;
		$this->name = $name;
		$this->shipName = $ship;
		$this->hardware = $hardware;
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function getName()
	{
		return $this->name;	
	}
	
	public function getShipName()
	{
		return $this->shipName;
	}
	
	protected $ship;
	
   /**
    * Retrieves the matching ship object instance for this fit,
    * if the ship can be found in the database. Note: always check
    * the return value of this method to avoid errors.
    * 
    * @return NULL|EVEShipInfo_Collection_Ship
    */
	public function getShip()
	{
		if(isset($this->ship)) {
			return $this->ship;
		}
		
		$this->ship = EVEShipInfo::getInstance()->createCollection()->getShipByName($this->getShipName());
		return $this->ship;
	}
	
	public function getHighSlots()
	{
		return $this->getHardware('hi');
	}
	
	public function getLowSlots()
	{
		return $this->getHardware('low');
	}
	
	public function getMedSlots()
	{
		return $this->getHardware('med');
	}
	
	public function getRigs()
	{
		return $this->getHardware('rig');
	}
	
	public function getDrones()
	{
		return $this->getHardware('drone');
	}
	
	protected function getHardware($type)
	{
		return $this->hardware[$type];
	}
	
	protected static $registeredFits = array();
	
	public function renderClientRegistration($linkID)
	{
		$fitID = $this->getID();
		if(isset(self::$registeredFits[$fitID])) {
			return '';
		}
	
		self::$registeredFits[$fitID] = true;
	
		$ship = $this->getShip();
	
		return
		$ship->renderClientRegistration().
		'<script>'.
		sprintf(
			"var fobj%s = EVEShipInfo.AddFit(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s);",
			$linkID,
			json_encode($linkID),
			json_encode($this->getID()),
			json_encode($this->getName()),
			json_encode($ship->getName()),
			json_encode($ship->getID()),
			json_encode($this->getHighSlots()),
			json_encode($this->getMedSlots()),
			json_encode($this->getLowSlots()),
			json_encode($this->getRigs()),
			json_encode($this->getDrones())
		).
		'</script>';
	}
	
}