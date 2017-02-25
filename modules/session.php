<?php
	/*
	 * Copyright (C) by Gustaf Haglund <ghaglund@bahnhof.se>
	 *
	 * This program is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU Affero General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU Affero General Public License for more details.
	 * You should have received a copy of the GNU Affero General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 */

	/* https://secure.php.net/SessionHandlerInterface */

	class session implements SessionHandlerInterface
	{
		use module_trait;

		public function _init()
		{
			session_set_save_handler($this, true);
			//$domain='example.com' session_set_cookie_params(600, '/', $domain, true, true);
			session_start();
		}

		public function open($savePath, $sessionName) {
			return $this->gc(ini_get('session.gc_maxlifetime'));
		}

		public function close() {
			return $this->gc(ini_get('session.gc_maxlifetime'));
		}

		public function read($id)
		{
			$db_data = $this->atlana->sql->queryAndFetch('SELECT * FROM sessions WHERE id = :id', array('id' => $id), false);

			if ($db_data) {
				return $db_data['content'];
			}

			return '';
		}

		public function write($id, $data)
		{
			$statement = $this->atlana->sql->prepare(
				'INSERT INTO sessions VALUES (:id, :data, :timestamp) ON DUPLICATE KEY UPDATE content = :data, timestamp = :timestamp'
			);
			return $statement->execute(array('id' => $id, 'data' => $data, 'timestamp' => time()));
		}

		public function destroy($id)
		{
			$statement = $this->atlana->sql->prepare('DELETE FROM sessions WHERE id = :id');
			return $statement->execute(array('id' => $id));
		}

		public function gc($maxlifetime)
		{
			$statement = $this->atlana->sql->prepare('DELETE FROM sessions WHERE (timestamp+:maxlifetime) < UNIX_TIMESTAMP()');
			return $statement->execute(array('maxlifetime' => $maxlifetime));
		}
	}

?>
