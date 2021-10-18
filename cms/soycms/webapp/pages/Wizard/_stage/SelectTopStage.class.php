<?php
SOY2::import("domain.cms.Page");

class SelectTopStage extends StageBase
{
    public function __construct()
    {
    }

    public function execute()
    {
        parent::__construct();
        $this->createAdd("page_type", "HTMLSelect", array(
        "options"=>array(
            Page::PAGE_TYPE_NORMAL=>"標準ページ",
            Page::PAGE_TYPE_BLOG=>"ブログページ",
//          Page::PAGE_TYPE_MOBILE => "携帯用ページ",
        ),
        "indexOrder"=>true,
        "name"=>"page_type"
        ));
    }

    public function checkNext()
    {
        if (!isset($_POST["page_type"])) {
            return false;
        } else {
            if ($_POST["page_type"] != @$this->wizardObj->pageType) {
              // ページの種類が変わった場合はデータをクリア
                $this->wizardObj = new StdClass();
            }
            $this->wizardObj->pageType = $_POST["page_type"];
            return true;
        }
    }

    public function checkBack()
    {
        return true;
    }

    public function getNextObject()
    {
        switch (@$_POST["page_type"]) {
            case Page::PAGE_TYPE_NORMAL:
//          case Page::PAGE_TYPE_MOBILE:
                return "HTML.TemplateSelectStage";
            case Page::PAGE_TYPE_BLOG:
                return "BLOG.TemplateSelectStage";
        }
        return "EndStage";
    }

    public function getBackObject()
    {
        return "StartStage";
    }
}
