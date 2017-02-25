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

	class requests extends module
	{
		public $last_request = [];

		public function _init()
		{
			$this->bcrypt_options = ['cost' => 12];
			$this->lowget = array_change_key_case($_GET, CASE_LOWER);
			$this->lowpost = array_change_key_case($_POST, CASE_LOWER);
		}

		public function define_rq_step($target, $step = 1) {
			if (!isset($_SESSION['rq_step'][$target])) {
				$_SESSION['rq_step'][$target] = $step;
			}
		}

		/**
		 *
		 *
		 *
		*/

		public function generic_request_handler($rqvals, $target, $touse = null, $nosession = false)
		{
			$rq = $touse;
			if ($touse === null) {
				$rq = $_POST;
				$touse = $_POST;
			}

			foreach ($rqvals as $k => $v)
			{
				$checkRequired = $v;
				$isArray = false;

				if ($touse === $rq && isset($touse[$k]) !== true && $nosession !== false) { /* code check about $noession */
			    	$touse = $_SESSION;
				}

				if (is_array($v))
				{
				    if (isset($v['step'])) {
						if ($_SESSION['rq_step'][$target] > $v['step'] or $_SESSION['rq_step'][$target] < $v['step']) {
							continue;
						}
					}
				    $checkRequired = $v[0];
				    $isArray = true;
				}

				if (isset($touse[$k]) !== true && $checkRequired === true) {
				    die(_("Atlana: Lack of required variable {$k}."));
					// var_dump(isset($touse)
				}

				if ($isArray && isset($touse[$k]))
				{
					if (isset($v['random']) && $v['random'] === true) {
						$touse[$k] = bin2hex(openssl_random_pseudo_bytes(16));
					}
					if (isset($v['strcheck'])) {
						if (strpos($touse[$k], $v['strcheck']) === false) {
							die(_("Atlana: Invalid {$k}."));
						}
					}
					if (isset($v['match'])) {
						if ($touse[$k] !== $touse[$v['match']]) {
							die(_("Atlana: The values {$k} and {$v['match']} does not match."));
						}
					}
					/* EOL: deprecated, should be removed in near future */
					if (isset($v['purifier'])) {
						// Expects purifier setup before this
						$touse[$k] = $this->atlana->purifier->purify($touse[$k]);
					}
					if (isset($v['blowfish']) && $v['blowfish'] === true) {
						$touse[$k] = password_hash($touse[$k], PASSWORD_BCRYPT, $this->bcrypt_options);
					}

					/*** For security reasons ***/
					if (isset($v['match'])) {
						$touse[$v['match']] = $touse[$k];
					}
				}
			}

			// TODO FIXME: regarding htmlpurifier

			if ($nosession === false && isset($touse[$k])) {
				$_SESSION[$k] = $touse[$k];
				$_SESSION['rq_step'][$target] += 1;
			} else {
				$this->last_request = $touse;
			}
		}

		/*
		 * do cleanup after generic_request_handler()
		 *
		*/

		public function generic_request_cleanup($rqvals, $target)
		{
			foreach ($rqvals as $key => $value) {
				unset($_SESSION[$key]);
			}

			unset($_SESSION['rq_step'][$target]);
		}

		/*
		 * go back in a form
		 *
		*/
		public function goBackForm()
		{
			$rqval = ['gobacktarget' => true];
			$this->generic_request_handler($rqval, null, $this->lowpost, true);

			if (isset($_SESSION['rq_step'][$_POST['goBackTarget']])) {
				if ($_SESSION['rq_step'][$_POST['goBackTarget']] > 1) {
					--$_SESSION['rq_step'][$_POST['goBackTarget']];
				}
			}
		}
	}

?>
