-- Function: clean_horde_vfs()

-- DROP FUNCTION clean_horde_vfs();

CREATE OR REPLACE FUNCTION clean_horde_vfs()
  RETURNS integer AS
$BODY$
DECLARE
    nb_delete integer;
	nb_diag integer;
BEGIN
    nb_delete := 0; 
	nb_diag := 0;
	DELETE FROM horde_vfs 
    WHERE vfs_path IN (
		SELECT vfs_path || '/' || vfs_name 
		FROM horde_vfs 
		WHERE vfs_path IN (
			SELECT hv.vfs_path || '/' || hv.vfs_name 
			FROM horde_vfs hv 
			LEFT JOIN kronolith_events ke 
				ON hv.vfs_name = ke.event_uid 
			WHERE hv.vfs_path = '.horde/kronolith/documents' 
				AND hv.vfs_type = 2 
				AND ke.event_uid IS null));

	GET DIAGNOSTICS nb_diag = ROW_COUNT;
	nb_delete := nb_delete + nb_diag;
	
	DELETE FROM horde_vfs 
    WHERE vfs_path IN (
		SELECT hv.vfs_path || '/' || hv.vfs_name 
		FROM horde_vfs hv 
		LEFT JOIN kronolith_events ke 
			ON hv.vfs_name = ke.event_uid 
		WHERE hv.vfs_path = '.horde/kronolith/documents' 
			AND hv.vfs_type = 2 
			AND ke.event_uid IS null);

	GET DIAGNOSTICS nb_diag = ROW_COUNT;
	nb_delete := nb_delete + nb_diag;

    DELETE FROM horde_vfs 
    WHERE vfs_path || '/' || vfs_name IN (
		SELECT hv.vfs_path || '/' || hv.vfs_name 
		FROM horde_vfs hv 
		LEFT JOIN kronolith_events ke 
			ON hv.vfs_name = ke.event_uid 
		WHERE hv.vfs_path = '.horde/kronolith/documents' 
			AND hv.vfs_type = 2 
			AND ke.event_uid IS null);

    GET DIAGNOSTICS nb_diag = ROW_COUNT;
	nb_delete := nb_delete + nb_diag;

    RETURN nb_delete;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION clean_horde_vfs()
  OWNER TO horde;
