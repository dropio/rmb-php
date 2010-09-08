<?php

class DocumentRole extends Role
{

    public function getPages() { return $this->values['pages']; }

    public function getPreview($type)
    {
        return false;
    }
}
