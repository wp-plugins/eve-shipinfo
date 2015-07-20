<?php

class EVEShipInfo_Admin_UI_Icon
{
    protected $type = null;

    protected $classes = array();

    protected $id = null;

    protected $attributes = array();

    protected $types = array(
        'ADD' => 'plus-alt',
    );

    public function __construct()
    {
    }

    public function add() { return $this->setType('ADD'); }

    /**
     * Sets the icon's type.
     * @param string $type
     * @return EVEShipInfo_Admin_UI_Icon
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Adds a class name that will be added to the
     * icon tag's class attribute.
     *
     * @param string $className
     * @return EVEShipInfo_Admin_UI_Icon
     */
    public function addClass($className)
    {
        if (!in_array($className, $this->classes)) {
            $this->classes[] = $className;
        }

        return $this;
    }

    public function makeDangerous()
    {
        return $this->addClass('text-error');
    }

    public function makeMuted()
    {
        return $this->addClass('muted');
    }

    public function makeSuccess()
    {
        return $this->addClass('text-success');
    }

    public function makeInformation()
    {
        return $this->addClass('text-info');
    }

   /**
    * Gives the icon a clickable style: the cursor
    * will be the click-enabled cursor. Optionally
    * a click handling statement can be specified.
    */
    public function makeClickable($statement=null)
    {
        if(!empty($statement)) {
            $this->setAttribute('onclick', $statement);    
        }
        
        return $this->addClass('clickable');
    }

    public function setID($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function render()
    {
        $this->setAttribute('class', 'dashicons dashicons-' . $this->types[$this->type] . ' ' . implode(' ', $this->classes));

        if(!empty($this->styles)) {
            $this->setAttribute('style', EVEShipInfo::array2styleString($this->styles));
        }

        $this->checkID();

        $attributes = array();
        foreach ($this->attributes as $key => $value) {
            $attributes[] = $key . '="' . $value . '"';
        }

        $tag = '<span ' . implode(' ', $attributes) . '></span>';

        return $tag;
    }

    protected function checkID()
    {
        if ($this->id == null) {
            return;
        }

        $this->setAttribute('id', $this->id);
    }

    /**
     * Override the toString method to allow an easier syntax
     * without having to call the render method manually.
     */
    public function __toString()
    {
        return $this->render();
    }
    
   /**
    * Displays a help cursor when hovering over the icon.
    * @return EVEShipInfo_Admin_UI_Icon
    */
    public function cursorHelp()
    {
        return $this->setStyle('cursor', 'help');
}

   /**
    * Sets a style for the icon's <code>style</code> attribute.
    * 
    * Example:
    * 
    * <pre>
    * $icon->setStyle('margin-right', '10px');
    * </pre>
    * 
    * @param string $name
    * @param string $value
    * @return EVEShipInfo_Admin_UI_Icon
    */
    public function setStyle($name, $value)
    {
        $this->styles[$name] = $value;
        return $this;
    }

    protected $styles = array();
}


