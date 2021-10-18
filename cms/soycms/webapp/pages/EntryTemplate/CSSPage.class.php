<?php

class CSSPage extends CMSWebPageBase
{
    public function __construct()
    {
        $result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateDetailAction")->run();
        $template = $result->getAttribute("entity");
        header("Content-Type: text/css");
        echo $template->getStyle();
        exit();
    }
}
