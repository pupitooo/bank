<?php

namespace Pto\Bank\Downloaders;

use Nette\Object;
use Kdyby\Curl\CurlWrapper;

/**
 * @author Petr Poupě
 */
abstract class DownloadRates extends Object implements IDownloadRates
{

    /** @var string[] */
    protected $links = array();

    /** @var \Nette\DateTime */
    protected $date;

    /** @var string */
    protected $default = self::EUR;

    /** @var CurlWrapper */
    private $curl;

    public function setDate(\Nette\DateTime $date = NULL)
    {
        $this->date = $date;
        return $this;
    }

    protected function getData()
    {
        return $this->curl();
    }

    abstract protected function curl();

    /**
     * @example
     * array(
     * 0 => additional information
     * 'CZK' => array(...)
     * )
     * @return array
     */
    abstract protected function save($data);

    /**
     * setup of proxy
     * @param CUrl $curl
     * @return void
     */
    public function setProxy($proxy, $port, $pass)
    {
        $curl = $this->getCurl();
        if ($curl) {
            $curl->setOptions(array(
                CURLOPT_PROXY => $proxy,
                CURLOPT_PROXYPORT => $port,
                CURLOPT_PROXYUSERPWD => $pass,
            ));
        }
        return $curl;
    }

    protected function getCurl()
    {
        if (!$this->curl) {
            $this->curl = new CurlWrapper;
        }
        return $this->curl;
    }

    public function setDefault($code)
    {
        $this->default = $code;
        return $this;
    }

}
