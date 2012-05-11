<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter
{
	public function renderFluentpdo() {
		$user = $this->fpdo->from('user', 2)->fetch();
		$this->template->user = $user;
		
		$articles = $this->fpdo
				->from('article')
				->where('published_at')
				->orderBy('published_at DESC');
		$this->template->articles = $articles;
	}

	public function renderNotorm() {
		$user = $this->notorm->user[2];
		$this->template->user = $user;
		
		$articles = $this->notorm
				->article('published_at')
				->order('published_at DESC');
		$this->template->articles = $articles;
	}
	
	public function renderArticles() {
		$this->template->articles = $this->context->articles->getPublished();
	}

}
