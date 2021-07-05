<?php


namespace LibMelanie\Api\Gn;


use LibMelanie\Sql\SqlMelanieRequests;

class SqlGnRequests extends SqlMelanieRequests
{

    /**
     * @var SELECT
     * @param Replace {datatree_id}
     * @param PDO :group_uid, :user_uid, :datatree_name, :attribute_name, :attribute_perm, :attribute_permfg
     */
    const listObjectsById = "
        SELECT  hd1.datatree_id as datatree_id, 
                hd1.user_uid as {user_uid}, 
                hd1.datatree_name as {datatree_name}, 
                hd1.datatree_ctag as {datatree_ctag}, 
                hd1.datatree_synctoken as {datatree_synctoken}, 
                hda1.attribute_value as {datatree_name}
            FROM horde_datatree AS hd1 
            INNER JOIN horde_datatree_attributes hda1 ON hd1.datatree_id = hda1.datatree_id AND hda1.attribute_name = 'name'
            WHERE 
                hd1.datatree_id = :datatree_id
            ";


}
