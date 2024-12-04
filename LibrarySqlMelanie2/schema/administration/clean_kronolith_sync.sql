-- Function: clean_kronolith_sync()

-- DROP FUNCTION clean_kronolith_sync();

CREATE OR REPLACE FUNCTION clean_kronolith_sync(a_limit integer DEFAULT 10)
  RETURNS integer AS
$BODY$
DECLARE
    mevents RECORD;
    nb_delete integer;
    v_cnt numeric;
BEGIN
    nb_delete := 0;
    v_cnt := 0;
    FOR mevents IN 
        SELECT count(*), event_uid, calendar_id 
            FROM kronolith_sync 
            GROUP BY event_uid, calendar_id 
            ORDER BY count DESC 
            LIMIT (a_limit)
    LOOP
        DELETE FROM kronolith_sync 
            WHERE event_uid = mevents.event_uid 
                AND calendar_id = mevents.calendar_id
                AND token NOT IN (SELECT token
                        FROM kronolith_sync 
                        WHERE event_uid = mevents.event_uid
                            AND calendar_id = mevents.calendar_id
                        ORDER BY token DESC
                        LIMIT 1);
        GET DIAGNOSTICS v_cnt = ROW_COUNT;
        nb_delete := nb_delete + v_cnt;
    END LOOP;

    RETURN nb_delete;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

