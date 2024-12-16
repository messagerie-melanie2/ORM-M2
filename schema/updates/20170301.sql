-- MAJ du 20170307

--
-- Ajout du syncToken dans la table horde_datatree
--

ALTER TABLE horde_datatree ADD COLUMN datatree_synctoken bigint NOT NULL DEFAULT 0;