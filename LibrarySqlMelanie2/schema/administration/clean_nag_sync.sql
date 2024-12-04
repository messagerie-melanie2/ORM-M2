-- Function: clean_nag_sync(integer)

-- DROP FUNCTION clean_nag_sync(integer);

CREATE OR REPLACE FUNCTION clean_nag_sync(a_limit integer DEFAULT 10)
  RETURNS integer AS
$BODY$
DECLARE
    mtasks RECORD;
    nb_delete integer;
    v_cnt numeric;
BEGIN
    nb_delete := 0;
    v_cnt := 0;
    FOR mtasks IN 
	SELECT count(*), task_uid, taskslist_id 
		FROM nag_sync 
		GROUP BY task_uid, taskslist_id 
		ORDER BY count DESC 
		LIMIT (a_limit)
    LOOP
	DELETE FROM nag_sync 
		WHERE task_uid = mtasks.task_uid 
			AND taskslist_id = mtasks.taskslist_id
			AND token NOT IN (SELECT token
					FROM nag_sync 
					WHERE task_uid = mtasks.task_uid
					AND taskslist_id = mtasks.taskslist_id
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