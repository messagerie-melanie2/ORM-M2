<?php

namespace LibMelanie\Api\Defaut;

use LibMelanie\Lib\MceObject;
use LibMelanie\Objects\ResourceMelanie;

/**
 * Classe resource par defaut
 * Certains champs sont mappés directement
 *
 * @author Groupe Messagerie/MTE - Apitech
 * @package LibMCE
 * @subpackage API/Defaut
 * @api
 * @method bool exists() Test si le partage existe, en fonction de l'object_id et du nom
 * @method bool save() Sauvegarde la priopriété dans la base de données
 * @method bool delete() Supprime le partage, en fonction de l'object_id et du nom
 */
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
