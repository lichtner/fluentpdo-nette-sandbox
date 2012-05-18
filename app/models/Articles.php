<?php

/**
 * Description of Article
 *
 * @author marek
 */
class Articles {
	
	/** @var FluentPDO */
	protected $fpdo;

	public function __construct(FluentPDO $fluentpdo) {
		$this->fpdo = $fluentpdo;
	}
	
	function getPublished() {
		$articles = $this->fpdo
				->from('article')
				->where('published_at')
				->orderBy('published_at DESC');
		return $articles;
	}
}

