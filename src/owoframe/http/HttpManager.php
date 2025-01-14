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

use owoframe\constant\HTTPStatusCodeConstant;
use owoframe\constant\Manager;

use owoframe\helper\Helper;
use owoframe\http\Session;
use owoframe\http\route\Router;

use owoframe\object\JSON;
use owoframe\utils\Logger;

class HttpManager implements HTTPStatusCodeConstant, Manager
{
	/**
	 * 日志识别前缀
	 */
	public const LOG_PREFIX = 'CRF/BeforeRoute';

	/**
	 * 默认的用于过滤的正则表达式
	 */
	public const DEFAULT_XSS_FILTER =
	[
		"/<(\\/?)(script|i?frame|style|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU",
		"/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
		"/select\b|insert\b|update\b|delete\b|drop\b|;|\"|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dump/is",
		// "/(\\\(|\\\)| |\s|!|@|#|\\\$|%|\\\^|&|\\\*|\\\-|_|\\\+|\\\=|\\\||)/isU",
		// "/[`~!@#$%^&*()_\-+=<>?:\\\"{}|,.\/;'\\[\]·~！#￥%……&*（）——\-+={}|《》？：“”【】、；‘'，。、]/im"
	];


	/**
	 * 黑名单配置文件
	 *
	 * @access protected
	 * @var Config
	 */
	protected static $ipList;

	/**
	 * 不记录日志的路由
	 *
	 * @var array
	 */
	public static $notLogUrl = [];

	/**
	 * 自定义的用于过滤的正则表达式
	 *
	 * @var array
	 */
	public static $customFilter = [];


	/**
	 * 启动HttpManager
	 *
	 * @author HanskiJay
	 * @since  2021-03-07
	 * @return void
	 */
	public function start(bool $autoDispatch = true) : void
	{
		$ip = Helper::getClientIp();
		if(!self::isIpValid($ip)) {
			Logger::info('[403@Banned] Client ' . $ip . '\'s IP is banned, request denied.');
			self::setStatusCode(403);
			return;
		}
		Logger::$logPrefix = self::LOG_PREFIX;
		if(stripos(implode(',', static::$notLogUrl), server('REQUEST_URI')) === false) Logger::info('[REQUEST@' . server('REQUEST_METHOD') . '] ' . $ip . ' -> ' . self::getCompleteUrl());
		if($autoDispatch) {
			if(ob_get_level() === 0) ob_start();
			Session::start();
			Router::dispatch();
		}
	}

	/**
	 * 日志记录过滤方法
	 *
	 * @author HanskiJay
	 * @since  2021-11-01
	 * @param  string      $uri URL地址
	 * @return void
	 */
	public static function pushInLogFilter(string $uri) : void
	{
		static::$notLogUrl[] = $uri;
	}


	/**
	 * HTTP 参数操作方法
	 */
	/**
	 * 设置HTTP状态码
	 *
	 * @author HanskiJay
	 * @since  2021-01-10
	 * @param  int      $code 状态码
	 * @return void
	 */
	public static function setStatusCode(int $code) : void
	{
		if(isset(self::HTTP_CODE[$code])) {
			header(((server('SERVER_PROTOCOL') !== null) ? server('SERVER_PROTOCOL') : 'HTTP/1.1') . " {$code} " . self::HTTP_CODE[$code], true, $code);
		}
	}

	/**
	 * 快速新建响应头实例
	 *
	 * @author HanskiJay
	 * @since  2021-03-18
	 * @param  callable|null    $callback 可回调参数
	 * @param  array            $params   回调参数传递
	 * @param  bool             $reload   重新生成响应实例
	 * @return Response
	 */
	public static function Response(?callable $callback = null, array $params = [], bool $reload = false) : Response
	{
		static $response;

		if($reload) {
			$response = null;
		}
		if(!$response instanceof Response) {
			$response = new Response($callback, $params);
		}
		return $response;
	}

	/**
	 * 设置自定义的XSS过滤器
	 *
	 * @author HanskiJay
	 * @since  2021-03-07
	 * @param  array       $filter 正则过滤器组
	 * @return void
	 */
	public static function setXssFilter(array $filter) : void
	{
		static::$customFilter = array_merge(static::$customFilter, $filter);
	}

	/**
	 * XSS跨站请求过滤
	 *
	 * @author HanskiJay
	 * @since  2021-02-07
	 * @param  string      $str         需要过滤的参数
	 * @param  string      $allowedHTML 允许的HTML标签 (e.g. "<a><b><div>" (将不会过滤这三个HTML标签))
	 * @return void
	 */
	public static function xssFilter(string &$str, string $allowedHTML = null) : void
	{
		$str = preg_replace(array_merge(self::DEFAULT_XSS_FILTER, static::$customFilter), '', strip_tags($str, $allowedHTML));
	}

	/**
	 * 返回整个的请求数据(默认返回原型)
	 *
	 * @author HanskiJay
	 * @since  2021-02-06
	 * @param  bool           $useXssFilter 是否使用默认的XSS过滤函数
	 * @param  callable|null  callback      回调参数
	 * @return array (开发者需注意在此返回参数时必须使回调参数返回数组)
	 */
	public static function getRequestMerge(bool $useXssFilter = true, ?callable $callback = null) : array
	{
		if($useXssFilter) {
			$get = $post = [];
			foreach(get(owohttp) as $k => $v) {
				$k = trim($k);
				$v = trim($v);
				static::xssFilter($k);
				static::xssFilter($v);
				$get[$k] = $v;
			}
			foreach(post(owohttp) as $k => $v) {
				$k = trim($k);
				$v = trim($v);
				static::xssFilter($k);
				static::xssFilter($v);
				$post[$k] = $v;
			}
			$array = ['get' => $get, 'post' => $post];
		} else {
			$array = ['get' => get(owohttp), 'post' => post(owohttp)];
		}
		return !is_null($callback) ? call_user_func_array($callback, $array) : $array;
	}


	/**
	 * ClientIp 操作方法
	 */
	/**
	 * 封禁一个IP
	 *
	 * @author HanskiJay
	 * @since  2021-03-09
	 * @param  string      $ip     IP地址
	 * @param  int|integer $toTime 封禁到时间(默认10分钟)
	 * @param  string      $reason 封禁理由
	 * @return void
	 */
	public static function banIp(string $ip, int $toTime = 10, string $reason = '') : void
	{
		if(Helper::isIp($ip)) {
			$encodedIp = base64_encode($ip);
		}
		$toTime = microtime(true) + $toTime * 60;
		if(!static::isBanned($ip)) {
			static::ipList()->set($encodedIp,
			[
				'origin'  => $ip,
				'banTime' => $toTime,
				'reason'  => $reason
			]);
		} else {
			static::ipList()->set($encodedIp.'.banTime', $toTime);
		}
		static::ipList()->save();
	}

	/**
	 * 判断IP地址是否被带时间封禁
	 *
	 * @author HanskiJay
	 * @since  2021-03-07
	 * @param  string      $ip IP地址
	 * @return boolean
	 */
	public static function isBanned(string $ip) : bool
	{
		if(Helper::isIp($ip)) {
			$ip = base64_encode($ip);
		}
		$ipData = static::ipList()->get($ip);
		return ($ipData !== null) && isset($ipData['banTime']);
	}

	/**
	 * 判断IP地址是否被永久封禁
	 *
	 * @author HanskiJay
	 * @since  2021-03-07
	 * @param  string      $ip IP地址
	 * @return boolean
	 */
	public static function isForeverBanned(string $ip) : bool
	{
		if(Helper::isIp($ip)) {
			$ip = base64_encode($ip);
		}
		if(!static::isBanned($ip)) {
			return false;
		}
		return static::ipList()->get($ip.'.banTime') == true;
	}

	/**
	 * 设置IP信息集
	 *
	 * @author HanskiJay
	 * @since  2021-03-09
	 * @param  string      $ip   IP地址
	 * @param  array       $data 自定义设置信息集
	 * @return JSON
	 */
	public static function setIpData(string $ip, array $data) : JSON
	{
		if(Helper::isIp($ip)) {
			$encodedIp = base64_encode($ip);
		}
		$ipData    = static::ipList()->get($encodedIp) ?? [];
		$ipData    = array_merge($ipData, $data);
		if(!isset($ipData['origin'])) {
			$ipData['origin'] = $ip;
		}
		static::ipList()->set($encodedIp, $ipData);
		static::ipList()->save();
		return static::ipList();
	}

	/**
	 * 判断当前IP的访问有效性
	 *
	 * @author HanskiJay
	 * @since  2021-03-13
	 * @param  string      $ip IP地址
	 * @return boolean
	 */
	private static function isIpValid(string $ip) : bool
	{
		if(Helper::isIp($ip)) {
			$encodedIp = base64_encode($ip);
		}
		if(!static::isBanned($ip)) {
			return true;
		}
		if(static::isForeverBanned($ip) || (microtime(true) - static::ipList()->get($encodedIp.'.banTime') > 0)) {
			return false;
		}
	}

	/**
	 * 返回黑名单配置文件实例
	 *
	 * @author HanskiJay
	 * @since  2021-03-07
	 * @return JSON
	 */
	public static function ipList() : JSON
	{
		if(!static::$ipList instanceof JSON) {
			static::$ipList = new JSON(F_CACHE_PATH . 'config' . DIRECTORY_SEPARATOR . 'ipList.json');
		}
		return static::$ipList;
	}


	/**
	 * URI/URL 方法
	 */
	/**
	 * 判断是否为HTTPS协议
	 *
	 * @author HanskiJay
	 * @since  2020-09-09 18:03
	 * @return boolean
	 */
	public static function isSecure() : bool
	{
		return (!empty(server('HTTPS')) && 'off' != strtolower(server('HTTPS')))
			|| (!empty(server('SERVER_PORT')) && 443 == server('SERVER_PORT'));
	}

	/**
	 * 获取完整请求HTTP地址
	 *
	 * @author HanskiJay
	 * @since  2020-09-09 18:03
	 * @return string
	*/
	public static function getCompleteUrl() : string
	{
		return server('REQUEST_SCHEME').'://'.server('HTTP_HOST').server('REQUEST_URI');
	}

	/**
	 * 获取根地址
	 *
	 * @author HanskiJay
	 * @since  2020-09-09 18:03
	 * @return string
	 */
	public static function getRootUrl() : string
	{
		return server('REQUEST_SCHEME').'://'.server('HTTP_HOST');
	}

	/**
	 * 返回自定义Url
	 *
	 * @author HanskiJay
	 * @since  2020-09-10 18:49
	 * @param  string      $name 名称
	 * @param  string      $path 路径
	 * @return string
	 */
	public static function betterUrl(string $name, string $path) : string
	{
		return trim($path, '/').'/'.str_replace('//', '/', ltrim(((0 === strpos($name, './')) ? substr($name, 2) : $name), '/'));
	}
}