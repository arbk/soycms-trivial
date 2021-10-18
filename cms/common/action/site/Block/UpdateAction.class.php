<?php

class UpdateAction extends SOY2Action
{
    private $id;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function execute($request, $form, $response)
    {
        $dao = SOY2DAOFactory::create("cms.BlockDAO");
        $block = $dao->getById($this->id);

        $component = $block->getBlockComponent();
        SOY2::cast($component, $form->object);
        if (strlen($form->object->displayCountFrom) === 0) {
            $component->setDisplayCountFrom(null);
        }
        if (strlen($form->object->displayCountTo) === 0) {
            $component->setDisplayCountTo(null);
        }

        $block->setObject($component);

        $dao->updateObject($block);

        CMSUtil::notifyUpdate();

        $this->setAttribute("Block", $block);

        return SOY2Action::SUCCESS;
    }
}

class UpdateActionForm extends SOY2ActionForm
{
    public $object;

    public function setObject($object)
    {
        $this->object = (object)$object;
    }
}
