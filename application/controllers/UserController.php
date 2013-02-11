<?php
/**
 *  UserController
 *
 * undocumented
 *
 * @author Corey Frang
 */
class UserController extends MW_Auth_Controller_User_Mongo {
	protected function _getOpenIDRoot()
	{
		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
			$scheme .= 's';
		}

		return sprintf("%s://%s%s",
			$scheme, $_SERVER['SERVER_NAME'], '/users/authenticate/');
	}

	public function openidAction()
	{
		parent::openIdAction();
		$this->_forward('login');
	}
	
	public function indexAction() {
		$paginator = Zend_Paginator::factory(EpicDb_Mongo::db('user')->fetchAll(array(), array('reputation' => -1)));
		$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1))
							->setItemCountPerPage(21);
		$this->view->profiles = $paginator;
	}
	
	public function registerAction() {		
		$form = $this->view->form = new EpicDb_Auth_Form_Register();
		if($this->getRequest()->isPost()) {
			if($profile = $form->process($this->getRequest()->getParams())) {
				$this->_redirect($this->view->url(array(
					'profile'=>EpicDb_Auth::getInstance()->getUserProfile(),
				), 'profile', true));
			}
		}
		if(MW_Auth::getInstance()->getUser()) {
			$this->_redirect("/user/associate");
		}
	}
	
	public function associateAction() {
		
	}
}