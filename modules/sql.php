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

	class sql
	{
		protected $options = array(
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			PDO::ATTR_EMULATE_PREPARES         => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);

		public function __construct($configuration)
		{
			$cfg = ['hostname' => 'localhost',
					'database' => 'atlana',
					'username' => 'root',
					'password' => 'atlana',
					'charset' => 'utf8mb4',
					'type' => 'mysql'];

			foreach ($cfg as $k => $v)
			{
				if (isset($configuration[$k]))
				{
					_check('string', $configuration[$k]);
					$cfg[$k] = $configuration[$k];
				}
			}

			$this->link = new PDO($cfg['type'].':host='.$cfg['hostname'].';dbname='.$cfg['database'].';charset='.$cfg['charset'], $cfg['username'], $cfg['password'], $this->options);

			$this->query("set names utf8");
		}

		/**
		 * query and fetch
		 *
		 * @param string $query the query
		 * @param array $qarray the query arguments
		 * @param bool $fetchAll if it should fetch all or not (default: True)
		 * @throws \InvalidArgumentException and \BadFunctionCallException
		 *
		*/
		public function queryAndFetch($query, $qarray, $fetchAll = True)
		{
			_check('bool', $fetchAll);
			$r = null;
			$statement = $this->prepare($query);

			if ($statement === false)
			{
				/*throw new \BadFunctionCallException(
					_('Atlana: Something went wrong with the database.')
				); */
				return $r;
			}

			$statement->execute($qarray);

			if ($fetchAll) {
				$r = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
			else {
				$r = $statement->fetch(PDO::FETCH_ASSOC);
			}

			return $r;
		}

		public function __call($func, $args)
		{
			return call_user_func_array(array($this->link, $func), $args);
		}
	}
?>
