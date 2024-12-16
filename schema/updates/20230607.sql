--
-- Name: listUserObjects(); Type: FUNCTION STABLE
--
CREATE OR REPLACE FUNCTION listUserObjects(
        p_group_uid             text, 
        p_user_uid              text, 
        p_attribute_name        text
    )
    RETURNS TABLE (
        datatree_id   		integer,
        user_uid		    varchar(255),
        datatree_name		varchar(255),
        datatree_ctag		varchar(120),
        datatree_synctoken	bigint,
        attribute_value		text,
        perm_object		    text
    )
    LANGUAGE plpgsql STABLE AS
$func$
BEGIN
    RETURN QUERY
    SELECT 
        hd.datatree_id      AS datatree_id, 
        user_uid            AS user_uid, 
        datatree_name       AS datatree_name, 
        datatree_ctag       AS datatree_ctag, 
        datatree_synctoken  AS datatree_synctoken, 
        attribute_value     AS attribute_value, 
        '30'                AS perm_object 
    FROM horde_datatree hd 
    INNER JOIN horde_datatree_attributes 
        USING (datatree_id) 
    WHERE group_uid         = p_group_uid
        AND user_uid        = p_user_uid
        AND attribute_name  = p_attribute_name;
END
$func$;

--
-- Name: getDefaultObject(); Type: FUNCTION STABLE
--
CREATE OR REPLACE FUNCTION getDefaultObject(
        p_user_uid              text, 
        p_pref_scope            text,
        p_pref_name             text,
        p_group_uid             text, 
        p_attribute_name        text,
        p_attribute_perm        text,
        p_attribute_permfg      text
    )
    RETURNS TABLE (
        datatree_id   		integer,
        user_uid		    varchar(255),
        datatree_name		varchar(255),
        datatree_ctag		varchar(120),
        datatree_synctoken	bigint,
        attribute_value		text,
        perm_object		    text
    )
    LANGUAGE plpgsql STABLE AS
$func$
BEGIN
    RETURN QUERY
    SELECT 
        hd.datatree_id          AS datatree_id, 
        hd.user_uid             AS user_uid, 
        hd.datatree_name        AS datatree_name, 
        hd.datatree_ctag        AS datatree_ctag, 
        hd.datatree_synctoken   AS datatree_synctoken, 
        hda2.attribute_value    AS attribute_value, 
        hda1.attribute_value    AS perm_object 
    FROM horde_prefs hp 
    INNER JOIN horde_datatree hd 
        ON hp.pref_value = hd.datatree_name 
    INNER JOIN horde_datatree_attributes hda1 
        ON hd.datatree_id = hda1.datatree_id 
    INNER JOIN horde_datatree_attributes hda2 
        ON hd.datatree_id = hda2.datatree_id 
    WHERE 
        (hda1.attribute_name = p_attribute_perm 
                OR hda1.attribute_name = p_attribute_permfg) 
            AND hda1.attribute_key  = p_user_uid
            AND hd.group_uid        = p_group_uid
            AND hda2.attribute_name = p_attribute_name
            AND hp.pref_scope       = p_pref_scope
            AND hp.pref_name        = p_pref_name
            AND hp.pref_uid         = p_user_uid
    LIMIT 1;
END
$func$;

--
-- Name: listSharedObjects(); Type: FUNCTION STABLE
--
CREATE OR REPLACE FUNCTION listSharedObjects(
        p_group_uid             text, 
        p_user_uid              text, 
        p_attribute_name        text,
        p_attribute_perm        text,
        p_attribute_permfg      text
    )
    RETURNS TABLE (
        datatree_id   		integer,
        user_uid		    varchar(255),
        datatree_name		varchar(255),
        datatree_ctag		varchar(120),
        datatree_synctoken	bigint,
        attribute_value		text,
        perm_object		    text
    )
    LANGUAGE plpgsql STABLE AS
$func$
BEGIN
    RETURN QUERY
    SELECT 
        hd1.datatree_id         AS datatree_id, 
        hd1.user_uid            AS user_uid, 
        hd1.datatree_name       AS datatree_name, 
        hd1.datatree_ctag       AS datatree_ctag, 
        hd1.datatree_synctoken  AS datatree_synctoken, 
        hda2.attribute_value    AS attribute_value, 
        hda1.attribute_value    AS perm_object 
    FROM horde_datatree hd1 
    INNER JOIN horde_datatree_attributes hda1 
        ON hd1.datatree_id = hda1.datatree_id 
    INNER JOIN horde_datatree_attributes hda2 
        ON hd1.datatree_id = hda2.datatree_id 
    WHERE 
        (hda1.attribute_name = p_attribute_perm 
            OR hda1.attribute_name = p_attribute_permfg) 
        AND hda1.attribute_key  = p_user_uid
        AND hd1.group_uid       = p_group_uid 
        AND hda2.attribute_name = p_attribute_name;
END
$func$;

--
-- Name: listObjectsByUid(); Type: FUNCTION STABLE
--
CREATE OR REPLACE FUNCTION listObjectsByUid(
        p_group_uid             text, 
        p_datatree_name         text,
        p_attribute_name        text,
        p_attribute_perm        text,
        p_attribute_permfg      text
    )
    RETURNS TABLE (
        datatree_id   		integer,
        user_uid		    varchar(255),
        datatree_name		varchar(255),
        datatree_ctag		varchar(120),
        datatree_synctoken	bigint,
        attribute_value		text,
        perm_object		    text
    )
    LANGUAGE plpgsql STABLE AS
$func$
BEGIN
    RETURN QUERY
    SELECT 
        hd1.datatree_id         AS datatree_id, 
        hd1.user_uid            AS user_uid, 
        hd1.datatree_name       AS datatree_name, 
        hd1.datatree_ctag       AS datatree_ctag, 
        hd1.datatree_synctoken  AS datatree_synctoken, 
        hda2.attribute_value    AS attribute_value, 
        hda1.attribute_value    AS perm_object 
    FROM horde_datatree hd1 
    INNER JOIN horde_datatree_attributes hda1 
        ON hd1.datatree_id = hda1.datatree_id 
    INNER JOIN horde_datatree_attributes hda2 
        ON hd1.datatree_id = hda2.datatree_id 
    WHERE (hda1.attribute_name = p_attribute_perm 
            OR hda1.attribute_name = p_attribute_permfg) 
        AND hd1.group_uid       = p_group_uid 
        AND hda2.attribute_name = p_attribute_name
        AND hd1.datatree_name   = p_datatree_name;
END
$func$;

--
-- Name: listObjectsByUidAndUser(); Type: FUNCTION STABLE
--
CREATE OR REPLACE FUNCTION listObjectsByUidAndUser(
        p_group_uid             text, 
        p_user_uid              text, 
        p_datatree_name         text,
        p_attribute_name        text,
        p_attribute_perm        text,
        p_attribute_permfg      text
    )
    RETURNS TABLE (
        datatree_id   		integer,
        user_uid		    varchar(255),
        datatree_name		varchar(255),
        datatree_ctag		varchar(120),
        datatree_synctoken	bigint,
        attribute_value		text,
        perm_object		    text
    )
    LANGUAGE plpgsql STABLE AS
$func$
BEGIN
    RETURN QUERY
    SELECT 
        hd1.datatree_id         AS datatree_id, 
        hd1.user_uid            AS user_uid, 
        hd1.datatree_name       AS datatree_name, 
        hd1.datatree_ctag       AS datatree_ctag, 
        hd1.datatree_synctoken  AS datatree_synctoken, 
        hda2.attribute_value    AS attribute_value, 
        hda1.attribute_value    AS perm_object 
    FROM horde_datatree hd1 
    INNER JOIN horde_datatree_attributes hda1 
	    ON hd1.datatree_id = hda1.datatree_id 
    INNER JOIN horde_datatree_attributes hda2 
	    ON hd1.datatree_id = hda2.datatree_id
    WHERE 
	    (hda1.attribute_name = p_attribute_perm 
		    OR hda1.attribute_name = p_attribute_permfg) 
        AND hda1.attribute_key  = p_user_uid 
        AND hd1.group_uid       = p_group_uid 
        AND hda2.attribute_name = p_attribute_name 
        AND hd1.datatree_name   = p_datatree_name; 
END
$func$;