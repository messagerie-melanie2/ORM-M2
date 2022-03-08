-- Schema du 20220224

--
-- Name: update_addressbook_ctag(); Type: FUNCTION
--

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
        DELETE FROM turba_sync WHERE addressbook_id = p_addressbook_id AND contact_uid = p_contact_uid;
        INSERT INTO turba_sync VALUES (a_datatree_synctoken, p_addressbook_id, p_contact_uid, p_action);
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
-- Name: update_calendar_ctag(); Type: FUNCTION
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


--
-- Name: update_taskslist_ctag(); Type: FUNCTION
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
        DELETE FROM nag_sync WHERE taskslist_id = p_taskslist_id AND task_uid = p_task_uid;
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

--
-- Name: horde_datatree; Type: TABLE
--

CREATE TABLE horde_datatree (
    datatree_id integer NOT NULL,
    group_uid character varying(255) NOT NULL,
    user_uid character varying(255) NOT NULL,
    datatree_name character varying(255) NOT NULL,
    datatree_parents character varying(255) NOT NULL,
    datatree_order integer,
    datatree_data text,
    datatree_serialized smallint DEFAULT 0 NOT NULL,
    datatree_updated timestamp without time zone,
    datatree_ctag character varying(120),
    datatree_synctoken bigint DEFAULT 0 NOT NULL
);

--
-- Name: horde_datatree_attributes; Type: TABLE
--

CREATE TABLE horde_datatree_attributes (
    datatree_id integer NOT NULL,
    attribute_name character varying(255) NOT NULL,
    attribute_key character varying(255),
    attribute_value text
);

--
-- Name: horde_datatree_seq; Type: SEQUENCE
--

CREATE SEQUENCE horde_datatree_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: horde_histories; Type: TABLE
--

CREATE TABLE horde_histories (
    history_id bigint NOT NULL,
    object_uid character varying(255) NOT NULL,
    history_action character varying(32) NOT NULL,
    history_ts bigint NOT NULL,
    history_desc text,
    history_who character varying(255),
    history_extra text
);

--
-- Name: horde_histories_seq; Type: SEQUENCE
--

CREATE SEQUENCE horde_histories_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: horde_prefs; Type: TABLE
--

CREATE TABLE horde_prefs (
    pref_uid character varying(255) NOT NULL,
    pref_scope character varying(16) DEFAULT ''::character varying NOT NULL,
    pref_name character varying(32) NOT NULL,
    pref_value text
);


--
-- Name: horde_vfs; Type: TABLE
--

CREATE TABLE horde_vfs (
    vfs_id bigint NOT NULL,
    vfs_type smallint NOT NULL,
    vfs_path character varying(255) NOT NULL,
    vfs_name character varying(255) NOT NULL,
    vfs_modified bigint NOT NULL,
    vfs_owner character varying(255) NOT NULL,
    vfs_data text
);

--
-- Name: horde_vfs_seq; Type: SEQUENCE
--

CREATE SEQUENCE horde_vfs_seq
    START WITH 625238
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: kronolith_events; Type: TABLE
--

CREATE TABLE kronolith_events (
    event_id character varying(64) NOT NULL,
    event_uid character varying(255),
    calendar_id character varying(255),
    event_creator_id character varying(255),
    event_description text,
    event_location text,
    event_status integer,
    event_attendees text,
    event_keywords text,
    event_exceptions text,
    event_title character varying(255),
    event_category character varying(80),
    event_recurenddate timestamp without time zone,
    event_start timestamp without time zone,
    event_end timestamp without time zone,
    event_alarm integer,
    event_modified integer,
    event_private integer,
    event_recurcount integer,
    event_recurtype smallint,
    event_recurinterval smallint,
    event_recurdays smallint,
    event_realuid varchar(255),
	event_created integer,
	event_modified_json integer,
	event_timezone TEXT,
	event_all_day smallint,
	event_is_deleted smallint,
	event_is_exception smallint,
	event_recurrence_id TEXT,
	event_organizer_json TEXT,
	organizer_calendar_id varchar(255),
	event_transparency varchar(10),
	event_sequence integer,
	event_priority smallint,
	event_alarm_json TEXT,
	event_recurrence_json TEXT,
	event_attachments_json TEXT,
	event_properties_json TEXT
);

--
-- Name: lightning_attributes; Type: TABLE
--

CREATE TABLE lightning_attributes (
    event_uid character varying(255) NOT NULL,
    calendar_id character varying(255) NOT NULL,
    user_uid character varying(255) NOT NULL,
    attribute_key character varying(255) NOT NULL,
    attribute_value text NOT NULL
);

--
-- Name: nag_tasks; Type: TABLE
--

CREATE TABLE nag_tasks (
    task_id character varying(32) NOT NULL,
    task_owner character varying(255) NOT NULL,
    task_name character varying(255) NOT NULL,
    task_uid character varying(255) NOT NULL,
    task_desc text,
    task_due integer,
    task_priority integer DEFAULT 0 NOT NULL,
    task_category character varying(80),
    task_completed smallint DEFAULT 0 NOT NULL,
    task_alarm integer DEFAULT 0 NOT NULL,
    task_private smallint DEFAULT 0 NOT NULL,
    task_creator character varying(255),
    task_assignee character varying(255),
    task_estimate double precision,
    task_completed_date integer,
    task_start integer,
    task_parent character varying(32),
    task_ts integer
);


--
-- Name: turba_objects; Type: TABLE
--

CREATE TABLE turba_objects (
    object_id character varying(32) NOT NULL,
    owner_id character varying(255) NOT NULL,
    object_type character varying(255) DEFAULT 'Object'::character varying NOT NULL,
    object_uid character varying(255),
    object_members text,
    object_name character varying(255),
    object_alias character varying(32),
    object_email character varying(255),
    object_homeaddress character varying(255),
    object_workaddress character varying(255),
    object_homephone character varying(25),
    object_workphone character varying(25),
    object_cellphone character varying(25),
    object_fax character varying(25),
    object_title character varying(255),
    object_company character varying(255),
    object_notes text,
    object_pgppublickey text,
    object_smimepublickey text,
    object_freebusyurl character varying(255),
    object_firstname character varying(255),
    object_lastname character varying(255),
    object_middlenames character varying(255),
    object_nameprefix character varying(255),
    object_namesuffix character varying(32),
    object_photo text,
    object_phototype character varying(10),
    object_bday character varying(10),
    object_homestreet character varying(255),
    object_homepob character varying(10),
    object_homecity character varying(255),
    object_homeprovince character varying(255),
    object_homepostalcode character varying(255),
    object_homecountry character varying(255),
    object_workstreet character varying(255),
    object_workpob character varying(10),
    object_workcity character varying(255),
    object_workprovince character varying(255),
    object_workpostalcode character varying(255),
    object_workcountry character varying(255),
    object_tz character varying(32),
    object_geo character varying(255),
    object_pager character varying(25),
    object_role character varying(255),
    object_logo text,
    object_logotype character varying(10),
    object_category character varying(80),
    object_url character varying(255),
    object_ts integer,
    object_email1 character varying(255),
    object_email2 character varying(255)
);

--
-- Name: kronolith_sync; Type: TABLE
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
-- Name: nag_sync; Type: TABLE
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
-- Name: turba_sync; Type: TABLE
--

CREATE TABLE turba_sync
(
  token bigint NOT NULL,
  addressbook_id character varying(255) NOT NULL,
  contact_uid character varying(255) NOT NULL,
  action character varying(3) NOT NULL,
  CONSTRAINT turba_sync_pkey PRIMARY KEY (token, addressbook_id)
); 


--
-- Name: pamela_tentativescnx; Type: TABLE
--

CREATE TABLE pamela_tentativescnx
(
  uid character varying(128) NOT NULL,
  lastcnx integer NOT NULL,
  nbtentatives integer NOT NULL,
  CONSTRAINT pamela_tentativescnx_pkey PRIMARY KEY (uid)
);


--
-- Name: pamela_mailcount; Type: TABLE
--

CREATE TABLE pamela_mailcount
(
  uid character varying(255) NOT NULL,
  send_time timestamp without time zone NOT NULL,
  nb_dest integer NOT NULL DEFAULT 0,
  address_ip character varying(16) NOT NULL DEFAULT '0.0.0.0'::character varying
);


--
-- Sequence "dwp_rss_seq"
-- Name: dwp_rss_seq; Type: SEQUENCE;
--

CREATE SEQUENCE dwp_rss_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Table "dwp_rss"
-- Name: dwp_rss; Type: TABLE;
--

CREATE TABLE dwp_rss
(
	rss_id bigint DEFAULT nextval('dwp_rss_seq'::text) PRIMARY KEY,
	rss_uid text NOT NULL UNIQUE,
	rss_title text NOT NULL,
	rss_url text NOT NULL,
	rss_source varchar(20) NOT NULL,
	rss_service text NOT NULL,
	rss_creator_id text NOT NULL
);


--
-- Sequence "dwp_news_seq"
-- Name: dwp_news_seq; Type: SEQUENCE;
--

CREATE SEQUENCE dwp_news_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Table "dwp_news"
-- Name: dwp_news; Type: TABLE;
--

CREATE TABLE dwp_news
(
	news_id bigint DEFAULT nextval('dwp_news_seq'::text) PRIMARY KEY,
	news_uid text NOT NULL UNIQUE,
	news_title text NOT NULL,
	news_description text,
	news_created timestamp with time zone DEFAULT now() NOT NULL,
	news_modified timestamp with time zone DEFAULT now() NOT NULL,
	news_service text NOT NULL,
	news_service_name text NOT NULL,
	news_creator_id text NOT NULL
);


--
-- Sequence "dwp_news_share_seq"
-- Name: dwp_news_share_seq; Type: SEQUENCE;
--

CREATE SEQUENCE dwp_news_share_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Table "dwp_news_share"
-- Name: dwp_news_share; Type: TABLE;
--

CREATE TABLE dwp_news_share
(
	news_share_id bigint DEFAULT nextval('dwp_news_share_seq'::text) PRIMARY KEY,
	news_share_service text NOT NULL,
	news_share_right varchar(1) NOT NULL, -- 'a' or 'p' or 'q'
	news_share_user_id text NOT NULL
);

--
-- Sequence "dwp_notifications_seq"
-- Name: dwp_notifications_seq; Type: SEQUENCE;
--

CREATE SEQUENCE dwp_notifications_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Table "dwp_notifications"
-- Name: dwp_notifications; Type: TABLE;
--

CREATE TABLE dwp_notifications
(
	notification_id bigint DEFAULT nextval('dwp_notifications_seq'::text) PRIMARY KEY,
	notification_uid text NOT NULL,
	notification_owner text NOT NULL,
	notification_from text,
	notification_title text NOT NULL,
	notification_content text NOT NULL,
	notification_category text NOT NULL,
	notification_action text,
    notification_created integer NOT NULL,
	notification_modified integer NOT NULL,
	notification_isread boolean NOT NULL,
	notification_isdeleted boolean NOT NULL
);


--
-- Name: horde_datatree_pkey; Type: CONSTRAINT
--

ALTER TABLE ONLY horde_datatree
    ADD CONSTRAINT horde_datatree_pkey PRIMARY KEY (datatree_id);


--
-- Name: horde_histories_pkey; Type: CONSTRAINT
--

ALTER TABLE ONLY horde_histories
    ADD CONSTRAINT horde_histories_pkey PRIMARY KEY (history_id);


--
-- Name: horde_prefs_pkey; Type: CONSTRAINT
--

ALTER TABLE ONLY horde_prefs
    ADD CONSTRAINT horde_prefs_pkey PRIMARY KEY (pref_uid, pref_scope, pref_name);


--
-- Name: horde_vfs_pkey; Type: CONSTRAINT
--

ALTER TABLE ONLY horde_vfs
    ADD CONSTRAINT horde_vfs_pkey PRIMARY KEY (vfs_id);


--
-- Name: kronolith_events_pkey; Type: CONSTRAINT
--

ALTER TABLE ONLY kronolith_events
    ADD CONSTRAINT kronolith_events_pkey PRIMARY KEY (event_id);


--
-- Name: lightning_attributes_pkey; Type: CONSTRAINT
--

ALTER TABLE ONLY lightning_attributes
    ADD CONSTRAINT lightning_attributes_pkey PRIMARY KEY (event_uid, calendar_id, user_uid, attribute_key);


--
-- Name: nag_tasks_pkey; Type: CONSTRAINT
--

ALTER TABLE ONLY nag_tasks
    ADD CONSTRAINT nag_tasks_pkey PRIMARY KEY (task_id);


--
-- Name: turba_objects_pkey; Type: CONSTRAINT
--

ALTER TABLE ONLY turba_objects
    ADD CONSTRAINT turba_objects_pkey PRIMARY KEY (object_id);


--
-- Name: datatree_attribute_idx; Type: INDEX
--

CREATE INDEX datatree_attribute_idx ON horde_datatree_attributes USING btree (datatree_id);


--
-- Name: datatree_attribute_key_idx; Type: INDEX
--

CREATE INDEX datatree_attribute_key_idx ON horde_datatree_attributes USING btree (attribute_key);


--
-- Name: datatree_attribute_name_idx; Type: INDEX
--

CREATE INDEX datatree_attribute_name_idx ON horde_datatree_attributes USING btree (attribute_name);


--
-- Name: datatree_attribute_value_idx; Type: INDEX
--

CREATE INDEX datatree_attribute_value_idx ON horde_datatree_attributes USING btree (attribute_value);


--
-- Name: datatree_datatree_name_idx; Type: INDEX
--

CREATE INDEX datatree_datatree_name_idx ON horde_datatree USING btree (datatree_name);


--
-- Name: datatree_group_idx; Type: INDEX
--

CREATE INDEX datatree_group_idx ON horde_datatree USING btree (group_uid);


--
-- Name: datatree_order_idx; Type: INDEX
--

CREATE INDEX datatree_order_idx ON horde_datatree USING btree (datatree_order);


--
-- Name: datatree_serialized_idx; Type: INDEX
--

CREATE INDEX datatree_serialized_idx ON horde_datatree USING btree (datatree_serialized);


--
-- Name: datatree_user_idx; Type: INDEX
--

CREATE INDEX datatree_user_idx ON horde_datatree USING btree (user_uid);


--
-- Name: history_action_idx; Type: INDEX
--

CREATE INDEX history_action_idx ON horde_histories USING btree (history_action);


--
-- Name: history_ts_idx; Type: INDEX
--

CREATE INDEX history_ts_idx ON horde_histories USING btree (history_ts);


--
-- Name: history_uid_idx; Type: INDEX
--

CREATE INDEX history_uid_idx ON horde_histories USING btree (object_uid);


--
-- Name: kronolith_calendar_idx; Type: INDEX
--

CREATE INDEX kronolith_calendar_idx ON kronolith_events USING btree (calendar_id);


--
-- Name: kronolith_uid_idx; Type: INDEX
--

CREATE INDEX kronolith_uid_idx ON kronolith_events USING btree (event_uid);


--
-- Name: kronolith_realuid_idx; Type: INDEX
--

CREATE INDEX kronolith_realuid_idx ON kronolith_events USING btree (event_realuid);


--
-- Name: nag_start_idx; Type: INDEX
--

CREATE INDEX nag_start_idx ON nag_tasks USING btree (task_start);


--
-- Name: nag_tasklist_idx; Type: INDEX
--

CREATE INDEX nag_tasklist_idx ON nag_tasks USING btree (task_owner);


--
-- Name: nag_uid_idx; Type: INDEX
--

CREATE INDEX nag_uid_idx ON nag_tasks USING btree (task_uid);

--
-- Name: turba_email_idx; Type: INDEX
--

CREATE INDEX turba_email_idx ON turba_objects USING btree (object_email);


--
-- Name: turba_firstname_idx; Type: INDEX
--

CREATE INDEX turba_firstname_idx ON turba_objects USING btree (object_firstname);


--
-- Name: turba_lastname_idx; Type: INDEX
--

CREATE INDEX turba_lastname_idx ON turba_objects USING btree (object_lastname);


--
-- Name: turba_owner_idx; Type: INDEX
--

CREATE INDEX turba_owner_idx ON turba_objects USING btree (owner_id);


--
-- Name: vfs_name_idx; Type: INDEX
--

CREATE INDEX vfs_name_idx ON horde_vfs USING btree (vfs_name);


--
-- Name: vfs_path_idx; Type: INDEX
--

CREATE INDEX vfs_path_idx ON horde_vfs USING btree (vfs_path);


--
-- Name: pamela_mailcount_nb_dest_idx; Type: INDEX
--

CREATE INDEX pamela_mailcount_nb_dest_idx ON pamela_mailcount USING btree (nb_dest);

  
--
-- Name: pamela_mailcount_send_time_idx; Type: INDEX
--

CREATE INDEX pamela_mailcount_send_time_idx ON pamela_mailcount USING btree (send_time);

  
--
-- Name: pamela_mailcount_uid_idx; Type: INDEX
--

CREATE INDEX pamela_mailcount_uid_idx ON pamela_mailcount USING btree (uid);


--
-- Name: kronolith_sync_calendar_token; Type: INDEX
--

CREATE INDEX kronolith_sync_calendar_token ON kronolith_sync USING btree (calendar_id, token DESC);


--
-- Name: nag_sync_taskslist_token; Type: INDEX
--

CREATE INDEX nag_sync_taskslist_token ON nag_sync USING btree (taskslist_id, token DESC);


--
-- Name: turba_sync_addressbook_token; Type: INDEX
--

CREATE INDEX turba_sync_addressbook_token ON turba_sync USING btree (addressbook_id, token DESC);


--
-- Name: dwp_rss_service_idx; Type: INDEX
--

CREATE INDEX dwp_rss_service_idx ON dwp_rss (rss_service);


--
-- Name: dwp_news_service_idx; Type: INDEX
--

CREATE INDEX dwp_news_service_idx ON dwp_news (news_service);


--
-- Name: dwp_news_share_user_id_idx; Type: INDEX
--

CREATE INDEX dwp_news_share_user_id_idx ON dwp_news_share (news_share_user_id);

--
-- Name: dwp_notifications_owner_idx; Type: INDEX
--

CREATE INDEX dwp_notifications_owner_idx ON dwp_notifications (notification_owner);


--
-- Name: dwp_notifications_created_modified_owner_idx; Type: INDEX
--

CREATE INDEX dwp_notifications_created_modified_owner_idx ON dwp_notifications (notification_created DESC, notification_modified DESC, notification_owner);


--
-- Name: trigger_addressbook_ctag; Type: TRIGGER;
--

CREATE TRIGGER trigger_addressbook_ctag AFTER INSERT OR DELETE OR UPDATE ON turba_objects FOR EACH ROW EXECUTE PROCEDURE update_addressbook_ctag();


--
-- Name: trigger_calendar_ctag; Type: TRIGGER
--

CREATE TRIGGER trigger_calendar_ctag AFTER INSERT OR DELETE OR UPDATE ON kronolith_events FOR EACH ROW EXECUTE PROCEDURE update_calendar_ctag();


--
-- Name: trigger_taskslist_ctag; Type: TRIGGER
--

CREATE TRIGGER trigger_taskslist_ctag AFTER INSERT OR DELETE OR UPDATE ON nag_tasks FOR EACH ROW EXECUTE PROCEDURE update_taskslist_ctag();

--
-- Sequence "workspaces_seq"
-- Name: workspaces_seq; Type: SEQUENCE; Schema: public; Owner: horde
--

-- DROP SEQUENCE workspaces_seq;

CREATE SEQUENCE workspaces_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Table "dwp_workspaces"
-- Name: dwp_workspaces; Type: TABLE; Schema: public; Owner: horde
--
-- DROP TABLE dwp_workspaces;

CREATE TABLE dwp_workspaces
(
	workspace_id bigint DEFAULT nextval('workspaces_seq'::text) PRIMARY KEY,
	workspace_uid varchar(40) NOT NULL,
	created timestamp with time zone DEFAULT now() NOT NULL,
	modified timestamp with time zone DEFAULT now() NOT NULL,
	workspace_creator varchar(255) NOT NULL,
	workspace_title varchar(255) NOT NULL,
	workspace_description text,
	workspace_logo text,
	workspace_ispublic smallint DEFAULT 0 NOT NULL,
	workspace_isarchived smallint DEFAULT 0 NOT NULL,
	workspace_objects text,
	workspace_links text,
	workspace_flux text,
	workspace_settings text
);

--
-- Table "dwp_shares"
-- Name: dwp_shares; Type: TABLE; Schema: public; Owner: horde
--

-- DROP TABLE dwp_shares;

CREATE TABLE dwp_shares
(
	workspace_id bigint NOT NULL
		REFERENCES dwp_workspaces (workspace_id) ON UPDATE CASCADE ON DELETE CASCADE,
	user_uid varchar(255) NOT NULL,
	rights varchar(1) NOT NULL
);

--
-- Sequence "hashtags_seq"
-- Name: hashtags_seq; Type: SEQUENCE; Schema: public; Owner: horde
--

-- DROP SEQUENCE hashtags_seq;

CREATE SEQUENCE hashtags_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Table "dwp_hashtags"
-- Name: dwp_hashtags; Type: TABLE; Schema: public; Owner: horde
--

-- DROP TABLE dwp_hashtags;

CREATE TABLE dwp_hashtags
(
	hashtag_id bigint DEFAULT nextval('hashtags_seq'::text) PRIMARY KEY,
	hashtag varchar(255) NOT NULL
);

--
-- Table "dwp_hashtags_workspaces"
-- Name: dwp_hashtags_workspaces; Type: TABLE; Schema: public; Owner: horde
--

-- DROP TABLE dwp_hashtags_workspaces;

CREATE TABLE dwp_hashtags_workspaces
(
	hashtag_id bigint NOT NULL
		REFERENCES dwp_hashtags (hashtag_id) ON UPDATE CASCADE ON DELETE CASCADE,
	workspace_id bigint NOT NULL
		REFERENCES dwp_workspaces (workspace_id) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Index pour les LIKE sur les hashtags : https://www.cybertec-postgresql.com/en/postgresql-more-performance-for-like-and-ilike-statements/

--
-- Name: dwp_hashtags_hashtag_idx; Type: INDEX
--
CREATE INDEX dwp_hashtags_hashtag_idx ON dwp_hashtags (hashtag);

--
-- Name: dwp_shares_user_idx; Type: INDEX
--
CREATE INDEX dwp_shares_user_idx ON dwp_shares (user_uid);

--
-- Name: dwp_workspaces_modified_idx; Type: INDEX
--
CREATE INDEX dwp_workspaces_modified_idx ON dwp_workspaces (modified DESC);

--
-- Name: dwp_workspaces_uid_idx; Type: INDEX
--
CREATE INDEX dwp_workspaces_uid_idx ON dwp_workspaces (workspace_uid);