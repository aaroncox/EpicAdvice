<?php
/**
 *  IndexController
 */
class IndexController extends EpicDb_Controller_Action {

	public function indexAction()
	{
		$this->_redirect("/questions");
	}
	public function oldAction()
	{
		// This is an HTML rip of our StackExchange site for testing links/etc
	}
	public function badgesAction() {}

	public function sitemapAction()
	{
		$this->_helper->layout->disableLayout();
		$this->getResponse()->setHeader('Content-type', 'text/plain');
		$this->_helper->viewRenderer->setNoRender(true);
		
		echo "<?xml version='1.0' encoding='UTF-8'?>\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$query = array(
			'votes.score' => array('$gte' => -2)
		);
		$questions = EpicDb_Mongo::db('question')->fetchAll($query,array('id' => -1));
		$slug = new MW_Filter_Slug();
		
		foreach ($questions as $question) {
			//<url>
			//<loc>http://epicadvice.com/questions/8205/1-battle-net-account-2-player-2-games</loc>
			//<lastmod>2011-04-09</lastmod>
			//<changefreq>monthly</changefreq>
			//<priority>0.2</priority>
			//</url>
			echo "\n<url><loc>";
			echo "http://".$_SERVER["HTTP_HOST"].$this->view->escape($this->view->url(array(
				'id' => $question->id,
				'slug' => $slug->filter($question->title),
			),'se_questions',true));
			echo "</loc>"; 
			echo "<lastmod>".date('Y-m-d', $question->touched ?: $question->_created )."</lastmod>";
			echo "<changefreq>monthly</changefreq>";
			echo "<priority>".min(1,(floor($question->votes['score'] / 2.5) / 10.0) + 0.1)."</priority>";
			echo "</url>";
		}
		echo "</urlset>";
	}
	
	public function faqAction() {

	}
	
	public function aboutAction() {

	}
}