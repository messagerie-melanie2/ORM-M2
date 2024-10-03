-- DROP TABLE dwp_posts_tagbypost;
-- DROP TABLE dwp_posts_tags;
-- DROP TABLE dwp_posts_reactions;
-- DROP TABLE dwp_posts_comments_like;
-- DROP TABLE dwp_posts_comments;
-- DROP TABLE dwp_posts_images;
-- DROP TABLE dwp_posts;

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

-- DROP INDEX dwp_posts_uid_idx;
-- DROP INDEX dwp_posts_images_uid_idx;
-- DROP INDEX dwp_posts_images_post_id_idx;
-- DROP INDEX dwp_posts_comments_uid_idx;
-- DROP INDEX dwp_posts_comments_post_id_idx;
-- DROP INDEX dwp_posts_reactions_post_id_idx;
-- DROP INDEX dwp_posts_reactions_post_id_reaction_type_user_uid_idx;
-- DROP INDEX dwp_posts_comments_like_comment_id_idx;
-- DROP INDEX dwp_posts_comments_like_comment_id_like_type_user_uid_idx;
-- DROP INDEX dwp_posts_tags_workspace_uid_idx;
-- DROP INDEX dwp_posts_tags_name_workspace_uid_idx;
-- DROP INDEX dwp_posts_tagbypost_post_id_idx;

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
