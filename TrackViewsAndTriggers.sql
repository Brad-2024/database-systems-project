/* Views */

-- example

DROP VIEW IF EXISTS TopAces;

CREATE VIEW TopAces AS
SELECT name FROM player,
(SELECT player_id, SUM(ace_count) AS total_aces
FROM (
    SELECT `match`.w_ace AS ace_count, `match`.winner_id AS player_id
    FROM `match`
    JOIN player ON player.id = `match`.winner_id
    JOIN tourney ON tourney.id = `match`.tourney_id
    WHERE `match`.w_ace > 0

    UNION ALL

    SELECT `match`.l_ace AS ace_count, `match`.loser_id AS player_id
    FROM `match`
    JOIN player ON player.id = `match`.loser_id
    JOIN tourney ON tourney.id = `match`.tourney_id
    WHERE `match`.l_ace > 0
) AS stats
GROUP BY player_id) AS grouped_aces
WHERE player.id = grouped_aces.player_id
ORDER BY total_aces DESC
LIMIT 10;

SELECT * FROM TopAces;

-- active_injuries
-- Shows all athletes with active injuries alongside their injury type, occurrence date, and latest treatment

DROP VIEW IF EXISTS active_injuries;

CREATE VIEW active_injuries AS
SELECT
    a.id AS athlete_id,
    CONCAT(u.first_name, ' ', u.last_name) AS athlete_name,
    i.type AS injury_type,
    i.occurence_date,
    MAX(t.date) AS last_treatment_date,
    t.type AS last_treatment_type
FROM Athlete a
JOIN Users u ON u.id = a.user_id
JOIN Treatment t ON t.athlete_id = a.id
JOIN Injury i ON i.id = t.injury_id
WHERE i.active = 'Y'
GROUP BY a.id, u.first_name, u.last_name, i.id, i.type, i.occurence_date, t.type;

SELECT * FROM active_injuries;

-- current_performance_trends
-- Shows each athlete's most recent race time per event compared to their personal best

DROP VIEW IF EXISTS current_performance_trends;

CREATE VIEW current_performance_trends AS
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

SELECT * FROM current_performance_trends;

-- weekly_training_trends
-- Shows each athlete's average workout success score and total training volume (distance) over the past 7 days

DROP VIEW IF EXISTS weekly_training_trends;

CREATE VIEW weekly_training_trends AS
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

SELECT * FROM weekly_training_trends;

-- training readiness indicator (red, orange, or green) based on recent data such as workouts, sleep, and soreness over 7–30 days
-- avg workout success (7 days), active injuries, and avg daily calories (30 days)

DROP VIEW IF EXISTS training_readiness_indicator;

CREATE VIEW training_readiness_indicator AS
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

SELECT * FROM training_readiness_indicator;

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