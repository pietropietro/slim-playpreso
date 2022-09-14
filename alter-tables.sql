ALTER TABLE userParticipations RENAME COLUMN placement TO position;
ALTER TABLE userParticipations RENAME COLUMN placed_at TO updated_at;
ALTER TABLE userParticipations ADD COLUMN score INT AFTER updated_at;
ALTER TABLE userParticipations ADD COLUMlN finished tinyint AFTER updated_at;
ALTER TABLE userParticipations RENAME COLUMN ppLeagueType_id TO ppTournamentType_id;
ALTER TABLE userParticipations MODIFY COLUMN ppTournamentType_id int AFTER user_id;
ALTER TABLE userParticipations DROP COLUMN ppCupType_id;
ALTER TABLE userParticipations DROP FOREIGN KEY userParticipations_ibfk_6;

ALTER TABLE ppLeagues RENAME COLUMN users_count TO user_count;
ALTER TABLE ppLeagues ADD COLUMN round_count INT AFTER user_count;
ALTER TABLE ppLeagues RENAME COLUMN ppLeagueType_id TO ppTournamentType_id;


ALTER TABLE leagues RENAME COLUMN league_level TO country_level;
ALTER TABLE leagues RENAME COLUMN league_name TO name;
ALTER TABLE leagues RENAME COLUMN league_tag TO tag;
ALTER TABLE leagues ADD COLUMN ls_suffix varchar(255) AFTER id;
ALTER TABLE leagues ADD COLUMN standings json AFTER country_level;
ALTER TABLE leagues ADD COLUMN updated_at timestamp AFTER created_at;

ALTER TABLE ppCups DROP FOREIGN KEY presoCups_ibfk_1;
ALTER TABLE ppCups RENAME COLUMN ppCupType_id TO ppTournamentType_id;
ALTER TABLE ppCups ADD FOREIGN KEY (ppTournamentType_id) REFERENCES ppTournamentTypes(id);
-- make it not nullabe 
ALTER TABLE ppCups MODIFY COLUMN ppTournamentType_id int NOT NULL;


ALTER TABLE ppCupGroups ADD COLUMN round_count INT AFTER rounds;
ALTER TABLE ppCupGroups ADD COLUMN user_count INT AFTER round_count;

ALTER TABLE teams DROP COLUMN last_five;
ALTER TABLE teams ADD COLUMN ls_id INT AFTER id;
ALTER TABLE teams RENAME COLUMN team_name TO name;
ALTER TABLE teams DROP COLUMN team_tag;

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


-- makes the column below NULLABLE, dropping the 222 thingy
ALTER TABLE guesses MODIFY COLUMN guess_home int;
ALTER TABLE guesses MODIFY COLUMN guess_away int;


RENAME table ppLeagueTypes to ppTournamentTypes;
ALTER TABLE ppTournamentTypes ADD COLUMN participants INT AFTER cost;
ALTER TABLE ppTournamentTypes ADD COLUMN is_ppCup tinyint AFTER id;
ALTER TABLE ppTournamentTypes RENAME COLUMN type TO name;

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