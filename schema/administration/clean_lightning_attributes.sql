-- Function: clean_lightning_attributes(a_delete)

-- DROP FUNCTION clean_lightning_attributes();
-- DROP FUNCTION clean_lightning_attributes(a_delete integer);

CREATE OR REPLACE FUNCTION clean_lightning_attributes(a_delete integer DEFAULT 0)
  RETURNS integer AS
$BODY$
DECLARE
    nb_delete integer;
BEGIN
    nb_delete := 0;

    IF a_delete = 1 THEN
        DELETE FROM lightning_attributes
        USING lightning_attributes la
        LEFT JOIN kronolith_events k 
            ON la.event_uid = k.event_uid 
            AND la.calendar_id = k.calendar_id 
        WHERE lightning_attributes.id = la.id
            AND k.event_uid IS NULL;

        GET DIAGNOSTICS nb_delete = ROW_COUNT;
    ELSE
        nb_delete := (SELECT count(*)::integer 
            FROM lightning_attributes la
            LEFT JOIN kronolith_events k 
                ON la.event_uid = k.event_uid 
                AND la.calendar_id = k.calendar_id 
            WHERE k.event_uid IS NULL);
    END IF;

    RETURN nb_delete;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION clean_lightning_attributes(a_delete integer)
OWNER TO horde;