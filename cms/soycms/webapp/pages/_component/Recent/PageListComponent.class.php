<?php

class PageListComponent extends HTMLList
{
    public function populateItem($entity)
    {
        $this->addLink("title", array(
            "text"=>(strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle(),
            "link"=>SOY2PageController::createLink("Page.Detail.") . $entity->getId()
        ));

        $this->addLink("content", array(
            "text" => "/" . $entity->getUri(),
            "link" => CMSUtil::getSiteUrl() . $entity->getUri(),
            "target" => "_blank",
            "rel" => "noopener"
        ));

        $this->addLabel("udate", array(
            "text"=>CMSUtil::getRecentDateTimeText($entity->getUdate()),
            "title" => date("Y-m-d H:i:s", $entity->getUdate())
        ));
    }
}
