<?php

namespace Pto\Bank\Storages;

interface IStorage
{

    public function import(array $data, $default);
}
