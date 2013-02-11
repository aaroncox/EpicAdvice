<?php
/**
 * 
 *
 * @author Corey Frang
 * @package R2Db_View
 * @subpackage Helper
 * @copyright Copyright (c) 2010 Momentum Workshop, Inc
 */

/**
 *  R2Db_View_Helper_HtmlFragment
 *
 * undocumented
 *
 * @author Corey Frang
 * @package R2Db_View
 * @subpackage Helper
 * @copyright Copyright (c) 2010 Momentum Workshop, Inc
 * @version $Id: HtmlFragment.php 309 2010-11-17 04:03:51Z root $
 */
class EpicAdvice_View_Helper_HtmlFragment extends Zend_View_Helper_Abstract {
  
  protected $_filter;
  public function __construct()
  {
    $this->_filter = new Zend_Filter_StripTags();
  }
  
  public function htmlFragment($html, $maxLength = 100)
  {
    $text = trim(preg_replace("/\s+/", ' ', $this->_filter->filter($html)));
    if (strlen($text) > $maxLength) return substr($text, 0, $maxLength).'...';
    return $text;
  }
}