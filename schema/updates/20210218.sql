--
-- Sequence "workspaces_seq"
-- Name: workspaces_seq; Type: SEQUENCE; Schema: public; Owner: horde
--

-- DROP SEQUENCE workspaces_seq;

CREATE SEQUENCE public.workspaces_seq
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

CREATE TABLE public.dwp_workspaces
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

CREATE INDEX dwp_workspaces_uid_idx ON public.dwp_workspaces (workspace_uid);

--
-- Table "dwp_shares"
-- Name: dwp_shares; Type: TABLE; Schema: public; Owner: horde
--

-- DROP TABLE dwp_shares;

CREATE TABLE public.dwp_shares
(
	workspace_id bigint NOT NULL
		REFERENCES public.dwp_workspaces (workspace_id) ON UPDATE CASCADE ON DELETE CASCADE,
	user_uid varchar(255) NOT NULL,
	rights varchar(1) NOT NULL
);

CREATE INDEX dwp_shares_user_idx ON public.dwp_shares (user_uid);

--
-- Sequence "hashtags_seq"
-- Name: hashtags_seq; Type: SEQUENCE; Schema: public; Owner: horde
--

-- DROP SEQUENCE hashtags_seq;

CREATE SEQUENCE public.hashtags_seq
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

CREATE TABLE public.dwp_hashtags
(
	hashtag_id bigint DEFAULT nextval('hashtags_seq'::text) PRIMARY KEY,
	hashtag varchar(255) NOT NULL
);

CREATE INDEX dwp_hashtags_hashtag_idx ON public.dwp_hashtags (hashtag);


--
-- Table "dwp_hashtags_workspaces"
-- Name: dwp_hashtags_workspaces; Type: TABLE; Schema: public; Owner: horde
--

-- DROP TABLE dwp_hashtags_workspaces;

CREATE TABLE public.dwp_hashtags_workspaces
(
	hashtag_id bigint NOT NULL
		REFERENCES public.dwp_hashtags (hashtag_id) ON UPDATE CASCADE ON DELETE CASCADE,
	workspace_id bigint NOT NULL
		REFERENCES public.dwp_workspaces (workspace_id) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Index pour les LIKE sur les hashtags : https://www.cybertec-postgresql.com/en/postgresql-more-performance-for-like-and-ilike-statements/
