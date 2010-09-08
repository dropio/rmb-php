<?php

Interface RoleType
{

    public function getPreview($type);

}

Abstract Class Role Implements RoleType
{
    
    const DEF_LOCATION  = 'DropioS3';
    private $values     = array();

    public function __construct($values=array())
    {
        $this->values = $values;

    }

    public function getName()     { return $this->values['name']; }
    public function getFileSize() { return $this->values['filesize']; }

    public function getLocation($name=self::DEF_LOCATION)
    {
      foreach($this->values['locations'] as $loc)
      {
        if($loc['name'] == $name) { return $loc; }
      }

      return false;
    }

    public function getStatus($location=self::DEF_LOCATION)
    {
        $loc = $this->getLocation($location);
        return (isset($loc['status'])) ? $log['status'] : false;
    }

    public function getFileUrl($location=self::DEF_LOCATION)
    {
        $loc = $this->getLocation($location);
        return (isset($loc['file_url'])) ? $loc['file_url'] : false;
    }

}
