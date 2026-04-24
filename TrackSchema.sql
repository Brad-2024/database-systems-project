-- Create Track database.
DROP DATABASE IF EXISTS Track;
CREATE DATABASE Track;

-- Use Track database.
USE Track;

-- Create Coach Table with incrementing ID.
CREATE TABLE Coach (
	id int NOT NULL AUTO_INCREMENT,
	first_name varchar(50),
	last_name varchar(50),
	phone_number varchar(12), -- e.g., "980-727-9900"
	email varchar(50),
    password_hash VARCHAR(255) NOT NULL,
	PRIMARY KEY (id) -- Primary Key for Coach Table
);

-- Create Trainer Table with incrementing ID.
CREATE TABLE Trainer (
	id int NOT NULL AUTO_INCREMENT,
	first_name varchar(50),
	last_name varchar(50),
	phone_number varchar(12), -- e.g., "980-727-9900"
	email varchar(50),
    password_hash VARCHAR(255) NOT NULL,
	PRIMARY KEY (id) -- Primary Key for Trainer Table
);

-- Create Athlete Table with incrementing ID.
CREATE TABLE Athlete (
	id int NOT NULL AUTO_INCREMENT,
	first_name varchar(50),
	last_name varchar(50),
	height varchar(7), -- e.g., 5' 11''
	weight int,
	dob date,
	sex enum('M','F','U'), -- U = unspecified?
	grad_year varchar(4), -- e.g., 2028
	event varchar(150), -- e.g., "60 m, 100 m, long jump, pentathlon" --> not atomic but an athlete can have multiple events?
	email varchar(50),
    password_hash VARCHAR(255) NOT NULL,
    coach_id int,
	trainer_id int,
	FOREIGN KEY (coach_id) REFERENCES Coach(id), -- Foreign Key to reference Coach Table for id
	FOREIGN KEY (trainer_id) REFERENCES Trainer(id), -- Foreign Key to reference Trainer Table for id
	PRIMARY KEY (id) -- Primary Key for Athlete Table
);

-- Create Meet Table with incrementing ID.
CREATE TABLE Meet (
	id int NOT NULL AUTO_INCREMENT,
	date date,
	name varchar(50),
	city varchar(50),
	state varchar(2),
	PRIMARY KEY (id) -- Primary Key for Meet Table
);

-- Create Race Table (weak entity).
CREATE TABLE Race (
	event varchar(20),
	time time,
	meet_id int,
	athlete_id int,
	FOREIGN KEY (meet_id) REFERENCES Meet(id), -- Foreign Key to reference Meet Table for id
	FOREIGN KEY (athlete_id) REFERENCES Athlete(id), -- Foreign Key to reference Athlete Table for id
	PRIMARY KEY (meet_id, athlete_id, event) -- Primary Key for Race Table
);

-- Create Injury Table with incrementing ID.
CREATE TABLE Injury (
	id int NOT NULL AUTO_INCREMENT,
	type enum('fracture', 'broken bone', 'etc.'), -- probably add more to this...
	occurence_date date,
	active enum('Y','N'),
	PRIMARY KEY (id) -- Primary Key for Injury Table
);

-- Create Treatment Table with incrementing ID.
CREATE TABLE Treatment (
	id int NOT NULL AUTO_INCREMENT,
	type enum('scrape', 'ice', 'etc.'), -- probably add more to this...
	date date,
	athlete_id int,
	trainer_id int, 
	injury_id int,
	FOREIGN KEY (athlete_id) REFERENCES Athlete(id), -- Foreign Key to reference Athlete Table for id
	FOREIGN KEY (trainer_id) REFERENCES Trainer(id), -- Foreign Key to reference Trainer Table for id
	FOREIGN KEY (injury_id) REFERENCES Injury(id), -- Foreign Key to reference Injury Table for id
	PRIMARY KEY (id) -- Primary Key for Treatment Table
);

CREATE TABLE Workout (
    id int NOT NULL AUTO_INCREMENT,
    date date,
    workout_success int CHECK (workout_success BETWEEN 0 AND 10),
    athlete_id int,
    FOREIGN KEY (athlete_id) REFERENCES Athlete(id),
    PRIMARY KEY (id)
);

CREATE TABLE Workout_Set (
    id int NOT NULL AUTO_INCREMENT,
    distance int,
    time_seconds int,
    workout_id int,
    FOREIGN KEY (workout_id) REFERENCES Workout(id),
    PRIMARY KEY (id)
);

CREATE TABLE Meal (
    id int NOT NULL AUTO_INCREMENT,
    date date,
    meal_type enum('breakfast', 'lunch', 'dinner', 'snack'),
    calories int,
    fats int,
    carbohydrates int,
    sugar int,
    protein int,
    athlete_id int,
    FOREIGN KEY (athlete_id) REFERENCES Athlete(id),
    PRIMARY KEY (id)
)
