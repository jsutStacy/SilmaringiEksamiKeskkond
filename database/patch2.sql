ALTER TABLE  `lesson` ADD  `type` ENUM(  'text',  'video',  'audio',  'presentation',  'test',  'images' ) NULL DEFAULT NULL ;

CREATE TABLE IF NOT EXISTS `lesson_files` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE  `lesson_files` ADD CONSTRAINT  `fk_lesson_to_lesson_files` FOREIGN KEY (  `lesson_id` ) REFERENCES  `silmaring`.`lesson` (
`id`
) ON DELETE CASCADE ON UPDATE RESTRICT ;