----- NEWS & NOTIFICATIONS

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

----- WORKSPACES

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

----- FORUM

--
-- Table "dwp_posts"
-- Name: dwp_posts; Type: TABLE; Schema: public; Owner: horde
--
-- DROP TABLE dwp_posts;

CREATE TABLE dwp_posts (
    "post_id" bigserial PRIMARY KEY,
    "post_title" text not null,
    "post_summary" text not null,
    "post_content" text not null,
    "post_uid" varchar(64) not null,
    "created" timestamp without time zone DEFAULT now() not null,
    "updated" timestamp without time zone DEFAULT now() not null,
    "user_uid" varchar(64) not null,
    "post_isdraft" smallint DEFAULT 0 NOT NULL,
    "post_settings" text,
    "workspace_uid" varchar(40) not null,
    "post_history" text
);

--
-- Table "dwp_posts_images"
-- Name: dwp_posts_images; Type: TABLE; Schema: public; Owner: horde
--
-- DROP TABLE dwp_posts_images;

CREATE TABLE dwp_posts_images (
    "image_id" bigserial PRIMARY KEY,
    "post_id" bigint not null
        references "dwp_posts" ("post_id") on delete cascade on update cascade,
    "image_uid" varchar(64) not null,
    "image_data" text not null
);

--
-- Table "dwp_posts_comments"
-- Name: dwp_posts_comments; Type: TABLE; Schema: public; Owner: horde
--
-- DROP TABLE dwp_posts_comments;

CREATE TABLE dwp_posts_comments (
    "comment_id" bigserial PRIMARY KEY,
    "comment_content" text not null,
    "comment_uid" varchar(64) not null,
    "created" timestamp without time zone DEFAULT now() not null,
    "updated" timestamp without time zone DEFAULT now() not null,
    "user_uid" varchar(64) not null,
    "post_id" bigint not null
        references "dwp_posts" ("post_id") on delete cascade on update cascade,
    "parent_comment_id" bigint default null
        references "dwp_posts_comments" ("comment_id") on delete cascade on update cascade
);

--
-- Table "dwp_posts_comments_like"
-- Name: dwp_posts_comments_like; Type: TABLE; Schema: public; Owner: horde
--
-- DROP TABLE dwp_posts_comments_like;

CREATE TABLE dwp_posts_comments_like (
    "like_id" bigserial PRIMARY KEY,
    "like_type" text not null,
    "updated" timestamp without time zone DEFAULT now() not null,
    "user_uid" varchar(64) not null,
    "comment_id" bigint not null
        references "dwp_posts_comments" ("comment_id") on delete cascade on update cascade
);

--
-- Table "dwp_posts_reactions"
-- Name: dwp_posts_reactions; Type: TABLE; Schema: public; Owner: horde
--
-- DROP TABLE dwp_posts_reactions;

CREATE TABLE dwp_posts_reactions (
    "reaction_id" bigserial PRIMARY KEY,
    "reaction_type" text not null,
    "user_uid" varchar(64) not null,
    "post_id" bigint not null
        references "dwp_posts" ("post_id") on delete cascade on update cascade,
    "updated" timestamp without time zone DEFAULT now() not null
);

-- Table "dwp_posts_tags"
-- Name: dwp_posts_tags; Type: TABLE; Schema: public; Owner: horde
--
-- DROP TABLE dwp_posts_tags;

CREATE TABLE dwp_posts_tags (
    "tag_id" bigserial PRIMARY KEY,
    "tag_name" text not null,
    "workspace_uid" varchar(40) not null
);

-- Table "dwp_posts_tagbypost"
-- Name: dwp_posts_tagbypost; Type: TABLE; Schema: public; Owner: horde
--
-- DROP TABLE dwp_posts_tagbypost;

CREATE TABLE dwp_posts_tagbypost (
    "tag_id" bigint not null
        references "dwp_posts_tags" ("tag_id") on delete cascade on update cascade,
    "post_id" bigint not null
        references "dwp_posts" ("post_id") on delete cascade on update cascade,
    PRIMARY KEY ("tag_id", "post_id")
);

--
-- Name: dwp_posts_uid_idx; Type: INDEX
--
CREATE INDEX dwp_posts_uid_idx ON dwp_posts (post_uid);

--
-- Name: dwp_posts_images_uid_idx; Type: INDEX
--
CREATE INDEX dwp_posts_images_uid_idx ON dwp_posts_images (image_uid);

--
-- Name: dwp_posts_images_post_id_idx; Type: INDEX
--
CREATE INDEX dwp_posts_images_post_id_idx ON dwp_posts_images (post_id);

--
-- Name: dwp_posts_comments_uid_idx; Type: INDEX
--
CREATE INDEX dwp_posts_comments_uid_idx ON dwp_posts_comments (comment_uid);

--
-- Name: dwp_posts_comments_post_id_idx; Type: INDEX
--
CREATE INDEX dwp_posts_comments_post_id_idx ON dwp_posts_comments (post_id);

--
-- Name: dwp_posts_reactions_post_id_idx; Type: INDEX
--
CREATE INDEX dwp_posts_reactions_post_id_idx ON dwp_posts_reactions (post_id);

--
-- Name: dwp_posts_reactions_post_id_reaction_type_user_uid_idx; Type: INDEX
--
CREATE INDEX dwp_posts_reactions_post_id_reaction_type_user_uid_idx ON dwp_posts_reactions (post_id, reaction_type, user_uid);

--
-- Name: dwp_posts_comments_like_comment_id_idx; Type: INDEX
--
CREATE INDEX dwp_posts_comments_like_comment_id_idx ON dwp_posts_comments_like (comment_id);

--
-- Name: dwp_posts_comments_like_comment_id_like_type_user_uid_idx; Type: INDEX
--
CREATE INDEX dwp_posts_comments_like_comment_id_like_type_user_uid_idx ON dwp_posts_comments_like (comment_id, like_type, user_uid);

--
-- Name: dwp_posts_tags_workspace_uid_idx; Type: INDEX
--
CREATE INDEX dwp_posts_tags_workspace_uid_idx ON dwp_posts_tags (workspace_uid);

--
-- Name: dwp_posts_tags_name_workspace_uid_idx; Type: INDEX
--
CREATE INDEX dwp_posts_tags_name_workspace_uid_idx ON dwp_posts_tags (tag_name, workspace_uid);

--
-- Name: dwp_posts_tagbypost_post_id_idx; Type: INDEX
--
CREATE INDEX dwp_posts_tagbypost_post_id_idx ON dwp_posts_tagbypost (post_id);
