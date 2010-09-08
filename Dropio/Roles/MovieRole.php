
<?php

class MovieRole extends Role
{
    public function getDuration() { return $this->values['duration']; }

    public function getPreview($type)
    {
        return false;
    }
}
