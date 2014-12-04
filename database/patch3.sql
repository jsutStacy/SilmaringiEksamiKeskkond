ALTER TABLE  `course` ADD  `start_date` DATE NOT NULL ;
ALTER TABLE  `course` ADD  `end_date` DATE NOT NULL ;

CREATE TABLE IF NOT EXISTS `user_lesson` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `lesson_id` int(10) NOT NULL,
  `done` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_to_user_lesson` (`user_id`),
  KEY `fk_lesson_to_user_lesson` (`lesson_id`)
);

ALTER TABLE `user_lesson`
  ADD CONSTRAINT `fk_lesson_to_user_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_to_user_lesson` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `homework` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_to_homework` (`user_id`),
  KEY `fk_lesson_to_homework` (`lesson_id`)
);

ALTER TABLE `homework`
  ADD CONSTRAINT `fk_lesson_to_homework` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_to_homework` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
  
ALTER TABLE `lesson_files`
  ADD CONSTRAINT `fk_user_to_lesson_files` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

ALTER TABLE `homework` ADD `description` TEXT NULL DEFAULT NULL AFTER `user_id`;

ALTER TABLE homework DROP FOREIGN KEY fk_lesson_to_homework;
ALTER TABLE homework DROP INDEX fk_lesson_to_homework;

ALTER TABLE homework CHANGE `lesson_id` `subsubject_id` INT( 10 ) NOT NULL;

ALTER TABLE `homework`
  ADD CONSTRAINT `fk_subsubject_to_homework` FOREIGN KEY (`subsubject_id`) REFERENCES `subsubject` (`id`) ON DELETE CASCADE;
  
ALTER TABLE `homework` DROP `feedback`;

CREATE TABLE IF NOT EXISTS `homework_answers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `homework_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `feedback` text,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

ALTER TABLE `homework_answers`
  ADD CONSTRAINT `fk_homework_to_homework_answers` FOREIGN KEY (`homework_id`) REFERENCES `homework` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_to_homework_answers` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;