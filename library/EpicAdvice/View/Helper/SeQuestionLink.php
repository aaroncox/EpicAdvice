<?php
/**
 * EpicAdvice_View_Helper_SeQuestionLink
 *
 * undocumented class
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class EpicAdvice_View_Helper_SeQuestionLink extends MW_View_Helper_HtmlTag
{
	public function seQuestionLink($question) {
		$filter = new MW_Filter_Slug();
		return $this->htmlTag("a", 
			array(
				'class' => 'question-link',
				'href' => '/questions/'.$question->id.'/'.$filter->filter($question->title),
			), 
			$question->title
		);
	}
}