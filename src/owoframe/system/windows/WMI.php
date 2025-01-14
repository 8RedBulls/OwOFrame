<?php

/************************************************************************
	 _____   _          __  _____   _____   _       _____   _____
	/  _  \ | |        / / /  _  \ |  _  \ | |     /  _  \ /  ___|
	| | | | | |  __   / /  | | | | | |_| | | |     | | | | | |
	| | | | | | /  | / /   | | | | |  _  { | |     | | | | | |  _
	| |_| | | |/   |/ /    | |_| | | |_| | | |___  | |_| | | |_| |
	\_____/ |___/|___/     \_____/ |_____/ |_____| \_____/ \_____/

	* Copyright (c) 2015-2021 OwOBlog-DGMT.
	* Developer: HanskiJay(Tommy131)
	* Telegram: https://t.me/HanskiJay
	* E-Mail: support@owoblog.com
	* GitHub: https://github.com/Tommy131
	*
	* @link https://www.sitepoint.com/php-wmi-dig-deep-windows-php/  (original)
	* @link https://blog.csdn.net/culh2177/article/details/108385131 (zh-CN translated)
	* @link https://docs.microsoft.com/en-us/windows/win32/api/wbemcli/nn-wbemcli-iwbemservices (MS API DOCS)
	* @link https://docs.microsoft.com/en-us/windows/win32/cimwin32prov/computer-system-hardware-classes (MS API DOCS)
	* Thanks for upper links help!

************************************************************************/

declare(strict_types=1);
namespace owoframe\system\windows;

use COM;
use Variant;
use owoframe\helper\Helper;
use owoframe\exception\ExtensionMissedException;

class WMI
{
	/**
	 * 连接到的命名空间
	 *
	 * @access protected
	 * @var string
	 */
	protected $namespace = 'root\cimv2';

	/**
	 * 执行脚本
	 *
	 * @access protected
	 * @var string
	 */
	protected $script = 'WbemScripting.SWbemLocator';

	/**
	 * WMI关键配置文件
	 *
	 * @access protected
	 * @var array
	 */
	protected $config =
	[
		'host' => '127.0.0.1',
		'user' => '',
		'pass' => ''
	];

	/**
	 * 连接实例
	 *
	 * @access protected
	 * @var Variant
	 */
	protected $connection;



	public function __construct(?string $script = null)
	{
		if((Helper::getOS() === Helper::OS_WINDOWS) && !extension_loaded('com_dotnet')) {
			throw new ExtensionMissedException('com_dotnet');
		}
		$this->script = $script ?? $this->script;
		$this->COM    = new COM($this->script);
	}


	/**
	 * 设置/更新WMI关键配置文件
	 *
	 * @author HanskiJay
	 * @since  2021-04-24
	 * @param  string      $index 键名
	 * @param  string      $value 值
	 * @return void
	 */
	public function set(string $index, string $value) : void
	{
		$this->config[$index] = $value;
	}

	/**
	 * 返回或创建一个COM连接
	 *
	 * @author HanskiJay
	 * @since  2021-04-24
	 * @return Variant
	 */
	public function getConnection() : Variant
	{
		if($this->connection instanceof Variant) {
			return $this->connection;
		}

		try {
			$this->connection = $this->COM->ConnectServer($this->get('host'), $this->namespace, $this->get('user', exec('whoami')), $this->get('pass'));
		} catch(\Throwable $e) {
			if($e->getCode() === -2147352567) {
				$this->connection = $this->COM->ConnectServer($this->get('host'), $this->namespace, null, null);
			}
		}

		if($this->connection) {
			$this->connection->Security_->impersonationLevel = $this->get('level', 3);
		}
		return $this->connection;
	}

	/**
	 * 返回真实的WMI实例
	 *
	 * @author HanskiJay
	 * @since  2021-04-24
	 * @link   https://powershell.one/wmi/root/cimv2 (所有可获取的类名)
	 * @param  string      $className
	 * @return Variant
	 */
	public function getRealWMI(string $className) : Variant
	{
		return $this->getConnection()->ExecQuery('SELECT * FROM ' . $className);
	}

	/**
	 * 返回COM实例化对象
	 *
	 * @author HanskiJay
	 * @since  2021-04-24
	 * @return COM
	 */
	public function COM() : COM
	{
		return $this->COM;
	}

	/**
	 * 获取WMI关键配置文件
	 *
	 * @author HanskiJay
	 * @since  2021-04-24
	 * @param  string      $index   键名
	 * @param  mixed       $default 默认返回值
	 * @return mixed
	 */
	public function get(string $index, $default = null)
	{
		return $this->config[$index] ?? $default;
	}
}