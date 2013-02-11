<?php
/**
 * WikiController
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class WikiController extends EpicDb_Controller_Action
{
	protected $_wiki = null;
	protected $_record = null;
	public function getWiki() {
		if($id = $this->getRequest()->getParam("record")) {
			$record = $this->_record = EpicDb_Mongo_Record::find(new MongoId($id));
			if(!$type = $this->getRequest()->getParam("type")) {
				$type = "new";
			}
			return $this->view->wiki = $this->_wiki = EpicDb_Mongo_Wiki::get($record, $type);
		} else {
			return new EpicDb_Mongo_Wiki();
		}
	}
	public function indexAction() {
		if( MW_Auth::getInstance()->getUser() ) {
			if( MW_Auth::getInstance()->getUser()->isMember( MW_Auth_Group_Super::getInstance() ) ) {		
				$wiki = $this->getWiki();
				if($type = $this->getRequest()->getParam('type')) {
					$form = $this->view->form = new EpicDb_Form_Wiki(array("wiki" => $wiki, "record" => $this->_record, "type" => $type));													
				} else {
					$form = $this->view->form = new EpicDb_Form_Wiki(array("wiki" => $wiki, "record" => $this->_record));								
				}
				$this->_handleMWForm($form, 'wiki');			
			} else {
				return $this->_redirect("/user/login");
			}
		}
	}
} // END class WikiController