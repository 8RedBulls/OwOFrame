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
namespace owoframe\exception;

use Throwable;

use owoframe\helper\BootStrapper as BS;
use owoframe\helper\Helper;
use owoframe\http\HttpManager as Http;
use owoframe\http\Response;
use owoframe\utils\Logger;

class ExceptionOutput
{

	public static function ErrorHandler($errno, $errstr, $errfile, $errline, $context, $trace = null)
	{
		if(error_reporting() === 0) return false;
		$errorConversion =
		[
			E_ERROR             => 'E_ERROR',
			E_WARNING           => 'E_WARNING',
			E_PARSE             => 'E_PARSE',
			E_NOTICE            => 'E_NOTICE',
			E_CORE_ERROR        => 'E_CORE_ERROR',
			E_CORE_WARNING      => 'E_CORE_WARNING',
			E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
			E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
			E_USER_ERROR        => 'E_USER_ERROR',
			E_USER_WARNING      => 'E_USER_WARNING',
			E_USER_NOTICE       => 'E_USER_NOTICE',
			E_STRICT            => 'E_STRICT',
			E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
			E_DEPRECATED        => 'E_DEPRECATED',
			E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
		];
		$errno = isset($errorConversion[$errno]) ? $errorConversion[$errno] : $errno;
		if(($pos = strpos($errstr, "\n")) !== false) $errstr = substr($errstr, 0, $pos);
		if(!preg_match('/Cannot use "parent" when current class scope has no parent/i', $errstr)) {
			$msg = "{$errno} happened: {$errstr} in {$errfile} at line {$errline}";
			if(Helper::isRunningWithCGI()) {
				/* if(!DEBUG_MODE) {
					return false;
				} */
				if(defined('LOG_ERROR') && LOG_ERROR) {
					$logged = '<span id="logged">--- Logged ---</span>';
					self::log($msg);
				} else {
					$logged = '';
				}
				echo str_replace(
					['{logged}', '{type}', '{message}', '{file}', '{line}', '{trace}', '{runTime}'],
					[$logged, $errno, $msg, $errfile, $errline, null, BS::getRunTime()],
				self::getTemplate());
			} else {
				self::log($msg);
			}
			exit(1);
		}
	}

	public static function ExceptionHandler(Throwable $exception)
	{
		$type  = "[" . (($exception instanceof OwOFrameException) ? "OwOError" : "PHPError")."] ";
		$type .= Helper::getShortClassName($exception);

		if(Helper::isRunningWithCGI()) {
			if(($exception instanceof MethodMissedException) && $exception->getJudgement()) {
				$response = Http::Response($exception->getAlternativeCall());
				$response->sendResponse();
				Response::getRunTimeDiv($exception::toggleRunTimeDivOutput(false));
				return;
			}

			if(defined('LOG_ERROR') && LOG_ERROR) {
				$logged = '<span id="logged">--- Logged ---</span>';
				self::log($exception->__toString());
			} else {
				$logged = '';
			}
			$fileName = method_exists($exception, 'getRealFile') ? $exception->getRealFile() : $exception->getFile();
			$realName = method_exists($exception, 'getRealLine') ? $exception->getRealLine() : $exception->getLine();
			echo str_replace(
				['{logged}', '{type}', '{message}', '{file}', '{line}', '{trace}', '{runTime}'],
				[$logged, $type, $exception->getMessage(),  $fileName, $realName, $exception->getTraceAsString(), BS::getRunTime()],
			self::getTemplate());
		} else {
			self::log($exception->__toString());
		}
		exit(1);
	}

	private static function log(string $msg) : void
	{
		$isCLI = Helper::isRunningWithCGI() ? '' : 'cli_';
		Logger::setLogFileName("owoblog_{$isCLI}error.log");
		Logger::$logPrefix = 'OwOBlogErrorHandler';
		Logger::emergency(trim(str2UTF8($msg)));
	}

	public static function getTemplate() : string
	{
		$debugMode = (DEBUG_MODE) ? '<span id="debugMode">DebugMode</span>' : '';
		return str_replace('{debugMode}', $debugMode, file_get_contents(FRAMEWORK_PATH . 'template' . DIRECTORY_SEPARATOR . 'ExceptionOutputTemplate.html'));
	}
}
?>