<?php

class PagerLogic extends SOY2LogicBase
{
    private $pageURL;
    private $page = 1;
    private $start;
    private $end;
    private $total;
    private $query;
    private $limit = 15;

    public function setPageURL($value)
    {
        $value = SOY2PageController::createLink($value);
        if ($value[strlen($value)-1] != "/") {
            $value .= "/";
        }
        $this->pageURL = $value;
    }
    // public function setPublishPageUrl($value)
    // {
    //     $value = soyshop_get_mypage_url() . "/" . $value;
    //     if ($value[strlen($value)-1] != "/") {
    //         $value .= "/";
    //     }
    //     $this->pageURL = $value;
    // }
    public function setPage($value)
    {
        $this->page = $value;
    }
    public function setStart($value)
    {
        $this->start = $value;
    }
    public function setEnd($value)
    {
        $this->end = $value;
    }
    public function setTotal($value)
    {
        $this->total = $value;
    }
    public function setLimit($value)
    {
        $this->limit = $value;
    }

    public function getCurrentPageURL()
    {
        return $this->pageURL . $this->page;
    }
    public function getPageURL()
    {
        return $this->pageURL;
    }
    public function getPage()
    {
        return $this->page;
    }
    public function getStart()
    {
        return $this->start;
    }
    public function getEnd()
    {
        return $this->end;
    }
    public function getTotal()
    {
        return $this->total;
    }
    public function getLimit()
    {
        return $this->limit;
    }
    public function getOffset()
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getNextParam()
    {
        $link = ($this->total > $this->end) ? $this->pageURL . ($this->page + 1) : $this->pageURL . $this->page;
        if (strlen($this->getQuery())) {
            $link .= "?" . $this->getQuery();
        }

        return array(
            "link" => $link,
            "class" => ($this->total <= $this->end) ? "pager_disable" : ""
        );
    }
    public function getPrevParam()
    {
        $link = ($this->page > 1) ? $this->pageURL . ($this->page - 1) : $this->pageURL . ($this->page);
        if (strlen($this->getQuery())) {
            $link .= "?" . $this->getQuery();
        }
        return array(
            "link" => $link,
            "class" => ($this->page <= 1) ? "pager_disable" : ""
        );
    }
    public function getPagerParam()
    {
        $last_page = ceil($this->total / $this->limit);
        $pagers = range(
            max(1, min($this->page - 4, $last_page - 9)),
            max(1, min($last_page, max(1, $this->page - 4) +9))
        );

        return array(
            "url" => $this->pageURL,
            "current" => $this->page,
            "list" => $pagers,
            "query" => $this->query,
        );
    }
    public function getSelectArray()
    {
        $pagers = range(1, $this->getLastPage());

        $array = array();
        foreach ($pagers as $page) {
            $array[ $page ] = $page;
        }

        return $array;
    }
    public function getLastPage()
    {
        return $this->limit > 0 ? max(1, ceil($this->total / $this->limit)) : 1 ;
    }

    /**
     * ?????????????????????
     *
     * ?????????
     * <!-- soy:id="count_start" /--> - <!-- soy:id="count_end" /--> of <!-- soy:id="count_max" /-->
     * page <!-- soy:id="current_page" /--> / <!-- soy:id="last_page" /-->
     * <!-- soy:id="pager_list" -->
     *   <a href="" soy:id="target_link" >1</a>
     * <!-- /soy:id="pager_list" -->
     *
     */
    public function buildPager($htmlObj)
    {
        $pager = $this;

        //??????????????????
        $htmlObj->addLabel("count_start", array(
            "text" => $pager->getStart()
        ));
        $htmlObj->addLabel("count_end", array(
            "text" => $pager->getEnd()
        ));
        $htmlObj->addLabel("count_max", array(
            "text" => $pager->getTotal()
        ));

        //????????????
        $htmlObj->addLabel("current_page", array(
            "text" => $pager->getPage()
        ));
        $htmlObj->addLabel("last_page", array(
            "text" => $pager->getLastPage()
        ));

        //?????????????????????????????????
        $htmlObj->addModel("has_multi_page", array(
            "visible" => ($pager->getLastPage() > 1)
        ));
        $htmlObj->addModel("has_prev", array(
            "visible" => ($pager->getStart() != 1)
        ));
        $htmlObj->addModel("has_next", array(
            "visible" => ($pager->getEnd() != $pager->getTotal())
        ));

        //????????????????????????
        $htmlObj->addLink("next_pager", $pager->getNextParam());
        $htmlObj->addLink("prev_pager", $pager->getPrevParam());
        $htmlObj->createAdd("pager_list", "SimplePager", $pager->getPagerParam());

        //????????????????????????
        $htmlObj->addForm("pager_jump", array(
            "method" => "get",
            "action" => $pager->getPageURL()
        ));
        $htmlObj->addSelect("pager_select", array(
            "name" => "page",
            "options" => $pager->getSelectArray(),
            "selected" => $pager->getPage(),
            "onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
        ));
    }

    public function getQuery()
    {
        return $this->query;
    }
    public function setQuery($query)
    {
        $this->query = $query;
    }
}
class SimplePager extends HTMLList
{
    private $url;
    private $current;
    private $query;

    protected function populateItem($bean)
    {
        $url = $this->url . $bean;
        if (strlen($this->query)) {
            $url .= "?" . $this->query;
        }

        $this->addLink("target_link", array(
            "text" => $bean,
            "link" => $url,
            "class" => ($this->current == $bean) ? "btn btn-primary" : "btn btn-default"
        ));
    }

    public function getUrl()
    {
        return $this->url;
    }
    public function setUrl($url)
    {
        if ($url[strlen($url)-1] != "/") {
            $url .= "/";
        }
        $this->url = $url;
    }
    public function getCurrent()
    {
        return $this->current;
    }
    public function setCurrent($cuttent)
    {
        $this->current = $cuttent;
    }
    public function setQuery($query)
    {
        $this->query = $query;
    }
}
