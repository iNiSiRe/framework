<?php

namespace Framework\Module\Administration;

abstract class Page
{
    protected $listFields = [];
    protected $editFields = [];

    abstract public function getEntity();

    /**
     * @return array
     */
    public function getListFields()
    {
        return $this->listFields;
    }

    /**
     * @return array
     */
    public function getEditFields()
    {
        return $this->editFields;
    }
}