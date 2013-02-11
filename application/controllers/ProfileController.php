<?php
/**
 * ProfileController
 *
 * undocumented class
 *
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class ProfileController extends EpicDb_Profile_Controller_Abstract
{
	public function init() {
		$this->view->action = $this->getRequest()->getParam("action");
		$this->view->layout()->setLayout('3-column');
		parent::init();
	}

	public function postRssContext()
	{
		$view = $this->view;
		$generator = new EpicDb_Feed_Generator(array('view'=>$view));
		$generator->setLink($view->url());
		$generator->setTitle(strip_tags($this->view->title) . " :: EpicAdvice for World of Warcraft");
		foreach ($this->view->posts as $post) $generator->addPost($post);
		$generator->toRss()->send();
	}

	public function getProfile() {
		$profile = parent::getProfile();
		if($profile) {
			$summary = $profile->summaryHelper;
			$this->view->$summary($profile);
			$context = $profile->contextHelper;
			$this->view->$context($profile);
		}
		return $profile;
	}

	public function viewAction() {
		$profile = $this->getProfile();
		
		$query = array();
		$query['$or'][] = array(
			'tags' =>
				array('$elemMatch' => array(
					'reason' => 'author',
					'ref' => $profile->createReference(),
					)
				)				
			);
		$query['$or'][] = array(
			'tags' =>
				array('$elemMatch' => array(
					'reason' => 'source',
					'ref' => $profile->createReference(),
					)
				)				
			);			
		// var_dump($query); exit;
		switch($this->getRequest()->getParam("sort")) {
			case "highest-voted":
				$sort = array('votes.score' => -1, '_created' => -1);
				break;
			case "lowest-voted":
				$sort = array('votes.score' => 1, '_created' => -1);
				break;
			case "oldest":
				$sort = array('_created' => 1);
				break;
			case "newest":
			default:
				$sort = array('_created' => -1);
				break;
		}
		$posts = EpicDb_Mongo::db('post')->fetchAll($query, $sort);
		// var_dump($query, $posts->export()); exit;
		$paginator = Zend_Paginator::factory($posts);
		$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1))->setItemCountPerPage(10);
		$this->view->posts = $paginator;
		
	}

	public function feedAction()
	{
		$profile = $this->getProfile();
		$this->view->title = $profile->name."'s public feed";
		$query = array(
			'tags' => array(
				'$elemMatch' => array('ref'=>$profile->createReference(), 'reason' => 'author')
			)
		);
		$this->view->posts = EpicDb_Mongo::db('post')->fetchAll($query, array('touched' => -1))->limit(25);
	}

	public function historyAction() {
		$profile = $this->getProfile();
		$query = array('tags' =>
			array('$elemMatch' => array(
				'reason' => 'author',
				'ref' => $profile->createReference(),
				)
			)
		);
		$sort = array('_created' => -1);
		
		$paginator = Zend_Paginator::factory(EpicDb_Mongo::db('question')->fetchAll($query, $sort));
		$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1))->setItemCountPerPage(10);
		$questions = $this->view->questions = $paginator;
		
		
		$paginator = Zend_Paginator::factory(EpicDb_Mongo::db('answer')->fetchAll($query+array('_parent' => array('$exists' => true)), $sort));
		$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1))->setItemCountPerPage(10);
		$answers = $this->view->answers = $paginator;
		// var_dump($answers->export()); exit;
	}

	// I'm not sure where we should put these? There's gonna be website, group, guild, etc
	public function createWebsiteAction() {
		$this->view->form = $form = new EpicDb_Form_Profile_Group_Website();
		$params = $this->getRequest()->getParams();
		if($this->getRequest()->isPost() && $form->isValid($params)) {
			if($form->process($params)) {
				$site = EpicDb_Mongo::db('website')->fetchOne(array('url' => $params['url']));
				$this->_redirect($this->view->url(array("profile" => $site, "type" => "website"), 'profile', true));
			}
			// var_dump($params); exit;
		}
	}
	
	// Yet another function that probably doesn't belong, but I neeeeeeds it... 
	public function manualCrawlAction() {
		$profile = $this->getProfile();
		if(EpicDb_Crawler::crawl($profile)) {
			$this->_redirect($this->view->url(array("profile" => $profile, "type" => $profile->_type), 'profile', true));
		}
		exit;
	}
} // END class ProfileController