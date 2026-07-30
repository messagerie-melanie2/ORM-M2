CREATE OR REPLACE FUNCTION update_calendar_ctag()
  RETURNS trigger AS
$BODY$
DECLARE
    calendar_ctag varchar;
    p_calendar_id varchar;
    p_event_uid varchar;
    p_action varchar;
    a_datatree_synctoken bigint;
BEGIN
    IF (TG_OP = 'DELETE') THEN
		p_calendar_id := OLD.calendar_id;
		p_event_uid := OLD.event_uid;
		p_action := 'del';
    ELSIF (TG_OP = 'INSERT') THEN
		p_calendar_id := NEW.calendar_id;
		p_event_uid := NEW.event_uid;
		p_action := 'add';
    ELSIF (TG_OP = 'UPDATE') THEN
		p_calendar_id := NEW.calendar_id;
		p_event_uid := NEW.event_uid;
		p_action := 'mod';
    END IF;

    -- ctag aléatoire : plus de scan/aggrégat coûteux sur kronolith_events
    calendar_ctag := md5(random()::text || clock_timestamp()::text);

    UPDATE horde_datatree SET datatree_ctag = calendar_ctag, datatree_synctoken = datatree_synctoken + 1 WHERE datatree_name = p_calendar_id AND group_uid = 'horde.shares.kronolith';
    SELECT datatree_synctoken INTO a_datatree_synctoken FROM horde_datatree WHERE datatree_name = p_calendar_id AND group_uid = 'horde.shares.kronolith';
    IF FOUND THEN
        DELETE FROM kronolith_sync WHERE calendar_id = p_calendar_id AND event_uid = p_event_uid;
        INSERT INTO kronolith_sync VALUES (a_datatree_synctoken, p_calendar_id, p_event_uid, p_action);
    END IF;

    IF (TG_OP = 'DELETE') THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$BODY$
LANGUAGE plpgsql;

CREATE INDEX CONCURRENTLY idx_lightning_attributes_calendar_uid
    ON lightning_attributes (calendar_id, event_uid);

CREATE INDEX CONCURRENTLY idx_kronolith_sync_calendar_uid
    ON kronolith_sync (calendar_id, event_uid);
