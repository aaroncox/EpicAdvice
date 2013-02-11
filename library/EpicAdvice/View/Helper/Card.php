<?php
/**
 * EpicAdvice_View_Helper_Card
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_View_Helper_Card extends EpicDb_View_Helper_Card
{
	public function card($record, $params = array()) {
		if(!isset($params['class'])) $params['class'] = '';
		if(!isset($params['iconClass'])) $params['iconClass'] = '';
		// If they are a user, lets color them! weeee
		$params['class'] .= ' light-bg ';
		$params['iconClass'] .= $this->color($record->reputation);
		return parent::card($record, $params);
	}
	
	public function color($value) {
		if((int)$value >= 5000) {
			return " bc-legendary bc-shadow";
		}
		if((int)$value >= 2500) {
			return " bc-epic bc-shadow";
		}
		if((int)$value >= 1000) {
			return " bc-rare bc-shadow";
		}
		if((int)$value >= 500) {
			return " bc-uncommon bc-shadow";
		}
		if((int)$value < 0) {
			return " bc-poor";
		}
		return " bc-common";
	}
} // END class EpicAdvice_View_Helper_Card