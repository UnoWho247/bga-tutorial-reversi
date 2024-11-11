
-- ------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- LwbTutorialReversi implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

CREATE TABLE IF NOT EXISTS `board` (
  `board_x` smallint(5) unsigned NOT NULL,
  `board_y` smallint(5) unsigned NOT NULL,
  `board_player` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`board_x`,`board_y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

