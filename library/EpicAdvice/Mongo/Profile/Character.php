<?php
/**
 * EpicAdvice_Mongo_Profile_Character
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_Mongo_Profile_Character extends EpicAdvice_Mongo_Profile
{
  protected static $_documentType = 'character';

	/**
	 * __construct - undocumented function
	 *
	 * @return void
	 * @author Aaron Cox <aaronc@fmanet.org>
	 **/
	public function __construct($data = array(), $config = array())
	{
		$this->addRequirements(array(
			'tags' => array('DocumentSet:EpicDb_Mongo_Tags', 'Required'),
		));
		return parent::__construct($data, $config);
	}

} // END class EpicAdvice_Mongo_Profile_Character