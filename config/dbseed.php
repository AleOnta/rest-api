<?php

require __DIR__ . '/../bootstrap.php';

$userTableStatement = <<<EOS
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(256) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
EOS;

$postsTableStatement = <<<EOS
CREATE TABLE IF NOT EXISTS posts (
    id SERIAL PRIMARY KEY,
    user_id INT,
    title VARCHAR(80),
    content VARCHAR(2048),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user
        FOREIGN KEY (user_id)
            REFERENCES users(id)
            ON DELETE CASCADE
);
EOS;

$seedUsers = <<<EOS
INSERT INTO users (email,username,password) 
VALUES('john.doe@example.com','johndoe','abcd'),
      ('jane.doe@example.com','janedoe','efgh'),
      ('david.wright@example.com','davidwr','hilm')
EOS;

$seedPosts = <<<EOS
INSERT INTO posts (user_id,title,content)
VALUES(1,'Post 1 seed','It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here, content here, making it look like readable English.'),
      (1,'Post 2 seed','It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here, content here, making it look like readable English.'),
      (2,'Post 3 seed','It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here, content here, making it look like readable English.'),
      (3,'Post 4 seed','It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using Content here, content here, making it look like readable English.')
EOS;

$onUpdateTrigger = <<<EOS
CREATE OR REPLACE FUNCTION update_updated_at() 
RETURNS TRIGGER AS $$
    BEGIN new.updated_at = current_timestamp;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER user_updated_at_trigger
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();

CREATE TRIGGER posts_updated_at_trigger
BEFORE UPDATE ON posts
FOR EACH ROW
EXECUTE FUNCTION update_updated_at();
EOS;

$db->exec($userTableStatement);
$db->exec($postsTableStatement);
$db->exec($seedUsers);
$db->exec($seedPosts);
$db->exec($onUpdateTrigger);
