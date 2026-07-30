-- Procedure: purge_events_histories_batch(a_date, a_batch, a_sleep_ms, a_vacuum)

-- DROP PROCEDURE purge_events_histories_batch(a_date timestamp without time zone, a_batch integer, a_sleep_ms integer);

CREATE OR REPLACE PROCEDURE purge_events_histories_batch(
    a_date      timestamp without time zone,
    a_batch     integer DEFAULT 10000,
    a_sleep_ms  integer DEFAULT 100
)
LANGUAGE plpgsql
AS $$
DECLARE
    a_timestamp     integer;
    rows_deleted    integer;
    total_deleted   bigint := 0;
    batch_num       integer := 0;
    total_to_delete bigint;
BEGIN
    a_timestamp := EXTRACT(EPOCH FROM a_date)::integer;

    DROP TABLE IF EXISTS ids_histories_to_purge;

    RAISE NOTICE '[%] Calcul de la liste complète des historiques à purger (date limite: %, timestamp: %)...',
        clock_timestamp()::timestamp(0), a_date, a_timestamp;

    CREATE TEMP TABLE ids_histories_to_purge AS
    SELECT hh.history_id
    FROM horde_histories hh
    LEFT JOIN kronolith_events ke
        ON SPLIT_PART(hh.object_uid, ':', 3) = ke.event_uid
    WHERE hh.history_ts < a_timestamp
      AND SPLIT_PART(hh.object_uid, ':', 1) = 'kronolith'
      AND ke.event_uid IS NULL;

    CREATE INDEX ON ids_histories_to_purge (history_id);

    SELECT count(*) INTO total_to_delete FROM ids_histories_to_purge;

    RAISE NOTICE '[%] % historiques identifiés, début de la purge par batch',
        clock_timestamp()::timestamp(0), total_to_delete;

    LOOP
        batch_num := batch_num + 1;

        WITH batch AS (
            DELETE FROM ids_histories_to_purge
            WHERE history_id = ANY(ARRAY(SELECT history_id FROM ids_histories_to_purge LIMIT a_batch))
            RETURNING history_id
        )
        DELETE FROM horde_histories hh
        USING batch b
        WHERE hh.history_id = b.history_id;

        GET DIAGNOSTICS rows_deleted = ROW_COUNT;
        total_deleted := total_deleted + rows_deleted;

        COMMIT;

        RAISE NOTICE '[%] Batch #%: % lignes supprimées (total: %/%)',
            clock_timestamp()::timestamp(0), batch_num, rows_deleted, total_deleted, total_to_delete;

        EXIT WHEN rows_deleted = 0;

        PERFORM pg_sleep(a_sleep_ms / 1000.0);
    END LOOP;

    DROP TABLE IF EXISTS ids_histories_to_purge;

    RAISE NOTICE '[%] Purge horde_histories terminée : % lignes supprimées au total en % batches',
        clock_timestamp()::timestamp(0), total_deleted, batch_num;
END;
$$;
ALTER PROCEDURE purge_events_histories_batch(a_date timestamp without time zone, a_batch integer, a_sleep_ms integer)
OWNER TO horde;