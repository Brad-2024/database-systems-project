/* Views --> remember to query this in the database to create the views 
(also, views must be updated regularly to prevent stale data)*/

-- ActiveInjuries: shows all athletes with active injuries alongside their injury type and occurrence date
DROP VIEW IF EXISTS ActiveInjuries;

CREATE VIEW ActiveInjuries AS
SELECT Athlete.id, Users.first_name, Users.last_name, Injury.type, Injury.occurence_date
FROM Athlete JOIN Users ON Athlete.user_id = Users.id JOIN Injury ON Athlete.id = Injury.athlete_id
WHERE Injury.active = 'Y';

SELECT * FROM ActiveInjuries
ORDER BY last_name ASC;

-- CurrentPerformanceTrendsPerEvent: shows each athlete's most recent race time per event
DROP VIEW IF EXISTS CurrentPerformanceTrendsPerEvent;

CREATE VIEW CurrentPerformanceTrendsPerEvent AS
SELECT athlete_id, first_name, last_name, event, time AS most_recent_time
FROM (SELECT Race.athlete_id, Users.first_name, Users.last_name, Race.event, Race.time,
ROW_NUMBER() OVER (PARTITION BY Race.athlete_id, Race.event ORDER BY Meet.date DESC, Race.id DESC) AS rn
FROM Race JOIN Meet ON Race.meet_id = Meet.id JOIN Athlete ON Athlete.id = Race.athlete_id
JOIN Users ON Athlete.user_id = Users.id WHERE Users.role = 'athlete') ranked WHERE rn = 1;

SELECT * FROM CurrentPerformanceTrendsPerEvent
ORDER BY last_name ASC;

-- BestPerformanceTrendsPerEvent: shows each athlete's best race time per event
DROP VIEW IF EXISTS BestPerformanceTrendsPerEvent;

CREATE VIEW BestPerformanceTrendsPerEvent AS
SELECT Race.athlete_id, Users.first_name, Users.last_name, Race.event, Min(Race.time) AS best_time
FROM Race JOIN Athlete ON Athlete.id = Race.athlete_id JOIN Users ON Athlete.user_id = Users.id WHERE Users.role = 'athlete'
GROUP BY Race.athlete_id, Users.first_name, Users.last_name, Race.event;

SELECT * FROM BestPerformanceTrendsPerEvent
ORDER BY last_name ASC;

-- WeeklyTrainingSuccessScore: shows each athlete's average workout success score over past 7 days
DROP VIEW IF EXISTS WeeklyTrainingSuccessScore;

CREATE VIEW WeeklyTrainingSuccessScore AS
SELECT Workout.athlete_id, Users.first_name, Users.last_name, SUM(Workout.workout_success) / 7 AS average_success_score
FROM Workout JOIN Athlete ON Workout.athlete_id = Athlete.id JOIN Users ON Athlete.user_id = Users.id
WHERE Workout.date >= CURDATE() - INTERVAL 7 DAY AND Users.role = 'athlete'
GROUP BY Workout.athlete_id, Users.first_name, Users.last_name;

SELECT * FROM WeeklyTrainingSuccessScore
ORDER BY average_success_score DESC;

-- WeeklyTrainingDistance: shows each athlete's average distance over the past 7 days
DROP VIEW IF EXISTS WeeklyTrainingDistance; 

CREATE VIEW WeeklyTrainingDistance AS
SELECT Workout.athlete_id, Users.first_name, Users.last_name, SUM(Workout_set.distance) / 7 AS average_distance
FROM Workout JOIN Workout_Set ON Workout.id = Workout_Set.workout_id JOIN Athlete ON Workout.athlete_id = Athlete.id JOIN Users ON Athlete.user_id = Users.id
WHERE Users.role = 'athlete' AND Workout.date >= CURDATE() - INTERVAL 7 DAY
GROUP BY Workout.athlete_id, Users.first_name, Users.last_name;

SELECT * FROM WeeklyTrainingDistance
ORDER BY average_distance DESC;

-- training readiness indicator (red, orange, or green) based on recent data such as workouts, sleep, and soreness over 7–30 days
-- avg workout success (7 days), active injuries, and avg daily calories (30 days)
-- (in-progress)
# DROP VIEW IF EXISTS TrainingReadinessIndicator;
#
# CREATE VIEW TrainingReadinessIndicator AS
#
# SELECT * FROM TrainingReadinessIndicator;

/* Triggers? */