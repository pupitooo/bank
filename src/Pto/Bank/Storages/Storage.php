<?php

namespace Pto\Bank\Storages;

use \Nette\Caching;
use Pto\Bank\BankException;
use Pto\Objects\Currency;

class Storage extends Caching\Cache implements IStorage
{

    /**
     * one per day
     * time for refresh HH:MM
     * @var string
     */
    protected $hourRefresh;

    public function __construct(Caching\IStorage $storage, $hour = '14:45')
    {
        parent::__construct($storage, "Pto.Bank");
        $this->hourRefresh = $hour;
    }

    /**
     *
     * @param array $data
     * @param string $default currency
     */
    public function import(array $data, $default)
    {
        if (isset($data[0])) {
            $this->save(0, $data[0]);
            unset($data[0]);
        }

        $this->recalculateRates($data, $default);

        //default set first
        $def = $data[$default];
        unset($data[$default]);
        $data = array($default => $def) + $data;

        foreach ($data as $key => $val) {
            $dp = NULL;
            if (!($val instanceof Currency)) {
                throw new BankException('Must be class Currency.');
            }
            if ($key == $default) {
                $dt = new \Nette\DateTime('tomorrow');
                if ($this->hourRefresh) {
                    list($hour, $min) = explode(':', $this->hourRefresh);
                    $dt->setTime($hour, $min, 0);
                }
                $dp = array(Caching\Cache::EXPIRATION => $dt);
            }
            $this->save($key, $val, $dp);
        }
    }

    private function recalculateRates(array &$data, $default)
    {
        if (!isset($data[$default]) || !($data[$default] instanceof Currency)) {
            throw new BankException("Default currency isn't in currency list. Try another list.");
        }

        if ($data[$default] instanceof Currency && $data[$default]->isRate(1)) {
            return;
        }

        $correction = $data[$default]->getRate();
        foreach ($data as $key => $value) {
            if ($value instanceof Currency && $value->getRate() > 0) {
                $data[$key]->setRate($correction / $value->getRate());
            } else {
                throw new BankException('Must be class Currency.');
            }
        }
    }

}
