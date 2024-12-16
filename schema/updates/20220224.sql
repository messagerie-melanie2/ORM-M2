--
-- Sequence "dwp_notifications_seq"
-- Name: dwp_notifications_seq; Type: SEQUENCE;
--

-- DROP SEQUENCE dwp_notifications_seq;

CREATE SEQUENCE public.dwp_notifications_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Table "dwp_notifications"
-- Name: dwp_notifications; Type: TABLE;
--
-- DROP TABLE dwp_notifications;

CREATE TABLE public.dwp_notifications
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

CREATE INDEX dwp_notifications_owner_idx ON dwp_notifications (notification_owner);
CREATE INDEX dwp_notifications_created_modified_owner_idx ON dwp_notifications (notification_created DESC, notification_modified DESC, notification_owner);
