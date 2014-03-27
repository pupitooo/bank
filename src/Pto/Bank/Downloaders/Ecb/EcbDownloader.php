<?php

namespace Pto\Bank\Downloaders\Ecb;

use Pto\Bank\Downloaders\DownloadRates;
use Pto\Objects\Currency;

class EcbDownloader extends DownloadRates
{

    /**
     * url where download rating
     * @var const
     */
    const URL_DAY = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * include czech rating !important
     * @var const
     */
    const ITEM_EUR = 'EUR|1';

    /**
     * @var const delimiter in self::ITEM_EUR
     */
    const PIPE = '|';
    const CODE = 0;
    const RATE = 1;

    protected $link = self::URL_DAY;

    /**
     * download resource
     * @return array
     */
    public function download()
    {
        $xml = simplexml_load_string($this->getData());
        return $this->save($xml);
    }

    protected function save($xml)
    {
        $code = array(new \Nette\DateTime((string) $xml->Cube->Cube["time"]));
        
        $ex = explode(self::PIPE, self::ITEM_EUR);
        if (count($ex) === 2 && $ex[self::RATE] >= 0) {
            $currCode = Currency::code($ex[self::CODE]);
            $code[$currCode] = new Currency($currCode, $ex[self::RATE]);
        }
        
        foreach ($xml->Cube->Cube->Cube as $rate) {
            $currCode = Currency::code((string) $rate["currency"]);
            $rate = (string) $rate["rate"];
            $code[$currCode] = new Currency($currCode, $rate);
        }
        return $code;
    }

    /**
     * data downloaded by CUrl
     * @return string
     */
    protected function curl()
    {
        $curl = $this->getCurl();
        $curl->setUrl($this->link);

        if (!$curl->isOk) {
            return NULL;
        } else {
            $curl->execute();
            return $curl->response;
        }
    }

}
