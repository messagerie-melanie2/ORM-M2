-- MAJ du 20170420

--
-- Création de la table pamela_tentativescnx pour lister les tentatives de connexions
--

CREATE TABLE pamela_tentativescnx
(
  uid character varying(128) NOT NULL,
  lastcnx integer NOT NULL,
  nbtentatives integer NOT NULL,
  CONSTRAINT pamela_tentativescnx_pkey PRIMARY KEY (uid)
);


--
-- Création de la table pamela_mailcount pour lister les envoies de messages
--

CREATE TABLE pamela_mailcount
(
  uid character varying(255) NOT NULL,
  send_time timestamp without time zone NOT NULL,
  nb_dest integer NOT NULL DEFAULT 0,
  address_ip character varying(16) NOT NULL DEFAULT '0.0.0.0'::character varying
);


-- Index: pamela_mailcount_nb_dest_idx

CREATE INDEX pamela_mailcount_nb_dest_idx
  ON pamela_mailcount
  USING btree
  (nb_dest);

  
-- Index: pamela_mailcount_send_time_idx

CREATE INDEX pamela_mailcount_send_time_idx
  ON pamela_mailcount
  USING btree
  (send_time);

  
-- Index: pamela_mailcount_uid_idx

CREATE INDEX pamela_mailcount_uid_idx
  ON pamela_mailcount
  USING btree
  (uid COLLATE pg_catalog."default");