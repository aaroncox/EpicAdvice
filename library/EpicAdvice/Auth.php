<?php
/**
 *
 *
 * @author Corey Frang
 * @package EpicAdvice_Auth
 * @copyright Copyright (c) 2010 Momentum Workshop, Inc
 */

/**
 *  EpicAdvice_Auth
 *
 * undocumented
 *
 * @author Corey Frang
 * @package EpicAdvice_Auth
 * @copyright Copyright (c) 2010 Momentum Workshop, Inc
 * @version $Id: Auth.php 701 2011-03-25 00:37:52Z root $
 */
class EpicAdvice_Auth extends EpicDb_Auth {

	/**
	 * private constructor - singleton pattern
	 *
	 * @return void
	 * @author Corey Frang
	 **/
	protected function __construct()
	{
	}

	/**
	 * Returns (or creates) the Instance - Singleton Pattern
	 *
	 * @return self
	 * @author Corey Frang
	 **/
	static public function getInstance()
	{
		if (self::$_instance === NULL) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * getUserRoles - undocumented function
	 *
	 * @return array of roles
	 * @author Aaron Cox <aaronc@fmanet.org>
	 **/
	public function getUserRoles(MW_Auth_Mongo_Role $role = null)
	{
		if (!$role) {
			if (!$role = $this->getUser()) {
				$role = MW_Auth_Mongo_Role::getGroup(MW_Auth_Group_Guest::getInstance());
			}
		}
		$return = array($role);
		// var_dump($role);
		foreach ($role->roles as $newRole)
		{
			if ($newRole) {
				// var_dump($newRole->groupName, in_array($newRole, $return));
				$roles = $this->getUserRoles($newRole, $return);
				$return = array_merge($return, $roles);
			}
		}

		return $return;
	}
}