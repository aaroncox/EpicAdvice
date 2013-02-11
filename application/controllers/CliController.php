<?php
/**
 * CliController
 *
 * `
 * 
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class CliController extends EpicDb_Controller_Cli
{
	public function indexAction() {
		var_dump(EpicDb_Mongo::db('question')->fetchAll(array(), array("_created" => -1), 1)->export());
		exit;
	}
} // END class CliController