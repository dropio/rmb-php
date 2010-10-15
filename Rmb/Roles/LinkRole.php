
<?php

class LinkRole extends Rmb_Role
{
    public function getUrl() { return $this->values['url']; }
    
    public function getPreview($type)
    {
        return false;
    }
}
