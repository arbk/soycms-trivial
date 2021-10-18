<?php

class CreateStage extends StageBase
{
    public function __construct()
    {
        SOY2::import("domain.cms.Page");
    }

    public function execute()
    {
        parent::__construct();

        $this->createAdd("page_type", "HTMLLabel", array(
            "text"=> ($this->wizardObj->pageType == Page::PAGE_TYPE_NORMAL) ? "標準ページ" : "携帯用ページ"
        ));


        $this->createAdd("page_name", "HTMLLabel", array(
            "text"=>(strlen($this->wizardObj->name))? $this->wizardObj->name : "[無題]"
        ));

        $this->createAdd("page_url", "HTMLLabel", array(
            "text"=>UserInfoUtil::getSiteUrl().$this->wizardObj->url
        ));

        try {
            $template = $this->run("Template.TemplateDetailAction", array("id"=>$this->wizardObj->template_id))->getAttribute("entity");
        } catch (Exception $e) {
            $template = new Template();
        }

        $this->createAdd("template_name", "HTMLLabel", array(
            "text"=>$template->getName()
        ));
    }

    public function checkNext()
    {
        if ((null===@$this->wizardObj->pageType)) {
            return false;
        }

        SOY2::import("domain.cms.Page");
        $page = new Page();
        $page->setTitle($this->wizardObj->name);
        $page->setUri($this->wizardObj->url);
        $page->setPageType($this->wizardObj->pageType);
        $page->setTemplate(@$this->wizardObj->template_id);

        $logic = SOY2Logic::createInstance("logic.site.Page.CreatePageLogic");
        try {
            $id = $logic->create($page);
            $this->addMessage("WIZARD_CREATE_PAGE_SUCCESS");
        } catch (Exception $e) {
            $this->addErrorMessage("WIZARD_CREATE_PAGE_FAILED");
            return false;
        }

        $this->wizardObj = new StdClass();
        $this->wizardObj->pageId = $id;

        return true;
    }

    public function checkBack()
    {
        return true;
    }

    public function getNextObject()
    {
        return "HTML.CreateFinishStage";
    }

    public function getBackObject()
    {
        return "HTML.PageConfigStage";
    }
}
