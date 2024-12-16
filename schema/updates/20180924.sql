-- Table: turba_sync

CREATE TABLE turba_sync
(
  token bigint NOT NULL,
  addressbook_id character varying(255) NOT NULL,
  contact_uid character varying(255) NOT NULL,
  action character varying(3) NOT NULL,
  CONSTRAINT turba_sync_pkey PRIMARY KEY (token, addressbook_id)
); 


-- Function: update_addressbook_ctag()

CREATE OR REPLACE FUNCTION update_addressbook_ctag()
  RETURNS trigger AS
$BODY$
DECLARE
    addressbook_ctag varchar;
    p_addressbook_id varchar;
    p_contact_uid varchar;
    p_action varchar;
    a_datatree_synctoken bigint;
BEGIN
    IF (TG_OP = 'DELETE') THEN
    p_addressbook_id := OLD.owner_id;
    p_contact_uid := OLD.object_uid;
    p_action := 'del';
    ELSIF (TG_OP = 'INSERT') THEN
    p_addressbook_id := NEW.owner_id;
    p_contact_uid := NEW.object_uid;
    p_action := 'add';
    ELSIF (TG_OP = 'UPDATE') THEN
    p_addressbook_id := NEW.owner_id;
    p_contact_uid := NEW.object_uid;
    p_action := 'mod';
    END IF;
        
    SELECT md5(CAST(sum(t.object_ts) AS varchar)) INTO addressbook_ctag FROM turba_objects t WHERE t.owner_id = p_addressbook_id;
    IF NOT FOUND THEN
        addressbook_ctag := md5(p_addressbook_id);
    END IF;
    UPDATE horde_datatree SET datatree_ctag = addressbook_ctag, datatree_synctoken = datatree_synctoken + 1 WHERE datatree_name = p_addressbook_id AND group_uid = 'horde.shares.turba';
    SELECT datatree_synctoken INTO a_datatree_synctoken FROM horde_datatree WHERE datatree_name = p_addressbook_id AND group_uid = 'horde.shares.turba';
    IF FOUND THEN
        INSERT INTO turba_sync VALUES (a_datatree_synctoken, p_addressbook_id, p_contact_uid, p_action);
    END IF;

    IF (TG_OP = 'DELETE') THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;