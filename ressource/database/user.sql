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

DROP USER IF EXISTS "webserver.sbpgames"@"127.0.0.1";
CREATE USER "webserver.sbpgames"@"127.0.0.1";

GRANT USAGE ON SBPGames.* TO "webserver.sbpgames"@"127.0.0.1";
GRANT SELECT, UPDATE ON SBPGames.Game TO "webserver.sbpgames"@"127.0.0.1";

FLUSH PRIVILEGES;