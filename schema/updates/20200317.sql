-- Index: turba_sync_addressbook_token

CREATE INDEX turba_sync_addressbook_token
  ON turba_sync
  USING btree
  (addressbook_id COLLATE pg_catalog."default", token DESC);


-- Index: nag_sync_taskslist_token

CREATE INDEX nag_sync_taskslist_token
  ON nag_sync
  USING btree
  (taskslist_id COLLATE pg_catalog."default", token DESC);