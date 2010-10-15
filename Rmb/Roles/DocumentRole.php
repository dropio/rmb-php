<?php

class DocumentRole extends Rmb_Role
{

    public function getPages() { return $this->values['pages']; }

    public function getPreview($type)
    {
        return false;
    }
}
