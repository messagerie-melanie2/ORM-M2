-- Schema du 20170307

--
-- Name: update_addressbook_ctag(); Type: FUNCTION; Schema: public;
--

CREATE FUNCTION update_addressbook_ctag() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    addressbook_ctag varchar;
    p_addressbook_id varchar;
BEGIN
    IF (TG_OP = 'DELETE') THEN
            p_addressbook_id := OLD.owner_id;
        ELSE
            p_addressbook_id := NEW.owner_id;
        END IF;
        
    SELECT md5(CAST(sum(t.object_ts) AS varchar)) INTO addressbook_ctag FROM turba_objects t WHERE t.owner_id = p_addressbook_id;
    IF FOUND THEN
        UPDATE horde_datatree SET datatree_ctag = addressbook_ctag WHERE datatree_name = p_addressbook_id AND group_uid = 'horde.shares.turba';
    END IF;

    IF (TG_OP = 'DELETE') THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$$;


--
-- Name: update_calendar_ctag(); Type: FUNCTION; Schema: public;
--

CREATE OR REPLACE FUNCTION update_calendar_ctag() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    calendar_ctag varchar;
    p_calendar_id varchar;
BEGIN
    IF (TG_OP = 'DELETE') THEN
            p_calendar_id := OLD.calendar_id;
        ELSE
            p_calendar_id := NEW.calendar_id;
        END IF;
        
    SELECT md5(CAST(sum(k.event_modified) AS varchar)) INTO calendar_ctag FROM kronolith_events k WHERE k.calendar_id = p_calendar_id;
    IF FOUND THEN
        UPDATE horde_datatree SET datatree_ctag = calendar_ctag WHERE datatree_name = p_calendar_id AND group_uid = 'horde.shares.kronolith';
    END IF;

    IF (TG_OP = 'DELETE') THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$$;


--
-- Name: update_taskslist_ctag(); Type: FUNCTION; Schema: public;
--

CREATE OR REPLACE FUNCTION update_taskslist_ctag() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    taskslist_ctag varchar;
    p_taskslist_id varchar;
BEGIN
    IF (TG_OP = 'DELETE') THEN
            p_taskslist_id := OLD.task_owner;
        ELSE
            p_taskslist_id := NEW.task_owner;
        END IF;
        
    SELECT md5(CAST(sum(n.task_ts) AS varchar)) INTO taskslist_ctag FROM nag_tasks n WHERE n.task_owner = p_taskslist_id;
    IF FOUND THEN
        UPDATE horde_datatree SET datatree_ctag = taskslist_ctag WHERE datatree_name = p_taskslist_id AND group_uid = 'horde.shares.nag';
    END IF;

    IF (TG_OP = 'DELETE') THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$$;


--
-- Name: horde_datatree; Type: TABLE; Schema: public;
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
-- Name: horde_datatree_attributes; Type: TABLE; Schema: public; 
--

CREATE TABLE horde_datatree_attributes (
    datatree_id integer NOT NULL,
    attribute_name character varying(255) NOT NULL,
    attribute_key character varying(255),
    attribute_value text
);

--
-- Name: horde_datatree_seq; Type: SEQUENCE; Schema: public;
--

CREATE SEQUENCE horde_datatree_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: horde_histories; Type: TABLE; Schema: public; 
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
-- Name: horde_histories_seq; Type: SEQUENCE; Schema: public;
--

CREATE SEQUENCE horde_histories_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: horde_prefs; Type: TABLE; Schema: public; 
--

CREATE TABLE horde_prefs (
    pref_uid character varying(255) NOT NULL,
    pref_scope character varying(16) DEFAULT ''::character varying NOT NULL,
    pref_name character varying(32) NOT NULL,
    pref_value text
);


--
-- Name: horde_vfs; Type: TABLE; Schema: public; 
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
-- Name: horde_vfs_seq; Type: SEQUENCE; Schema: public;
--

CREATE SEQUENCE horde_vfs_seq
    START WITH 625238
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: kronolith_events; Type: TABLE; Schema: public; 
--

CREATE TABLE kronolith_events (
    event_id character varying(32) NOT NULL,
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
    event_recurdays smallint
);

--
-- Name: lightning_attributes; Type: TABLE; Schema: public; 
--

CREATE TABLE lightning_attributes (
    event_uid character varying(255) NOT NULL,
    calendar_id character varying(255) NOT NULL,
    user_uid character varying(255) NOT NULL,
    attribute_key character varying(255) NOT NULL,
    attribute_value text NOT NULL
);

--
-- Name: nag_tasks; Type: TABLE; Schema: public; 
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
-- Name: turba_objects; Type: TABLE; Schema: public; 
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
-- Name: horde_datatree_pkey; Type: CONSTRAINT; Schema: public; 
--

ALTER TABLE ONLY horde_datatree
    ADD CONSTRAINT horde_datatree_pkey PRIMARY KEY (datatree_id);


--
-- Name: horde_histories_pkey; Type: CONSTRAINT; Schema: public; 
--

ALTER TABLE ONLY horde_histories
    ADD CONSTRAINT horde_histories_pkey PRIMARY KEY (history_id);


--
-- Name: horde_prefs_pkey; Type: CONSTRAINT; Schema: public; 
--

ALTER TABLE ONLY horde_prefs
    ADD CONSTRAINT horde_prefs_pkey PRIMARY KEY (pref_uid, pref_scope, pref_name);


--
-- Name: horde_vfs_pkey; Type: CONSTRAINT; Schema: public; 
--

ALTER TABLE ONLY horde_vfs
    ADD CONSTRAINT horde_vfs_pkey PRIMARY KEY (vfs_id);


--
-- Name: kronolith_events_pkey; Type: CONSTRAINT; Schema: public; 
--

ALTER TABLE ONLY kronolith_events
    ADD CONSTRAINT kronolith_events_pkey PRIMARY KEY (event_id);


--
-- Name: lightning_attributes_pkey; Type: CONSTRAINT; Schema: public; 
--

ALTER TABLE ONLY lightning_attributes
    ADD CONSTRAINT lightning_attributes_pkey PRIMARY KEY (event_uid, calendar_id, user_uid, attribute_key);


--
-- Name: nag_tasks_pkey; Type: CONSTRAINT; Schema: public; 
--

ALTER TABLE ONLY nag_tasks
    ADD CONSTRAINT nag_tasks_pkey PRIMARY KEY (task_id);


--
-- Name: turba_objects_pkey; Type: CONSTRAINT; Schema: public; 
--

ALTER TABLE ONLY turba_objects
    ADD CONSTRAINT turba_objects_pkey PRIMARY KEY (object_id);


--
-- Name: datatree_attribute_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_attribute_idx ON horde_datatree_attributes USING btree (datatree_id);


--
-- Name: datatree_attribute_key_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_attribute_key_idx ON horde_datatree_attributes USING btree (attribute_key);


--
-- Name: datatree_attribute_name_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_attribute_name_idx ON horde_datatree_attributes USING btree (attribute_name);


--
-- Name: datatree_attribute_value_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_attribute_value_idx ON horde_datatree_attributes USING btree (attribute_value);


--
-- Name: datatree_datatree_name_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_datatree_name_idx ON horde_datatree USING btree (datatree_name);


--
-- Name: datatree_group_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_group_idx ON horde_datatree USING btree (group_uid);


--
-- Name: datatree_order_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_order_idx ON horde_datatree USING btree (datatree_order);


--
-- Name: datatree_serialized_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_serialized_idx ON horde_datatree USING btree (datatree_serialized);


--
-- Name: datatree_user_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX datatree_user_idx ON horde_datatree USING btree (user_uid);


--
-- Name: history_action_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX history_action_idx ON horde_histories USING btree (history_action);


--
-- Name: history_ts_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX history_ts_idx ON horde_histories USING btree (history_ts);


--
-- Name: history_uid_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX history_uid_idx ON horde_histories USING btree (object_uid);


--
-- Name: kronolith_calendar_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX kronolith_calendar_idx ON kronolith_events USING btree (calendar_id);


--
-- Name: kronolith_uid_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX kronolith_uid_idx ON kronolith_events USING btree (event_uid);


--
-- Name: nag_start_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX nag_start_idx ON nag_tasks USING btree (task_start);


--
-- Name: nag_tasklist_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX nag_tasklist_idx ON nag_tasks USING btree (task_owner);


--
-- Name: nag_uid_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX nag_uid_idx ON nag_tasks USING btree (task_uid);

--
-- Name: turba_email_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX turba_email_idx ON turba_objects USING btree (object_email);


--
-- Name: turba_firstname_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX turba_firstname_idx ON turba_objects USING btree (object_firstname);


--
-- Name: turba_lastname_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX turba_lastname_idx ON turba_objects USING btree (object_lastname);


--
-- Name: turba_owner_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX turba_owner_idx ON turba_objects USING btree (owner_id);


--
-- Name: vfs_name_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX vfs_name_idx ON horde_vfs USING btree (vfs_name);


--
-- Name: vfs_path_idx; Type: INDEX; Schema: public; 
--

CREATE INDEX vfs_path_idx ON horde_vfs USING btree (vfs_path);


--
-- Name: trigger_addressbook_ctag; Type: TRIGGER;
--

CREATE TRIGGER trigger_addressbook_ctag AFTER INSERT OR DELETE OR UPDATE ON turba_objects FOR EACH ROW EXECUTE PROCEDURE update_addressbook_ctag();


--
-- Name: trigger_calendar_ctag; Type: TRIGGER; Schema: public;
--

CREATE TRIGGER trigger_calendar_ctag AFTER INSERT OR DELETE OR UPDATE ON kronolith_events FOR EACH ROW EXECUTE PROCEDURE update_calendar_ctag();


--
-- Name: trigger_taskslist_ctag; Type: TRIGGER; Schema: public;
--

CREATE TRIGGER trigger_taskslist_ctag AFTER INSERT OR DELETE OR UPDATE ON nag_tasks FOR EACH ROW EXECUTE PROCEDURE update_taskslist_ctag();

