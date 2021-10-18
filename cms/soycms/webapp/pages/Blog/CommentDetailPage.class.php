<?php

class CommentDetailPage extends CMSWebPageBase
{
    public function doPost()
    {
        if (soy2_check_token()) {
            $result = $this->run("EntryComment.CommentUpdateAction", array(
            "commentId"=>$this->id
            ));
        }

        $this->jump("Blog.CommentDetail." . $this->id);
    }

    protected $id;

    public function __construct($arg)
    {
        $commentId = @$arg[0];
        $this->id = $commentId;

        //記事公開管理者権限が必要
        if (!UserInfoUtil::hasEntryPublisherRole()) {
            echo CMSMessageManager::get("SOYCMS_ERROR");
            exit;
        }

        $result = $this->run("EntryComment.CommentDetailAction", array("commentId"=>$commentId));

        if (!$result->success()) {
            echo CMSMessageManager::get("SOYCMS_ERROR");
            exit();
        }

        parent::__construct();
        $comment = $result->getAttribute("entity");

        $result = $this->run("Entry.EntryDetailAction", array("id"=>$comment->getEntryId()));

        if (!$result->success()) {
            echo CMSMessageManager::get("SOYCMS_ERROR");
            exit();
        }

        $entry = $result->getAttribute("Entry");
        $title = $comment->getTitle();

        $author = $comment->getAuthor();

        if (strlen($comment->getMailAddress()) != 0) {
            $author .= "(" . $comment->getMailAddress() . ")";
        }

        if (strlen($title) == 0) {
            $title = CMSMessageManager::get("SOYCMS_NO_TITLE");
        }

        $this->createAdd("title", "HTMLLabel", array(
            "text"=>$title
        ));
          $this->addForm("title_form");
          $this->addInput("title_edit", array(
            "name" => "title",
            "value" => $title
          ));

        $this->createAdd("author", "HTMLLabel", array(
            "text"=>$author
        ));
        $this->addForm("author_form");
        $this->addInput("author_edit", array(
            "name" => "author",
            "value" => $author
        ));

        $this->createAdd("entry_title", "HTMLLabel", array(
            "text"=>$entry->getTitle()
        ));

        $this->createAdd("submit_date", "HTMLLabel", array(
            "text"=>date("Y-m-d H:i:s", $comment->getSubmitDate())
        ));

        $this->createAdd("state", "HTMLLabel", array(
            "text"=>($comment->getIsApproved() == 0) ? CMSMessageManager::get("SOYCMS_DENY") : CMSMessageManager::get("SOYCMS_ALLOW")
        ));

        $this->createAdd("content", "HTMLLabel", array(
            "text"=>$comment->getBody()
        ));

        $this->createAdd("comment_form", "HTMLForm");
        $this->createAdd("content_edit", "HTMLTextArea", array(
            "text"=>$comment->getBody(),
            "name"=>"content"
        ));

        $extraValues = "";
        $extraValuesArray = $comment->getExtraValuesArray();
        foreach ($extraValuesArray as $k => $v) {
            $extraValues = $extraValues . $k . ": " . $v . "\n";
        }
        $this->createAdd("extra_values", "HTMLLabel", array(
            "text"=>$extraValues
        ));
    }
}
