ALTER TABLE userParticipations RENAME COLUMN placement TO position;
ALTER TABLE userParticipations RENAME COLUMN placed_at TO updated_at;
ALTER TABLE userParticipations ADD COLUMN score INT AFTER updated_at;
ALTER TABLE userParticipations ADD COLUMN tot_preso INT AFTER points;
ALTER TABLE userParticipations ADD COLUMN tot_unox2 INT AFTER points;
ALTER TABLE userParticipations ADD COLUMN tot_locked INT AFTER points;
ALTER TABLE userParticipations ADD COLUMlN finished tinyint AFTER updated_at;
ALTER TABLE userParticipations RENAME COLUMN ppLeagueType_id TO ppTournamentType_id;
ALTER TABLE userParticipations RENAME COLUMN points TO tot_points;
ALTER TABLE userParticipations MODIFY COLUMN ppTournamentType_id int AFTER user_id;
ALTER TABLE userParticipations DROP COLUMN ppCupType_id;
ALTER TABLE userParticipations DROP FOREIGN KEY userParticipations_ibfk_6;
ALTER TABLE userParticipations RENAME COLUMN score TO points;


ALTER TABLE users ADD COLUMN admin tinyint AFTER username;


ALTER TABLE ppLeagues RENAME COLUMN users_count TO user_count;
ALTER TABLE ppLeagues ADD COLUMN round_count INT AFTER user_count;
ALTER TABLE ppLeagues RENAME COLUMN ppLeagueType_id TO ppTournamentType_id;


ALTER TABLE leagues RENAME COLUMN league_level TO country_level;
ALTER TABLE leagues RENAME COLUMN league_name TO name;
ALTER TABLE leagues RENAME COLUMN league_tag TO tag;
ALTER TABLE leagues ADD COLUMN ls_suffix varchar(255) AFTER id;
ALTER TABLE leagues ADD COLUMN standings json AFTER country_level;
ALTER TABLE leagues ADD COLUMN updated_at timestamp AFTER created_at;
ALTER TABLE leagues ADD COLUMN use_match_ls_suffix tinyint default 0 AFTER ls_suffix;


ALTER TABLE ppCups DROP FOREIGN KEY presoCups_ibfk_1;
ALTER TABLE ppCups RENAME COLUMN ppCupType_id TO ppTournamentType_id;
ALTER TABLE ppCups ADD FOREIGN KEY (ppTournamentType_id) REFERENCES ppTournamentTypes(id);
-- make it not nullabe 
ALTER TABLE ppCups MODIFY COLUMN ppTournamentType_id int NOT NULL;
ALTER TABLE ppCups ADD COLUMN slug varchar(255) AFTER id;
ALTER TABLE ppCups ADD UNIQUE (slug);



ALTER TABLE ppCupGroups DROP COLUMN round_count;
ALTER TABLE ppCupGroups DROP COLUMN user_count;
ALTER TABLE ppCupGroups RENAME COLUMN groupTag TO tag;

ALTER TABLE teams DROP COLUMN last_five;
ALTER TABLE teams ADD COLUMN ls_id INT AFTER id;
ALTER TABLE teams RENAME COLUMN team_name TO name;
ALTER TABLE teams DROP COLUMN team_tag;
ALTER TABLE teams MODIFY id INT AUTO_INCREMENT;

ALTER TABLE matches DROP COLUMN league_name;
ALTER TABLE matches DROP COLUMN external_api_name;
ALTER TABLE matches DROP COLUMN hometeam_name;
ALTER TABLE matches DROP COLUMN awayteam_name;
-- makes the column below NULLABLE, dropping the 222 thingy
ALTER TABLE matches MODIFY COLUMN score_home int;
ALTER TABLE matches MODIFY COLUMN score_away int;
ALTER TABLE matches RENAME COLUMN external_api_matchid TO ls_id;
ALTER TABLE matches RENAME COLUMN hometeam_id TO home_id;
ALTER TABLE matches RENAME COLUMN awayteam_id TO away_id;
ALTER TABLE matches MODIFY id INT AUTO_INCREMENT;
ALTER TABLE matches ADD COLUMN ls_suffix varchar(255) AFTER ls_id;



-- makes the column below NULLABLE, dropping the 222 thingy
ALTER TABLE guesses MODIFY COLUMN guess_home int;
ALTER TABLE guesses MODIFY COLUMN guess_away int;
ALTER TABLE guesses RENAME COLUMN score TO points;
-- make columns nullable
ALTER TABLE guesses MODIFY COLUMN UNOX2 int;
ALTER TABLE guesses MODIFY COLUMN GGNG int;
ALTER TABLE guesses MODIFY COLUMN UO25 int;
ALTER TABLE guesses MODIFY COLUMN PRESO int;


RENAME table ppLeagueTypes to ppTournamentTypes;
ALTER TABLE ppTournamentTypes ADD COLUMN participants INT AFTER cost;
ALTER TABLE ppTournamentTypes ADD COLUMN is_ppCup tinyint AFTER id;
ALTER TABLE ppTournamentTypes ADD COLUMN can_join tinyint AFTER participants;
ALTER TABLE ppTournamentTypes RENAME COLUMN is_ppCup TO cup_format;

ALTER TABLE ppTournamentTypes RENAME COLUMN type TO name;
ALTER TABLE ppTournamentTypes ADD COLUMN rgb varchar(255) AFTER participants;
Update ppTournamentTypes set rgb=CONCAT_WS(", ", red, green, blue);
ALTER TABLE ppTournamentTypes DROP COLUMN red;
ALTER TABLE ppTournamentTypes DROP COLUMN green;
ALTER TABLE ppTournamentTypes DROP COLUMN blue;

ALTER TABLE ppTournamentTypes ADD COLUMN cup_format json AFTER participants;

ALTER TABLE ppTournamentTypes MODIFY COLUMN name varchar(255) NOT NULL;




ALTER TABLE pproundmatches drop COLUMN created_at;


-- to make it nullable
ALTER TABLE ppTournamentTypes MODIFY COLUMN rounds int;
ALTER TABLE ppTournamentTypes MODIFY COLUMN level int;

drop table ppCupTypes;

mysqldump presodump2 ppTournamentTypes > ppTournamentTypesDump.sql
INSERT INTO `ppTournamentTypes`(is_ppCup,name,level,rounds,cost,participants,image_url,red,green,blue) VALUES (1,'EUROS',NULL,NULL,500,NULL,NULL,0,0,255);


-- TO disbale foreign key constraints
SET FOREIGN_KEY_CHECKS = 0;


SELECT *
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE constraint_schema = 'presodump2' AND table_name = 'userParticipations';


-- create user
INSERT INTO `users`(username, points, password, email, country) VALUES ("test3" , 100, "JDJ5JDEwJEcueFRJUWVBQmdmcThDNldKbEU2ZWUyQmwvTVVwVWV1Mm1YYkRqT2JVbS5rZDB4WTZDNjV1", "test3@test.it", "test");


---test delete user to start ppleague
delete from userparticipations where user_id=260;
update ppleagues set started_at = NULL, user_count=19 where id=53;
update users set points=100 where id=260;
delete from guesses where created_at > "2022-09-07";
delete from pproundmatches where created_at > "2022-09-07";
delete from pprounds where created_at > "2022-09-07";


update matches set verified_at=null where round=6 and created_at>'2022-07-07';


select distinct l.id, l.ls_suffix from leagues l join matches m on m.league_id=l.id where m.date_start between '2022-09-18' and '2022-09-19' and verified_at is null;

$this->db->join("matches m", "m.league_id=l.id", "INNER");
        $this->db->where('m.verified_at IS NULL');
        $start = date("Y-m-d H:i:s");
        $finish = date("Y-m-d H:i:s", strtotime('-1 days'));
        $this->db->where('m.date_start', array($start, $finish), 'BETWEEN');
        return $this->db->query("select distinct l.id, l.ls_suffix from leagues l");
    }

    update matches set verified_At=null where league_id=6 and date_Start between '2022-09-17' and '2022-09-20';


INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (80, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (81, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (82, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (83, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (84, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (85, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (86, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (87, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (88, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (89, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (90, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (91, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (92, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (93, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (94, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (95, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (96, 16, 67);
INSERT INTO `userParticipations`(user_id, ppTournamentType_id,ppLeague_id) VALUES (97, 16, 67);

