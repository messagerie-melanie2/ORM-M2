-- Procedure: clean_lightning_attributes_batch(a_batch, a_sleep_ms)

-- DROP PROCEDURE clean_lightning_attributes_batch(a_batch integer, a_sleep_ms integer);

CREATE OR REPLACE PROCEDURE clean_lightning_attributes_batch(
    a_batch     integer DEFAULT 10000,
    a_sleep_ms  integer DEFAULT 100
)
LANGUAGE plpgsql
AS $$
DECLARE
    rows_deleted    integer;
    total_deleted   bigint := 0;
    batch_num       integer := 0;
    total_to_delete bigint;
BEGIN
    DROP TABLE IF EXISTS ids_to_purge;

    RAISE NOTICE '[%] Calcul de la liste complète des lignes orphelines...',
        clock_timestamp()::timestamp(0);

    CREATE TEMP TABLE ids_to_purge AS
    SELECT la.id
    FROM lightning_attributes la
    LEFT JOIN kronolith_events k
        ON la.event_uid = k.event_uid
        AND la.calendar_id = k.calendar_id
    WHERE k.event_uid IS NULL;

    CREATE INDEX ON ids_to_purge (id);

    SELECT count(*) INTO total_to_delete FROM ids_to_purge;

    RAISE NOTICE '[%] % lignes orphelines identifiées, début de la suppression par batch',
        clock_timestamp()::timestamp(0), total_to_delete;

    LOOP
        batch_num := batch_num + 1;

        WITH batch AS (
            DELETE FROM ids_to_purge
            WHERE id = ANY(ARRAY(SELECT id FROM ids_to_purge LIMIT a_batch))
            RETURNING id
        )
        DELETE FROM lightning_attributes la
        USING batch b
        WHERE la.id = b.id;

        GET DIAGNOSTICS rows_deleted = ROW_COUNT;
        total_deleted := total_deleted + rows_deleted;

        COMMIT;

        RAISE NOTICE '[%] Batch #%: % lignes supprimées (total: %/%)',
            clock_timestamp()::timestamp(0), batch_num, rows_deleted, total_deleted, total_to_delete;

        EXIT WHEN rows_deleted = 0;

        PERFORM pg_sleep(a_sleep_ms / 1000.0);
    END LOOP;

    DROP TABLE IF EXISTS ids_to_purge;

    RAISE NOTICE '[%] Nettoyage terminé : % lignes supprimées en % batches',
        clock_timestamp()::timestamp(0), total_deleted, batch_num;
END;
$$;
ALTER PROCEDURE clean_lightning_attributes_batch(a_batch integer, a_sleep_ms integer)
OWNER TO horde;