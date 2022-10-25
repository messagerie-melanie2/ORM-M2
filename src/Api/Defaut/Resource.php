<?php

namespace LibMelanie\Api\Defaut;

use LibMelanie\Lib\MceObject;
use LibMelanie\Objects\ResourceMelanie;

class Resource extends MceObject
{
    public function __construct(int $id = null)
    {
        $this->get_class = get_class($this);
        $this->objectmelanie = new ResourceMelanie();
        if($id) {
            $this->objectmelanie->id = $this->id = $this->object_id = $id;
        }
    }

    public function load() {
        return $this->objectmelanie->load();
    }

}
