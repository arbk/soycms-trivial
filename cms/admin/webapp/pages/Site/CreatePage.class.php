<?php
SOY2::import("domain.cms.SiteConfig");

class CreatePage extends CMSUpdatePageBase
{
    public function doPost()
    {
        if (soy2_check_token()) {
            if ($this->createSite()) {
                $this->addMessage("CREATE_SUCCESS");
                $this->jump("Site");
            } else {
                $this->addErrorMessage("CREATE_FAILED");
                $errors = CMSMessageManager::getErrorMessages();
                $this->jump("Site.Create");
            }
        }
    }

    public function __construct()
    {
        if (!UserInfoUtil::isDefaultUser()) {
            //デフォルトユーザのみ作成可能
            $this->jump("Site");
            exit;
        }

        parent::__construct();

        if (false == $this->checkTargetDirectoryWritable()) {
            $this->addErrorMessage("TARGET_DIRECTORY_NOT_WRITABLE");
        }

        $this->addForm("create_site_form");

        //文字コードの追加
        $this->addSelect("encoding", array(
            "options" => $this->getEncordingList(),
            "name" => "encoding"
        ));

        // //サイトのコピー機能（既存サイトのデータを渡す） 初期値は「コピーしない」
        // $this->addSelect("copy_from", array(
        //     "options" => $this->getSiteList(),
        //     "name" => "copyFrom",
        //     "selected" =>"none"
        // ));

        $siteList = SOY2Logic::createInstance("logic.admin.Site.SiteLogic")->getSiteList();

        if (count($siteList) != 0 || SOYCMS_DB_TYPE != "mysql") {
            DisplayPlugin::hide("only_first_site");
        }

        $this->addCheckBox("separate", array(
            "value"=>"0",
            "name"=>"separate",
            "label"=>$this->getMessage("ADMIN_MAKE_WEBSITE_IN_ADMIN_DB")
        ));

        $messages = CMSMessageManager::getMessages();
        $messages_visible = (count($messages) > 0);
        $this->addLabel("message", array(
            "text" => implode($messages),
            "visible" => $messages_visible,
        ));
        $errors = CMSMessageManager::getErrorMessages();
        $errors_visible = (count($errors) > 0);
        $this->addLabel("error", array(
            "text" => implode($errors),
            "visible" => $errors_visible,
        ));
        $this->addModel("has_message_or_error", array(
            "visible" => $messages_visible || $errors_visible,
        ));
    }

    /**
     * 文字コードの種類を取得する
     */
    public function getEncordingList()
    {
        return SiteConfig::getCharsetLists();
    }

    /**
     * サイトを作成します
     * @return boolean
     */
    public function createSite()
    {
        $action = SOY2ActionFactory::createInstance("Site.CreateAction");
        $result = $action->run();

        if ($result->success()) {
            $site = $result->getAttribute("Site");

//          SOY2::import("util.CMSFileManager");
//          CMSFileManager::insertAll($site->getPath());
        } else {
            //
        }

        return $result->success();
    }

    /**
     * サイトの書き込み権限をチェックする
     */
    public function checkTargetDirectoryWritable()
    {
        $targetDir = SOYCMS_TARGET_DIRECTORY;
        return (is_writable($targetDir));
    }

    //  /**
    //  * コピーのもととなるサイトを指定するため、サイト一覧を取得する。
    //  */
    // public function getSiteList()
    // {
    //     $sites = $this->getLoginableSiteList();
    //     $siteList = array();
    //     $siteList["none"] = "コピーせず新規作成";
    //     foreach ($sites as $site) {
    //         $siteList[$site->getId()] = $site->getSiteName();
    //     }
    //     return $siteList;
    // }

    //  /**
    //  * 現在のユーザIDからログイン可能なサイトオブジェクトのリストを取得する
    //  */
    // public function getLoginableSiteList()
    // {
    //     $SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
    //     return $SiteLogic->getSiteOnly();
    // }
}
