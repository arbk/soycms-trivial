<?php
class IndexPage extends CMSWebPageBase
{
//  const NUMBER_OF_ENTRIES = 5;

    public $blogIds;

    public function doPost()
    {
        if (soy2_check_token()) {
            if (isset($_POST["clear_cache"])) {
                $dir = UserInfoUtil::getSiteDirectory().SOYCMS_CACHE_DIRNAME."/";
                CMSUtil::unlinkAllIn($dir, true);
            }
            $this->jump("Index");
        }
    }

    public function __construct()
    {
        // 記事管理者以上の時
        if (UserInfoUtil::hasSiteAdminRole()) {
            $initDetect = $this->run("Init.InitDetectAction");
            if ($initDetect->success()) {
                if ($initDetect->getAttribute("detect")) {
                  // 初めてサイトにアクセスする場合は２択ページに飛ぶ
                    $this->jump("Init");
                    exit();
                }
            }
        }

        if (!UserInfoUtil::hasSiteAdminRole()) {
            SOY2PageController::jump("Simple");
        }

        parent::__construct();

        $siteConfig = $this->getSiteConfig();
        if ($siteConfig->isShowOnlyAdministrator()) {
            $this->addMessage("SOYCMS_CONFIG_SHOW_ONLY_ADMINISTRATOR");
        }

        //プラグインによるコンテンツの追加
        $contents = array();
        $onLoad = CMSPlugin::getEvent('onAdminTop');
        foreach ($onLoad as $plugin) {
            $func = $plugin[0];
            $res = call_user_func($func);
            if (!isset($res["title"])) {
                continue; //コンテンツ名が無い場合はスルー
            }
            $contents[] = $res;
        }

        DisplayPlugin::toggle("plugin_area", (count($contents) > 0));
        $this->createAdd("plugin_area_list", "_component.Top.TopPagePluginAreaListComponent", array(
            "list" => $contents
        ));

        $this->addLabel("widgets", array(
          "html"=>$this->getWidgetsHTML()
        ));

        HTMLHead::addLink("dashboard", array(
          "rel"=>"stylesheet",
          "type"=>"text/css",
          "href"=>SOY2PageController::createRelativeLink("./css/dashboard.css") . "?" . SOYCMS_BUILD_TIME
        ));

        $result = $this->run("Entry.RecentListAction"/*, array("limit" => self::NUMBER_OF_ENTRIES)*/);

        $this->createAdd("recentEntries", "_component.Recent.EntryListComponent", array(
          "list"=>$result->getAttribute("list"),
          "labels"=>$result->getAttribute("labels")
        ));

        $this->createAdd("recentPage", "_component.Recent.PageListComponent", array(
          "list" => $this->run("Page.RecentPageListAction"/*, array("limit" => self::NUMBER_OF_ENTRIES)*/)->getAttribute("list")
        ));

        $result = $this->run("Page.PageListAction", array("buildTree"=>true));
        $options = $result->getAttribute("PageTree");

        $this->addSelect("page_tree", array(
          "options"=>$options,
          "indexOrder"=>true,
          "onchange"=>"location.href='" . SOY2PageController::createLink("Page.Detail.") . "'+this.value;"
        ));

        $this->addModel("is_entry_template_enabled", array(
          "visible" => CMSUtil::isEntryTemplateEnabled(),
        ));
        $this->addModel("is_page_template_enabled", array(
          "visible" => CMSUtil::isPageTemplateEnabled(),
        ));

      // 最近のコメントを出力
        SOY2::import("domain.cms.BlogPage");
        $this->outputCommentList();
        $this->outputTrackbackList();
    }

    public function getWidgetsHTML()
    {
        $result = $this->run("Plugin.PluginListAction");
        $list = $result->getAttribute("plugins");

        $box = array(array(), array(), array());

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
            $html = "<div class=\"panel-heading\">" . $plugin->getName() . "</div>";
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

        $this->createAdd("recentComment", "_component.Recent.CommentListComponent", array(
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

        $this->createAdd("recentTrackback", "_component.Recent.TrackbackListComponent", array(
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
                return array("blog"=>$blogIds[$blogId], "entry" => $entry);
            }
        }
    }

    private function getSiteConfig()
    {
        $result = SOY2ActionFactory::createInstance("SiteConfig.DetailAction")->run();
        return $result->getAttribute("entity");
    }
}
