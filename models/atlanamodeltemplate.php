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

	abstract class AtlanaModelTemplate
	{
		protected $tplengine = null;
		public $tplvars = array();
		protected $debug = false;

		public function tag($key, $value = null)
		{
			if (is_array($key) && $value === null) {
				$this->tplvars['atlana'] = array_merge($this->tplvars['atlana'], $key);
			}
			elseif (isset($this->tplvars['atlana'][$key]) && is_array($this->tplvars['atlana'][$key])) {
				array_push($this->tplvars['atlana'][$key], $value);
			}
			else
			{
				_check('string', $key);
				$this->tplvars['atlana'][$key] = $value;
			}
		}

		public function render($tplfile, $targs = null)
		{
			if ($targs === null) {
				$targs = $this->tplvars;
			}
			return $this->tplengine->render($tplfile, $targs);
		}

		public function __construct($tplPath, $options = null, $debug = false)
		{
			$this->debug = $debug;
			$this->tplvars['atlana'] = array();
		}
	}
?>
