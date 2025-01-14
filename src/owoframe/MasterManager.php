<?php

/*********************************************************************
	 _____   _          __  _____   _____   _       _____   _____
	/  _  \ | |        / / /  _  \ |  _  \ | |     /  _  \ /  ___|
	| | | | | |  __   / /  | | | | | |_| | | |     | | | | | |
	| | | | | | /  | / /   | | | | |  _  { | |     | | | | | |  _
	| |_| | | |/   |/ /    | |_| | | |_| | | |___  | |_| | | |_| |
	\_____/ |___/|___/     \_____/ |_____/ |_____| \_____/ \_____/

	* Copyright (c) 2015-2021 OwOBlog-DGMT.
	* Developer: HanskiJay(Tommy131)
	* Telegram:  https://t.me/HanskiJay
	* E-Mail:    support@owoblog.com
	* GitHub:    https://github.com/Tommy131

**********************************************************************/

declare(strict_types=1);
namespace owoframe;

use Composer\Autoload\ClassLoader;
use owoframe\application\AppManager;
use owoframe\console\Console;
use owoframe\constant\Manager;
use owoframe\event\EventManager;
use owoframe\helper\BootStrapper as BS;
use owoframe\helper\Helper;
use owoframe\http\FileUploader;
use owoframe\http\HttpManager as Http;
use owoframe\module\ModuleLoader;
use owoframe\object\INI;
use owoframe\redis\RedisManager as Redis;

final class MasterManager implements Manager
{
	/**
	 * 主进程实例
	 *
	 * @access protected
	 * @var MasterManager
	 */
	private static $instance = null;

	/**
	 * ClassLoader实例
	 *
	 * @access private
	 * @var ClassLoader
	 */
	private static $classLoader;

	/**
	 * 绑定标签到类
	 *
	 * @access protected
	 * @var array
	 */
	protected $bind =
	[
		'app'          => AppManager::class,
		'console'      => Console::class,
		'event'        => EventManager::class,
		'fileuploader' => FileUploader::class,
		'http'         => Http::class,
		'redis'        => Redis::class,
		'unknown'      => null
	];

	/**
	 * 对象实例列表
	 *
	 * @access protected
	 * @var array
	 */
	protected $instances = [];



	public function __construct(?ClassLoader $classLoader = null)
	{
		if(!BS::isRunning()) {
			static::$instance = $this;
			if($classLoader !== null) {
				static::$classLoader = $classLoader;
			}
			BS::initializeSystem();
			Container::getInstance()->bind('unknown', new class implements Manager {});

			foreach(['DEBUG_MODE', 'LOG_ERROR', 'DEFAULT_APP_NAME', 'DENY_APP_LIST'] as $define) {
				if(Helper::isRunningWithCLI()) {
					if(($define === 'DEFAULT_APP_NAME') || ($define === 'DENY_APP_LIST')) {
						continue;
					}
				}
				if(!defined($define)) {
					throw error("Constant parameter '{$define}' not found!");
				}
			}
			if(INI::_global('system.autoInitDatabase', true) == true) {
				\owoframe\database\DbConfig::init();
			}
			AppManager::setPath(APP_PATH);
			ModuleLoader::setPath(MODULE_PATH);
			ModuleLoader::autoLoad($this);
			define('OWO_INITIALIZED', true); // Define this constant to let the system know that OwOFrame has been initialized;
		}
	}


	public function stop() : void
	{
		// TODO: 结束任务相关;
	}


	/**
	 * 返回选择的管理器
	 *
	 * @author HanskiJay
	 * @since  2021-03-04
	 * @param  string      $bindTag 绑定标识
	 * @param  array       $params  传入参数
	 * @return AppManager|Console|EventManager|FileUploader|Http|Redis|UserManager
	 */
	public function getManager(string $bindTag, array $params = []) : Manager
	{
		$bindTag = strtolower($bindTag);
		if(!isset($this->bind[$bindTag])) {
			$bindTag = 'unknown';
		}
		if(!isset($this->instances[$bindTag])) {
			$container = Container::getInstance();
			$container->bind($bindTag, $this->bind[$bindTag]);
			$this->instances[$bindTag] = $container->make($bindTag, $params);
		}
		return $this->instances[$bindTag];
	}

	/**
	 * 返回系统运行状态
	 *
	 * @author HanskiJay
	 * @since  2021-03-04
	 * @return boolean
	 */
	public static function isRunning() : bool
	{
		return BS::isRunning();
	}

	/**
	 * 返回类加载器
	 *
	 * @author HanskiJay
	 * @since  2021-03-06
	 * @return ClassLoader|null
	 */
	public static function getClassLoader() : ?ClassLoader
	{
		return static::$classLoader;
	}

	/**
	 * 返回容器单例实例
	 *
	 * @author HanskiJay
	 * @since  2021-03-05
	 * @return MasterManager
	 */
	public static function getInstance() : MasterManager
	{
		if(!static::$instance instanceof MasterManager) {
			static::$instance = new static;
		}
		return static::$instance;
	}
}