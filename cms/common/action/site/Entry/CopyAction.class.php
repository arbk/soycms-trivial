<?php
SOY2::import("base.validator.SOY2ActionFormValidator_ArrayValidator");

/**
 * エントリーを複製します
 */
class CopyAction extends SOY2Action
{
    /**
     * Entry.idを直接指定
     */
    private $id;

    public function setId($id)
    {
        $this->id = $id;
    }

    protected function execute(SOY2ActionRequest &$request, SOY2ActionForm &$form, SOY2ActionResponse &$response)
    {
        if ($this->id) {
            $entryIds = array($this->id);
        } else {
            if ($form->hasError()) {
                foreach ($form as $key => $value) {
                    $this->setErrorMessage($key, $form->getErrorString($key));
                }
                return SOY2Action::FAILED;
            }

            $entryIds = $form->getEntry();
        }

        $logic = SOY2LogicContainer::get("logic.site.Entry.EntryLogic");
        $historyLogic = SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic");

        try {
            foreach ($entryIds as $id) {
                $entry = $logic->getById($id);
                $entry->setTitle($entry->getTitle()." ".CMSMessageManager::get("ENTRY_COPY_TITLE"));
                $entry->setIsPublished(false);
//              $entry->setCdate(time());
                $entry->setAlias(empty($entry->getAlias())?"":$entry->getAlias()."_cp");

                $newId = $logic->create($entry);

                $labelIds = $logic->getLabelIdsByEntryId($id);
                foreach ($labelIds as $labelId) {
                    $logic->setEntryLabel($newId, $labelId);
                }

                //CMS:PLUGIN callEventFunction
                CMSPlugin::callEventFunc('onEntryCopy', array($id,$newId));

                //history
                $historyLogic->onCopy($entry, $id);
            }

            return SOY2Action::SUCCESS;
        } catch (Exceptino $e) {
            return SOY2Action::FAILED;
        }
    }
}

class CopyActionForm extends SOY2ActionForm
{
    private $entry;

    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @validator Array {"type":"number"}
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }
}
