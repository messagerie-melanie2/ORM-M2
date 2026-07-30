-- Procedure: clean_horde_vfs_batch()

-- DROP PROCEDURE clean_horde_vfs_batch(a_batch integer, a_sleep_ms integer);

CREATE OR REPLACE PROCEDURE clean_horde_vfs_batch(
    a_batch     integer DEFAULT 200,
    a_sleep_ms  integer DEFAULT 200
)
LANGUAGE plpgsql
AS $$
DECLARE
    batch_folders   text[];
    rows_deleted    integer;
    batch_total     integer;
    total_deleted   bigint := 0;
    batch_num       integer := 0;
    total_folders   bigint;
BEGIN
    DROP TABLE IF EXISTS orphan_folders;

    RAISE NOTICE '[%] Calcul de la liste complète des dossiers orphelins...',
        clock_timestamp()::timestamp(0);

    CREATE TEMP TABLE orphan_folders AS
    SELECT hv.vfs_path || '/' || hv.vfs_name AS orphan_path
    FROM horde_vfs hv
    LEFT JOIN kronolith_events ke
        ON hv.vfs_name = ke.event_uid
    WHERE hv.vfs_path = '.horde/kronolith/documents'
      AND hv.vfs_type = 2
      AND ke.event_uid IS NULL;

    CREATE INDEX ON orphan_folders (orphan_path);

    SELECT count(*) INTO total_folders FROM orphan_folders;

    RAISE NOTICE '[%] % dossiers orphelins identifiés, début du nettoyage par batch',
        clock_timestamp()::timestamp(0), total_folders;

    LOOP
        batch_num := batch_num + 1;
        batch_total := 0;

        WITH batch AS (
            DELETE FROM orphan_folders
            WHERE orphan_path = ANY(ARRAY(SELECT orphan_path FROM orphan_folders LIMIT a_batch))
            RETURNING orphan_path
        )
        SELECT array_agg(orphan_path) INTO batch_folders FROM batch;

        EXIT WHEN batch_folders IS NULL OR array_length(batch_folders, 1) IS NULL;

        -- Niveau 2 : contenu des sous-dossiers
        DELETE FROM horde_vfs
        WHERE vfs_path IN (
            SELECT vfs_path || '/' || vfs_name
            FROM horde_vfs
            WHERE vfs_path = ANY(batch_folders)
        );
        GET DIAGNOSTICS rows_deleted = ROW_COUNT;
        batch_total := batch_total + rows_deleted;

        -- Niveau 1 : contenu direct
        DELETE FROM horde_vfs
        WHERE vfs_path = ANY(batch_folders);
        GET DIAGNOSTICS rows_deleted = ROW_COUNT;
        batch_total := batch_total + rows_deleted;

        -- Niveau 0 : le dossier lui-même
        DELETE FROM horde_vfs
        WHERE vfs_path || '/' || vfs_name = ANY(batch_folders);
        GET DIAGNOSTICS rows_deleted = ROW_COUNT;
        batch_total := batch_total + rows_deleted;

        total_deleted := total_deleted + batch_total;

        COMMIT;

        RAISE NOTICE '[%] Batch #%: % dossiers, % lignes supprimées (total: %)',
            clock_timestamp()::timestamp(0), batch_num, array_length(batch_folders, 1),
            batch_total, total_deleted;

        PERFORM pg_sleep(a_sleep_ms / 1000.0);
    END LOOP;

    DROP TABLE IF EXISTS orphan_folders;

    RAISE NOTICE '[%] Nettoyage horde_vfs terminé : % lignes supprimées en % batches',
        clock_timestamp()::timestamp(0), total_deleted, batch_num;
END;
$$;
ALTER PROCEDURE clean_horde_vfs_batch(a_batch integer, a_sleep_ms integer)
  OWNER TO horde;
