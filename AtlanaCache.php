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

	class AtlanaCache
	{
		private $use_apc_string = 'apc_';
		private $use_apc = false;
		private $internals = [];
		private $atlana = null;
		private $debug = false;

		public function __construct($debug = false, $atlana)
		{
			$apc = false;
			$apcu = false;

			/* compatible with the newer and legacy version of apc(u) */
			if ($apc = function_exists('apc_add') || $apcu = function_exists('apcu_add'))
			{
				$this->use_apc = true;
				if ($apcu) {
					$this->use_apc_string = 'apcu_';
				}

				$funcs = ['add', 'fetch', 'delete', 'clear_cache'];
				$flen = count($funcs);
				for ($c = 0; $c < $flen; ++$c) {
					$fstr = 'apc_'.$funcs[$c];
					$apc_func = $this->use_apc_string.$funcs[$c];
					$this->$fstr = $apc_func;
				}
			}

			if ($debug === true && $this->use_apc === true) {
				$this->apc_clear_cache();
				$this->apc_clear_cache('user');
				$this->apc_clear_cache('opcode');
			}

			$this->atlana = $atlana;
			$this->debug = $debug;
		}

		/* Wrapper for apc(u). */
		public function __call($func, $args)
		{
			if (function_exists($func)) {
				call_user_func_array($func, $args);
			}
		}

		public function get_file($file, $callback = 'file_get_contents')
		{
			if ($this->debug) {
				echo getcwd();
			}

			if ($this->use_apc) {
				if (!($filedata = $this->apc_fetch('file_'.$file))) {
					$this->apc_add('file_'.$file, $filedata = call_user_func($callback, $file));
				}

				return $filedata;
			}
			else
			{
				return call_user_func($callback, $file);
			}
		}

		public function get_conf($confini, $callback = null)
		{
			if ($callback === null) {
				$callback = array($this, 'get_file');
			}

			if ($this->use_apc) {
				if (!($conf = $this->apc_fetch('conf_'.$confini))) {
					$this->apc_add('conf_'.$confini, $conf = parse_ini_string(call_user_func($callback, $confini)));
				}

				return $conf;
			}
			else
			{
				if (isset($this->internals['conf_'.$confini])) {
					return $this->internals['conf_'.$confini];
				} else {
					$this->internals['conf_'.$confini] = parse_ini_string(call_user_func($callback, $confini));
					return $this->get_conf($confini);
				}
			}
		}

		/**
		 * @param $query the file to look for, for sql query
		 * @param $args query args (:userID / ? in query)
		 * @note only singlefetching for now, if not via wrapper
		*/
		public function get_db($action, $args, $fetchAll = True)
		{
			/*$cargs = func_get_args();
			$ccargs = count($cargs);
			$args = array();
			$action = $cargs[0];
			$query = null;

			_check('string', $action);

			for ($c = 1; $c < $ccargs; ++$c)
			{
				$args[$c] = $cargs[$c];
			} */

			$query = null;

			//EOL: $this->delete('file', __ATLANAROOT__.'/sql/'.$table.'.sql');
			$sql_paths = array_merge([__ATLANAROOT__.'/sql'], $this->atlana->module_paths);
			foreach ($sql_paths as $sqlpath)
			{
				//_debug("%s\n", $sqlpath.'/'.$action.'.sql');
				if (file_exists($sqlpath.'/'.$action.'.sql')) {
					$query = $this->get_file($sqlpath.'/'.$action.'.sql');
					break;
				}
			}

			if ($query === null) {
				throw new Exception(
					_('AtlanaCache: Needs a query')
				);
			}

			return $this->atlana->sql->queryAndFetch($query, $args, $fetchAll);
		}

		/*
		 *
		 * @param $query The query
		 * @param $qargs Arguments for query (like %d, %s or :userID)
		 * @param $save_as Save in cache as (example atlana_inactive_users)
		 * @param $singlefetch Single row or more rows to fetch
		 * @param $ttl The time amount to save the data in cache
		 *
		 *
		*/
		public function save_db_result($query, $qargs, $save_as, $singlefetch, $ttl = 60*60*24)
		{
			$result = $this->atlana->sql->queryAndFetch($query, $qargs, $singlefetch);

			/* TODO */
		}
	}

?>
