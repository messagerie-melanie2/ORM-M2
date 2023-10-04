-- Function: purge_events()

-- DROP FUNCTION purge_events(a_date timestamp without time zone, a_delete integer);

CREATE OR REPLACE FUNCTION purge_events(a_date timestamp without time zone DEFAULT '2000-01-01', a_delete integer DEFAULT 0)
  RETURNS TABLE(nb_events integer, nb_delete_histories integer, nb_delete_attributes integer, nb_delete_events integer) 
AS $BODY$
DECLARE
    mevents RECORD;
    nb_events integer;
    nb_delete_histories integer;
    nb_delete_attributes integer;
    nb_delete_events integer;
    nb_diag integer;
BEGIN
    nb_events             := 0;
    nb_delete_histories   := 0;
    nb_delete_attributes  := 0;
    nb_delete_events      := 0;
    nb_diag               := 0;

    FOR mevents IN 
        SELECT event_uid, calendar_id 
            FROM kronolith_events 
            WHERE event_end <= a_date 
                OR (event_recurtype >= 1 
                    AND event_recurenddate <= a_date)
    LOOP
        IF a_delete = 1 THEN
            DELETE FROM horde_histories
                WHERE object_uid = 'kronolith:' || mevents.calendar_id || ':' || mevents.event_uid;

            GET DIAGNOSTICS nb_diag = ROW_COUNT;
	          nb_delete_histories := nb_delete_histories + nb_diag;

            DELETE FROM lightning_attributes
                WHERE event_uid = mevents.event_uid 
                    AND calendar_id = mevents.calendar_id;

            GET DIAGNOSTICS nb_diag = ROW_COUNT;
	          nb_delete_attributes := nb_delete_attributes + nb_diag;

            DELETE FROM kronolith_events 
                WHERE event_uid = mevents.event_uid 
                    AND calendar_id = mevents.calendar_id;
            
            GET DIAGNOSTICS nb_diag = ROW_COUNT;
	          nb_delete_events := nb_delete_events + nb_diag;

        END IF;

        nb_events := nb_events + 1;

    END LOOP;

    RETURN query SELECT nb_events, nb_delete_histories, nb_delete_attributes, nb_delete_events;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION purge_events(a_date timestamp without time zone, a_delete integer)
  OWNER TO horde;
