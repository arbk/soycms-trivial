<?php

class CreateNewLabelPage extends CMSWebPageBase
{
    public function __construct()
    {
        $result = $this->run("Label.LabelCreateAction");

        if ($result->success()) {
            echo json_encode(
                array(
                    "result"=>1,
                    "message"=>CMSMessageManager::get("LABEL_CREATE_SUCCESS"),
                    "labelId"=>$result->getAttribute("id")
                )
            );
        } else {
            echo json_encode(
                array(
                    "result"=>0,
                    "message"=>CMSMessageManager::get("LABEL_CREATE_FAILED")
                )
            );
        }

        exit;
    }
}
