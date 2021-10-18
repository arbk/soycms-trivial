<?php

class SOYAppUtil
{
    public static function checkAppAuth($appId = "inquiry")
    {
        $auth = false;
        $useSiteDb = false;

        if ($appId == "inquiry") {
            $useSiteDb = SOYINQUIRY_USE_SITE_DB;
        } else {
            $useSiteDb = SOYMAIL_USE_SITE_DB;
        }

        if ($useSiteDb) {
            $session = SOY2ActionSession::getUserSession();

          // ルート権限の場合、サイト側のデータベースの定数がtrueだったら絶対にtrue
            if ($session->getAttribute("isdefault")) {
                return true;
            }

            $userId = $session->getAttribute("userid");

            $old = self::switchAdminMode();

            $appDao = SOY2DAOFactory::create("admin.AppRoleDAO");
            try {
                $appRoles = $appDao->getByUserId($userId);
            } catch (Exception $e) {
                $appRoles = array();
            }

            self::resetAdminMode($old);

          // SOY Appで設定されている権限を調べる
            if (isset($appRoles[$appId]) && $appRoles[$appId]->getAppRole() > 0) {
                $auth = true;
            }
        }

        return $auth;
    }

    public static function createAppLink($appId = "inquiry")
    {
      // index.phpがある場合はindex.phpの二つ前のディレクトリまで戻る
        if (strpos($_SERVER["REQUEST_URI"], F_FRCTRLER) !==false) {
            $adminPath = substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], "/" . F_FRCTRLER));
        } else {
            $adminPath = $_SERVER["REQUEST_URI"];
        }

        $root = dirname($adminPath);
        if ($root== "/") {
            //dirname("/") => "/"となり、"//app/..."となってしまうので別扱い
            return "/app/" . F_FRCTRLER. "/" . $appId;
        } else {
            return $root . "/app/" . F_FRCTRLER. "/" . $appId;
        }
    }

    private static function switchAdminMode()
    {
        $old = array();

        $old["dsn"] = SOY2DAOConfig::Dsn();
        $old["user"] = SOY2DAOConfig::user();
        $old["pass"] = SOY2DAOConfig::pass();

        SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
        SOY2DAOConfig::user(ADMIN_DB_USER);
        SOY2DAOConfig::pass(ADMIN_DB_PASS);

        return $old;
    }

    private static function resetAdminMode($old)
    {

        SOY2DAOConfig::Dsn($old["dsn"]);
        SOY2DAOConfig::user($old["user"]);
        SOY2DAOConfig::pass($old["pass"]);
    }

    public static function switchAppMode($appId = "inquiry")
    {
        $old = array();

        $old["root"] = SOY2::RootDir();
        $old["dao"] = SOY2DAOConfig::DaoDir();
        $old["entity"] = SOY2DAOConfig::EntityDir();
        $old["dsn"] = SOY2DAOConfig::Dsn();
        $old["user"] = SOY2DAOConfig::user();
        $old["pass"] = SOY2DAOConfig::pass();

        //公開側でも使用できるように
        if (!defined("SOYCMS_COMMON_DIR")) {
            define("SOYCMS_COMMON_DIR", SOY2::RootDir());
        }

        SOY2::RootDir(dirname(SOYCMS_COMMON_DIR) . "/app/webapp/" . $appId . "/src/");
        SOY2DAOConfig::DaoDir(SOY2::RootDir() . "domain/");
        SOY2DAOConfig::EntityDir(SOY2::RootDir() . "domain/");

        if (SOYCMS_DB_TYPE == "sqlite") {
          // SOYMailはdbファイル名がappIdと異なるから修正
            if ($appId == "mail") {
                $appId = "soymail";
            }

            SOY2DAOConfig::Dsn("sqlite:" . SOYCMS_COMMON_DIR . "db/" . $appId . ".db");
        } else {
          // MySQLの場合は管理側のDB
            SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
            SOY2DAOConfig::user(ADMIN_DB_USER);
            SOY2DAOConfig::pass(ADMIN_DB_PASS);
        }

        return $old;
    }

    public static function resetAppMode($old)
    {
        SOY2::RootDir($old["root"]);
        SOY2DAOConfig::DaoDir($old["dao"]);
        SOY2DAOConfig::EntityDir($old["entity"]);
        SOY2DAOConfig::Dsn($old["dsn"]);
        SOY2DAOConfig::user($old["user"]);
        SOY2DAOConfig::pass($old["pass"]);
    }
}
