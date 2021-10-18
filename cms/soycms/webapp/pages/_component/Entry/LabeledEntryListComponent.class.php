<?php

class LabeledEntryListComponent extends HTMLList
{
    public static $tabIndex = 0;

    private $labelIds;
    private $labelList;

    public function setLabelIds($labelIds)
    {
        $this->labelIds = $labelIds;
    }

    public function setLabelList($list)
    {
        $this->labelList = $list;
    }

    public function populateItem($entity)
    {
        $this->addInput("entry_check", array(
            "type"=>"checkbox",
            "name"=>"entry[]",
            "value"=>$entity->getId()
        ));

        $entity->setTitle(strip_tags($entity->getTitle()));
        $title_link = SOY2HTMLFactory::createInstance("HTMLLink", array(
            "text"=>((strlen($entity->getTitle())==0)?CMSMessageManager::get("SOYCMS_NO_TITLE"):$entity->getTitle()),
            "link"=>SOY2PageController::createLink("Entry.Detail.".$entity->getId()),
            "title"=>$entity->getTitle()
        ));

        $this->add("title", $title_link);

        $status = SOY2HTMLFactory::createInstance("HTMLLabel", array(
            "text" => $entity->getStateMessage()
        ));

        $this->add("status", $status);

        $this->addLabel("content", array(
            "text"  => mb_strimwidth(SOY2HTML::ToText($entity->getContent()), 0, 100, "..."),
            "title" => mb_strimwidth(SOY2HTML::ToText($entity->getContent()), 0, 1000, "..."),
        ));

        $labelId = (isset($this->labelIds[0])) ? (int)$this->labelIds[0] : 0;

        $displayOrder = null;
        if (strpos($_SERVER["REQUEST_URI"], "/Entry/List")) { //ラベル毎の記事一覧ページとコードを統合するための条件分岐
            $displayOrder = $this->logic()->getDisplayOrder($entity->getId(), $labelId);
        } elseif (method_exists($entity, 'getDisplayOrder')) {
            $displayOrder = $entity->getDisplayOrder();
        }

        $this->addLabel("create_date", array(
            "text" => CMSUtil::getRecentDateTimeText($entity->getCdate()),
            "title"=> date("Y-m-d H:i:s", $entity->getCdate())
        ));
//      $this->addLabel("update_date", array(
//          "text" => CMSUtil::getRecentDateTimeText($entity->getUdate()),
//          "title"=> date("Y-m-d H:i:s",$entity->getUdate())
//      ));

        $this->addInput("order", array(
            "type"=>"text",
            "name"=> (isset($labelId)) ? "displayOrder[".$entity->getId()."][". $labelId ."]" : "",
            "value"=> $displayOrder,
            "size"=>"5",
            "tabindex" => self::$tabIndex++
        ));

        //ラベル表示部
        $this->createAdd("label", "_component.Entry.EntryLabelListComponent", array(
            "list" => $this->labelList,
            "entryLabelIds"=>$entity->getLabels(),
        ));
    }

    private function logic()
    {
        static $logic;
        if ((null===$logic)) {
            $logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
        }
        return $logic;
    }
}
