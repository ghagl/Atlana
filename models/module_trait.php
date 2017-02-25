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

	trait module_trait
	{
		public $atlana = null;

		public function __get($k)
		{
			_debug("AtlanaModel __get got a wish for the var %s, backtrace: %s<br>", $k, print_r(debug_backtrace(), true));

			if (isset($this->atlana->$k) || is_callable($this->atlana->$k)) {
				return $this->atlana->$k;
			}

			return $this->atlana->__get($k);
		}

		public function __set($k, $v)
		{
			_debug("AtlanaModel __set got a wish to set the var %s<br>", $k);
			$this->atlana->$k = $v;
		}

		public function __call($func, $args)
		{
			return call_user_func_array(array($this->atlana, $func), $args);
		}

		public function __construct($atlana)
		{
			$this->atlana = $atlana;

			if (method_exists($this, '_init')) {
				$this->_init();
			}
		}
	}

?>
