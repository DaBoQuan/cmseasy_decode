<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class attachment extends table
{
    public $name = 'a_attachment';

    function del($id)
    {
        $attach = $this->getrow($id);
        if (is_array($attach) && $attach['path'])
            @unlink(ROOT . '/' . $attach['path']);
        $this->rec_delete($id);
    }
}