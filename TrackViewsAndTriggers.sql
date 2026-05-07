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

/* TrainingReadinessIndicator (red, orange, or green) based on: 
+ (a) Average workout success over the past 7 days, 
+ (b) Active injuries, 
+ (c) Average daily calories over the past 30 days.

red = has an active injury, avg workout success < 4, or avg daily calories < 1200
orange = no active injuries, avg workout success < 7, or avg daily calories < 2000
green = no active injuries, avg workout success >= 7, and avg daily calories >= 2000
*/
DROP VIEW IF EXISTS TrainingReadinessIndicator;

CREATE VIEW TrainingReadinessIndicator AS
SELECT Athlete.id AS athlete_id, Users.first_name, Users.last_name,
COALESCE(WorkoutStats.avg_workout_success, 0) AS avg_workout_success_7_days,
COALESCE(InjuryStats.active_injuries, 0) AS active_injuries,
COALESCE(MealStats.avg_daily_calories, 0) AS avg_daily_calories_30_days,
CASE
	WHEN COALESCE(InjuryStats.active_injuries, 0) > 0 THEN 'R'
	WHEN COALESCE(WorkoutStats.avg_workout_success, 0) < 4 THEN 'R'
	WHEN COALESCE(MealStats.avg_daily_calories, 0) < 1200 THEN 'R'
	WHEN COALESCE(InjuryStats.active_injuries, 0) = 0 AND COALESCE(WorkoutStats.avg_workout_success, 0) < 7 THEN 'O'
	WHEN COALESCE(InjuryStats.active_injuries, 0) = 0 AND COALESCE(MealStats.avg_daily_calories, 0) < 2000 THEN 'O'
	WHEN COALESCE(InjuryStats.active_injuries, 0) = 0 AND COALESCE(WorkoutStats.avg_workout_success, 0) >= 7 AND COALESCE(MealStats.avg_daily_calories, 0) >= 2000 THEN 'G'
END AS training_readiness_indicator
FROM Athlete JOIN Users ON Athlete.user_id = Users.id
LEFT JOIN (
	SELECT athlete_id, AVG(workout_success) AS avg_workout_success
	FROM Workout
	WHERE date >= CURDATE() - INTERVAL 7 DAY
	GROUP BY athlete_id
) AS WorkoutStats ON Athlete.id = WorkoutStats.athlete_id
LEFT JOIN (
	SELECT athlete_id, COUNT(*) AS active_injuries
	FROM Injury
	WHERE active = 'Y'
	GROUP BY athlete_id
) AS InjuryStats ON Athlete.id = InjuryStats.athlete_id
LEFT JOIN (
	SELECT athlete_id, SUM(calories) / 30 AS avg_daily_calories
	FROM Meal
	WHERE date >= CURDATE() - INTERVAL 30 DAY
	GROUP BY athlete_id
) AS MealStats ON Athlete.id = MealStats.athlete_id
WHERE Users.role = 'athlete';

SELECT * FROM TrainingReadinessIndicator
ORDER BY training_readiness_indicator ASC, last_name ASC;