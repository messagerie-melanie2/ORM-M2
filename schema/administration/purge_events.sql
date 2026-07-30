-- Function: purge_events()

-- DROP FUNCTION purge_events(a_date timestamp without time zone, a_delete integer);

CREATE OR REPLACE FUNCTION purge_events(a_date timestamp without time zone, a_delete integer DEFAULT 0)
  RETURNS integer
AS $BODY$
DECLARE
    nb_events integer;
BEGIN
    nb_events := 0;

    IF a_delete = 1 THEN
        DELETE FROM kronolith_events 
            WHERE event_end <= a_date 
                OR (event_recurtype >= 1 
                    AND event_recurenddate <= a_date);

        GET DIAGNOSTICS nb_events = ROW_COUNT;
    ELSE
        nb_events := (SELECT count(*)::integer 
            FROM kronolith_events 
            WHERE event_end <= a_date 
                OR (event_recurtype >= 1 
                    AND event_recurenddate <= a_date));
    END IF;

    RETURN nb_events;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION purge_events(a_date timestamp without time zone, a_delete integer)
  OWNER TO horde;
