-- Index: kronolith_realuid_idx

CREATE INDEX kronolith_realuid_idx
  ON kronolith_events
  USING btree
  (event_realuid);
