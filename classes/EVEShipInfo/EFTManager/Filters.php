<?php

class EVEShipInfo_EFTManager_Filters
{
	const ERROR_INVALID_ORDER_FIELD = 460001;
	
	const ERROR_INVALID_ORDER_DIR = 460002;
	
	const ERROR_INVALID_INVISIBILITY_SETTING = 460003; 
	
   /**
    * @var EVEShipInfo_EFTManager
    */
	protected $manager;
	
	public function __construct(EVEShipInfo_EFTManager $manager)
	{
		$this->manager = $manager;
	}
	
	protected $search;
	
	public function setSearch($terms)
	{
		$this->search = $terms;
	}
	
	public function getSearch()
	{
		return $this->search;
	}
	
	public function getFittings()
	{
		$result = array();
		$fittings = $this->manager->getFittings();
		foreach($fittings as $fit) {
			if($this->isMatch($fit)) {
				$result[] = $fit;
			}
		}
		
		usort($result, array($this, 'handle_sortResult'));
		
		return $result;
	}
	
	protected function isMatch(EVEShipInfo_EFTManager_Fit $fit)
	{
		if(isset($this->search) && !$this->matchSearch($fit)) {
			return false;
		}
		
		if($this->visibility != 'any') {
			if($fit->getVisibility() != $this->visibility) {
				return false;
			}
		}
		
		return true;
	}
	
	protected function matchSearch(EVEShipInfo_EFTManager_Fit $fit)
	{
		if(mb_stristr($fit->getName(), $this->search)) {
			return true;
		}
		
		if(mb_stristr($fit->getShipName(), $this->search)) {
			return true;
		}
		
		return false;
	}
	
	protected $orderFields = array(
		'name', 
		'id', 
		'ship', 
		'visibility', 
		'added'
	);
	
	protected $orderBy = 'name';
	
	protected $orderDir = 'asc';

	public function orderFieldExists($field)
	{
		return in_array($field, $this->orderFields);
	}
	
	public function setOrderBy($field)
	{
		if(!in_array($field, $this->orderFields)) {
			throw new InvalidArgumentException(
				'Tried setting an invalid order field',
				self::ERROR_INVALID_ORDER_FIELD	
			);
		}
		
		$this->orderBy = $field;
	}
	
	public function getOrderBy()
	{
		return $this->orderBy;
	}
	
	public function setOrderDir($dir)
	{
		if(!$this->orderDirExists($dir)) {
			throw new InvalidArgumentException(
				'Tried setting an invalid order direction',
				self::ERROR_INVALID_ORDER_DIR	
			);
		}
		
		$this->orderDir = $dir;
	}
	
	public function orderDirExists($dir)
	{
		if($dir == 'asc' || $dir == 'desc') {
			return true;
		}
		
		return false;
	}
	
	public function getOrderDir()
	{
		return $this->orderDir;
	}
	
   /**
    * Callback method used to sort the fittings list.
    * 
    * @param EVEShipInfo_EFTManager_Fit $a
    * @param EVEShipInfo_EFTManager_Fit $b
    * @return number
    */
	public function handle_sortResult(EVEShipInfo_EFTManager_Fit $a, EVEShipInfo_EFTManager_Fit $b)
	{
		switch($this->orderBy) {
			case 'name':
				$valA = $a->getName();
				$valB = $b->getName();
				break;
				
			case 'id':
				$valA = $a->getID();
				$valB = $b->getID();
				break;
			
			case 'ship':
				$valA = $a->getShipName();
				$valB = $b->getShipName();
				break;
				
			case 'visibility':
				$valA = $a->getVisibility();
				$valB = $b->getVisibility();
				break;

			case 'added':
				$valA = $a->getDateAdded()->format('Y.m.d H:i:s');
				$valB = $b->getDateAdded()->format('Y.m.d H:i:s');
				break;
		}
		
		$dir = 1;
		if($this->orderDir=='desc') {
			$dir = -1;
		}
		
		return strnatcasecmp($valA, $valB) * $dir;
	}
	
	public function renderOrderBySelect()
	{
		$options = array(
			'name' => __('Fitting name', 'EVEShipInfo'),
			'id' => __('ID', 'EVEShipInfo'),
			'ship' => __('Ship name', 'EVEShipInfo'),
			'visibility' => __('Visibility', 'EVEShipInfo'),
			'added' => __('Date added', 'EVEShipInfo')
		);
		
		$html = 
		'<select name="order_by">';
			foreach($options as $value => $label) {
				$selected = '';
				if($value==$this->orderBy) {
					$selected = ' selected="selected"';
				}
				
				$html .=
				'<option value="'.$value.'"'.$selected.'>'.
					$label.
				'</option>';
			}
			$html .=
		'</select>';
			
		return $html;
	}
	
	public function renderOrderDirSelect()
	{
		$options = array(
			'asc' => __('Ascending', 'EVEShipInfo'),
			'desc' => __('Descending', 'EVEShipInfo')
		);
		
		$html =
		'<select name="order_dir">';
			foreach($options as $value => $label) {
				$selected = '';
				if($value==$this->orderDir) {
					$selected = ' selected="selected"';
				}
			
				$html .=
				'<option value="'.$value.'"'.$selected.'>'.
					$label.
				'</option>';
			}
			$html .=
		'</select>';
			
		return $html;
	}

	public function renderVisibilitySelect()
	{
		$options = array(
			'any' => __('Any visibility', 'EVEShipInfo'),
			EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC => __('Public', 'EVEShipInfo'),
			EVEShipInfo_EFTManager_Fit::VISIBILITY_PRIVATE => __('Private', 'EVEShipInfo')
		);
		
		$html =
		'<select name="visibility">';
			foreach($options as $value => $label) {
				$selected = '';
				if($value==$this->visibility) {
					$selected = ' selected="selected"';
				}
			
				$html .=
				'<option value="'.$value.'"'.$selected.'>'.
					$label.
				'</option>';
			}
			$html .=
		'</select>';
			
		return $html;
	}
	
	protected $visibility = 'any';
	
	protected $visibilities = array(
		'any',
		EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC,
		EVEShipInfo_EFTManager_Fit::VISIBILITY_PUBLIC
	);
	
	public function setVisibility($visibility)
	{
		if(!$this->visibilityExists($visibility)) {
			throw new InvalidArgumentException(
				'Tried setting an invalid visibility setting.',
				self::ERROR_INVALID_INVISIBILITY_SETTING	
			);
		}
		
		$this->visibility = $visibility;
	}
	
	public function visibilityExists($visibility)
	{
		return in_array($visibility, $this->visibilities);
	}
}