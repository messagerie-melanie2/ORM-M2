-- Function: list_tables_by_size()

-- DROP FUNCTION list_tables_by_size();

CREATE OR REPLACE FUNCTION list_tables_by_size()
RETURNS TABLE(table_name text
       , total_relation_size text
       , indexes_size text
       , total_relation_size_bytes bigint )
AS 
$$
   	select
		table_name,
		pg_size_pretty(pg_total_relation_size(quote_ident(table_name))) AS total_relation_size,
		pg_size_pretty(pg_indexes_size(quote_ident(table_name))) AS indexes_size,
		pg_total_relation_size(quote_ident(table_name)) AS total_relation_size_bytes 
	from information_schema.tables
	where table_schema = 'public'
	order by 4 desc;
$$ LANGUAGE sql;

ALTER FUNCTION list_tables_by_size()
  OWNER TO horde;
