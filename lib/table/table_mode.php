<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_mode {
    function vaild() {
        return true;
    }
    function filter($Exc=true) {
        foreach(front::$post as $key =>$value) {
            if(is_string($value))
                front::$post[$key]=tool::filterXss($Exc ?$value : tool::removehtml($value));
        }
    }
    function add_before(act $act=null) {
    }
    function add_after() {
    }
    function edit_before() {
    }
    function save_before() {
        foreach(front::$post as $k=>$p) {
            if(preg_match('/^my_/',$k) &&is_array($p))  front::$post[$k]=implode(',',$p);
            if(preg_match('/^attr1/',$k))  front::$post[$k]=implode(',',$p);
        }
    }
    function save_after($aid='') {
    }
    function view_before(&$data=null) {
    }
    function delete_before($id='') {
    }
    function delete_after() {
    }
}