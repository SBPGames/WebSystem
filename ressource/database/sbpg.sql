/* SBP Games web system (Framework, Implemented websites, Designs and Branding)  
 * Copyright &copy; 2024 - SBP Games (https://www.sbpgames.fr/)
 * 
 * 1. Copy, distribution, derivative works or reuse of **the designs
 * and the branding of SBP Games websites** is PROHIBITED (⚠️).
 * 
 * 2. **Our framework and its implementations (websites)** is free software: you
 * can redistribute it and/or modify it under the terms of the GNU Lesser
 * General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * The latter is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License and the GNU
 * Lesser General Public License along with the **SBP Games framework and its
 * implementations** (Links: [GNU GPL v3](COPYING) &
 * [GNU LGPL v3](COPYING.LESSER)). If not, see https://www.gnu.org/licenses/.
 * -----------------------------------------------------------------------------
 * @author Xibitol <contact@pimous.dev>
 */

DROP TABLE IF EXISTS Game;

-- Table: Game
CREATE TABLE Game(
	identifier VARCHAR(3) NOT NULL PRIMARY KEY
		CHECK(UPPER(identifier) = identifier),
	name VARCHAR(32) NOT NULL,

	github VARCHAR(32) NULL,
	steam INTEGER(32) NULL,

	visitsCount BIGINT(64) NOT NULL DEFAULT 0
) ENGINE InnoDB CHARACTER SET "utf8mb4" COLLATE "utf8mb4_unicode_ci";

INSERT INTO Game(identifier, name, github, steam) VALUES
	("GE", "Game Engine", "GameEngine", NULL),
	("TSL", "The Seven Lives", "TheSevenLives", NULL),
	("THJ", "The Halley Journey", "TheHalleyJourney", NULL);