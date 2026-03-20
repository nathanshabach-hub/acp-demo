<?php

namespace App\Controller;

use App\Controller\AppController;

class PagesController extends AppController {

	public function initialize() {
		parent::initialize();
		$this->loadComponent('Flash');
		$this->loadModel('Pages');
	}

	public function privacyPolicy() {
		return $this->renderStaticPage('privacy-policy');
	}

	public function termsAndConditions() {
		return $this->renderStaticPage('terms');
	}

	protected function renderStaticPage($slug) {
		$this->viewBuilder()->setLayout('home');

		$pageContent = $this->Pages->find()->where(['Pages.slug' => $slug])->first();
		if (!$pageContent) {
			$this->Flash->error('Page not found.');
			return $this->redirect(['controller' => 'homes', 'action' => 'index']);
		}

		$this->set('pageContent', $pageContent);
		$this->set('title_for_layout', $pageContent->static_page_title . ' ' . TITLE_FOR_PAGES);

		return $this->render('details');
	}
}