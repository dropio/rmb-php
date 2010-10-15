
<?php

class ImageRole extends Rmb_Role
{

    public function getHeight() { return $this->values['height']; }
    public function getWidth()  { return $this->values['width']; }

    public function getPreview($type)
    {
        return 'abc123';
    }
}
