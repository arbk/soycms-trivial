<?php

/**
 * @entity cms.Page
 */
abstract class PageDAO extends SOY2DAO
{
    /**
     * @return id
     * @trigger onUpdate
     */
    abstract public function insert(Page $bean);

    /**
     * @no_persistent #pageType#,#pageConfig#
     * @trigger onUpdate
     */
    abstract public function update(Page $bean);

    /**
     * @columns id,#pageConfig#
     */
    abstract public function updatePageConfig(Page $bean);

    /**
     * @columns isTrash
     * @query id = :id
     */
    abstract public function updateTrash($id, $isTrash);

    /**
     * @trigger onDelete
     */
    abstract public function delete($id);

    /**
     * @return object
     */
    abstract public function getById($id);

    abstract public function getByParentPageId($parentPageId);

    /**
     * @index id
     * @order page_type,id
     */
    abstract public function get();

    /**
     * @return object
     * @query uri = :uri
     */
    abstract public function getByUri($uri);

    /**
     * @index id
     * @query ##pageType## = :pageType
     * @order id
     */
    abstract public function getByPageType($pageType);

    /**
     * @return column_page_count
     * @columns count(id) as page_count
     * @query ##pageType## = :pageType
     */
    abstract public function countByPageType($pageType);

    /**
     * @return object
     * @query uri = :uri AND (isTrash = 0 OR isTrash IS NULL) AND isPublished = 1
     */
    abstract public function getActivePageByUri($uri);

    /**
     * @return object
     * @query ##pageType## = :pageType
     */
    abstract public function getErrorPage($pageType = Page::PAGE_TYPE_ERROR);

    /**
     * @column id,uri
     * @query uri = :uri
     */
    public function checkUri($uri)
    {
        $query = $this->getQuery();
        try {
            $result = $this->executeQuery($query, $this->getBinds());
        } catch (Exception $e) {
            return false;
        }
        return (boolean)count($result);
    }

    /**
     * @final
     */
    public function onUpdate($sql, $binds)
    {
        $binds[":udate"] = SOYCMS_NOW;

        if ($sql->prefix == 'update') {
            $pageDAO = SOY2DAOFactory::create("cms.PageDAO");
            $historyDAO = SOY2DAOFactory::create("cms.TemplateHistoryDAO");

            $page = $pageDAO->getById($binds[":id"]);

            $history = new TemplateHistory();
            $history->setPageId($binds[':id']);
            $history->setContents($page->getTemplate());
            $history->setUpdateDate(SOYCMS_NOW);

            $historyDAO->insert($history);

            //テンプレート履歴は無制限に残すようにする
//          $historyDAO->deletePastHistory($history->getPageId());
        }

        $binds[":openPeriodStart"] = CMSUtil::encodeDate($binds[":openPeriodStart"], true);
        $binds[":openPeriodEnd"] = CMSUtil::encodeDate($binds[":openPeriodEnd"], false);

        return array($sql,$binds);
    }

    /**
     * @final
     */
    public function onDelete($sql, $binds)
    {
        static $historyDAO;
        if (!$historyDAO) {
            $historyDAO = SOY2DAOFactory::create("cms.TemplateHistoryDAO");
        }

        if ($sql->prefix == 'delete') {
            //削除直前のテンプレート履歴を取る
            $page = $this->getById($binds[':id']);
            $history = new TemplateHistory();
            $history->setPageId($binds[':id']);
            $history->setContents($page->getTemplate());
            $history->setUpdateDate(SOYCMS_NOW);
            $historyDAO->insert($history);

            //テンプレート履歴を削除せずに残しておく
//          $historyDAO->deletePastHistory($history->getPageId());
        }

        return array($sql,$binds);
    }

    /**
     * @order udate desc
     */
    abstract public function getRecentPages();

    /**
     * @query page_type  <> 300
     */
    abstract public function getPagesWithoutErrorPage();

    public function getInRange($offset, $count, $order)
    {
        $query = $this->getQuery();
        switch ($order) {
            case "type":
                $query->order = "page_type, id";
                break;
            case "udate":
                $query->order = "udate desc, id";
                break;
            case "id":
                $query->order = "id";
                break;
        }

        $sql = $query->__toString() . " limit ".((int)$count)." OFFSET ".((int)$offset);

        $res = $this->executeQuery($sql, $this->getBinds());

        $result = array();

        foreach ($res as $key => $value) {
            $result[$value["id"]] = $this->getObject($value);
        }

        return $result;
    }

    /**
     * @sql SELECT COUNT(id) AS count FROM Page
     * @return row
     */
    abstract public function getTotalPageCount();

    /**
     * 公開中かつ公開期間内のページで最も早く公開期間外になるページ
     * @columns min(openPeriodEnd) as openPeriodEndMin
     * @query page_type  <> 300 AND isTrash <> 1 AND isPublished = 1 AND (openPeriodEnd > :now AND openPeriodStart <= :now)
     * @return column_openPeriodEndMin
     */
    abstract public function getNearestClosingPage($now);

    /**
     * 公開中かつ公開期間外のページで最も早く公開期間内になるページ
     * @columns min(openPeriodStart) as openPeriodStartMin
     * @query page_type  <> 300 AND isTrash <> 1 AND isPublished = 1 AND (openPeriodStart > :now)
     * @return column_openPeriodStartMin
     */
    abstract public function getNearestOpeningPage($now);
}
