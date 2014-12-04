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