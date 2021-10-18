<?php
/**
 * 記事の履歴を取得します
 * @init entryId
 * @init historyId
 * @attribute EntryHistory
 */
class HistoryDetailAction extends SOY2Action
{
    private $entryId;
    private $historyId;

    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;
    }
    public function setHistoryId($id)
    {
        $this->historyId = $id;
    }

    protected function execute(SOY2ActionRequest &$request, SOY2ActionForm &$form, SOY2ActionResponse &$response)
    {
        try {
            $history = SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic")->getHistory($this->historyId);
            if ($history->getEntryId() != $this->entryId) {
                return SOY2Action::FAILED;
            }

            $this->setAttribute("EntryHistory", $history);
            return SOY2Action::SUCCESS;
        } catch (Exception $e) {
            return SOY2Action::FAILED;
        }
    }
}
