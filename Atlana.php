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

	require_once 'vendor/autoload.php';
	require_once 'AtlanaCache.php';

	define('__ATLANAROOT__', __DIR__);

	class Atlana
	{
		private $defaultTemplateFolder = '../views';
		private $defaultCfg = '../conf/atlana.ini';
		private $debug = false;

		public $cache = null;
		public $cfg = null;
		public $sql = null;
		public $lowget = null;
		public $lowpost = null;

		public $tpl = null;
		public $tploptions = null;

		public $modules = array();
		public $module_vars = array();
		public $module_paths = array();

		/**
		 * autoload
		 *
		*/
		public function autoload(string $class_name)
		{
			$class_name = strtolower($class_name);
			$folders = array_merge([__DIR__.'/modules', __DIR__.'/models', __DIR__.'/../modules'], $this->module_paths);

			foreach ($folders as $folder) {
				if (file_exists($folder.'/'.$class_name.'.php')) {
					require_once $folder.'/'.$class_name.'.php';
				}
			}
		}

		/**
		 * if debug mode
		 *
		*/
		protected function debugmsg(string $dmsg)
		{
			if ($this->debug) {
				return $dmsg;
			}

			return null;
		}

		/**
		 * exception handler
		 *
		*/
		public function exception($exception)
		{
			header('HTTP/1.1 500 Internal Server Error', true, 500);
			$excmsg = $exception->getMessage();

			if ($this->tpl !== null)
			{
				die($this->tpl->render(
					'errors/500.thtml',
					array('exception' => $this->debugmsg($excmsg))
				));
			}

			die($this->debugmsg($excmsg));
		}

		/**
		 * can be used for making it possible to load modules,
		 * outside the usual module tree but that is discouraged
		 *
		*/
		public function loadOutside(string $path) {
			array_push($this->module_paths, $path);
		}

		/**
		 * @param string $tpl The template
		 * @param string $folder Which folder
		 * @throws Twig or Plates exceptions and \LogicException
		 *
		*/
		public function loadTemplates(string $tpl, string $folder = 'none')
		{
			$folder = ($folder === "none" ? $this->$defaultTemplateFolder : $folder);
			$tplPath = $folder.'/'.$tpl;

			if (!file_exists($tplPath.'/'.'errors'))
			{
				throw new \LogicException(
					_('Atlana: A template folder for error exceptions is needed')
				);
			}

			if (!isset($this->cfg['tpl_engine']))
			{
				throw new \LogicException(
					_('Atlana: You need tpl_engine in your Atlana configuration file in order to load templates')
				);
			} else {
				$this->tpl = new $this->cfg['tpl_engine']($tplPath, $this->tploptions, $this->debug);
				return;
			}

			throw new \LogicException(
				_('Atlana: A template engine is required in order to load templates')
			);
		}

		/**
		 * add variables to tpl engine
		 *
		*/
		public function tag(string $key, $value = null) {
			$this->tpl->tag($key, $value);
		}

		public function dump() {
			var_dump($this->tpl->tplvars);
		}

		/**
		 * render pages using the tpl engine
		 *
		*/
		public function render($page) {
			return $this->tpl->render($page);
		}

		public function probeCoreModules()
		{
			$core_modules = null;

			if (isset($this->cfg['core_modules'])) {
				$core_modules = explode(', ', $this->cfg['core_modules']);
			} else {
				return;
			}

			foreach ($core_modules as $module) {
				$this->{$module};
			}
		}

		public function __get(string $module)
		{
			if (!isset($this->modules[$module])) {
				$this->modules[$module] = new $module($this);
			}

			return $this->modules[$module];
		}

		public function __construct(bool $debug = false, string $cfg = 'default_cfg_path', bool $sqluse = true)
		{
			mb_internal_encoding('UTF-8');
			mb_http_output('UTF-8');

			set_exception_handler(array($this, 'exception'));
			spl_autoload_register(array($this, 'autoload'));

			if ($cfg === 'default_cfg_path') {
				$cfg = $this->defaultCfg;
			}
			$this->debug = $debug;

			$this->cache = new AtlanaCache($debug, $this);
			$this->cfg = $this->cache->get_conf($cfg);
			if ($sqluse) {
				$this->sql = new sql($this->cfg);
			}

			date_default_timezone_set($this->cfg['timezone']);
			$this->probeCoreModules();
		}
	}

	/* PHP setting */
	ini_set("magic_quotes_runtime", 0);
?>
