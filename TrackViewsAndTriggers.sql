/* Views */

-- ActiveInjuries: shows all athletes with active injuries alongside their injury type and occurrence date

DROP VIEW IF EXISTS ActiveInjuries;

CREATE VIEW ActiveInjuries AS
SELECT id, first_name, last_name, type, occurence_date FROM 
((SELECT id, first_name, last_name FROM Users
WHERE role = 'athlete') as athletes JOIN Injury
ON athletes.id = Injury.athlete_id) as athlete_injuries
WHERE active = 'Y'
ORDER BY last_name ASC;

SELECT * FROM ActiveInjuries;

-- CurrentPerformanceTrends: shows each athlete's most recent race time per event compared to their personal best
-- (in-progress)
DROP VIEW IF EXISTS CurrentPerformanceTrends;

CREATE VIEW CurrentPerformanceTrends AS

SELECT * FROM CurrentPerformanceTrends;

-- WeeklyTrainingSuccessScore: shows each athlete's average workout success score over past 7 days

DROP VIEW IF EXISTS WeeklyTrainingSuccessScore;

CREATE VIEW WeeklyTrainingSuccessScore AS
SELECT athlete_id, first_name, last_name, average_success_score 
FROM ((SELECT athlete_id, (SUM(workout_success)/7) as average_success_score FROM Workout 
WHERE date >= CURDATE() - INTERVAL 7 DAY
GROUP BY athlete_id) as average JOIN Users 
ON athlete_id = Users.id) as athlete_and_average
ORDER BY average_success_score DESC;

SELECT * FROM WeeklyTrainingSuccessScore;

-- WeeklyTrainingDistance: shows each athlete's average distance over the past 7 days
-- (in-progress)
SELECT athlete_id, (SUM(distance)/7) as average_distance FROM Workout, Workout_Set
WHERE 

SELECT * FROM WeeklyTrainingTrends;

-- training readiness indicator (red, orange, or green) based on recent data such as workouts, sleep, and soreness over 7–30 days
-- avg workout success (7 days), active injuries, and avg daily calories (30 days)
-- (in-progress)

DROP VIEW IF EXISTS TrainingReadinessIndicator;

CREATE VIEW TrainingReadinessIndicator AS

SELECT * FROM TrainingReadinessIndicator;

/* Triggers */

-- example

DROP TRIGGER IF EXISTS onInsertionPlayer;
DELIMITER //
CREATE TRIGGER onInsertionPlayer BEFORE INSERT ON player
FOR EACH ROW
BEGIN
	IF NEW.ioc IN ("RUS", "EST") THEN
	SET NEW.ioc = "USR";
	END IF;
END
//
DELIMITER ;

INSERT INTO player (name, dob, hand, height, ioc) VALUES ("Joe Bob", "1991-01-01", "L", 182, "RUS");
SELECT * FROM player WHERE name = "Joe Bob";

-- calorie max ping for coaches

-- updating an athlete’s personal record when a meet result beats their current PR

-- flagging an athlete if soreness levels exceed 7 for several consecutive days