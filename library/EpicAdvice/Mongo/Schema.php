<?php
/**
 * EpicDb_Mongo_Schema
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_Mongo_Schema extends EpicDb_Mongo_Schema {
	protected $_version = 0;
  protected $_tag = 'epicadvice';
	protected $_classMap = array(
		// Profile Types
		'profile' => 'EpicDb_Mongo_Profile',
		'user' => 'EpicDb_Mongo_Profile_User',
		'group' => 'EpicDb_Mongo_Profile_Group',
		'website' => 'EpicDb_Mongo_Profile_Group_Website',
		'guild' => 'EpicDb_Mongo_Profile_Group_Guild',
		'invitation' => 'EpicDb_Mongo_Profile_Group_Invitation',
		'character' => 'EpicAdvice_Mongo_Profile_Character',
		'characters' => 'EpicAdvice_Mongo_Profile_Characters',
		// Record Types
		'tag' => 'EpicDb_Mongo_Record_Tag',
		'record' => 'EpicDb_Mongo_Record',
		'resource' => 'EpicDb_Mongo_Record_Resource',
		// Post Types
		'post' => 'EpicAdvice_Mongo_Post',
		'question' => 'EpicDb_Mongo_Post_Question',
		'question-comment' => 'EpicDb_Mongo_Post_Question_Comment',
		'seed' => 'EpicDb_Mongo_Seed',
		'answer' => 'EpicDb_Mongo_Post_Question_Answer',
		'comment' => 'EpicDb_Mongo_Post_Comment',
		'article-rss' => 'EpicDb_Mongo_Post_Article_RSS',
		'wiki' => 'EpicDb_Mongo_Wiki',
		'message' => 'EpicDb_Mongo_Post_Message',
		// votes
		'vote' => 'EpicDb_Mongo_Vote',
		'follows-cache' => 'EpicDb_Mongo_Cache_Followers',
		'metaKeys' => 'EpicDb_Mongo_MetaKeys',
	);
	
	public function check()
	{
		$parent = new EpicDb_Mongo_Schema();
		$parent->check($this);
		return parent::check();
	}
	
	public function updateFrom($version)
  {
    $db = self::getMongoDb();
    switch($version) {
				//       case 0:
				// $this->getCollectionForType('profile')->remove(array('_type' => array('$exists' => false)));
				
    }
	}
}