
<?php

class MovieRole extends Rmb_Role
{
    public function getDuration() { return $this->values['duration']; }

    public function getPreview($type)
    {
        return false;
    }
}
