DROP TABLE IF EXISTS `message` ;
DROP TABLE IF EXISTS `assist` ;
DROP TABLE IF EXISTS `offer` ;
DROP TABLE IF EXISTS `lesson` ;
DROP TABLE IF EXISTS `level` ;
DROP TABLE IF EXISTS `Teacher` ;
DROP TABLE IF EXISTS `user` ;

-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `firstname` VARCHAR(125) NOT NULL,
  `lastname` VARCHAR(125) NOT NULL,
  `birthday` DATE NOT NULL,
  `email` VARCHAR(125) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `validate` TINYINT NULL,
  `phone` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Teacher`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `Teacher` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(45) NOT NULL,
  `description` LONGTEXT NULL,
  `user_id` INT NOT NULL,
  `image` VARCHAR(125) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Teacher_user`
    FOREIGN KEY (`id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `level`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `level` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `level` VARCHAR(45) NOT NULL,
  `description` LONGTEXT NULL,
  `logo` VARCHAR(125) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lesson`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lesson` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` LONGTEXT NULL,
  `level_id` INT NULL,
  `logo` VARCHAR(125) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_lesson_level`
    FOREIGN KEY (`level_id`)
    REFERENCES `level` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `offer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `offer` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `teacher_id` INT NOT NULL,
  `lesson_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_offer_teacher`
    FOREIGN KEY (`teacher_id`)
    REFERENCES `Teacher` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_offer_lesson`
    FOREIGN KEY (`lesson_id`)
    REFERENCES `lesson` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `assist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `assist` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `offer_id` INT NOT NULL,
  `meet` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_assist_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_assist_offer`
    FOREIGN KEY (`offer_id`)
    REFERENCES `offer` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `message`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `message` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `title` VARCHAR(125) NOT NULL,
  `message` VARCHAR(45) NULL,
  `message_date` DATE NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_message_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
