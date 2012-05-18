<?php

/**
 * Base class for all application presenters.
 * 
 * @param SystemContainer $context
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var FluentPDO */
	protected $fpdo;
	
	/** @var NotORM */
	protected $notorm;

	protected function startup() {
		parent::startup();
		$this->fpdo = $this->getService('fpdo');
		$this->notorm = $this->getService('notorm');
	}
	
	protected function beforeRender() {
		$this->template->fpdo = $this->fpdo;
		$this->template->notorm = $this->notorm;
	}

}
