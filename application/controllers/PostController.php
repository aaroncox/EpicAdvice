<?php
/**
 *  IndexController
 */
class PostController extends EpicDb_Post_Controller_Abstract {

	protected function _rssTitle()
	{
		return strip_tags($this->view->title) . " :: EpicAdvice for World of Warcraft";
	}

	protected function _formRedirect($form, $key, $ajax) {
		if ($key == 'ask') {
			return $this->view->url(array(
				'post' => $form->getPost()
			), 'post', true);
		}
		return parent::_formRedirect($form, $key, $ajax);
	}

	protected $_post;
	public function getPost()
	{
		if ($this->_post) return $this->_post;
		$this->_post = $this->view->post = $post = $this->getRequest()->getParam('post');
		if (!$post) throw new MW_Controller_404Exception('Unknown Post');
		if($post) {
			$context = $post->contextHelper;
			$this->view->$context($post);
		}
		return $post;
	}

	public function questionsAction() {

		$request = $this->getRequest();
		$auth = $this->_helper->auth;

		// var_dump($auth->getUser()->roles[0]->export());

		if ($request->getParam('ask')) {
			if(!$auth->getUser()) {
				throw new MW_Auth_Exception("You must be logged in to post a question.");
			}
			$question = EpicDb_Mongo::newDoc('question');
			$this->view->form = $form = $question->getEditForm();
			$this->_helper->viewRenderer('ask');
			$this->_handleMWForm($form, 'ask');

		} else if($question = $request->getParam('id')) {
			$query = array(
				'id' => (int) $this->getRequest()->getParam('id')
			);
			$question = $this->view->question = EpicDb_Mongo::db('question')->fetchOne($query);
			if (!$question) {
				$answer = EpicDb_Mongo::db('answer')->fetchOne($query);
				if (!$answer) throw new MW_Controller_404Exeption('Invalid Question ID');
				$question = $answer->_parent;
				$slug = new MW_Filter_Slug();
				return $this->_redirect($this->view->url(array(
					'id' => $question->id,
					'slug' => $slug->filter($question->title),
					'answer' => $answer->id,
				)).'#answer-'.$answer->id);
			}
			$this->_post = $question;
			$this->getRequest()->setParam('post', $question);
			if (!$this->getRequest()->getParam("format")) {
				$this->_forward('view');
			}
		} else {
			$query = array();
			if($this->view->tag = $tag = $request->getParam("tagged")) {
				$queryData = EpicDb_Search::getInstance()->parseQueryString($tag);
				$query = $queryData['query'];
				$tags = $queryData['terms']['tagged'];
				$query["_type"] = "question";
				$tagLinks = array();
				foreach ($tags as $tag) {
					if ($tag instanceOf EpicDb_Mongo_Record) {
						$tagLinks[] = $this->view->recordLink($tag)."";
					} else {
						$tagLinks[] = $this->view->profileLink($tag)."";
					}
				}
				$this->view->title = "Recent Questions Tagged ".implode(", ", $tagLinks);

				$this->view->headLink()->appendAlternate(
					$this->view->url(array(
						'tagged' => $request->getParam('tagged')
					),'se_feeds_tag',true),
					"application/rss+xml",
					$this->view->title
				);
			} else {
				$this->view->title = "Recent Questions";
				$this->view->headLink()->appendAlternate(
					$this->view->url(array(),'se_feeds',true),
					"application/rss+xml",
					$this->view->title
				);
			}
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
					$sort = array('touched' => -1, '_created' => -1);
					break;
			}
			
			$questions = EpicDb_Mongo::db('question')->fetchAll($query, $sort);

			$paginator = Zend_Paginator::factory($questions);
			$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));

			$this->view->questions = $paginator;


			$this->view->popularTags = array();// EpicDb_Mongo_Post::getTagsByUsage();
		}
	}
	public function viewAction() {
		// we should probably just call this "post" in the view too...
		$this->view->post = $post = $this->getPost();

		$params = $this->getRequest()->getParams();
		
		$this->postJson();
		
		// var_dump($post->tags->getTag('author')->export()); exit;
		if ( $post instanceOf EpicDb_Mongo_Post_Question ) {
			$this->view->headLink()->appendAlternate(
				$this->view->url(array(
					'id' => $post->id,
				),'se_feeds_question',true),
				"application/rss+xml",
				$post->title
			);
			$slug = new MW_Filter_Slug();
			$this->view->headLink()->append((object)array(
				'rel' => 'canonical',
				'href' => $this->view->url(array(
						'id' => $post->id,
						'slug' => $slug->filter($post->title),
					),"se_questions", true)
			));
		} else {
			$this->view->headLink()->append((object)array(
				'rel' => 'canonical',
				'href' => $this->view->url(array(
						'post' => $post,
					),"post", true)
			));
		}
		if($this->_helper->auth->getUserProfile() && $post instanceOf EpicDb_Mongo_Post_Question ) {
			$newAnswer = EpicDb_Mongo::db('answer');
			$newAnswer->_parent = $post;
			$answerForm = $this->view->answerForm = $newAnswer->getEditForm();
			$this->_handleMWForm($answerForm, 'answer');
		}
	}
	public function answerAction() {
		$query = array(
			'id' => (int) $this->getRequest()->getParam('id')
		);
		$question = $this->view->post = EpicDb_Mongo::db('question')->fetchOne($query);
		$this->view->hideComments = true;
		if($this->_helper->auth->getUserProfile()) {
			$newAnswer = EpicDb_Mongo::db('answer');
			$newAnswer->_parent = $question;
			$answerForm = $this->view->answerForm = $newAnswer->getEditForm();
			$this->_handleMWForm($answerForm, 'answer');
		}
	}
	public function editAction() {
		$post = $this->getPost();
		// MW_Auth::getInstance()->requirePrivilege($post, 'edit');
		$this->view->form = $form = $post->getEditForm();
		$this->_handleMWForm($form);
	}

	public function revisionsAction() {
		$this->view->post = $this->getPost();
	}

	public function sourceAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$post = $this->getPost();
		$rev = $this->getRequest()->getParam("rev");
		// so that rev 0 works, check string length...
		if(strlen($rev)) {
			$rev = (int) $rev;
			$source = $post->revisions[$rev]->source ?: $post->revisions[$rev]->body;
		} else {
			$source = $post->source ?: $post->body;
		}
		$this->getResponse()->setHeader("Content-type", "text/plain");
		echo $source;
	}

	public function unansweredAction() {
		$query = array();
		$sort = array('_created' => -1);
		$limit = 30;
		$this->view->questions = EpicDb_Mongo::db('question')->fetchAll($query, $sort, $limit);
		$this->view->popularTags = EpicDb_Mongo_Post::getTagsByUsage();
	}

	public function commentAction() {
		$question = $this->view->question = $this->getPost();
		// MW_Auth::getInstance()->requirePrivilege($post, 'comment');
		$newComment = EpicDb_Mongo::db('question-comment');
		$newComment->_parent = $question;
		$commentForm = $this->view->form = $newComment->getEditForm();
		$this->_handleMWForm($commentForm, 'question-comment');
	}
	
	public function replyAction() {
		$post = $this->view->post = $this->getPost();
		// MW_Auth::getInstance()->requirePrivilege($post, 'comment');
		$newComment = EpicDb_Mongo::db('comment');
		$newComment->_parent = $post;
		$commentForm = $this->view->commentForm = $newComment->getEditForm();
		$this->_handleMWForm($commentForm, 'comment');
	}
}
