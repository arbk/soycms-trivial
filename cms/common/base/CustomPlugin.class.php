<?php

class CustomPlugin extends PluginBase
{
    public function executePlugin($soyValue)
    {
        $this->setInnerHTML("<?php echo CMSPlugin::callCustomFieldFunctions('".$soyValue."'); ?>");
        return;
    }
}
