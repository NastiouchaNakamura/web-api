-- Challenges
CREATE TABLE IF NOT EXISTS sandbox.api_nsi_challenges (
	id varchar(63) NOT NULL,
	flag varchar(63) NOT NULL,
	stars_count tinyint unsigned NOT NULL,
	title varchar(63) NOT NULL,
	diamond_deadline_dt DATETIME NOT NULL,
	gold_deadline_dt DATETIME NOT NULL,
	CONSTRAINT `PRIMARY` PRIMARY KEY (id)
);

-- Profiles
CREATE TABLE IF NOT EXISTS sandbox.api_nsi_profiles (
	username varchar(63) NOT NULL,
	pw_hash varchar(255) NOT NULL,
	first_name varchar(255) NOT NULL,
	last_name varchar(255) NOT NULL,
	class varchar(255) NOT NULL,
	creation_dt datetime NOT NULL,
	displayable tinyint(1) NOT NULL
	CONSTRAINT `PRIMARY` PRIMARY KEY (username)
);

-- Requests
CREATE TABLE IF NOT EXISTS sandbox.api_nsi_requests (
	id int auto_increment NOT NULL,
	dt datetime NOT NULL,
	ip varchar(39) NOT NULL,
	challenge_id varchar(63) NOT NULL,
	username varchar(63) NULL,
	CONSTRAINT `PRIMARY` PRIMARY KEY (id),
	CONSTRAINT api_nsi_requests_api_nsi_challenges_FK FOREIGN KEY (challenge_id) REFERENCES sandbox.api_nsi_challenges(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT api_nsi_requests_api_nsi_profiles_FK FOREIGN KEY (username) REFERENCES sandbox.api_nsi_profiles(username) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Stars
CREATE TABLE IF NOT EXISTS sandbox.api_nsi_stars (
	username varchar(63) NOT NULL,
	challenge_id varchar(63) NOT NULL,
	dt DATETIME NOT NULL,
	CONSTRAINT api_nsi_stars_pk PRIMARY KEY (username, challenge_id),
	CONSTRAINT api_nsi_stars_api_nsi_profiles_FK FOREIGN KEY (username) REFERENCES sandbox.api_nsi_profiles(username) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT api_nsi_stars_api_nsi_challenges_FK FOREIGN KEY (challenge_id) REFERENCES sandbox.api_nsi_challenges(id) ON DELETE CASCADE ON UPDATE CASCADE
);
