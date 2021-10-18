<?php

class UploadPage extends CMSWebPageBase
{
    public function doPost()
    {
        if (soy2_check_token()) {
            $result = $this->run("Template.TemplateUploadAction");

            if ($result->success()) {
                $this->addMessage("PAGE_TEMPLATE_UPLOAD_SUCCESS");
            } else {
                $this->addErrorMessage("PAGE_TEMPLATE_UPLOAD_FAILED");
            }
        } else {
            $this->addErrorMessage("PAGE_TEMPLATE_UPLOAD_FAILED");
        }

        echo '<!DOCTYPE html><html><head><title>-</title><script type="text/javascript">parent.location.reload();</script></head></html>';
    }

    public function __construct()
    {
        parent::__construct();
        $this->createAdd("upload_form", "HTMLForm");

        if (CMSUtil::checkZipEnable(true)) {
            DisplayPlugin::visible("enable_zip");
            DisplayPlugin::hide("disable_zip");
        } else {
            DisplayPlugin::hide("enable_zip");
            DisplayPlugin::visible("disable_zip");
        }
    }
}
