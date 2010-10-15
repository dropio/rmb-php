<?php

class NoteRole extends Role
{
    public function getContents() { return $this->values['contents']; }

    public function getPreview($type)
    {
        return false;
    }
}
