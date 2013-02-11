<?php
/**
 * DashboardController
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class DashboardController extends EpicDb_Controller_Action
{
	public function nonUserAction() {
		$paginator = Zend_Paginator::factory(EpicDb_Mongo::db('post')->getPublicPosts());
		$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
		$this->view->posts = $paginator;
	}
	public function indexAction() {
		if($this->view->profile = $profile = $this->getUserProfile()) {
			$paginator = Zend_Paginator::factory($profile->getFollowedPosts());
			$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
			$this->view->posts = $paginator;
		} else {
			$this->_forward("non-user");
		}
	}
} // END class DashboardController