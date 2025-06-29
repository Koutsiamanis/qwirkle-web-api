-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table qwirkle.board
CREATE TABLE IF NOT EXISTS `board` (
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `color` varchar(10) DEFAULT NULL,
  `shape` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`x`,`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table qwirkle.board: ~0 rows (approximately)

-- Dumping structure for procedure qwirkle.clean_board
DELIMITER //
CREATE PROCEDURE `clean_board`()
BEGIN
    DELETE FROM board;

    UPDATE tiles
    SET owner = NULL,
        placed = FALSE;
END//
DELIMITER ;

-- Dumping structure for table qwirkle.games
CREATE TABLE IF NOT EXISTS `games` (
  `player1` varchar(50) NOT NULL,
  `player2` varchar(50) DEFAULT NULL,
  `status` enum('not active','initialized','started','ended','aborted') NOT NULL DEFAULT 'not active',
  `current_player` varchar(50) DEFAULT NULL,
  `winner` varchar(50) DEFAULT NULL,
  `last_change` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table qwirkle.games: ~0 rows (approximately)

-- Dumping structure for procedure qwirkle.place_tile
DELIMITER //
CREATE PROCEDURE `place_tile`(
    IN p_player VARCHAR(50),   
    IN p_color VARCHAR(10),     
    IN p_shape VARCHAR(10),     
    IN p_x INT,                  
    IN p_y INT                  
)
BEGIN
    IF EXISTS (
        SELECT 1 FROM tiles
        WHERE color = p_color AND shape = p_shape AND owner = p_player
    ) THEN
        INSERT INTO board (x, y, color, shape)
        VALUES (p_x, p_y, p_color, p_shape);

        UPDATE tiles
        SET owner = NULL, placed = TRUE
        WHERE color = p_color AND shape = p_shape AND owner = p_player
        LIMIT 1;

        UPDATE games
        SET last_change = current_timestamp();

        UPDATE games
        SET current_player = CASE
            WHEN current_player = player1 THEN player2
            ELSE player1
        END
        WHERE current_player = p_player;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tile is not available for the player.';
    END IF;
END//
DELIMITER ;

-- Dumping structure for table qwirkle.players
CREATE TABLE IF NOT EXISTS `players` (
  `name` varchar(50) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `token` varchar(255) NOT NULL,
  `last_action` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`name`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table qwirkle.players: ~0 rows (approximately)

-- Dumping structure for table qwirkle.tiles
CREATE TABLE IF NOT EXISTS `tiles` (
  `color` varchar(10) NOT NULL,
  `shape` varchar(10) NOT NULL,
  `copy_number` int(11) NOT NULL,
  `owner` varchar(50) DEFAULT NULL,
  `placed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`color`,`shape`,`copy_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table qwirkle.tiles: ~108 rows (approximately)
INSERT INTO `tiles` (`color`, `shape`, `copy_number`, `owner`, `placed`) VALUES
	('blue', 'circle', 1, NULL, 0),
	('blue', 'circle', 2, NULL, 0),
	('blue', 'circle', 3, NULL, 0),
	('blue', 'clover', 1, NULL, 0),
	('blue', 'clover', 2, NULL, 0),
	('blue', 'clover', 3, NULL, 0),
	('blue', 'diamond', 1, NULL, 0),
	('blue', 'diamond', 2, NULL, 0),
	('blue', 'diamond', 3, NULL, 0),
	('blue', 'square', 1, NULL, 0),
	('blue', 'square', 2, NULL, 0),
	('blue', 'square', 3, NULL, 0),
	('blue', 'star', 1, NULL, 0),
	('blue', 'star', 2, NULL, 0),
	('blue', 'star', 3, NULL, 0),
	('blue', 'triangle', 1, NULL, 0),
	('blue', 'triangle', 2, NULL, 0),
	('blue', 'triangle', 3, NULL, 0),
	('green', 'circle', 1, NULL, 0),
	('green', 'circle', 2, NULL, 0),
	('green', 'circle', 3, NULL, 0),
	('green', 'clover', 1, NULL, 0),
	('green', 'clover', 2, NULL, 0),
	('green', 'clover', 3, NULL, 0),
	('green', 'diamond', 1, NULL, 0),
	('green', 'diamond', 2, NULL, 0),
	('green', 'diamond', 3, NULL, 0),
	('green', 'square', 1, NULL, 0),
	('green', 'square', 2, NULL, 0),
	('green', 'square', 3, NULL, 0),
	('green', 'star', 1, NULL, 0),
	('green', 'star', 2, NULL, 0),
	('green', 'star', 3, NULL, 0),
	('green', 'triangle', 1, NULL, 0),
	('green', 'triangle', 2, NULL, 0),
	('green', 'triangle', 3, NULL, 0),
	('orange', 'circle', 1, NULL, 0),
	('orange', 'circle', 2, NULL, 0),
	('orange', 'circle', 3, NULL, 0),
	('orange', 'clover', 1, NULL, 0),
	('orange', 'clover', 2, NULL, 0),
	('orange', 'clover', 3, NULL, 0),
	('orange', 'diamond', 1, NULL, 0),
	('orange', 'diamond', 2, NULL, 0),
	('orange', 'diamond', 3, NULL, 0),
	('orange', 'square', 1, NULL, 0),
	('orange', 'square', 2, NULL, 0),
	('orange', 'square', 3, NULL, 0),
	('orange', 'star', 1, NULL, 0),
	('orange', 'star', 2, NULL, 0),
	('orange', 'star', 3, NULL, 0),
	('orange', 'triangle', 1, NULL, 0),
	('orange', 'triangle', 2, NULL, 0),
	('orange', 'triangle', 3, NULL, 0),
	('purple', 'circle', 1, NULL, 0),
	('purple', 'circle', 2, NULL, 0),
	('purple', 'circle', 3, NULL, 0),
	('purple', 'clover', 1, NULL, 0),
	('purple', 'clover', 2, NULL, 0),
	('purple', 'clover', 3, NULL, 0),
	('purple', 'diamond', 1, NULL, 0),
	('purple', 'diamond', 2, NULL, 0),
	('purple', 'diamond', 3, NULL, 0),
	('purple', 'square', 1, NULL, 0),
	('purple', 'square', 2, NULL, 0),
	('purple', 'square', 3, NULL, 0),
	('purple', 'star', 1, NULL, 0),
	('purple', 'star', 2, NULL, 0),
	('purple', 'star', 3, NULL, 0),
	('purple', 'triangle', 1, NULL, 0),
	('purple', 'triangle', 2, NULL, 0),
	('purple', 'triangle', 3, NULL, 0),
	('red', 'circle', 1, NULL, 0),
	('red', 'circle', 2, NULL, 0),
	('red', 'circle', 3, NULL, 0),
	('red', 'clover', 1, NULL, 0),
	('red', 'clover', 2, NULL, 0),
	('red', 'clover', 3, NULL, 0),
	('red', 'diamond', 1, NULL, 0),
	('red', 'diamond', 2, NULL, 0),
	('red', 'diamond', 3, NULL, 0),
	('red', 'square', 1, NULL, 0),
	('red', 'square', 2, NULL, 0),
	('red', 'square', 3, NULL, 0),
	('red', 'star', 1, NULL, 0),
	('red', 'star', 2, NULL, 0),
	('red', 'star', 3, NULL, 0),
	('red', 'triangle', 1, NULL, 0),
	('red', 'triangle', 2, NULL, 0),
	('red', 'triangle', 3, NULL, 0),
	('yellow', 'circle', 1, NULL, 0),
	('yellow', 'circle', 2, NULL, 0),
	('yellow', 'circle', 3, NULL, 0),
	('yellow', 'clover', 1, NULL, 0),
	('yellow', 'clover', 2, NULL, 0),
	('yellow', 'clover', 3, NULL, 0),
	('yellow', 'diamond', 1, NULL, 0),
	('yellow', 'diamond', 2, NULL, 0),
	('yellow', 'diamond', 3, NULL, 0),
	('yellow', 'square', 1, NULL, 0),
	('yellow', 'square', 2, NULL, 0),
	('yellow', 'square', 3, NULL, 0),
	('yellow', 'star', 1, NULL, 0),
	('yellow', 'star', 2, NULL, 0),
	('yellow', 'star', 3, NULL, 0),
	('yellow', 'triangle', 1, NULL, 0),
	('yellow', 'triangle', 2, NULL, 0),
	('yellow', 'triangle', 3, NULL, 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
