<?php

namespace Adm\Crud\Handler;

class Copy extends Edit
{
    /**
     * {@inheritdoc}
     */
    protected function getFormAction(): string
    {
        return $this->getCrudUrl($this->getConfigProperty('url_slug'), 'store', null);
    }
}
