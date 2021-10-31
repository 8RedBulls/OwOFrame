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
namespace owoframe\utils;

use owoframe\helper\Helper;

class LogWriter
{
	/* @string 默认日志记录文件名称 */
	public const DEFAULT_LOG_NAME = "owoblog_run.log";
	/* @string 日志记录格式 */
	public const LOG_FORMAT = "[%s][%s][%s/%s] > %s";

	/* @string 日志记录文件名称 */
	private static $fileName;
	/* @string 日志记录称号 */
	public static $logPrefix = 'OwOWeb';
	/* @int 最大文件大小(mb) */
	public static $maxFileSize = 1024; // mb, 日志文件大小大于这个值时自动截断并且生成新的日志;


	/**
	 * @method      success
	 * @description 日志写入: INFO 等级(仅颜色显示不同)
	 * @param       string      $message 日志内容
	 * @param       string      $color   默认输出颜色(仅在CLI模式下)
	 * @return      void
	 */
	public static function success(string $message, string $color = TextFormat::GREEN) : void
	{
		self::write($message, 'SUCCESS', $color);
	}

	/**
	 * @method      info
	 * @description 日志写入: INFO 等级
	 * @param       string      $message 日志内容
	 * @param       string      $color   默认输出颜色(仅在CLI模式下)
	 * @return      void
	 */
	public static function info(string $message, string $color = TextFormat::WHITE) : void
	{
		self::write($message, 'INFO', $color);
	}

	/**
	 * @method      warning
	 * @description 日志写入: WARNING 等级
	 * @param       string      $message 日志内容
	 * @param       string      $color   默认输出颜色(仅在CLI模式下)
	 * @return      void
	 */
	public static function warning(string $message, string $color = TextFormat::GOLD) : void
	{
		self::write($message, 'WARNING', $color);
	}

	/**
	 * @method      error
	 * @description 日志写入: ERROR 等级
	 * @param       string      $message 日志内容
	 * @param       string      $color   默认输出颜色(仅在CLI模式下)
	 * @return      void
	 */
	public static function error(string $message, string $color = TextFormat::RED) : void
	{
		self::write($message, 'ERROR', $color);
	}

	/**
	 * @method      emergency
	 * @description 日志写入: EMERGENCY 等级
	 * @param       string      $message 日志内容
	 * @param       string      $color   默认输出颜色(仅在CLI模式下)
	 * @return      void
	 */
	public static function emergency(string $message, string $color = TextFormat::LIGHT_RED) : void
	{
		self::write($message, 'EMERGENCY', $color);
	}

	/**
	 * @method      debug
	 * @description 日志写入: DEBUG 等级
	 * @param       string      $message 日志内容
	 * @param       string      $color   默认输出颜色(仅在CLI模式下)
	 * @return      void
	 */
	public static function debug(string $message, string $color = TextFormat::GRAY) : void
	{
		self::write($message, 'DEBUG', $color);
	}

	/**
	 * @method      setFileName
	 * @description 写入日志
	 * @author      HanskiJay
	 * @doneIn      2021-01-23
	 * @param       string      $message 日志内容
	 * @param       string      $level   日志等级
	 */
	public static function write(string $message, string $level, string $color = TextFormat::WHITE) : void
	{
		if(is_null(static::$fileName)) static::$fileName = LOG_PATH . self::DEFAULT_LOG_NAME;

		if(is_file(static::$fileName) && (filesize(static::$fileName) >= static::$maxFileSize * 1000)) {
			rename(static::$fileName, str_replace('.log', '', static::$fileName) . date('_Y_m_d') . '.log');
		}
		$message = $color . sprintf(self::LOG_FORMAT, date('Y-m-d'), date('H:i:s'), static::$logPrefix, $level, $message) . PHP_EOL;

		if(Helper::isRunningWithCLI()) {
			echo TextFormat::parse($message);
			$message = TextFormat::clean($message);
		}
		file_put_contents(static::$fileName, $message, FILE_APPEND | LOCK_EX);
	}

	/**
	 * @method      setFileName
	 * @description 删除错误日志
	 * @author      HanskiJay
	 * @doneIn      2021-01-23
	 * @param       string      $fileName 日志名称
	 */
	public static function cleanLog(string $fileName = '') : void
	{
		if(is_null($fileName)) $fileName = LOG_PATH . static::$fileName;
		if(is_file($fileName)) unlink($fileName);
	}

	/**
	 * @method      setFileName
	 * @description 设置日志名称
	 * @author      HanskiJay
	 * @doneIn      2021-01-23
	 * @param       string      $fileName 日志名称]
	 */
	public static function setFileName(string $fileName) : void
	{
		static::$fileName = LOG_PATH . $fileName;
	}
}
?>