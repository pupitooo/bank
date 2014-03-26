<?php

namespace Pto\Bank;

use Pto\Bank\Downloaders;
use Pto\Bank\Storages;
use Pto\Objects\Currency;
use Nette\Http\SessionSection;
use Nette\Http\Request;
use Nette\Templating\Template;

/**
 * Bank
 *
 * @author Petr Poupě
 */
class Bank
{

    const DOWNLOAD_METHOD_CNB = "CNB";
    const DOWNLOAD_METHOD_ECB = "ECB";

//------------------------------------------------------------------------------

    /** @var Storages\Storage */
    private $storage;

    /** @var \Nette\Http\SessionSection */
    private $session;

    /** @var \Nette\Http\Request */
    private $request;

    /** @var Downloaders\IDownloadRates */
    private $downloader;

//------------------------------------------------------------------------------

    /** @var string */
    private $default = NULL;

    /** @var Currency[] */
    private $currencies = array();

//------------------------------------------------------------------------------

    public function __construct(Storages\Storage $storage, Request $request, SessionSection $session)
    {
        $this->storage = $storage;
        $this->session = $session;
        $this->request = $request;
    }

    private function init()
    {
        $data = $this->getDownload()->download();
        $this->storage->import($data, $this->getDefault());
        $this->session->defaultCurrency = $this->getDefault();
    }

    /**
     * Create or Get Currency
     * @return Currency
     */
    public function loadCurrency($code, $rate = NULL, $options = array())
    {
        $code = Currency::code($code);
        if (isset($this->currencies[$code])) {
            return $this->currencies[$code];
        } else {
            return $this->setCurrency($code, $rate, $options);
        }
    }

    /**
     * Only for create new items
     * @param type $code
     * @param type $rate
     * @param type $options
     * @return \Pto\Bank\Currency
     */
    private function setCurrency($code, $rate = NULL, $options = NULL)
    {
        if ($this->default === NULL) {
            $this->default = $code;
            $rate = 1;
        } elseif ($rate === NULL) {
            $rate = $this->getActualRate($code);
        }

        $currency = new Currency($code, $rate);
        $currency->setProfil($options);

        $this->currencies[$code] = $currency;
        return $currency;
    }
    
    public function setCurrencyOptions($code, $rate)
    {
        $code = Currency::code($code);
        if (isset($this->currencies[$code])) {
            $this->currencies[$code]->setRate($rate);
        } else {
            throw new BankException("This currency isn't set");
        }
    }

    public function getDefault()
    {
        if ($this->default === NULL) {
            throw new BankException("Must be set one currency at least");
        }
        return $this->default;
    }

    public function setDownload($method)
    {
        switch ($method) {
            case self::DOWNLOAD_METHOD_CNB:
                $this->downloader = new Downloaders\CnbDownloader;
                $this->downloader->addAnother();
                break;
            case self::DOWNLOAD_METHOD_ECB:
            default:
                $this->downloader = new Downloaders\EcbDownloader;
                break;
        }
    }

    /**
     * 
     * @return Downloaders\IDownloadRates
     */
    private function getDownload()
    {
        if ($this->downloader === NULL) {
            $this->setDownload(self::DOWNLOAD_METHOD_ECB);
        }
        return $this->downloader;
    }

    private function getActualRate($code)
    {
        $rate = $this->getRateFromStorage($code);
        if ($rate !== NULL) {
            return $rate;
        } else {
            $this->init();
            $rate = $this->getRateFromStorage($code);
            if ($rate !== NULL) {
                return $rate;
            } else {
                throw new BankException("This currency isn't exist in selected download method. Try another method.'");
            }
        }
    }

    private function getRateFromStorage($code)
    {
        if ($this->storage[$code] instanceof Currency) {
            return $this->storage[$code]->getRate();
        } else {
            return NULL;
        }
    }

    /**
     * transfer number by exchange rate
     * @param double|int|string $price number
     * @param string|FALSE $from default currency, FALSE no transfer
     * @param string $to output currency
     * @param int $round number round
     * @return double
     */
    public function change($price, $from = NULL, $to = NULL, $round = NULL)
    {
        $price = (float) $price;

        $to = $to ? $to : $this->getDefault();

        if ($from !== FALSE && $to) {
            $from = $from ? $from : $this->getDefault();

            if (Currency::code($to) != Currency::code($from)) {
                $fromCurrency = $this->loadCurrency($from);
                $toCurrency = $this->loadCurrency($to);

                $price *= $toCurrency->getRate() / $fromCurrency->getRate();
            }
        }

        if ($round !== NULL) {
            $price = round($price, $round);
        }

        return $price;
    }

    /**
     * count, format price and set vat
     * @param number $number price
     * @param string|bool $from FALSE currency doesn't counting, NULL set actual
     * @param string $to output currency, NULL set actual
     * @return number string
     */
    public function format($number, $from = NULL, $to = NULL)
    {
        $to = $to ? $to : $this->getDefault();
        $number = $this->change($number, $from, $to, NULL);
        $toCurrency = $this->loadCurrency($to);
        return $toCurrency->profil->render($number);
    }
    
    /**
     * Alias for format
     * @param type $number
     * @param type $to
     * @return type
     */
    public function formatTo($number, $to = NULL)
    {
        return $this->format($number, NULL, $to);
    }

    /**
     * create helper to template
     */
    public function registerAsHelper(Template $template)
    {
        $template->registerHelper('currency', callback($this, 'format'));
        $template->registerHelper('currencyTo', callback($this, 'formatTo'));
        $template->bank = $this;
    }

}
