<?php
/**
 * EpicAdvice_Mongo_Profile_User
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_Mongo_Profile_User extends EpicDb_Mongo_Profile_User
{	
	/**
	 * __construct - undocumented function
	 *
	 * @return void
	 * @author Aaron Cox <aaronc@fmanet.org>
	 **/
	public function __construct($data = array(), $config = array())
	{
		$this->addRequirements(array(
			'characters' => array("DocumentSet:EpicAdvice_Mongo_Profile_Characters", "required"),
		));
		return parent::__construct($data, $config);
	}
	
	public function cardProperties($view) {
		$character = $this->characters->getPrimary();
		if(!$character) return array();
		return array(
			'plays on' => $character->realm,
			'in the' => strtoupper($character->region),
		);
	}
} // END class EpicAdvice_Mongo_Profile_User