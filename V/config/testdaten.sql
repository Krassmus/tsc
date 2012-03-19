INSERT INTO spieler SET login = 'gast1', password = 'gast1';

INSERT INTO forces SET id = '1', name = 'Reticulaner';
INSERT INTO relate_sp_f SET force_id = '1', spieler = 'gast1';

INSERT INTO gruppe SET id = '1', name = 'testgruppe', founder_login = 'gast1';
INSERT INTO relate_f_gr SET force_id = '1', gruppe_id = '1';

INSERT INTO relate_master_gr SET spieler = 'gast1', gruppe_id = '1';