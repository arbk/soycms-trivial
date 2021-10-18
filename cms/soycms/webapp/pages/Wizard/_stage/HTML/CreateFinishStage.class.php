<?php

class CreateFinishStage extends StageBase
{
    public function __construct()
    {
    }

    public function execute()
    {
        parent::__construct();

//      $this->createAdd("preview_link","HTMLLink",array(
//          "link"=>SOY2PageController::createLink("Page.Preview.".$this->wizardObj->pageId)
//      ));

        $this->createAdd("open_and_exit", "HTMLLink", array(
            "link"=>"#",
            "onclick"=>"$('#op_code').val('open');$('#main_form').attr('action',''".SOY2PageController::createLink("Wizard")."?next');$('#main_form').submit();"
        ));

        $this->createAdd("finish", "HTMLLink", array(
            "link"=>"#",
            "onclick"=>"$('#op_code').val('finish');$('#main_form').attr('action','".SOY2PageController::createLink("Wizard")."?next');$('#main_form').submit();"
        ));

        $this->createAdd("move_detail", "HTMLLink", array(
            "link"=>"#",
            "onclick"=>"$('#op_code').val('move_detail');$('#main_form').attr('action','".SOY2PageController::createLink("Wizard")."?next');$('#main_form').submit();"
        ));
    }

    public function checkNext()
    {
        if (isset($_POST["op_code"])) {
            if ($_POST["op_code"] == "open") {
                $result = $this->run("Page.SetOpenAction", array("pageIds"=>array($this->wizardObj->pageId)));
                if ($result->success()) {
                    $this->addMessage("WIZARD_PAGE_OPEN_SUCCCESS");
                    return true;
                } else {
                    $this->addErrorMessage("WIZARD_PAGE_OPEN_FAILED");
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function checkBack()
    {
        return true;
    }

    public function getNextObject()
    {
        if (isset($_POST["op_code"])) {
            switch ($_POST["op_code"]) {
                case "open":
                    return "HTML.ConfirmStage";
                case "move_detail":
                    $this->wizardObj->end_redirect_address = "Page.Detail.".$this->wizardObj->pageId;
                    return "EndStage";
                case "finish":
                    return "EndStage";
            }
        }
        return "EndStage";
    }

    public function getBackObject()
    {
        return "HTML.PageConfigStage";
    }

    public function getNextString()
    {
        return "";
    }

    public function getBackString()
    {
        return "";
    }
}
