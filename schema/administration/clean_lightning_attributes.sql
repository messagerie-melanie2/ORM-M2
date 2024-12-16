-- Function: clean_lightning_attributes()

-- DROP FUNCTION clean_lightning_attributes();

CREATE OR REPLACE FUNCTION clean_lightning_attributes()
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
        SELECT l.calendar_id, l.event_uid 
            FROM lightning_attributes l 
            LEFT JOIN kronolith_events k 
                ON l.event_uid = k.event_uid 
                AND l.calendar_id = l.calendar_id 
            WHERE k.event_uid IS NULL
    LOOP
        DELETE FROM lightning_attributes 
            WHERE event_uid = mevents.event_uid 
                AND calendar_id = mevents.calendar_id;
        GET DIAGNOSTICS v_cnt = ROW_COUNT;
        nb_delete := nb_delete + v_cnt;
    END LOOP;

    RETURN nb_delete;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;