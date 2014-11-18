CREATE TABLE IF NOT EXISTS `subsubject` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `subject_id` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE  `subsubject` ADD CONSTRAINT  `fk_subject_to_subsubject` FOREIGN KEY (  `subject_id` ) REFERENCES  `silmaring`.`subject` (
`id`
) ON DELETE CASCADE ON UPDATE RESTRICT ;

ALTER TABLE  `lesson` CHANGE  `subject_id`  `subsubject_id` INT( 10 ) NULL DEFAULT NULL ;

ALTER TABLE  `lesson` DROP FOREIGN KEY  `fk_subject_to_lesson` ;

ALTER TABLE  `lesson` ADD CONSTRAINT  `fk_subsubject_to_lesson` FOREIGN KEY (  `subsubject_id` ) REFERENCES  `silmaring`.`subsubject` (
`id`
) ON DELETE CASCADE ON UPDATE RESTRICT ;