<?php
/**
 * EpicAdvice_Mongo_Profile_Characters
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_Mongo_Profile_Characters extends Shanty_Mongo_DocumentSet
{
	protected $_requirements = array(
			'$' => array('Document:EpicAdvice_Mongo_Profile_Character', 'AsReference'),
		);
	public function getPrimary() {
		// This isn't working properly, but I wanted it to return the first for now
		foreach($this as $character) {
			return $character;
		}
		return null;
	}
}