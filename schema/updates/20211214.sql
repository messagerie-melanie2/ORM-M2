-- Index: kronolith_sync_calendar_token

-- DROP INDEX kronolith_sync_calendar_token;

CREATE INDEX kronolith_sync_calendar_token
  ON kronolith_sync
  USING btree
  (calendar_id, token DESC);


-- Index: nag_sync_taskslist_token

-- DROP INDEX nag_sync_taskslist_token;

CREATE INDEX nag_sync_taskslist_token
  ON nag_sync
  USING btree
  (taskslist_id, token DESC);



-- Index: turba_sync_addressbook_token

-- DROP INDEX turba_sync_addressbook_token;

CREATE INDEX turba_sync_addressbook_token
  ON turba_sync
  USING btree
  (addressbook_id, token DESC);
