<?php

class EVEShipInfo_VirtualPage_ShipDetail extends EVEShipInfo_VirtualPage
{
   /**
    * @var EVEShipInfo_Collection_Ship
    */
	protected $ship;
	
	public function __construct($plugin)
	{
		parent::__construct($plugin);
		
		$this->ship = $this->plugin->getActiveShip();
	}
	
	public function renderTitle()
	{
		return $this->ship->getName();
	}
	
	public function renderContent()
	{
		$html =
		'<div class="ship-tagline">'.
			$this->ship->getRaceName().' '.$this->ship->getGroupName().
		'</div>';
		
		if($this->ship->hasScreenshot('Front')) {
		    $html .= sprintf(
			    '<p>'.
				    '<img src="%s" alt="%s"/>'.
			    '</p>',
		    	$this->ship->getScreenshotURL('Front'),
		    	sprintf(__('%1$s frontal view', 'EVEShipInfo'), $this->ship->getName())
			);
		}
		
		$html .=
		'<p class="ship-description">'.
    		nl2br(strip_tags($this->ship->getDescription()), true).
    	'</p>';
		
		if($this->ship->hasScreenshot('Side')) {
		    $html .= sprintf(
			    '<p>'.
				    '<img src="%s" alt="%s"/>'.
			    '</p>',
		    	$this->ship->getScreenshotURL('Side'),
		    	sprintf(__('%1$s side view', 'EVEShipInfo'), $this->ship->getName())
			);
		}
		
		$launchers = __('No launchers', 'EVEShipInfo');
		$launcherAmount = $this->ship->getLauncherHardpoints();
		if($launcherAmount == 1) {
			$launchers = __('1 launcher', 'EVEShipInfo');
		} else if($launcherAmount > 1) {
			$launchers = sprintf(__('%s launchers', 'EVEShipInfo'), $launcherAmount);
		}
		
		$turrets = __('No turrets', 'EVEShipInfo');
		$turretAmount = $this->ship->getTurretHardpoints();
		if($turretAmount == 1) {
			$turrets = __('1 turret', 'EVEShipInfo');
		} else if($turretAmount > 1) {
			$turrets = sprintf(__('%s turrets', 'EVEShipInfo'), $turretAmount);
		}
		
		$drones = __('None', 'EVEShipInfo');
		if($this->ship->getDronebaySize() > 0) {
			$drones = 
    		$this->ship->getDronebaySize(true).' / '.
    		$this->ship->getDroneBandwidth(true);
		}
		
		$cargo = __('None', 'EVEShipInfo');
		if($this->ship->getCargobaySize() > 0) {
			$cargo = $this->ship->getCargobaySize(true);
		}
		
		$slots = __('None', 'EVEShipInfo');
		if($this->ship->getHighSlots() > 0) {
			$slots = 
			$this->ship->getHighSlots().' / '.
		    $this->ship->getMedSlots().' / '.
		    $this->ship->getLowSlots();
		}
		
		$html .=
    	'<p class="ship-slots">'.
    		__('Slots', 'EVEShipInfo').': '.
    		$slots.' - '.
    		$launchers.', '.
    		$turrets.
    	'</p>'.
    	'<p>'.
    		__('Cargo bay', 'EVEShipInfo').': '.
    		$cargo.
    	'</p>'.
    	'<p>'.
    		__('Drones', 'EVEShipInfo').': '.$drones.
    	'</p>'.
    	'<p>'.
    		__('Warp speed', 'EVEShipInfo').': '.
    		$this->ship->getWarpSpeed(true).'<br/>'.
    		__('Max velocity', 'EVEShipInfo').': '.
    		$this->ship->getMaxVelocity(true).'<br/>'.
    		__('Agility', 'EVEShipInfo').': '.
    		$this->ship->getAgility(true).
    	'</p>'.
    	'<p>'.
    		__('Capacitor', 'EVEShipInfo').': '.
    		sprintf(__('%s power output', 'EVEShipInfo'), $this->ship->getPowerOutput(true)).' / '.
    		sprintf(__('%s capacity', 'EVEShipInfo'), $this->ship->getCapacitorCapacity(true)).' / '.
    		sprintf(__('%s recharge rate', 'EVEShipInfo'), $this->ship->getCapacitorRechargeRate(true)).
    	'</p>'.
    	'<p>'.
    		__('Shield', 'EVEShipInfo').': '.$this->ship->getShieldHitpoints(true).' / '.
    		sprintf(__('%s recharge rate', 'EVEShipInfo'), $this->ship->getShieldRechargeRate(true)).'<br/>'.
    		__('Armor', 'EVEShipInfo').': '.$this->ship->getArmorHitpoints(true).'<br/>'.
    		__('Structure', 'EVEShipInfo').': '.$this->ship->getStructureHitpoints(true).' / '.
    		sprintf(__('%s  signature radius', 'EVEShipInfo'), $this->ship->getSignatureRadius(true)).
    	'</p>'.
		'<p>'.	
			__('Max target range', 'EVEShipInfo').': '.$this->ship->getMaxTargetingRange(true).' / '.
			__('Max locked targets', 'EVEShipInfo').': '.$this->ship->getMaxLockedTargets().'<br/>'.
			__('Scan speed', 'EVEShipInfo').': '.$this->ship->getScanSpeed(true).' / '.
			__('Scan resolution', 'EVEShipInfo').': '.$this->ship->getScanResolution(true).
		'</p>';
		
		return $html;
	}
	
	public function getGUID()
	{
		return $this->ship->getViewURL();	
	}
	
	public function getPostName()
	{
		return 'eve/ship/'.$this->ship->getName();
	}
	
}