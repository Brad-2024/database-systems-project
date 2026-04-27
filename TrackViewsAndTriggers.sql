/* Views */

-- ActiveInjuries: shows all athletes with active injuries alongside their injury type and occurrence date

DROP VIEW IF EXISTS ActiveInjuries;

CREATE VIEW ActiveInjuries AS
SELECT id, first_name, last_name, type, occurence_date FROM 
((SELECT id, first_name, last_name FROM Users
WHERE role = 'athlete') as athletes JOIN Injury
ON athletes.id = Injury.athlete_id) as athlete_injuries
WHERE active = 'Y';

SELECT * FROM ActiveInjuries;

-- CurrentPerformanceTrends: shows each athlete's most recent race time per event compared to their personal best

DROP VIEW IF EXISTS CurrentPerformanceTrends;

CREATE VIEW CurrentPerformanceTrends AS
SELECT
    a.id AS athlete_id,
    CONCAT(u.first_name, ' ', u.last_name) AS athlete_name,
    r.event,
    MIN(r.time) AS personal_best,
    (SELECT r2.time
        FROM Race r2
        JOIN Meet m2 ON m2.id = r2.meet_id
        WHERE r2.athlete_id = a.id AND r2.event = r.event
        ORDER BY m2.date DESC
        LIMIT 1
    ) AS most_recent_time
FROM Athlete a
JOIN Users u ON u.id = a.user_id
JOIN Race r ON r.athlete_id = a.id
GROUP BY a.id, u.first_name, u.last_name, r.event;

SELECT * FROM CurrentPerformanceTrends;

-- WeeklyTrainingTrends: shows each athlete's average workout success score and total training volume (distance) over the past 7 days

DROP VIEW IF EXISTS WeeklyTrainingTrends;

CREATE VIEW WeeklyTrainingTrends AS
SELECT
    a.id AS athlete_id,
    CONCAT(u.first_name, ' ', u.last_name) AS athlete_name,
    ROUND(AVG(w.workout_success), 2) AS avg_workout_success,
    SUM(ws.distance) AS total_distance,
    COUNT(DISTINCT w.id) AS total_workouts
FROM Athlete a
JOIN Users u ON u.id = a.user_id
JOIN Workout w ON w.athlete_id = a.id
JOIN Workout_Set ws ON ws.workout_id = w.id
WHERE w.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY a.id, u.first_name, u.last_name;

SELECT * FROM WeeklyTrainingTrends;

-- training readiness indicator (red, orange, or green) based on recent data such as workouts, sleep, and soreness over 7–30 days
-- avg workout success (7 days), active injuries, and avg daily calories (30 days)

DROP VIEW IF EXISTS TrainingReadinessIndicator;

CREATE VIEW TrainingReadinessIndicator AS
SELECT
    a.id AS athlete_id,
    CONCAT(u.first_name, ' ', u.last_name) AS athlete_name,
    ROUND(AVG(w.workout_success), 2) AS avg_workout_success,
    COUNT(DISTINCT i.id) AS active_injuries,
    ROUND(AVG(daily_calories.total_calories), 2) AS avg_daily_calories,
    CASE
        WHEN COUNT(DISTINCT i.id) > 0 THEN 'red'
        WHEN AVG(w.workout_success) < 5 THEN 'orange'
        WHEN AVG(w.workout_success) >= 5 AND COUNT(DISTINCT i.id) = 0 THEN 'green'
        ELSE 'orange'
    END AS readiness_indicator
FROM Athlete a
JOIN Users u ON u.id = a.user_id
LEFT JOIN Workout w ON w.athlete_id = a.id AND w.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
LEFT JOIN Treatment t ON t.athlete_id = a.id
LEFT JOIN Injury i ON i.id = t.injury_id AND i.active = 'Y'
LEFT JOIN (
    SELECT athlete_id, date, SUM(calories) AS total_calories
    FROM Meal
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY athlete_id, date
) AS daily_calories ON daily_calories.athlete_id = a.id
GROUP BY a.id, u.first_name, u.last_name;

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