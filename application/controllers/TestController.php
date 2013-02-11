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

class TestController extends EpicDb_Controller_Action {

	public function preDispatch()
	{
		if ( APPLICATION_ENV != 'development' ) {
			if (! MW_Auth::getInstance()->getSession()->sudoUserId ) $this->_helper->auth->requirePrivilege(new MW_Auth_Resource_Super('test'));
		}
		set_time_limit(0);
	}
	
	public function aclAction()
	{
		var_dump($this->_helper->auth->getUser()->roles[0]->roles[0]); exit;
	}

	public function iwannabeanadminAction() {
		$user = MW_Auth::getInstance()->getUser();
		$user->removeGroup(MW_Auth_Group_User::getInstance());
		$user->addGroup(MW_Auth_Group_Super::getInstance());
		$user->save();
		exit;
	}

	public function iwannabeanuserAction() {
		$user = MW_Auth::getInstance()->getUser();
		$user->removeGroup(MW_Auth_Group_Super::getInstance());
		$user->addGroup(MW_Auth_Group_User::getInstance());
		$user->save();
		exit;
	}
	
	public function fixUsersAction() {
		$super = MW_Auth_Group_Super::getInstance();
		$userGroup = MW_Auth_Group_User::getInstance();

		foreach(EpicDb_Mongo::db('user')->fetchAll() as $user) {
			if (!$user->user) {
				echo "Warning: /user/".$user->id." is not connected to a user<br/>";
				continue;
			}
			if (!$user->user->isMember($super)) $user->user->addGroup($userGroup);
			$user->user->save();
		} 
		echo "done"; exit;
	}
	
	public function designAwesomeAction() {
		$this->_helper->layout->setLayout('awesome/layout-2c');
		$query = array();
		$sort = array('touched' => -1, '_created' => -1);
		$limit = 30;
		
		$questions = EpicDb_Mongo::db('question')->fetchAll($query, $sort);

		$paginator = Zend_Paginator::factory($questions);
		$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));

		$this->view->questions = $paginator;
		
		
	}
}