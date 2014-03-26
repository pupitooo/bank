<?php

namespace Pto\Bank\Downloaders;

interface IDownloadRates
{

    const EUR = "eur";
    const CZK = "czk";

    public function download();

    public function setDefault($code);
}
