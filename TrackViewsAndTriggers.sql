/* Views --> remember to query this in the database to create the views 
(also, views must be updated regularly to prevent stale data)*/

-- ActiveInjuries: shows all athletes with active injuries alongside their injury type and occurrence date
DROP VIEW IF EXISTS ActiveInjuries;

CREATE VIEW ActiveInjuries AS
SELECT id, first_name, last_name, type, occurence_date FROM 
((SELECT Athlete.id, first_name, last_name FROM
(SELECT id, first_name, last_name FROM Users
WHERE role = 'athlete') AS users JOIN Athlete
ON users.id = Athlete.user_id) as athletes JOIN Injury
ON athletes.id = Injury.athlete_id) as athlete_injuries
WHERE active = 'Y'
ORDER BY last_name ASC;

SELECT * FROM ActiveInjuries;

-- CurrentPerformanceTrendsPerEvent: shows each athlete's most recent race time per event
DROP VIEW IF EXISTS CurrentPerformanceTrendsPerEvent;

CREATE VIEW CurrentPerformanceTrendsPerEvent AS
SELECT athlete_id, first_name, last_name, performance.event, time AS most_recent_time
FROM ((SELECT Race.athlete_id, Race.event, Race.time FROM Race JOIN 
(SELECT athlete_id, event, MAX(Meet.date) AS most_recent_date FROM Race JOIN Meet
ON Race.meet_id = Meet.id
GROUP BY athlete_id, event) AS most_recent
ON Race.athlete_id = most_recent.athlete_id AND Race.event = most_recent.event
JOIN Meet ON Race.meet_id = Meet.id
WHERE Meet.date = most_recent.most_recent_date) AS performance JOIN
(SELECT Athlete.id, first_name, last_name FROM
(SELECT id, first_name, last_name FROM Users
WHERE role = 'athlete') AS users JOIN Athlete
ON users.id = Athlete.user_id) AS athletes
ON performance.athlete_id = athletes.id) AS performance_with_athletes
ORDER BY last_name ASC;

SELECT * FROM CurrentPerformanceTrendsPerEvent;

-- BestPerformanceTrendsPerEvent: shows each athlete's best race time per event
DROP VIEW IF EXISTS BestPerformanceTrendsPerEvent;

CREATE VIEW BestPerformanceTrendsPerEvent AS
SELECT athlete_id, first_name, last_name, performance.event, time AS best_time
FROM ((SELECT Race.athlete_id, Race.event, MIN(Race.time) AS time FROM Race
GROUP BY Race.athlete_id, Race.event) AS performance JOIN
(SELECT Athlete.id, first_name, last_name FROM
(SELECT id, first_name, last_name FROM Users
WHERE role = 'athlete') AS users JOIN Athlete
ON users.id = Athlete.user_id) AS athletes
ON performance.athlete_id = athletes.id) AS performance_with_athletes
ORDER BY last_name ASC;

SELECT * FROM BestPerformanceTrendsPerEvent;

-- WeeklyTrainingSuccessScore: shows each athlete's average workout success score over past 7 days
DROP VIEW IF EXISTS WeeklyTrainingSuccessScore;

CREATE VIEW WeeklyTrainingSuccessScore AS
SELECT athlete_id, first_name, last_name, average_success_score 
FROM ((SELECT athlete_id, SUM(workout_success)/7 AS average_success_score FROM Workout 
WHERE `date` >= CURDATE() - INTERVAL 7 DAY
GROUP BY athlete_id) AS average JOIN 
(SELECT Athlete.id, first_name, last_name FROM
(SELECT id, first_name, last_name FROM Users
WHERE role = 'athlete') AS users JOIN Athlete
ON users.id = Athlete.user_id) as athletes
ON average.athlete_id = athletes.id) AS athletes_and_averages
ORDER BY average_success_score DESC;

SELECT * FROM WeeklyTrainingSuccessScore;

-- WeeklyTrainingDistance: shows each athlete's average distance over the past 7 days
DROP VIEW IF EXISTS WeeklyTrainingDistance; 

CREATE VIEW WeeklyTrainingDistance AS
SELECT athlete_id, first_name, last_name, SUM(distance)/7 AS average_distance FROM 
(SELECT wt_id, athlete_id, first_name, last_name, distance
FROM ((SELECT id AS wt_id, athlete_id FROM Workout
WHERE `date` >= CURDATE() - INTERVAL 7 DAY) AS workouts JOIN 
(SELECT Athlete.id, first_name, last_name FROM
(SELECT id, first_name, last_name FROM Users
WHERE role = 'athlete') AS users JOIN Athlete
ON users.id = Athlete.user_id) as athletes 
ON workouts.athlete_id = athletes.id) AS workouts_with_athletes JOIN Workout_Set
ON wt_id = workout_id) AS workout_sets
GROUP BY athlete_id
ORDER BY average_distance DESC;

SELECT * FROM WeeklyTrainingDistance;

-- training readiness indicator (red, orange, or green) based on recent data such as workouts, sleep, and soreness over 7–30 days
-- avg workout success (7 days), active injuries, and avg daily calories (30 days)
-- (in-progress)
DROP VIEW IF EXISTS TrainingReadinessIndicator;

CREATE VIEW TrainingReadinessIndicator AS

SELECT * FROM TrainingReadinessIndicator;

/* Triggers? */