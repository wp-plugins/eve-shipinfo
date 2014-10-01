<?php

class EVEShipInfo_Admin_Page_StuffBox
{
	protected $content;
	
	protected $title;
	
   /**
    * @var EVEShipInfo_Admin_Page_Tab
    */
	protected $tab;
	
	protected static $counter = 0;
	
	public function __construct(EVEShipInfo_Admin_Page_Tab $tab, $title=null)
	{
		self::$counter++;
		
		$this->id = 'stuff'.self::$counter;
		$this->tab = $tab;
		$this->title = $title;
	}
	
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}
	
	public function render()
	{
		$html = 
		'<div id="'.$this->id.'" class="stuffbox">';
			if(isset($this->title)) {
				$html .=
				'<h3>'.$this->title.'</h3>';
			}
			$html .=
			'<div class="inside">'.
				$this->content.
			'</div>'.
		'</div>';
			
		return $html;
	}
	
	public function display()
	{
		echo $this->render();
		return $this;
	}
}