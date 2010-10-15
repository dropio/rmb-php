<?php

class AudioRole extends Role
{
    public function getDuration() { return $this->values['duration']; }
    public function getArtist()   { return $this->values['artist']; }
    public function getTrackTitle()   { return $this->values['track_title']; }

    public function getPreview($type)
    {
        return false;
    }
}
