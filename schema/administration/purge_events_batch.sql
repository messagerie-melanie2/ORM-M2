-- Procedure: purge_events_batch()

-- DROP PROCEDURE purge_events_batch(a_date timestamp without time zone, a_batch integer, a_sleep_ms integer);

CREATE OR REPLACE PROCEDURE purge_events_batch(
    a_date      timestamp without time zone,
    a_batch     integer DEFAULT 10000,
    a_sleep_ms  integer DEFAULT 100
)
LANGUAGE plpgsql
AS $$
DECLARE
    rows_deleted   integer;
    total_deleted  bigint := 0;
    batch_num      integer := 0;
    total_to_delete bigint;
BEGIN
    DROP TABLE IF EXISTS ids_to_purge;

    RAISE NOTICE '[%] Calcul de la liste complète des événements à purger...',
        clock_timestamp()::timestamp(0);

    CREATE TEMP TABLE ids_to_purge AS
    SELECT event_id
    FROM kronolith_events
    WHERE event_end <= a_date
       OR (event_recurtype >= 1 AND event_recurenddate <= a_date);

    CREATE INDEX ON ids_to_purge (event_id);

    SELECT count(*) INTO total_to_delete FROM ids_to_purge;

    RAISE NOTICE '[%] % événements identifiés, début de la purge par batch',
        clock_timestamp()::timestamp(0), total_to_delete;

    LOOP
        batch_num := batch_num + 1;

        WITH batch AS (
            DELETE FROM ids_to_purge
            WHERE event_id = ANY(ARRAY(SELECT event_id FROM ids_to_purge LIMIT a_batch))
            RETURNING event_id
        )
        DELETE FROM kronolith_events ke
        USING batch b
        WHERE ke.event_id = b.event_id;

        GET DIAGNOSTICS rows_deleted = ROW_COUNT;
        total_deleted := total_deleted + rows_deleted;

        COMMIT;

        RAISE NOTICE '[%] Batch #%: % lignes supprimées (total: %/%)',
            clock_timestamp()::timestamp(0), batch_num, rows_deleted, total_deleted, total_to_delete;

        EXIT WHEN rows_deleted = 0;

        PERFORM pg_sleep(a_sleep_ms / 1000.0);
    END LOOP;

    DROP TABLE IF EXISTS ids_to_purge;

    RAISE NOTICE '[%] Purge terminée : % lignes supprimées au total en % batches',
        clock_timestamp()::timestamp(0), total_deleted, batch_num;
END;
$$;
ALTER PROCEDURE purge_events_batch(a_date timestamp without time zone, a_batch integer, a_sleep_ms integer)
  OWNER TO horde;
