<?php

class IndexPage extends CMSWebPageBase
{
    public function __construct()
    {
        //初期管理者のみ
        if (!UserInfoUtil::isDefaultUser()) {
            SOY2PageController::jump("");
        }

        /*
         * アップグレード対象のサイトだけ抽出
         */
        $logic = SOY2LogicContainer::get("logic.admin.Upgrade.UpdateAdminLogic", array(
            "target" => "admin"
        ));

        if (!$logic->hasUpdate()) {
            SOY2PageController::jump("");
        }

        parent::__construct();

        $this->addActionLink("update_link", array(
            "link" => SOY2PageController::createLink("Upgrade.CMS.Complete")
        ));
    }
}
