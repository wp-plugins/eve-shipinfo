<?php

abstract class EVEShipInfo_Admin_UI_Renderable
{
   /**
    * @var EVEShipInfo_Admin_UI
    */
    protected $ui;
    
    protected $id;
    
	public function __construct(EVEShipInfo_Admin_UI $ui, $id)
	{
		$this->ui = $ui;
		$this->id = $id;
	}

    abstract public function render();
    
    public function display()
    {
    	echo $this->render();
    }
    
    public function toString()
    {
    	return $this->render();
    }
}