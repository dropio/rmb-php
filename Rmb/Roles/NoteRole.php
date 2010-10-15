<?php

class NoteRole extends Rmb_Role
{
    public function getContents() { return $this->values['contents']; }

    public function getPreview($type)
    {
        return false;
    }
}
