<?php

class OutOfDateEntryListActoin extends SOY2Action{

    private $offset;
	private $limit;


	function setOffset($offset) {
    	$this->offset = $offset;
    }
    function setLimit($limit) {
    	$this->limit = $limit;
    }

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic", array("offset" => $this->offset, "limit" => $this->limit));

		try{
			$list = $logic->getOutOfDateEntryList();
			$this->setAttribute("Entities",$list);
			//合計件数を返す
			$this->setAttribute("total",$logic->getTotalCount());
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;
    }
}
