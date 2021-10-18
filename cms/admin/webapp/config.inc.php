<?php
/**
 * PageController
 * SOY2PageControllerを使う前にinitする必要がある
 */
SOY2::import("base.CMSAdminPageController");
SOY2PageController::init("CMSAdminPageController");

/*
 * 管理画面の共通設定
 */
SOY2HTMLConfig::CacheDir(dirname(dirname(__FILE__)) . "/cache/");
SOY2DAOConfig::DaoCacheDir(dirname(dirname(__FILE__)) . "/cache/");

//必須コンポーネントのimport
SOY2::import("base.CMSWebPageBase");
SOY2::import("base.CMSFormBase");
SOY2::import("base.MessagePlugin");
SOY2::import("domain.admin.Site");
SOY2::import("domain.cms.SiteConfig");
SOY2::import("util.CMSToolBox");
SOY2::import("util.CMSMessageManager");
SOY2::import("util.CMSPlugin");
SOY2::import("util.CMSUtil");
SOY2::import("util.ServerInfoUtil");
SOY2::import("util.UserInfoUtil");
SOY2::import("util.SOYShopUtil");


//メッセージのディレクトリ
CMSMessageManager::addMessageDirectoryPath(CMS_SOYBOY_MESSAGE_DIR);
CMSMessageManager::addMessageDirectoryPath(CMS_HELP_MESSAGE_DIR);
CMSMessageManager::addMessageDirectoryPath(CMS_CONTROLPANEL_MESSAGE_DIR);

if(defined("SOYCMS_ASP_MODE")){

}else{
	//SOY2DAOの設定
	SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
	SOY2DAOConfig::user(ADMIN_DB_USER);
	SOY2DAOConfig::pass(ADMIN_DB_PASS);

	//DBの初期化処理
	if(ADMIN_DB_EXISTS != true){
		SOY2PageController::redirect("./init.php");
		exit;
	}
}

//ログインチェック
if(!UserInfoUtil::isLoggined()){
	SOY2ActionConfig::ActionDir(SOY2ActionConfig::ActionDir() . "login/");
	SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/Login/");
}else{
	SOY2ActionConfig::ActionDir(SOY2ActionConfig::ActionDir() . "admin/");
	SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");

	//初期管理者とそれ以外で表示を変える
	DisplayPlugin::toggle("for_default_user", UserInfoUtil::isDefaultUser());
	DisplayPlugin::toggle("for_not_default_user", !UserInfoUtil::isDefaultUser());
}

//デフォルトでは切り替えのリンクは隠しておく（必要に応じてconfig.ext.phpで表示を切り替える）
DisplayPlugin::hide("ext_mode_link");

//お知らせ配信のFeedのURL
define("SOYCMS_INFO_FEED", "http://www.soycms.net/info/feed/");

//多言語テンプレート
define("SOYCMS_LANGUAGE_DIR", str_replace("\\", "/", dirname(__FILE__) . "/language/"));
