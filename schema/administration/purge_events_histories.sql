-- Function: purge_events_histories(a_timestamp, a_delete)

-- DROP FUNCTION purge_events_histories(a_timestamp integer, a_delete integer);

CREATE OR REPLACE FUNCTION purge_events_histories(a_timestamp integer, a_delete integer DEFAULT 0)
  RETURNS integer AS
$BODY$
DECLARE
    nb_delete integer;
BEGIN
    nb_delete := 0;

    IF a_delete = 1 THEN
        DELETE FROM horde_histories
        USING horde_histories hh
        LEFT JOIN kronolith_events ke
        ON SPLIT_PART(object_uid, ':', 3) = ke.event_uid 
        WHERE horde_histories.history_id = hh.history_id
            AND hh.history_ts < a_timestamp
            AND SPLIT_PART(hh.object_uid, ':', 1) = 'kronolith'
            AND ke.event_uid IS NULL;

        GET DIAGNOSTICS nb_delete = ROW_COUNT;
    ELSE
        nb_delete := (SELECT count(*)::integer 
                        FROM horde_histories hh
                        LEFT JOIN kronolith_events ke
                        ON SPLIT_PART(object_uid, ':', 3) = ke.event_uid 
                        WHERE hh.history_ts < a_timestamp
                            AND SPLIT_PART(hh.object_uid, ':', 1) = 'kronolith'
                            AND ke.event_uid IS NULL);
    END IF;

    RETURN nb_delete;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION purge_events_histories(a_timestamp integer, a_delete integer)
OWNER TO horde;