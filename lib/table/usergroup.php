<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class usergroup extends table {

    static $me;

    public static function getInstance()
    {
        if (!self::$me) {
            $class = new usergroup();
            $class->init();
            self::$me = $class;
        }
        return self::$me;
    }

	function getcols($act) {
		return $this->getcolslist();
	}
	function init() {
        $this->group = $this->getrows(null, 99, '1');
        $name = array();
        foreach ($this->group as $g) {
            if(front::$user['groupid'] == 2 || $g['groupid'] > front::$user['groupid']) {
                $name[$g['groupid']] = $g['name'];
            }
        }
        $this->names = $name;
	}
	/*public static function getInstance() {
		$class=new usergroup();
		$class->init();
		return $class;
	}*/
	static function name($groupid) {
		static $names;
		if (!isset($name)) {
			$group=usergroup::getInstance();
			$name=$group->names;
		}
		if (isset($name[$groupid]))
			return $name[$groupid];
		else
			return false;
	}
	static function option() {
		$group=usergroup::getInstance();
		return $group->names;
	}
	
	public static function getRoles($groupid){
		$group = usergroup::getInstance();
		$groups = $group->getrow("groupid='$groupid'");
		if($groups['powerlist'] != ''){
			return unserialize($groups['powerlist']);
		}
	}
}