<?php

class EVEShipInfo_Collection_Filter
{
   /**
    * @var EVEShipInfo_Collection
    */
	protected $collection;
	
	protected $orderBy = 'name';
	
	protected $ascending = true;
	
	public function __construct(EVEShipInfo_Collection $collection)
	{
		$this->collection = $collection;
	}
	
	public function getOrderBy()
	{
		return $this->orderBy;
	}
	
	public function getOrderDir()
	{
		if($this->ascending) {
			return 'ascending';
		}
		
		return 'descending';
	}
	
	public function orderByName($ascending=true)
	{
		return $this->setOrderBy('name', $ascending);
	}
	
	public function orderByGroup($ascending=true)
	{
		return $this->setOrderBy('group', $ascending);
	}
	
	public function orderByAgility($ascending=true)
	{
		return $this->setOrderBy('agility', $ascending);
	}
	
	public function orderByVelocity($ascending=true)
	{
		return $this->setOrderBy('velocity', $ascending);
	}
	
	public function orderByWarpSpeed($ascending=true)
	{
		return $this->setOrderBy('warpspeed', $ascending);
	}
	
	public function orderByHighSlots($ascending=true)
	{
		return $this->setOrderBy('highslots', $ascending);
	}
	
	public function orderByMedSlots($ascending=true)
	{
	    return $this->setOrderBy('medslots', $ascending);
	}
	
	public function orderByLowSlots($ascending=true)
	{
	    return $this->setOrderBy('lowslots', $ascending);
	}
	
	public function setOrderBy($field, $ascending=true)
	{
		if(!isset($this->orderFields)) {
			$this->getOrderFields();
		}
		
		if(isset($this->orderFields[$field])) {
			$this->orderBy = $field;
			$this->ascending = $ascending;
		} else {
			$this->addWarning(sprintf(__('Unknown order field %1$s.', 'EVEShipInfo'), '['.$field.']'));
		}
		
		return $this;
	}
	
	protected $messages = array();
	
	protected function addWarning($message)
	{
		if(!in_array($message, $this->messages)) {
			$this->messages[] = $message;
		}
	}
	
	public function hasMessages()
	{
		return !empty($this->messages);
	}
	
	public function getMessages()
	{
		return $this->messages;
	}
	
	public function count()
	{
		return count($this->getShips());
	}
	
	protected $selectedGroups;
	
	public function selectGroup($groupID)
	{
		if(isset($this->virtualGroups[$groupID])) {
			return $this->selectGroups($this->virtualGroups[$groupID]['groups']);
		}

		if(!isset($this->selectedGroups)) {
			$this->selectedGroups = array();
		}
		
		if($this->groupIDExists($groupID) && !isset($this->selectedGroups[$groupID])) {
			$this->selectedGroups[$groupID] = true;
		}
		
		return $this;
	}
	
	protected $limit = 0;
	
	public function setLimit($limit)
	{
		$this->limit = $limit;
	}
	
   /**
    * Selects a collection of ship groups at once.
    * 
    * @param array $groupIDs Indexed array with group IDs
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectGroups($groupIDs)
	{
		foreach($groupIDs as $groupID) {
			$this->selectGroup($groupID);
		}
		
		return $this;
	}
	
   /**
    * Helper method: selects all frigate ship groups, from
    * regular frigates to interceptors or expedition frigates.
    * 
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectFrigates()
	{
		return $this->selectGroups($this->virtualGroups[999993]['groups']);
	}
	
   /**
    * Helper method: selects all battleship ship groups, from
    * regular battleships to marauders and black ops.
    * 
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectBattleships()
	{
		return $this->selectGroups($this->virtualGroups[999992]['groups']);
	}
	
	protected $virtualGroups = array(
		9001 => array(
			'name' => 'All industrials',
			'groups' => array(
				28, // industrial
				380, // deep space transport
				463, // mining barge
				513, // freighter
				543, // exhumer
				883, // capital industrial ships
				902, // jump freighter
				941, // industrial command ship
				1202, // blockade runner
			)
		),
		9002 => array(
			'name' => 'All battleships',
			'groups' => array(
				27, // battleship
				898, // black ops
				900, // marauders
			) 
		),
		9003 => array(
			'name' => 'All frigates',
			'groups' => array(
				25, // frigates
				324, // assault frigates
				541, // interdictor
				830, // covert ops
				831, // interceptor
				839, // electronic attack ship
				1283, // expedition frigate
			)
		),
		9004 => array(
		    'name' => 'All capitals',
		    'groups' => array(
		    	883, // capital industrial ships
		    	547, // carrier
		    	485, // dreadnought
		    	659, // supercarrier
		    	30, // titan
		    )
		),
		9005 => array(
		    'name' => 'All battlecruisers',
		    'groups' => array(
		    	1201, // attack battlecruiser
		    	419, // combat battlecruiser
		    	540, // command ship
		    )
		),
		9006 => array(
		    'name' => 'All cruisers',
		    'groups' => array(
		    	906, // combat recon ship
		    	26, // cruiser
		    	833, // force recon ship
		    	358, // heavy assault cruiser
		    	894, // heavy interdiction cruiser
		    	832, // logistics
		    	963, // strategic cruiser
		    )
		)
	);
	
	protected $groups;
	
	public function getGroups()
	{
		if(isset($this->groups)) {
			return $this->groups;
		}
		
		$this->groups = $this->collection->getGroups();
		foreach($this->virtualGroups as $id => $def) {
			$this->groups[$id] = $def['name'];
		}
		
		asort($this->groups);
		
		return $this->groups;
	}
	
   /**
    * Helper method: selects all industrial ship groups, from regular industrials
    * to blockade runners.
    * 
    * @return EVEShipInfo_Collection_Filter
    */
	public function selectIndustrials()
	{
		return $this->selectGroups($this->virtualGroups[99991]['groups']);		
	}
	
	public function selectGroupNames($names)
	{
		foreach($names as $name) {
			$this->selectGroupName($name);
		}
		
		return $this;
	}
	
	public function selectGroupName($name)
	{
		$id = $this->getGroupIDByName($name);
		if($id) {
			$this->selectGroup($id);
		}
		
		return $this;
	}
	
	public function getGroupIDByName($name)
	{
		if(!isset($this->groups)) {
			$this->getGroups();
		}
		
		$name = strtolower($name);
		foreach($this->groups as $id => $groupName) {
			if(strtolower($groupName)==$name) {
				return $id;
			}
		}
		
		return null;
	}
	
	public function groupIDExists($groupID)
	{
		if(!isset($this->groups)) {
			$this->getGroups();
		}
		
		return isset($this->groups[$groupID]);
	}
	
   /**
    * Retrieves an indexed array with all ship object instances
    * matching the current criteria.
    * 
    * @return multitype:<EVEShipInfo_Collection_Ship>
    */
	public function getShips()
	{
		$ships = $this->collection->getShips();
		$total = count($ships);
		$result = array();
		for($i=0; $i < $total; $i++) {
			$ship = $ships[$i];
			if($this->isMatch($ship)) {
				$result[] = $ship;
			}
		}	
		
		usort($result, array($this, 'sortResults'));

		if($this->limit > 0 && $this->limit <= $total) {
		    return array_slice($result, 0, $this->limit);
		}
		
		return $result;
	}
	
   /**
    * Checks if the specified ship matches the currently selected
    * filter criteria.
    * 
    * @param EVEShipInfo_Collection_Ship $ship
    * @return boolean
    */
	protected function isMatch(EVEShipInfo_Collection_Ship $ship)
	{
		if(isset($this->search)) {
			if(!$ship->search($this->search)) {
				return false;
			}
		}
		
		// we want to limit by race
		if(isset($this->races) && !in_array($ship->getRaceID(), $this->races)) {
			return false;
		}
		
		if(isset($this->highslots) && !$this->matchNumber($ship->getHighSlots(), $this->highslots)) {
			return false;
		}
		
		if(isset($this->medslots) && !$this->matchNumber($ship->getMedSlots(), $this->medslots)) {
	        return false;
		}
		
		if(isset($this->lowslots) && !$this->matchNumber($ship->getLowSlots(), $this->lowslots)) {
	        return false;
		}
		
		if(isset($this->selectedGroups) && !isset($this->selectedGroups[$ship->getGroupID()])) {
			return false;
		}
		
		if(isset($this->agility) && !$this->matchNumber($ship->getAgility(), $this->agility)) {
			return false;
		}
		
		if(isset($this->warpSpeed) && !$this->matchNumber($ship->getWarpSpeed(), $this->warpSpeed)) {
		    return false;
		}
		
		if(isset($this->velocity) && !$this->matchNumber($ship->getVelocity(), $this->velocity)) {
			return false;
		}
		
		$attribs = array_values($this->attributes);
		$total = count($attribs);
		for($i=0; $i<$total; $i++) {
			$attrib = $attribs[$i];
			$value = $ship->getAttributeValue($attrib['name']);
			switch($attrib['type']) {
				case 'numeric':
					if(!$this->matchNumber($value, $attrib['expression'])) {
						return false;
					}
					break;
			}
		}
		
		return true;	
	}
	
   /**
    * Matches a number with an expression. Examples:
    * 
    * 4
    * bigger than 4
    * smaller than 8
    * bigger or equals 5
    * smaller or equals 4
    * between 5 and 7
    * 
    * @param unknown $number
    * @param unknown $expression
    * @return boolean
    */
	protected function matchNumber($number, $expression)
	{
		$expression = trim($expression);
		if(empty($expression)) {
			return true;
		}
		
		// match two numbers
		if(is_numeric($expression)) {
			if($number==$expression) {
				return true;
			}
			
			return false;
		}
		
		if(preg_match('/bigger[ ]*than[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $amount = $regs[1];
		    if($number > $amount) {
		    	return  true;
		    }
		    
		    return false;
		}
		
		if(preg_match('/smaller[ ]*than[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $amount = $regs[1];
		    if($number < $amount) {
		        return  true;
		    }
		
		    return false;
		}
		
		if(preg_match('/bigger[ ]*or[ ]*equals[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $amount = $regs[1];
		    if($number >= $amount) {
		        return  true;
		    }
		
		    return false;
		}

		if(preg_match('/smaller[ ]*or[ ]*equals[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $amount = $regs[1];
		    if($number <= $amount) {
		        return  true;
		    }
		
		    return false;
		}
		
		if(preg_match('/between[ ]*([0-9.]+)[ ]*and[ ]*([0-9.]+)/si', $expression, $regs)) {
		    $from = $regs[1];
		    $to = $regs[2];
		    if($to < $from) {
		    	$from = $regs[2];
		    	$to = $regs[1];
		    }
		    
		    if($number >= $from && $number <= $to) {
		    	return true;
		    }
		
		    return false;
		}
		
		$this->addWarning(sprintf(__('The expression %1$s could not be recognized.', 'EVEShipInfo'), '['.$expression.']'));
		
		return true;
	}
	
	protected $orderFields;
	
   /**
    * Retrieves an associative array with field name > field label pairs
    * of all fields that it is possible to order the list by. The name
    * can be used as parameter when setting the order via the {@link setOrderBy()}
    * method.
    * 
    * @return multitype:<string, string>
    */
	public function getOrderFields()
	{
		if(!isset($this->orderFields)) {
			$this->orderFields = array(
				'agility' => __('Agility', 'EVEShipInfo'),
				'group' => __('Group', 'EVEShipInfo'),
				'name' => __('Name', 'EVEShipInfo'),
				'warpspeed' => __('Warp speed', 'EVEShipInfo'),
				'velocity' => __('Velocity', 'EVEShipInfo'),
				'highslots' => __('High slots', 'EVEShipInfo'),
				'medslots' => __('Med slots', 'EVEShipInfo'),
				'lowslots' => __('Low slots', 'EVEShipInfo')
			); 
		}
		
		return $this->orderFields;
	}
	
   /**
    * Retrieves an associative array with race name > race label pairs
    * for all races supported for filtering the list. Both the name and
    * label work when specifying a race.
    * 
    * @return multitype:<string,string>
    */
	public function describeRaces()
	{
		$names = $this->collection->getRaceNames();
		$races = array();
		foreach($names as $name) {
			$races[strtolower($name)] = $name;
		}
		
		return $races;
	}
	
	public function describeGroups()
	{
		$groups = $this->getGroups();
		$described = array();
		foreach($groups as $id => $name) {
			$described[strtolower($name)] = $name;
		}
		
		return $described;
	}
	
	protected function sortResults(EVEShipInfo_Collection_Ship $a, EVEShipInfo_Collection_Ship $b)
	{
		$dir = 1;
		if(!$this->ascending) {
			$dir = -1;
		}
		
		switch($this->orderBy) {
			case 'agility':
				$aVal = $a->getAgility();
				$bVal = $b->getAgility();
				break;
				
			case 'group':
				$aVal = $a->getGroupName();
				$bVal = $b->getGroupName();
				break;
				
			case 'warpspeed':
				$aVal = $a->getWarpSpeed();
				$bVal = $b->getWarpSpeed();
				break;
				
			case 'highslots':
				$aVal = $a->getHighSlots();
				$bVal = $b->getHighSlots();
				break;

			case 'medslots':
			    $aVal = $a->getMedSlots();
			    $bVal = $b->getMedSlots();
			    break;
				
		    case 'lowslots':
		        $aVal = $a->getLowSlots();
		        $bVal = $b->getLowSlots();
		        break;
			         
			case 'name':
			default:
				$aVal = $a->getName();
				$bVal = $b->getName();
				break;
		}
		
		if($aVal > $bVal) {
			return 1*$dir;
		}
		
		if($aVal < $bVal) {
			return -1*$dir;
		}
		
		return 0;
	}

	protected $races;
	
	public function selectRaceByID($raceID)
	{
		if($this->collection->raceIDExists($raceID)) {
			if(!isset($this->races)) {
				$this->races = array();
			}
			
			if(!in_array($raceID, $this->races)) {
				$this->races[] = $raceID;
			}
		}
		
		return $this;
	}
	
	public function selectRaceByName($raceName)
	{
		$id = $this->collection->getRaceIDByName($raceName);
		return $this->selectRaceByID($id);
	}
	
	public function selectRaceNames($names)
	{
		foreach($names as $name) {
			$this->selectRaceByName($name);
		}
		
		return $this;
	}
	
	public function selectRaceIDs($raceIDs)
	{
		foreach($raceIDs as $raceID) {
			$this->selectRace($raceID);
		}
		
		return $this;
	}
	
	protected $attributes = array();

	public function selectAgility($expression)
	{
		$this->selectAttribute('agility', 'numeric', $expression);
	}
	
	protected function selectAttribute($name, $type, $expression)
	{
		$this->attributes[] = array(
			'name' => $name,
			'type' => $type,
			'expression' => $expression
		);
	}
	
	protected $warpSpeed;
	
	public function selectWarpSpeed($expression)
	{
		$this->warpSpeed = $expression;
	}
	
	protected $velocity;
	
	public function selectVelocity($expression)
	{
		$this->velocity = $expression;
	}
	
	protected $highslots;
	
	public function selectHighSlots($expression)
	{
		$this->highslots = $expression;
	}

	protected $medslots;
	
	public function selectMedSlots($expression)
	{
		$this->medslots = $expression;
	}

	protected $lowslots;
	
	public function selectLowSlots($expression)
	{
		$this->lowslots = $expression;
	}
	
	protected $search;
	
	public function selectSearch($terms)
	{
		$this->search = $terms;
	}
	
	public function getGroupNames()
	{
		
	}
	
	public function getVirtualGroupGroupNames($groupID)
	{
		if(!isset($this->virtualGroups[$groupID])) {
			return array();
		}
		
		$names = array();
		foreach($this->virtualGroups[$groupID]['groups'] as $gid) {
			$names[] = $this->collection->getGroupNameByID($gid);	
		} 
		
		return $names;
	}
}
