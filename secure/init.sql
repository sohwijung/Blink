-- TODO: Put ALL SQL in between `BEGIN TRANSACTION` and `COMMIT`
BEGIN TRANSACTION;

CREATE TABLE `images` (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`file_name`	TEXT NOT NULL,
    `file_ext` TEXT NOT NULL,
    `file_source` TEXT NOT NULL
);

CREATE TABLE `tags` (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    `tag`   TEXT NOT NULL UNIQUE
);

CREATE TABLE `links` (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    `image_id`  INTEGER NOT NULL,
    `tag_id`    INTEGER NOT NULL
);
-- TODO: initial seed data

INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Chicago Tribune', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Chicago River', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('DC Building', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Streets of Chicago', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Snee Hall', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Johnson Museum', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Santa Monica Pier', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Californian Nights', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Streets of NY', 'jpg', 'Sohwi Jung');
INSERT INTO `images` (file_name, file_ext, file_source) VALUES ('Chicago Lights', 'jpg', 'Sohwi Jung');

INSERT INTO `tags` (id, tag) VALUES (1, 'Architechtural');
INSERT INTO `tags` (id, tag) VALUES (2, 'Nature');
INSERT INTO `tags` (id, tag) VALUES (3, 'Sunset');
INSERT INTO `tags` (id, tag) VALUES (4, 'California');
INSERT INTO `tags` (id, tag) VALUES (5, 'Chicago');
INSERT INTO `tags` (id, tag) VALUES (6, 'Cornell');

INSERT INTO `links` (image_id, tag_id) VALUES (1, 1);
INSERT INTO `links` (image_id, tag_id) VALUES (3, 1);
INSERT INTO `links` (image_id, tag_id) VALUES (4, 1);
INSERT INTO `links` (image_id, tag_id) VALUES (5, 1);
INSERT INTO `links` (image_id, tag_id) VALUES (2, 2);
INSERT INTO `links` (image_id, tag_id) VALUES (8, 2);
INSERT INTO `links` (image_id, tag_id) VALUES (7, 3);
INSERT INTO `links` (image_id, tag_id) VALUES (8, 3);
INSERT INTO `links` (image_id, tag_id) VALUES (7, 4);
INSERT INTO `links` (image_id, tag_id) VALUES (8, 4);
INSERT INTO `links` (image_id, tag_id) VALUES (3, 5);
INSERT INTO `links` (image_id, tag_id) VALUES (4, 5);
INSERT INTO `links` (image_id, tag_id) VALUES (10, 5);
INSERT INTO `links` (image_id, tag_id) VALUES (5, 6);
INSERT INTO `links` (image_id, tag_id) VALUES (6, 6);









COMMIT;
