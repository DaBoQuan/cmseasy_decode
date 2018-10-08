<?php 

class attr1{
	public static $var=array();

    function name($id) {
    	$name = '';
    	$attr1 = settings::getInstance()->getrow(array('tag'=>'table-archive'));
    	if ($attr1['value'])
            self::$var = @unserialize($attr1['value']);
        else
            self::$var = array();
        preg_match_all('/\(([\d\w]+)\)(\S+)/is',self::$var['attr1'],$result,PREG_SET_ORDER);
        $id_arr = explode(',',$id);
        foreach($result as $v){
       	    foreach($id_arr as $t_v){
       	    	if(in_array($t_v,$v))
       	    	   $name .= $v[2].' / ';
       	    }
        }
        return $name;
    }
}
