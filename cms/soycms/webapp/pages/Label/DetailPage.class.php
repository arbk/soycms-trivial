<?php

class DetailPage extends CMSWebPageBase
{
    private $labelId;

    public function doPost()
    {
        if (soy2_check_token()) {
            $res = $this->run("Label.LabelUpdateAction", array(
            "id" => $this->labelId
            ));

            if ($res->success()) {
                  $this->addMessage("LABEL_UPDATE_SUCCESS");
            } else {
                $this->addMessage("LABEL_UPDATE_FAILED");
            }

            $this->jump("Label.Detail.".$this->labelId);
        }
    }

    public function __construct($args)
    {
        $this->labelId = (isset($args[0])) ? (int)$args[0] : null;

        parent::__construct();

        $res = $this->run("Label.LabelDetailAction", array(
        "id" => $this->labelId
        ));

        //無かった場合
        if (!$res->success()) {
            $this->jump("Label");
        }

        $this->setupWYSIWYG();

        $label = $res->getAttribute("label");
        $this->buildForm($label);

        //アイコンリスト
        $this->createAdd("image_list", "_component.Label.LabelIconListComponent", array(
        "list" => $this->getLabelIconList()
        ));

        // colorpickerプラグイン
        HTMLHead::addLink("colorpicker", array(
        "rel" => "stylesheet",
        "href" => SOY2PageController::createRelativeLink("./js/colorpicker/colorpicker.css"),
        ));
        $this->addLabel("colorpicker", array(
        "src" => SOY2PageController::createRelativeLink("./js/colorpicker/colorpicker.js"),
        ));

        $this->addForm("update_form");
    }

    private function buildForm(Label $entity)
    {
        $this->addInput("caption", array(
        "value" => $entity->getCaption(),
        "name" => "caption"
        ));

        $this->addInput("alias", array(
        "value" => $entity->getAlias(),
        "name" => "alias"
        ));

        $this->addImage("label_icon", array(
        "src" => $entity->getIconUrl(),
        "onclick" => "javascript:changeImageIcon(".$entity->getId().");"
        ));
        $this->addInput("icon", array(
        "value" => $entity->getIcon(),
        "name" => "icon",
        "id" => "labelicon"
        ));

        $this->addTextArea("description", array(
        "value" => $entity->getDescription(),
        "name" => "description"
        ));

        $this->addInput("color", array(
        "value" => sprintf("%06X", $entity->getColor()),
        "name" => "color"
        ));

        $this->addInput("background_color", array(
        "value" => sprintf("%06X", $entity->getBackgroundColor()),
        "name" => "backgroundColor"
        ));

        $this->addLabel("preview", array(
        "text"=> $entity->getCaption(),
        "style"=> "color:#" . sprintf("%06X", $entity->getColor()).";background-color:#" . sprintf("%06X", $entity->getBackgroundColor()) . ";padding:5px;line-height:1.7;"
        ));
    }

    /**
     * ラベルに使えるアイコンの一覧を返す
     */
    private function getLabelIconList()
    {
        $files = scandir(CMS_LABEL_ICON_DIRECTORY);

        $return = array();
        foreach ($files as $file) {
            if ($file[0] == ".") {
                continue;
            }

            $return[] = (object)array(
            "filename" => $file,
            "url" => CMS_LABEL_ICON_DIRECTORY_URL . $file,
            );
        }

        return $return;
    }

    private function setupWYSIWYG()
    {
        //Call Event
        CMSPlugin::callEventFunc("onLabelSetupWYSIWYG");

        $jsVarsAndPaths = array(
        "InsertLinkAddress" => "Entry.Editor.InsertLink",
        "InsertImagePage" => "Entry.Editor.FileUpload",
        "CreateLabelLink" => "Entry.CreateNewLabel",
        "templateAjaxURL" => "EntryTemplate.GetTemplateAjaxPage",
        );

        //Cookieからエディタのタイプを取得
        $editor = isset($_COOKIE["label_text_editor"]) ? $_COOKIE["label_text_editor"] : "plain" ;

        $scriptsArr = array(
        "plain"=> array(
            "./js/editor/PlainTextEditor.js",
            "./js/editor/EntryEditorFunctions.js",
        ),
        "tinyMCE" => array(
            "./js/tinymce/tinymce.min.js",
            "./js/editor/RichTextEditor.js",
            "./js/editor/EntryEditorFunctions.js",
        )
        );
        $jsFiles = isset($scriptsArr[$editor]) ? $scriptsArr[$editor] : $scriptsArr["tinyMCE"];

        //bootstrapを使った管理画面用（JavaScriptはファイル末尾で読み込む）
        self::createAddJavaScript($jsVarsAndPaths, $jsFiles);
    }

  /**
   * 記事編集に必要なJavaScriptをcreateAddで追加する。soy:id=entry_editor_javascripts
   */
    private function createAddJavaScript($jsVarsAndPaths, $scriptFiles)
    {
        $script = array();
        $script[] = '<script type="text/javascript">'."\n";
        foreach ($jsVarsAndPaths as $var => $path) {
            $script[] = 'var '.$var.' = "' . soy2_h(SOY2PageController::createLink($path)) . '";';
        }
//      if(SOYCMSEmojiUtil::isInstalled()){
//          $script[] = 'var mceSOYCMSEmojiURL = "'.soy2_h(SOYCMSEmojiUtil::getEmojiInputPageUrl().'#tinymce').'";';
//      }
        $script[] = '</script>'."\n";

        foreach ($scriptFiles as $path) {
            $script[] = '<script type="text/javascript" src="'.soy2_h(SOY2PageController::createRelativeLink($path)."?".SOYCMS_BUILD_TIME).'"></script>';
        }
        //ラベルのチェック状況を調べる
        $script[] = '<script type="text/javascript">';
        $script[] = "$('input[name=\"label[]\"]').each(function(ele){";
        $script[] = "  toggle_labelmemo(\$(this).val(), \$(this).is(\":checked\"));";
        $script[] = "});";
        $script[] = '</script>'."\n";

        $this->addLabel("entry_editor_javascripts", array(
        "html" => implode("\n", $script),
        ));
    }
}
