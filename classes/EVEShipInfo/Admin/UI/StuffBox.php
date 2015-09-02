<?php

class EVEShipInfo_Admin_UI_StuffBox extends EVEShipInfo_Admin_UI_Renderable
{
    protected $collapsible = false;
    
    protected $content;
    
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}
	
    protected $title;
    
    public function setTitle($title)
    {
    	$this->title = $title;
    	return $this;
    }
    
   /**
    * @var EVEShipInfo_Admin_UI_Icon
    */
    protected $icon;
    
    public function setIcon(EVEShipInfo_Admin_UI_Icon $icon)
    {
    	$this->icon = $icon;
		return $this;    	
    }
    
    public function setCollapsible($collapsible=true)
    {
    	$this->collapsible = $collapsible;
    	return $this;
    }
    
    protected $collapsed = false;
    
    public function setCollapsed($collapsed=true)
    {
        $this->setCollapsible();
    	$this->collapsed = $collapsed;
    	return $this;
    }
    
    protected $abstract;
    
    public function setAbstract($abstract)
    {
    	$this->abstract = $abstract;
    	return $this;
    }
    
    public function makeError()
    {
    	return $this->addClass('meta-box-error');
    }
    
    protected $classes = array();
    
    public function addClass($class)
    {
    	if(!in_array($class, $this->classes)) {
    		$this->classes[] = $class;
    	}
    	
    	return $this;
    }
    
	public function render()
	{
		$this->addClass('meta-box-sortables');
		
	    $class = 'stuffbox';
	    if($this->collapsible) {
	    	$class = 'postbox';
	    }
	    
		$html = 
		'<div class="'.implode(' ', $this->classes).'">'.
			'<div id="'.$this->id.'" class="'.$class.'">';
				if($this->collapsible) {
				    $html .=
	 			    '<div class="handlediv" title="'.__('Click to toggle').'" onclick="jQuery(\'#'.$this->id.'-inside\').toggle()"><br></div>';
				}
				
				$title = '';
				if(isset($this->icon)) {
					$title = $this->icon->render() . ' ';
				}
				
				if(isset($this->title)) {
					$title .= $this->title;
				}
				
				if(!empty($title)) {
				    $handle = '';
				    if($this->collapsible) {
				        $handle = ' class="hndle" style="cursor:pointer;" onclick="jQuery(\'#'.$this->id.'-inside\').toggle()"';
				    }
					$html .=
					'<h3'.$handle.'>'.$title.'</h3>';
				}
				
				$collapsed = '';
				if($this->collapsed) {
					$collapsed = ' style="display:none"';
				}
				
				$html .=
				'<div class="inside" id="'.$this->id.'-inside"'.$collapsed.'>';
					if(isset($this->abstract)) {
						$html .=
						'<p>'.$this->abstract.'</p>';
					} else if($this->collapsible) {
						$html .= '<br/>';
					}
					
					$html .=
					$this->content.
				'</div>'.
			'</div>'.
		'</div>';
			
		return $html;
	}
}