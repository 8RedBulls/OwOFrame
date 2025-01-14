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
namespace owoframe\http;

use owoframe\exception\OwOFrameException;
use owoframe\object\INI;
use owoframe\redis\RedisConnector;

class Session
{
	/**
	 * 启动Session
	 *
	 * @author HanskiJay
	 * @since  2021-02-13
	 * @return void
	 */
	public static function start() : void
	{
		try {
			if(!self::isStarted()) {
				if(INI::_global('redis.enable', true) && extension_loaded("redis"))
				{
					if(strtolower(ini_get("session.save_handler")) === "files") {
						ini_set("session.save_handler", "redis");
					}
					$server = INI::_global('redis.server', '127.0.0.1');
					$port   = INI::_global('redis.port', 6379);
					$auth   = INI::_global('redis.auth', null);

					$connector = RedisConnector::getInstance();
					$connector->cfg('host',     $server, true);
					$connector->cfg('port',     $port,   true);
					$connector->cfg('password', $auth,   true);

					if($redis = $connector->getConnection()) {
						$connector->forceUsePassword();
					} else {
						throw new OwOFrameException('Could not use Redis for Session saver!');
					}

					$auth   = ($auth !== null) ? "?auth={$auth}" : '';
					ini_set("session.save_path", "tcp://{$server}:{$port}{$auth}");
				}
				ini_set('session.gc_maxlifetime', (string) (defined('SESSION_EXPIRE_TIME') ? SESSION_EXPIRE_TIME : '10800')); // 设置PHP_SESSION自动过期时间;
				session_start();
			}
		} catch(\Throwable $e) {
			throw error($e->getMessage());
		}
	}

	/**
	 * 判断Session启动状态
	 *
	 * @constant    PHP_SESSION_DISABLED 会话是被禁用的
	 * @constant    PHP_SESSION_NONE     会话是启用的, 但不存在当前会话
	 * @constant    PHP_SESSION_ACTIVE   会话是启用的, 而且存在当前会话
	 *
	 * @author HanskiJay
	 * @since  2021-03-14
	 * @return boolean
	 */
	public static function isStarted() : bool
	{
		return session_status() === PHP_SESSION_ACTIVE;
	}

	/**
	 * 检查是否存在单个Session数据
	 *
	 * @author HanskiJay
	 * @since  2021-02-13
	 * @param  string      $storeKey 存储名
	 * @return boolean
	 */
	public static function has(string $storeKey) : bool
	{
		return isset($_SESSION[$storeKey]);
	}

	/**
	 * 新增一个Session数据
	 *
	 * @author HanskiJay
	 * @since  2021-02-13
	 * @param  string      $storeKey       存储名
	 * @param  mixed       $data           数据
	 * @param  boolean     $rewriteAllowed 是否允许重写
	 * @return void
	 */
	public static function set(string $storeKey, $data, bool $rewriteAllowed = false) : void
	{
		if(!self::has($storeKey) || $rewriteAllowed) {
			$_SESSION[$storeKey] = $data;
		}
	}

	/**
	 * 获取一个Session数据
	 *
	 * @author HanskiJay
	 * @since  2021-02-13
	 * @param  string      $storeKey 存储名
	 * @param  mixed       $default  默认返回结果
	 * @return mixed
	 */
	public static function get(string $storeKey, $default = null)
	{
		return $_SESSION[$storeKey] ?? $default;
	}

	/**
	 * 获取全部的Session数据
	 *
	 * @author HanskiJay
	 * @since  2021-02-13
	 * @param  string      $storeKey 存储名
	 * @return array
	 */
	public static function getAll() : array
	{
		return $_SESSION ?? [];
	}

	/**
	 * 删除单个Session数据
	 *
	 * @author HanskiJay
	 * @since  2021-02-13
	 * @param  string      $storeKey 存储名
	 * @return void
	 */
	public static function delete(string $storeKey) : void
	{
		if(self::has($storeKey)) {
			unset($_SESSION[$storeKey]);
		}
	}

	/**
	 * 重置Session数据
	 *
	 * @author HanskiJay
	 * @since  2021-02-13
	 * @return void
	 */
	public static function reset() : void
	{
		$_SESSION = [];
	}
}
?>