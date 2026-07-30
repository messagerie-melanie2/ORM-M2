-- Function: list_long_running_queries()

-- DROP FUNCTION list_long_running_queries();

CREATE OR REPLACE FUNCTION list_long_running_queries()
RETURNS TABLE(pid integer
       , duration interval
       , usename name
       , query text )
AS 
$$
   SELECT pid,
		now() - query_start AS duration,
		usename,
		query
	FROM pg_stat_activity
	WHERE state = 'active'
		AND now() - query_start > interval '5 minutes'
	ORDER BY duration DESC;
$$ LANGUAGE sql;

ALTER FUNCTION list_long_running_queries()
  OWNER TO horde;
