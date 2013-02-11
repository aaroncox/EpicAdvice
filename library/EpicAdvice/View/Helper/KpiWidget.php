<?php
/**
 * EpicAdvice_View_Helper_KpiWidget
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_View_Helper_KpiWidget extends EpicDb_View_Helper_KpiWidget
{
	public function kpiWidget($value, $params = array()) {
		$params += array("class" => "inline-flow rounded center-shadow");
		return parent::kpiWidget($value, $params);
	}
} // END class EpicAdvice_View_Helper_KpiWidget