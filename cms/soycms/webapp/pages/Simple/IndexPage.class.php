<?php
class IndexPage extends CMSWebPageBase
{
    public $blogIds;

    public function __construct()
    {
        parent::__construct();

        $this->createAdd("widgets", "HTMLLabel", array(
        "html"=>$this->getWidgetsHTML()
        ));

        HTMLHead::addLink("avav", array(
          "rel"=>"stylesheet",
          "type"=>"text/css",
          "href"=>SOY2PageController::createRelativeLink("./css/dashboard.css") . "?" . SOYCMS_BUILD_TIME
        ));

        $result = $this->run("Entry.RecentListAction"/*, array(
          "limit"=>10
        )*/);

        $this->createAdd("recentEntries", "RecentEntryList", array(
          "list"=>$result->getAttribute("list"),
          "labels"=>$this->getLabelList()
        ));

        // 最近のコメントを出力
        SOY2::import("domain.cms.BlogPage");
        $this->outputCommentList();
        $this->outputTrackbackList();

        // 記事テーブルのCSS
        HTMLHead::addLink("entrytree", array(
          "rel"=>"stylesheet",
          "type"=>"text/css",
          "href"=>SOY2PageController::createRelativeLink("./css/entry/entry.css") . "?" . SOYCMS_BUILD_TIME
        ));

        // 記事操作周りを出力
        $this->outputEntryLink();
    }

    public function getWidgetsHTML()
    {
        $result = $this->run("Plugin.PluginListAction");
        $list = $result->getAttribute("plugins");

        $box = array(array(),array(),array());

        $counter = 0;
        foreach ($list as $plugin) {
            if (!$plugin->getCustom()) {
                continue;
            }
            if (!$plugin->isActive()) {
                continue;
            }

            $customs = $plugin->getCustom();

            $id = $plugin->getId();
            $html = "<div class=\"widget_top\">" . $plugin->getName() . "</div>";
            $html .= "<div class=\"widget_middle\">";

            foreach ($customs as $mkey => $custom) {
                if ($custom["func"]) {
                    $html .= '<iframe src="' . SOY2PageController::createLink("Plugin.CustomPage") . '?id=' . $id . '&menuId=' . $mkey . '"' . ' style="width:230px;border:0;" frameborder="no"></iframe>';
                } else {
                    $html .= $custom["html"];
                }
            }

            $html .= "</div>";
            $html .= "<div class=\"widget_bottom\"></div>";

            $box[$counter][] = $html;

            $counter++;
            if ($counter > 2) {
                $counter = 0;
            }
        }

        $widgets = "<table><tr>";
        foreach ($box as $key => $htmls) {
            $widgets .= "<td id=\"widigets_$key\" style=\"width:245px;vertical-align:top;\">";
            $widgets .= implode("", $htmls);
            $widgets .= "</td>";
        }
        $widgets .= "</tr></table>";

        return $widgets;
    }

    public function outputCommentList()
    {
        $blogArray = $this->getBlogIds();
        $blogIds = array_keys($blogArray);

        $commentListLogic = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic");
        $comments = $commentListLogic->getComments($blogIds, 3, 0);

        if (count($comments) == 0) {
            DisplayPlugin::hide("only_comment_exists");
        }

        foreach ($comments as $key => $comment) {
            $comment->info = $this->getBlogId($comment->getEntryId());
        }

        $this->createAdd("recentComment", "RecentCommentList", array(
        "list"=>$comments
        ));
    }

    public function outputTrackbackList()
    {
        $blogArray = $this->getBlogIds();
        $blogIds = array_keys($blogArray);

        $logic = SOY2Logic::createInstance("logic.site.Entry.EntryTrackbackLogic");

        $trackbacks = $logic->getByLabelIds($blogIds, 3, 0);

        if (count($trackbacks) == 0) {
            DisplayPlugin::hide("only_trackback_exists");
        }

        foreach ($trackbacks as $key => $trackback) {
            $trackbacks[$key]->info = $this->getBlogId($trackback->getEntryId());
        }

        $this->createAdd("recentTrackback", "RecentTrackbackList", array(
        "list"=>$trackbacks
        ));
    }

    public function getBlogIds()
    {
        if ((null===$this->blogIds)) {
            $blogs = $this->run("Blog.BlogListAction")->getAttribute("list");
            $this->blogIds = array();

            foreach ($blogs as $blog) {
                if (null!==$blog->getBlogLabelId()) {
                    $this->blogIds[$blog->getBlogLabelId()] = $blog;
                }
            }
        }

        return $this->blogIds;
    }

    public function getBlogId($entryId)
    {
        $blogIds = $this->getBlogIds();

        $entryLogic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
        $entry = $entryLogic->getById($entryId);

        $labels = $entry->getLabels();

        foreach (array_keys($blogIds) as $blogId) {
            if (in_array($blogId, $labels)) {
                return array("blog"=>$blogIds[$blogId], "entry"=>$entry);
            }
        }
    }

    public function outputEntryLink()
    {
        // ラベル一覧を取得
        $this->labelList = $this->getLabelList();

        $list = $this->run("Label.RecentLabelListAction")->getAttribute("list");
        $recent = array();
        foreach ($list as $key => $value) {
            if (isset($this->labelList[$value])) {
                $recent[$key] = $this->labelList[$value];
            }
        }

        $this->createAdd("recent_labels", "RecentLabelList", array(
        "list"=>$recent
        ));
    }

    /**
     * ラベルオブジェクト一覧を取得
     * page/Entry/IndexPage.class.phpからコピー
     */
    public function getLabelList()
    {
        $action = SOY2ActionFactory::createInstance("Label.LabelListAction");
        $result = $action->run();

        if ($result->success()) {
            return $result->getAttribute("list");
        } else {
            return array();
        }
    }
}

class RecentCommentList extends HTMLList
{
    protected function populateItem($entity)
    {
        $blog = @$entity->info["blog"];
        $entry = @$entity->info["entry"];

        if ((null===$blog)) {
            $blog = new BlogPage();
        }
        if ((null===$entry)) {
            $entry = new Entry();
        }

        $title = ((strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle());
        $title .= strlen($entity->getAuthor()) == 0 ? "" : "(" . $entity->getAuthor() . ")";

        $this->createAdd("title", "HTMLLink", array(
        "link"=>SOY2PageController::createLink("Blog.Comment." . $blog->getId()),
        "text"=>$title
        ));

        $this->createAdd("content", "HTMLLabel", array(
        "text"=>$entry->getTitle() . "(" . $blog->getTitle() . ")"
        ));
        $this->createAdd("udate", "HTMLLabel", array(
        "text"=>CMSUtil::getRecentDateTimeText($entity->getSubmitDate()),
        "title"=>date("Y-m-d H:i:s", $entity->getSubmitDate())
        ));
    }
}

class RecentTrackbackList extends HTMLList
{
    protected function populateItem($entity)
    {
        $blog = @$entity->info["blog"];
        $entry = @$entity->info["entry"];

        if ((null===$blog)) {
            $blog = new BlogPage();
        }
        if ((null===$entry)) {
            $entry = new Entry();
        }

        $title = ((strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle());
        $title .= strlen($entity->getBlogName()) == 0 ? "" : "(" . $entity->getBlogName() . ")";

        $this->createAdd("title", "HTMLLink", array(
        "link"=>SOY2PageController::createLink("Blog.Trackback." . $blog->getId()),
        "text"=>$title
        ));
        $this->createAdd("content", "HTMLLabel", array(
        "text"=>$entry->getTitle() . "(" . $blog->getTitle() . ")"
        ));
        $this->createAdd("udate", "HTMLLabel", array(
        "text"=>CMSUtil::getRecentDateTimeText($entity->getSubmitDate()),
        "title"=>date("Y-m-d H:i:s", $entity->getSubmitDate())
        ));
    }
}

class RecentEntryList extends HTMLList
{
    private $labels = array();

    public function setLabels($array)
    {
        if (is_array($array)) {
            $this->labels = $array;
        }
    }

    protected function populateItem($entity)
    {

        $this->createAdd("title", "HTMLLink", array(
        "link"=>SOY2PageController::createLink("Entry.Detail") . "/" . $entity->getId(),
        "text"=>(strlen($entity->getTitle()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") : $entity->getTitle(),
        ));


        // ラベルは３つまで表示
        $selectedList = $entity->getLabels();
        $labelText = "";
        $strlen = 0;
        $counter = 0;
        foreach ($this->labels as $label) {
            if (!in_array($label->getId(), $selectedList)) {
                continue;
            }

            if ($counter > 3) {
                $labelText .= "...";
                break;
            }

            $attr = array();
            $attr[] = 'href="'.soy2_h(SOY2PageController::createLink("Entry.List")."/".$label->getId()).'"';
            $attr[] = 'class="label label-default label-soy"';
            $attr[] = 'style="color:#' . sprintf("%06X", $label->getColor()).'; background-color:#' . sprintf("%06X", $label->getBackgroundColor()).'; margin-left:4px;"';

            // ある文字数越えたら追加しない
            if (($strlen + strlen($label->getCaption())) > 300) {
                continue;
            }

            $strlen .= strlen($label->getCaption()) + 2;
            $labelText .= '<a '.implode(" ", $attr).'>'.$label->getDisplayCaption().'</a>';

            $counter++;
        }

        $this->createAdd("content", "HTMLLabel", array(
        "html"=>$labelText
        ));

        $this->createAdd("udate", "HTMLLabel", array(
        "text"=>CMSUtil::getRecentDateTimeText($entity->getUdate()),
        "title"=>date("Y-m-d H:i:s", $entity->getUdate())
        ));
    }

    public function foldingDescription($description, $width = 20)
    {
        // 折り返しありの場合
        $tmp = "";
        $strlen = 0;

        $counter = mb_strlen($description) / $width + 1;

        for ($i = 0; $i < $counter; ++$i) {
            $str = mb_strimwidth($description, $strlen, $width);

            if (strlen($str) < 1) {
                continue;
            }

            if ($i != 0) {
                $tmp .= "<br>";
            }
            $tmp .= soy2_h($str);
            $strlen += mb_strlen($str);
        }

        return $tmp;
    }
}

/**
 * page/Entry/IndexPage.class.phpからコピー
 */
class RecentLabelList extends HTMLList
{
    public function populateItem($entity)
    {
        $this->createAdd("label_icon", "HTMLImage", array(
        "src"=>$entity->getIconUrl()
        ));
        $this->createAdd("label_link", "HTMLLink", array(
          "link"=>SOY2PageController::createLink("Entry.List." . $entity->getId())
        ));
        $this->createAdd("label_title", "HTMLLabel", array(
          "html"=>$entity->getDisplayCaption() . " (" . $entity->getEntryCount() . ")"
        ));
    }
}
