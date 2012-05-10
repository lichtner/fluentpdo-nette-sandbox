<?php

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var FluentPDO */
	protected $fpdo;


	protected function startup() {
		parent::startup();
		$this->fpdo = $this->getService('fpdo');
	}
	
	protected function beforeRender() {
		$this->template->fpdo = $this->fpdo;
	}

}
