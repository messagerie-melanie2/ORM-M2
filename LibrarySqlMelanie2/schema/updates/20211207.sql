--
-- Sequence "dwp_rss_seq"
-- Name: dwp_rss_seq; Type: SEQUENCE;
--

-- DROP SEQUENCE dwp_rss_seq;

CREATE SEQUENCE public.dwp_rss_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Table "dwp_rss"
-- Name: dwp_rss; Type: TABLE;
--
-- DROP TABLE dwp_rss;

CREATE TABLE public.dwp_rss
(
	rss_id bigint DEFAULT nextval('dwp_rss_seq'::text) PRIMARY KEY,
	rss_uid text NOT NULL UNIQUE,
	rss_title text NOT NULL,
	rss_url text NOT NULL,
	rss_source varchar(20) NOT NULL,
	rss_service text NOT NULL,
	rss_creator_id text NOT NULL
);

CREATE INDEX dwp_rss_service_idx ON dwp_rss (rss_service);

--
-- Sequence "dwp_news_seq"
-- Name: dwp_news_seq; Type: SEQUENCE;
--

-- DROP SEQUENCE dwp_news_seq;

CREATE SEQUENCE public.dwp_news_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Table "dwp_news"
-- Name: dwp_news; Type: TABLE;
--
-- DROP TABLE dwp_news;

CREATE TABLE public.dwp_news
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

CREATE INDEX dwp_news_service_idx ON dwp_news (news_service);

--
-- Sequence "dwp_news_share_seq"
-- Name: dwp_news_share_seq; Type: SEQUENCE;
--

-- DROP SEQUENCE dwp_news_share_seq;

CREATE SEQUENCE public.dwp_news_share_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Table "dwp_news_share"
-- Name: dwp_news_share; Type: TABLE;
--
-- DROP TABLE dwp_news_share;

CREATE TABLE public.dwp_news_share
(
	news_share_id bigint DEFAULT nextval('dwp_news_share_seq'::text) PRIMARY KEY,
	news_share_service text NOT NULL,
	news_share_right varchar(1) NOT NULL, -- 'a' or 'p' or 'q'
	news_share_user_id text NOT NULL
);

CREATE INDEX dwp_news_share_user_id_idx ON dwp_news_share (news_share_user_id);