<?php
/**
 * EpicAdvice_View_Helper_ArmoryLink
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_View_Helper_ArmoryLink extends MW_View_Helper_HtmlTag
{
	public function armoryLink($character) {
		$filter = new MW_Filter_Slug();
		$url = "http://".$character->region.".battle.net/wow/".$character->region."/character/".$filter->filter($character->realm)."/".$character->name."/simple";
		return $this->htmlTag("a", array(
			'href' => $url,
			'rel' => 'no-follow',
		), $character->name." (".$character->realm." - ".$character->region.")");
	}
} // END class EpicAdvice_View_Helper_ArmoryLink