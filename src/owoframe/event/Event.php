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
namespace owoframe\event;

abstract class Event
{
	/**
	 * 事件回调状态
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $isCalled = false;

	/**
	 * 设置事件是否已被回调
	 *
	 * @author HanskiJay
	 * @since  2021-04-11
	 * @return void
	 */
	public function setCalled(bool $status = true) : void
	{
		$this->isCalled = $status;
	}

	/**
	 * 判断事件是否已被回调
	 *
	 * @author HanskiJay
	 * @since  2021-04-11
	 * @return boolean
	 */
	public function isCalled() : bool
	{
		return $this->isCalled;
	}
}