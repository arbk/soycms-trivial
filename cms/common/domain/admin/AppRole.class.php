<?php
/**
 * @table AppRole
 */
class AppRole
{
    /**
     * アプリの権限なし
     */
    const APP_NO_ROLE = 0;

    /**
     * アプリの管理者(アプリ内の設定を変更出来る)
     */
    const APP_SUPER_USER = 1;

    /**
     * アプリの操作者（アプリ内の設定は変更出来ない）
     */
    const APP_USER = 2;

    /**
     * @id
     */
    private $id;

    /**
     * @column user_id
     */
    private $userId;

    /**
     * @column app_id
     */
    private $appId;

    /**
     * @column app_role
     */
    private $appRole;

    /**
     * @column app_role_config
     */
    private $appRoleConfig;

    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getUserId()
    {
        return $this->userId;
    }
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
    public function getAppId()
    {
        return $this->appId;
    }
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }
    public function getAppRole()
    {
        return $this->appRole;
    }
    public function setAppRole($appRole)
    {
        $this->appRole = $appRole;
    }
    public function getAppRoleConfig()
    {
        return $this->appRoleConfig;
    }
    public function setAppRoleConfig($appRoleConfig)
    {
        $this->appRoleConfig = $appRoleConfig;
    }

    /* serialize */
    public function getUnserializeConfig()
    {
        return soy2_unserialize($this->appRoleConfig);
    }
    public function setSerializeConfig($appRoleConfig)
    {
        $this->appRoleConfig = soy2_serialize($appRoleConfig);
    }

    /**
     * Selectで使うための権限リスト
     * @param Boolean useMultipleRole 複数権限設定（App管理者, App操作者）を使う場合はtrue
     */
    public static function getRoleLists($useMultipleRole = false)
    {
        if ($useMultipleRole) {
            return array(
                self::APP_NO_ROLE => CMSMessageManager::get("ADMIN_NO_ROLE"),
                self::APP_SUPER_USER => CMSMessageManager::get("ADMIN_APP_SUPER_USER"),
                self::APP_USER => CMSMessageManager::get("ADMIN_APP_USER"),
            );
        } else {
            return array(
                self::APP_NO_ROLE => CMSMessageManager::get("ADMIN_NO_ROLE"),
                self::APP_SUPER_USER => CMSMessageManager::get("ADMIN_LOGIN_POSSIBLE"),
            );
        }
    }

    /**
     * Selectで使うための権限リスト
     * SOY Shop（1.5.0以下）内から呼び出される。1.5.1以上では使われないが互換性維持のため残しておく。
     */
    public static function getRoleListsText()
    {
        return self::getRoleLists(true);
    }
}
