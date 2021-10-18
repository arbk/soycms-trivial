<?php
/*
 * Created on 2009/10/30
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
CategoryInfoPlugin::register();

class CategoryInfoPlugin{

    const PLUGIN_ID = "soycms_category_info";

	private $isWYSIWYG = false;


    function getId(){
        return self::PLUGIN_ID;
    }

    function init(){

        CMSPlugin::addPluginMenu($this->getId(),array(
            "name"=>"カテゴリー詳細表示プラグイン",
            "description"=>"ブログページのカテゴリーページにカテゴリーの説明を表示する",
            "author"=>"日本情報化農業研究所",
            "url"=>"http://www.n-i-agroinformatics.com",
            "mail"=>"soycms@soycms.net",
            "version"=>"1.1"
        ));
        CMSPlugin::addPluginConfigPage($this->getId(),array(
            $this,"config_page"
        ));

		CMSPlugin::setEvent("onLabelSetupWYSIWYG",$this->getId(),array($this,"onSetupWYSIWYG"));

        //公開側のページを表示させたときに、文字列を表示する
        CMSPlugin::setEvent('onPageOutput',$this->getId(),array($this,"onPageOutput"));
    }


    /**
     * ヘルプを表示
     */
    function config_page(){
		SOY2::import("site_include.plugin.soycms_category_info.config.CategoryInfoConfigPage");
		$form = SOY2HTMLFactory::createInstance("CategoryInfoConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
    }

	function onSetupWYSIWYG(){
		$_COOKIE["label_text_editor"] = ($this->getIsWYSIWYG()) ? "tinyMCE" : "plain";
	}

    /**
     * ラベルのdescriptionを取得
     *
     * @return
     */
    function getCategoryDescription($obj){

        $label = $obj->label;
        return $label->getDescription();
    }

    /**
     * 公開側の出力
     */
    function onPageOutput($obj){

        //ブログではない時は動作しません
        if(false == ($obj instanceof CMSBlogPage)){
            return;
        }

        //カテゴリページ以外では空にしておく
        $labelDescription = "";

        //カテゴリーアーカイブページでしか動作しません
        if($obj->mode == CMSBlogPage::MODE_CATEGORY_ARCHIVE){
            $labelDescription = $this->getCategoryDescription($obj);
        }

        $obj->addModel("is_description", array(
            "soy2prefix" => "b_block",
            "visible" => (isset($labelDescription) && strlen($labelDescription) > 0)
        ));

        //categoryラベルのメモを表示する
        $obj->addLabel("category_description", array(
            "soy2prefix" => "b_block",
            "html" => nl2br($labelDescription)
        ));
    }

	function getIsWYSIWYG(){
		return $this->isWYSIWYG;
	}
	function setIsWYSIWYG($isWYSIWYG){
		$this->isWYSIWYG = $isWYSIWYG;
	}

    /**
     * プラグインの登録
     */
    public static function register(){

        $obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
        if(!$obj){
            $obj = new CategoryInfoPlugin();
        }

        CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
    }
}
