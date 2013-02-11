<?php 
/**
 * EpicAdvice_View_Helper_Context
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_View_Helper_Context extends EpicDb_View_Helper_Context
{
	public function insertAd($text) {
		// if(APPLICATION_ENV == 'production') {
			$array = $this->getArrayCopy();
			array_splice($array, 2, 0, $text);
			$this->exchangeArray($array);
		// }
		return $this;
	}
} // END class EpicAdvice_View_Helper_Context