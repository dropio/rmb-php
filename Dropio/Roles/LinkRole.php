
<?php

class LinkRole extends Role
{
    public function getUrl() { return $this->values['url']; }
    
    public function getPreview($type)
    {
        return false;
    }
}
