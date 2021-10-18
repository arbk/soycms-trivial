<?php

class EntryListPage extends CMSWebPageBase
{
    protected $id;
    protected $page;
    protected $labelIds;

    public function doPost()
    {
        if (soy2_check_token()) {
            switch ($_POST['op_code']) {
                case 'delete':
                    // 削除実行
                    $result = $this->run("Entry.RemoveAction");
                    if ($result->success()) {
                        $this->addMessage("ENTRY_REMOVE_SUCCESS");
                    } else {
                        $this->addErrorMessage("ENTRY_REMOVE_FAILED");
                    }
                    $this->jump("Blog.EntryList." . $this->id . "." . implode(".", $this->labelIds));
                    break;
                case 'copy':
                    // 複製実行
                    $result = $this->run("Entry.CopyAction");
                    if ($result->success()) {
                        $this->addMessage("ENTRY_COPY_SUCCESS");
                    } else {
                        $this->addErrorMessage("ENTRY_COPY_FAILED");
                    }
                    $this->jump("Blog.EntryList." . $this->id . "." . implode(".", $this->labelIds));
                    break;
                case 'setPublish':
                    // 公開状態にする
                    $result = $this->run("Entry.PublishAction", array('publish'=>true));
                    if ($result->success()) {
                        $this->addMessage("ENTRY_PUBLISH_SUCCESS");
                    } else {
                        $this->addErrorMessage("ENTRY_PUBLISH_FAILED");
                    }
                    $this->jump("Blog.EntryList." . $this->id . "." . implode(".", $this->labelIds));
                    break;
                case 'setnonPublish':
                    // 非公開状態にする
                    $result = $this->run("Entry.PublishAction", array('publish'=>false));
                    if ($result->success()) {
                        $this->addMessage("ENTRY_NONPUBLISH_SUCCESS");
                    } else {
                        $this->addErrorMessage("ENTRY_NONPUBLISH_FAILED");
                    }
                    $this->jump("Blog.EntryList." . $this->id . "." . implode(".", $this->labelIds));
                    break;
                case 'update_display':
                    // 表示順が押された（と判断してるけど）
                    $result = $this->run("EntryLabel.UpdateDisplayOrderAction");

                    if ($result->success()) {
                        $this->addMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_SUCCESS");
                        $this->jump("Blog.EntryList." . $this->id . "." . implode(".", $this->labelIds));
                    } else {
                        $this->addErrorMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_FAILED");
                        $this->jump("Blog.EntryList." . $this->id);
                    }
                    break;
            }
        } else {
            $this->jump("Blog.EntryList.".$this->id);
        }
        exit();
    }


    /**
     * クッキーに保存
     */
    private function updateCookie($labelIds)
    {
        $timeout = 0;
        $path = dirname($_SERVER['SCRIPT_NAME']) . '/';

        // Entry_List
        $cookieName = "Entry_List";
        $value = implode('.', $labelIds);
//      setcookie($cookieName, $value, $timeout, $path);
        soy2_setcookie($cookieName, $value, array("expires"=>$timeout,"path"=>$path));

        // Entry_List_Limit
        if (isset($_GET['limit'])) {
            $cookieName = "Entry_List_Limit";
            $value = $_GET['limit'];
//          setcookie($cookieName, $value, $timeout, $path);
            soy2_setcookie($cookieName, $value, array("expires"=>$timeout,"path"=>$path));
        }
    }

    public function __construct($arg)
    {
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : (isset($_COOKIE['Entry_List_Limit']) ? (int)$_COOKIE['Entry_List_Limit'] : SOYCMS_INI_NUMOF_ENTRY);

        // 最初の値はブログページID
        $id = array_shift($arg);
        $this->id = $id;

        $labelIds = isset($arg) ? $arg : array();
        $this->labelIds = $labelIds;

        $this->updateCookie($labelIds);

        $result = $this->run("Blog.DetailAction", array("id"=>$id));
        if (!$result->success()) {
            $this->addMessage("PAGE_DETAIL_GET_FAILED");
            $this->jump("Page");
            exit();
        }

        $page = $result->getAttribute("Page");
        $labelId = $page->getBlogLabelId();

//      $result = $this->run("Entry.EntryListAction", array("id"=>$labelId));
//      $entries = $result->getAttribute("Entities");

        // ラベルIDには必ずブログに使用するラベルIDが必要
        if (!in_array($labelId, $this->labelIds)) {
            array_unshift($this->labelIds, $labelId);
        }

        parent::__construct();

        // ラベル一覧を取得
        $labelList = $this->getLabelList();

        // 自分自身へのリンク
        $currentLink = SOY2PageController::createLink("Blog.EntryList") . "/" . $id . "/" . implode("/", $this->labelIds);

        // 無効なラベルIDを除く
        foreach ($this->labelIds as $key => $value) {
            if (!is_numeric($value)) {
                unset($this->labelIds[$key]);
            }
        }

        // 記事を取得
        list($entries, $count, $offset) = $this->getEntries($offset, $limit, $this->labelIds);

        require_once(__DIR__ . '/_EntryBlankPage.class.php');
        $this->createAdd("no_entry_message", "_EntryBlankPage", array(
            "pageId"=>$this->id,
            "visible"=>(0 >= count($entries))
        ));

        if (0 >= count($entries)) {
            DisplayPlugin::hide("must_exist_entry");
        }

        // 記事一覧の表を作成
        $this->createAdd("list", "LabeledEntryList", array(
            "labelIds"=>$labelIds,
            "labelList"=>$labelList,
            "list"=>$entries,
            "pageId"=>$id,
            "labelId"=>$labelId,
            "page"=> $page,
        ));

        $labelState = array();
        $url = SOY2PageController::createLink("Blog.EntryList." . $id);
        foreach ($this->labelIds as $labelId) {
            if (!isset($labelList[$labelId])) {
                continue;
            }
            $label = $labelList[$labelId];
            $url .= "/" . $label->getId();
            $labelState[] = "<a href=\"$url\">" . $label->getDisplayCaption() . "</a>";
        }
        $this->createAdd("label_state", "HTMLLabel", array(
            "html"=>implode("&nbsp;&gt;&nbsp;", $labelState)
        ));


        // 子ラベルを取得
        $labels = $this->run("EntryLabel.NarrowLabelListAction", array(
            "labelIds"=>$this->labelIds
        ))->getAttribute("labels");

        // 子ラベルボタンを作成
        $this->createAdd("sublabel_list", "SubLabelList", array(
            "list"=>$labels,
            "labelList"=>$labelList,
            "currentLink"=>$currentLink,
            "pageId"=>$id
        ));

        // 新規作成リンクを作成
        $this->createAdd("create_link", "HTMLLink", array(
            "link"=>SOY2PageController::createLink("Blog.Entry") . "/" . $id
        ));

        // ページャーを作成
        $this->createAdd("topPager", "EntryPagerComponent", array(
            "arguments"=>array($offset, $limit, $count, $currentLink)
        ));

        // CSS
        HTMLHead::addLink("entrytree", array(
            "rel"=>"stylesheet",
            "type"=>"text/css",
            "href"=>SOY2PageController::createRelativeLink("./css/entry/entry.css")
        ));
        HTMLHead::addLink("blog_entrylist", array(
            "rel"=>"stylesheet",
            "type"=>"text/css",
            "href"=>SOY2PageController::createRelativeLink("./css/blog/entrylist.css")
        ));

        $this->createAdd("showCountINI", "HTMLLink", array(
            "text"=>"[" . SOYCMS_INI_NUMOF_ENTRY . "]",
            "link"=>$currentLink . "?limit=" . SOYCMS_INI_NUMOF_ENTRY
        ));
        $this->createAdd("showCountM", "HTMLLink", array(
            "text"=>"[50]",
            "link"=>$currentLink . "?limit=50"
        ));
        $this->createAdd("showCountL", "HTMLLink", array(
            "text"=>"[100]",
            "link"=>$currentLink . "?limit=100"
        ));
        $this->createAdd("showCountXL", "HTMLLink", array(
            "text"=>"[400]",
            "link"=>$currentLink . "?limit=400"
        ));

        // フォーム
        $this->createAdd("index_form", "HTMLForm");

        // 表示順更新ボタンの追加
        $this->createAdd("display_order_submit", "HTMLInput", array(
            "name"=>"display_order_submit",
            "value"=>CMSMessageManager::get("SOYCMS_DISPLAYORDER"),
            "type"=>"submit",
            "tabindex"=>LabeledEntryList::$tabIndex++
        ));

        // 削除ボタンの追加
        $this->createAdd("remove_submit", "HTMLInput", array(
            "name"=>"remove_submit",
            "value"=>CMSMessageManager::get("SOYCMS_DELETE_ENTRY"),
            "type"=>"submit"
        ));

        $this->createAdd("BlogMenu", "Blog.BlogMenuPage", array(
            "arguments"=>array($this->id)
        ));

        // ツールボックス
        if ($page->isActive() == Page::PAGE_ACTIVE) {
            $pageUrl = CMSUtil::getSiteUrl() . ((strlen($page->getUri()) > 0) ? $page->getUri() . "/" : "");

            // カテゴリー別アーカイブページへのリンク
            $category = @$labelList[array_pop($this->labelIds)];
            if ($page->getGenerateCategoryFlag() && isset($category) && in_array($category->getId(), $page->getCategoryLabelList())) {
                CMSToolBox::addLink(
                    CMSMessageManager::get("SOYCMS_SHOW_CATEGORYARCHIVEPAGE"),
                    $pageUrl . $page->getCategoryPageUri() . "/" . rawurlencode($category->getAlias()),
                    false,
                    "this.target = '_blank'"
                );
            }
        }
        CMSToolBox::addPageJumpBox();

        // 一括ラベル操作のURL
        if (count($labelIds) == 0 || !is_numeric(implode("", $labelIds))) {
            $this->addScript("parameters", array(
            "script"=>'var listPanelURI = "' . SOY2PageController::createLink("Entry.ListPanel") . '"'
            ));
        } else {
            $this->addScript("parameters", array(
            "script"=>'var listPanelURI = "' . SOY2PageController::createLink("Entry.ListPanel." . implode('.', $labelIds)) . '"'
            ));
        }

        // 操作用のJavaScript
        $this->addScript("entry_list", array(
            "script"=> file_get_contents(__DIR__."/../Entry/script/entry_list.js")
        ));

        // トップラベル以外は表示順更新を消す
        if (count($this->labelIds) >= 2) {
            DisplayPlugin::hide("no_label");
        }

        if (!UserInfoUtil::hasEntryPublisherRole()) {
            DisplayPlugin::hide("publish");
            DisplayPlugin::hide("no_label");
        }
    }

    /**
     * 複数ラベルを指定して記事を取得
     * ラベルがnullの時は、すべての記事を表示させる
     * @param $offset,$limit,$labelId
     * @return (entry_array,記事の数,大きすぎた場合最終オフセット)
     */
    private function getEntries($offset, $limit, $labelIds)
    {
        // If array $labelIds is empty
        if (count($labelIds) == 0) {
            return array(array(), 0, 0);
        }

        // ラベルIDに数字以外が含まれていたらアウト
        foreach ($labelIds as $labelId) {
            if (!is_numeric($labelId)) {
                return array(array(), 0, 0);
            }
        }

        $action = SOY2ActionFactory::createInstance("Entry.EntryListAction", array(
            "ids"=>$labelIds,
            "offset"=>$offset,
            "limit"=>$limit,
            "ignoreColumns"=>array("more"),
        ));
        $result = $action->run();
        $entities = $result->getAttribute("Entities");
        $totalCount = $result->getAttribute("total");

        return array($entities,$totalCount,min($offset, $totalCount));
    }

    /**
     * ラベルをすべて取得
     */
    private function getLabelList()
    {
        $action = SOY2ActionFactory::createInstance("Label.LabelListAction");
        $result = $action->run();

        if ($result->success()) {
            $list = $result->getAttribute("list");
            return $list;
        } else {
            return array();
        }
    }
}

class LabeledEntryList extends HTMLList
{
    public static $tabIndex = 0;

    private $labelIds;
    private $labelList;
    private $pageId;
    private $labelId;
    private $page;

    private $logic;

    public function setLabelIds($labelIds)
    {
        $this->labelIds = $labelIds;
    }

    public function setLabelList($list)
    {
        $this->labelList = $list;
    }

    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }
    public function setLabelId($labelId)
    {
        $this->labelId = $labelId;
    }
    public function setPage($page)
    {
        $this->page = $page;
    }

    protected function populateItem($entity)
    {
        $this->createAdd("entry_check", "HTMLInput", array(
            "type"=>"checkbox",
            "name"=>"entry[]",
            "value"=>$entity->getId()
        ));

        $entity->setTitle(strip_tags($entity->getTitle()));
        $title_link = SOY2HTMLFactory::createInstance("HTMLLink", array(
            "text"=>((strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle()),
            "link"=>SOY2PageController::createLink("Blog.Entry." . $this->pageId . "." . $entity->getId()),
//          "title"=>$entity->getTitle()
        ));

        $this->add("title", $title_link);

        $pageUrl = UserInfoUtil::getSiteUrl() . ((strlen($this->page->getUri()) > 0) ? $this->page->getUri() . "/" : "");
        $this->createAdd("status", "HTMLLink", array(
        "text"=>$entity->getStateMessage(),
            "link" => $pageUrl.$this->page->getEntryPageUri()."/".rawurlencode($entity->getAlias()),
        ));

        $this->createAdd("content", "HTMLLabel", array(
            "text"=>mb_strimwidth(SOY2HTML::ToText($entity->getContent()), 0, 100, "..."),
//          "title"=>mb_strimwidth(SOY2HTML::ToText($entity->getContent()), 0, 1000, "..."),
        ));

        $this->createAdd("create_date", "HTMLLabel", array(
            "text"=>CMSUtil::getRecentDateTimeText($entity->getCdate()),
            "title" => date("Y-m-d H:i:s", $entity->getCdate()),
        ));

        if (!$this->logic) {
            $this->logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
        }
        $this->createAdd("order", "HTMLInput", array(
            "type"=>"text",
            "name"=>"displayOrder[" . $entity->getId() . "][" . $this->labelId . "]",
            "value"=>$this->logic->getDisplayOrder($entity->getId(), $this->labelId),
            "size"=>"5",
            "tabindex"=>self::$tabIndex++
        ));

        // ラベル表示部
        $this->createAdd("label", "LabelList", array(
            "list"=>$this->labelList,
            "entryLabelIds"=>$entity->getLabels(),
            "pageId"=>$this->pageId
        ));
    }
}

class LabelList extends HTMLList
{
    private $pageId;
    private $entryLabelIds = array();

    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    public function setEntryLabelIds($list)
    {
        if (is_array($list)) {
            $this->entryLabelIds = $list;
        }
    }

    protected function populateItem($label)
    {
        $this->createAdd("entry_list_link", "HTMLLink", array(
        "link"=>SOY2PageController::createLink("Blog.EntryList." . $this->pageId . "." . $label->getId()),
            "text" => $label->getCaption(),
            "visible" => in_array($label->getId(), $this->entryLabelIds),
            "style"=> "color:#" . sprintf("%06X", $label->getColor()).";"
            ."background-color:#" . sprintf("%06X", $label->getBackgroundColor()).";",
        ));
    }
}

class SubLabelList extends HTMLList
{
    private $labelList;
    private $currentLink;
    private $pageId;

    public function setCurrentLink($link)
    {
        $this->currentLink = $link;
    }

    public function setLabelList($list)
    {
        $this->labelList = $list;
    }

    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    protected function populateItem($labelId)
    {
        $label = $this->labelList[$labelId];

        if (!$label instanceof Label) {
            $label = new Label();
        }

        $this->createAdd("label_icon", "HTMLImage", array(
            "src"=>$label->getIconUrl(),
            "title" => $label->getBranchName(),
            "alt" => "",
        ));
        $this->createAdd("label_link", "HTMLLink", array(
            "link" => $this->currentLink ."/".$label->getId(),
            "title"=>$label->getCaption(),
        ));
        $this->createAdd("label_name", "HTMLLabel", array(
            "text" => $label->getCaption(),
            "title" => $label->getBranchName(),
        ));
        $this->addLabel("label_entries_count", array(
            "text" => ( (int)$label->getEntryCount())
        ));
    }
}
