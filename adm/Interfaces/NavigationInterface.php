<?php

namespace Adm\Interfaces;

interface NavigationInterface
{
    /**
     * Returns CMS navigation items
     * 
     * @return array
     */
    public function getNavItems() : array;
}