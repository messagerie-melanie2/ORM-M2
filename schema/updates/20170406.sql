-- MAJ du 20170406

--
-- Création de la table kronolith_sync pour lister les synchronisations des événements
--

CREATE TABLE kronolith_sync
(
	token bigint NOT NULL,
	calendar_id VARCHAR(255) NOT NULL,
	event_uid VARCHAR(255) NOT NULL,
	action VARCHAR(3) NOT NULL, -- add, mod, del
	CONSTRAINT kronolith_sync_pkey PRIMARY KEY (token, calendar_id)
);


--
-- Création de la table nag_sync pour lister les synchronisations des tâches
--

CREATE TABLE nag_sync
(
	token bigint NOT NULL,
	taskslist_id VARCHAR(255) NOT NULL,
	task_uid VARCHAR(255) NOT NULL,
	action VARCHAR(3) NOT NULL, -- add, mod, del
	CONSTRAINT nag_sync_pkey PRIMARY KEY (token, taskslist_id)
);


--
-- Mise à jour du trigger d'update du calendar_ctag pour mettre à jour le syncToken
--

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
        
    SELECT md5(CAST(sum(k.event_modified) AS varchar)) INTO calendar_ctag FROM kronolith_events k WHERE k.calendar_id = p_calendar_id;
    IF NOT FOUND THEN
        calendar_ctag := md5(p_calendar_id);
    END IF;
    UPDATE horde_datatree SET datatree_ctag = calendar_ctag, datatree_synctoken = datatree_synctoken + 1 WHERE datatree_name = p_calendar_id AND group_uid = 'horde.shares.kronolith';
    SELECT datatree_synctoken INTO a_datatree_synctoken FROM horde_datatree WHERE datatree_name = p_calendar_id AND group_uid = 'horde.shares.kronolith';
    IF FOUND THEN
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


--
-- Mise à jour du trigger d'update du taskslist_ctag pour mettre à jour le syncToken
--

CREATE OR REPLACE FUNCTION update_taskslist_ctag()
  RETURNS trigger AS
$BODY$
DECLARE
    taskslist_ctag varchar;
    p_taskslist_id varchar;
    p_task_uid varchar;
    p_action varchar;
    a_datatree_synctoken bigint;
BEGIN
    IF (TG_OP = 'DELETE') THEN
		p_taskslist_id := OLD.task_owner;
		p_task_uid := OLD.task_uid;
		p_action := 'del';
    ELSIF (TG_OP = 'INSERT') THEN
		p_taskslist_id := NEW.task_owner;
		p_task_uid := NEW.task_uid;
		p_action := 'add';
    ELSIF (TG_OP = 'UPDATE') THEN
		p_taskslist_id := NEW.task_owner;
		p_task_uid := NEW.task_uid;
		p_action := 'mod';
    END IF;
        
    SELECT md5(CAST(sum(n.task_ts) AS varchar)) INTO taskslist_ctag FROM nag_tasks n WHERE n.task_owner = p_taskslist_id;
    IF NOT FOUND THEN
        taskslist_ctag := md5(p_taskslist_id);
    END IF;
    UPDATE horde_datatree SET datatree_ctag = taskslist_ctag, datatree_synctoken = datatree_synctoken + 1 WHERE datatree_name = p_taskslist_id AND group_uid = 'horde.shares.nag';
    SELECT datatree_synctoken INTO a_datatree_synctoken FROM horde_datatree WHERE datatree_name = p_taskslist_id AND group_uid = 'horde.shares.nag';
    IF FOUND THEN
        INSERT INTO nag_sync VALUES (a_datatree_synctoken, p_taskslist_id, p_task_uid, p_action);
    END IF;

    IF (TG_OP = 'DELETE') THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$BODY$
LANGUAGE plpgsql;