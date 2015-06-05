<?php

class EVEShipInfo_EFTManager_Fit
{
	const ERROR_INVALID_VISIBILITY = 450001;
	
	const VISIBILITY_PUBLIC = 'public';
	
	const VISIBILITY_PRIVATE = 'private';
	
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
    * @var array
    */
	protected $hardware;
	
   /**
    * The fitting ID.
    * @var integer
    */
	protected $id;
	
   /**
    * @var string "public" or "private"
    * @see EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE
    * @see EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC
    */
	protected $visibility;
	
   /**
    * Timestamp of the creation date.
    * @var float
    */
	protected $added;
	
	public function __construct(EVEShipInfo_EFTManager $manager, $id, $visibility, $added, $name, $ship, $hardware)
	{
		$this->manager = $manager;
		$this->id = $id;
		$this->visibility = $visibility;
		$this->added = $added;
		$this->name = $name;
		$this->shipName = $ship;
		$this->hardware = $hardware;
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function getVisibility()
	{
		return $this->visibility;
	}
	
   /**
    * Retrieves a pretty, human readable formatted creation date.
    * @return string
    */
	public function getDateAddedPretty()
	{
		$date = $this->getDateAdded();
		
		if($date->format('d.m.Y')==date('d.m.Y')) {
			return __('Today', 'EVEShipInfo').' '.$date->format('H:i');
		}
		
		$format = 'd F Y H:i';
		if($date->format('Y')==date('Y')) {
			$format = 'd F H:i'; 
		}
		
		return $date->format($format);
	}
	
   /**
    * Whether this ship is public and will be shown in the
    * ship pages.
    * 
    * @return boolean
    */
	public function isPublic()
	{
		if($this->visibility==self::VISIBILITY_PUBLIC) {
			return true;
		}
		
		return false;
	}
	
   /**
    * Whether this fit is private and will not be listed 
    * publicly in the ship pages.
    * 
    * @return boolean
    */
	public function isPrivate()
	{
		if($this->visibility==self::VISIBILITY_PRIVATE) {
			return true;
		}
		
		return false;
	}
	
   /**
    * The date and time the fit was added to the collection.
    * @return DateTime
    */
	public function getDateAdded()
	{
		$date = new DateTime();
		$date->setTimestamp($this->added);
		return $date;
	}
	
   /**
    * The name of the fit.
    * @return string
    */
	public function getName()
	{
		return $this->name;	
	}
	
   /**
    * The name of the ship this fit is for.
    * @return string
    * @see getShip()
    */
	public function getShipName()
	{
		return $this->shipName;
	}
	
   /**
    * @var EVEShipInfo_Collection_Ship
    */
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
		if(isset($this->hardware[$type])) {
			return $this->hardware[$type];
		}
		
		return null;
	}
	
	protected static $registeredFits = array();
	
   /**
    * Registers this fit clientside by adding the required 
    * javascript code to add the fit's data to the page and
    * enable showing it.
    * 
    * @param string $linkID The ID of the anchor element tied to this fit
    * @return string
    */
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

	public function toArray()
	{
		return array(
			'id' => $this->id,
			'visibility' => $this->visibility,
			'added' => $this->added,
			'name' => $this->name,
			'ship' => $this->shipName,
			'hardware' => $this->hardware
		);
	}
	
	public function makePrivate()
	{
		return $this->setVisibility(self::VISIBILITY_PRIVATE);
	}
	
	public function makePublic()
	{
		return $this->setVisibility(self::VISIBILITY_PUBLIC);
	}
	
   /**
    * Sets the visibility setting.
    * 
    * @param string $visibility
    * @throws InvalidArgumentException
    * @return boolean
    * @see makePrivate()
    * @see makePublic()
    */
	public function setVisibility($visibility)
	{
		if($visibility != self::VISIBILITY_PRIVATE && $visibility != self::VISIBILITY_PUBLIC) {
			throw new InvalidArgumentException(
				'Invalid value for the visibility key.', 
				self::ERROR_INVALID_VISIBILITY
			);
		}
		
		if($this->visibility == $visibility) {
			return false;
		}
		
		$this->visibility = $visibility;
		return true;
	}
}