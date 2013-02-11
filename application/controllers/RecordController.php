<?php
/**
 * RecordController
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class RecordController extends EpicDb_Controller_Action
{
	public function init() {
		$this->view->layout()->setLayout('3-column');
		parent::init();
	}
	public function indexAction() {
		
	}
	
	public function relatedAction() {
		$record = $this->getRecord();
		$this->view->posts = $record->getRelatedPosts();
	}
	
	public function getRecord()
	{
		$record = $this->getRequest()->getParam("record");
		if($record) {
			$summary = $record->summaryHelper;
			$this->view->$summary($record);
			$context = $record->contextHelper;
			$this->view->$context($record);
		}		
		return $this->view->record = $record;
	}
	
	public function viewAction() {
		$record = $this->getRecord();
		
	}
	
	public function listAction() {
		$type = $this->getRequest()->getParam('type');
		if($type) {
			$query = array();
			$sort = array("name" => 1);
			$limit = 50;
			$this->view->records = EpicDb_Mongo::db($type)->fetchAll($query, $sort, $limit);
		}
	}
	public function tagListAction() {
		$this->view->layout()->setLayout('2-column');
		$paginator = Zend_Paginator::factory(EpicDb_Mongo_Post::getTagsByUsage());
		$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1))->setItemCountPerPage(30);
		$this->view->records = $paginator;
		
	}
	public function unfollowAction() {
		$this->_helper->auth->unfollow($this->getRecord());
		$this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
	}


	public function followAction() {
		$this->_helper->auth->follow($this->getRecord());
		$this->_redirect($this->getRequest()->getServer('HTTP_REFERER'));
	}
	
	public function adminAction() {
		EpicDb_Auth::getInstance()->requirePrivilege(new MW_Auth_Resource_Super());			
		$record = $this->getRecord();
		$this->view->adminForms = $adminForms = $record->getAdminForms();
		$currentForm = $this->getRequest()->getParam('form');
		if($currentForm && isset($adminForms[$currentForm])) {
	    $this->_handleMWForm($adminForms[$currentForm], $currentForm);
			return $this->_redirect($this->view->url(array('form'=>null)));
    }
		foreach($adminForms as $key => $form) {
			$form->setAction($this->view->url(array('form'=>$key)));
		}		
	}
	
} // END class RecordController