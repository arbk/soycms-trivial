<?php

class CMSAdminPageController extends SOY2PageController
{
    public function onNotFound($path = null, $args = null, $classPath = null)
    {
        /**
         * ページがなかったらr=[REQUEST_URI]としてルートに転送
         * 非ログイン時にログインしていないと見られないURIへアクセスしようとしているのを想定している
         */

        //デフォルトパスの場合はすでにルートにアクセスしているので転送しない（無限リダイレクト回避）
        if ($this->getRequestPath() == "Index") {
            parent::onNotFound(null, null, null);
        }

        if (strpos($_SERVER["REQUEST_URI"], "Logout")===false) {
            $redirectParam = "?r=".rawurlencode(self::createRelativeLink($_SERVER["REQUEST_URI"]));
        } else {
            $redirectParam = "";
        }

        self::redirect("./".$redirectParam);
        exit;
    }

    /**
     * pathが転送していいパスかどうかをチェックする
     */
    public static function isAllowedPath($path, $allowed = null)
    {
        static $allowedPathes;
        if (!$allowedPathes) {
            $allowedPathes = array(
                SOY2PageController::createRelativeLink("../admin/"),
                SOY2PageController::createRelativeLink("../app/"),
                SOY2PageController::createRelativeLink("../soycms/"),
//              SOY2PageController::createRelativeLink("../soyshop/"),
            );
        }

        if (isset($allowed)) {
            $allowedPathes = array(SOY2PageController::createRelativeLink($allowed));
        }

        $path = SOY2PageController::createRelativeLink($path);

        foreach ($allowedPathes as $allowed) {
            if (strpos($path, $allowed) === 0) {
                return true;
            }
        }

        return false;
    }
}
