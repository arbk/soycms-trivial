<?php

class FooterPage extends CMSHTMLPageBase
{
    public function execute()
    {
        $this->addLabel("cms_name", array(
            "text" => CMSUtil::getCMSName()
        ));

        // $year = date("Y", SOYCMS_BUILD_TIME);
        // if ($year > 2007) {
        //     $year = "2007-" . $year;
        // }
        // $copyright = $this->getMessage("COMMON_FOOTER_COPYRIGHT", array("YEAR" => $year));

        $this->createAdd("copyright", "HTMLLabel", array(
            "html" => $this->getMessage("COMMON_FOOTER_COPYRIGHT")
        ));

        $this->addLabel("version", array(
                "text" => SOYCMS_VERSION,
        ));

        $this->addLabel("developer_name", array(
            "text" => CMSUtil::getDeveloperName()
        ));
    }
}
