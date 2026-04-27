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

-- current_performance_trends

-- weekly_training_trends

-- training readiness indicator (red, orange, or green) based on recent data such as workouts, sleep, and soreness over 7–30 days

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