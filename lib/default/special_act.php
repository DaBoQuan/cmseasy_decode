<?php 

class special_act extends act
{
    function list_action()
    {
        $this->view->page = intval(front::get('page')) ? intval(front::get('page')) : 1;
        $this->pagesize = intval(config::get('list_pagesize'));
        $limit = (($this->view->page - 1) * $this->pagesize) . ',' . $this->pagesize;
        $special = new special();
        $specials = $special->getrows('', $limit, 'listorder,spid desc');
        foreach ($specials as $order => $sp) {
            $specials[$order]['url'] = special::url($sp['spid']);
        }
        $this->view->specials = $specials;
        $this->view->record_count = $special->rec_count('');
        front::$record_count = $this->view->record_count;
        $this->view->page = true;
    }

    function show_action()
    {
        $this->view->page = intval(front::get('page')) ? intval(front::get('page')) : 1;
        $this->pagesize = intval(config::get('list_pagesize'));
        $limit = (($this->view->page - 1) * $this->pagesize) . ',' . $this->pagesize;
        $special = new special();
        $spid = intval(front::get('spid'));
        $this->view->special = $special->getrow(array('spid'=>$spid));
        $this->view->archive['title'] = $this->view->special['title'];
        $this->view->pages = true;
        $archive = new archive();
        $archives = $archive->getrows(array('spid'=>$spid,'checked'=>1), $limit);
        foreach ($archives as $order => $arc) {
            $archives[$order]['url'] = archive::url($arc);
            $archives[$order]['catname'] = category::name($arc['catid']);
            $archives[$order]['caturl'] = category::url($arc['catid']);
            $archives[$order]['adddate'] = sdate($arc['adddate']);
            $archives[$order]['stitle'] = strip_tags($arc['title']);
        }
        $tpl = $this->view->special['template'];
        $this->view->archives = $archives;
        $this->view->record_count = $archive->rec_count(array('spid'=>$spid));
        front::$record_count = $this->view->record_count;
        $this->view->spid = front::get('spid');
        if ($tpl) {
            $this->out($tpl);
        }
    }

    function out($tpl)
    {
        if (front::$debug) return;
        $this->render($tpl);
        $this->out = true;
        exit;
    }

    function end()
    {
        $this->render();
    }
}