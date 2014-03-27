<?php

namespace Pto\Bank\Downloaders;

use Pto\Objects\Currency;
use Pto\Helpers;

class CnbDownloader extends DownloadRates {

    /**
     * url where download rating
     * @var const
     */
    const URL1_DAY = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';
    const URL2_DAY = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_ostatnich_men/kurzy.txt';

    /**
     * param for another day DD.MM.YYYY
     */
    const URL_PARAM = '?date=';

    /**
     * include czech rating !important
     * @var const
     */
    const ITEM_CZK = 'Česká Republika|koruna|1|CZK|1';

    /**
     * @var const delimiter in self::ITEM_CZK
     */
    const PIPE = '|';
    const CODE = 3;
    const COUNTRY = 0; //only czech
    const NAME = 1; //only czech
    const HOME = 2;
    const TO = 4;

    protected $links = array(self::URL1_DAY);

    /**
     * download resource
     * @return array
     */
    public function download() {
        $data = explode("\n", Helpers\Math::stroke2point(trim($this->getData())));
        $firstLine = explode(' #', $data[0]);
        $data[0] = new \Nette\DateTime($firstLine[0]);
        $data[1] = self::ITEM_CZK;
        return $this->save($data);
    }

    public function addAnother() {
        $this->links[] = self::URL2_DAY;
    }

    protected function save($data) {
        $code = array($data[0]);
        unset($data[0]);
        foreach ($data as $val) {
            $ex = explode(self::PIPE, $val);
            if (count($ex) != 5 || $ex[self::TO] <= 0 || isset($code[$ex[self::TO]])) {
                continue;
            }

            $currCode = Currency::code($ex[self::CODE]);
            $obj = $code[$currCode] = new Currency($currCode, $ex[self::TO]);
            $obj->country = $ex[self::COUNTRY];
            $obj->name = $ex[self::NAME];
        }
        return $code;
    }

    /**
     * data downloaded by CUrl
     * @return string
     */
    protected function curl() {
        $curl = $this->getCurl();
        $list = NULL;
        foreach ($this->links as $key => $link) {
            $curl->setUrl($this->fillDate($link));

            if (!$curl->isOk) {
                continue;
            }

            $curl->execute();
            $list .= $curl->response;
        }
        return $list;
    }

    /**
     * apply date for download
     * @return void
     */
    private function fillDate($link) {
        if ($this->date) {
            $date = $this->date->format('d.m.Y');
            $link .= self::URL_PARAM . $date;
        }
        return $link;
    }

}
