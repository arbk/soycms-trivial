<?php

class DownloadPage extends CMSWebPageBase
{
    public function __construct($arg)
    {
//      if(soy2_check_token()){
            parent::__construct();
            $id = @$arg[0];
        if ((null===$id)) {
            $this->jump("Template");
            exit;
        } else {
            $result = SOY2ActionFactory::createInstance("Template.TemplateDownloadAction", array("id"=>$id))->run();
        }
//      }else{
//              $this->jump("Template");
//      }
        exit;
    }
}
