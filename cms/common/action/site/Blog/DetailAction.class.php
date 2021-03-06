<?php

class DetailAction extends SOY2Action
{
    public $id;

    public function setId($id)
    {
        $this->id = $id;
    }

    protected function execute(SOY2ActionRequest &$request, SOY2ActionForm &$form, SOY2ActionResponse &$response)
    {
        $dao = SOY2DAOFactory::create("cms.BlogPageDAO");
        try {
            $page = $dao->getById($this->id);
        } catch (Exception $e) {
            return SOY2Action::FAILED;
        }

        // 無限遠時刻をnullになおす
        $page->setOpenPeriodEnd(CMSUtil::decodeDate($page->getOpenPeriodEnd()));
        $page->setOpenPeriodStart(CMSUtil::decodeDate($page->getOpenPeriodStart()));

        $this->setAttribute("Page", $page);

        return SOY2Action::SUCCESS;
    }
}
