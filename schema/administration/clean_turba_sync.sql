-- Function: clean_turba_sync(integer)

-- DROP FUNCTION clean_turba_sync(integer);

CREATE OR REPLACE FUNCTION clean_turba_sync(a_limit integer DEFAULT 10)
  RETURNS integer AS
$BODY$
DECLARE
    mcontacts RECORD;
    nb_delete integer;
    v_cnt numeric;
BEGIN
    nb_delete := 0;
    v_cnt := 0;
    FOR mcontacts IN 
	SELECT count(*), contact_uid, addressbook_id 
		FROM turba_sync 
		GROUP BY contact_uid, addressbook_id 
		ORDER BY count DESC 
		LIMIT (a_limit)
    LOOP
	DELETE FROM turba_sync 
		WHERE contact_uid = mcontacts.contact_uid 
			AND addressbook_id = mcontacts.addressbook_id
			AND token NOT IN (SELECT token
					FROM turba_sync 
					WHERE contact_uid = mcontacts.contact_uid
					AND addressbook_id = mcontacts.addressbook_id
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