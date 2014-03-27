Pupitooo/Bank
===========================

Bank is PHP script works with currencies. This extension is for [Nette framework 2+](http://nette.org/).


Requirements
------------

Pupitooo\Bank requires PHP 5.3.2 or higher.

- [Nette Framework 2.1.x](https://github.com/nette/nette)
- [Kdyby\CUrl](https://github.com/kdyby/curl)
- [Pupitooo\Helpers](https://github.com/pupitooo/helpers)
- [Pupitooo\Objects](https://github.com/pupitooo/objects)


Installation
------------

The best way to install Pupitooo/Bank is using  [Composer](http://getcomposer.org/):

```sh
$ composer require pupitooo/bank:@dev
```

Example NEON config
-------------------
<pre>
services:
    - Pto\Bank\Storages\Storage
    - Nette\Http\SessionSection(@session, 'bank')
    bank:
            class: Pto\Bank\Bank
            setup: 
                    - loadCurrency('EUR', NULL, {mask: '1 S', thousand: ' ', point: ',', zeroClear: FALSE, decimal: 2, symbol: € })
                    - loadCurrency('CZK', NULL, {mask: '1 S', thousand: ' ', point: ',', zeroClear: FALSE, decimal: 2, symbol: Kč})
                    - loadCurrency('USD')
</pre>

Example Nette 2.1 use
-------------------
In Presenter

<pre>
    /** @var \Pto\Bank\Bank @inject */
    public $bank;

    protected function startup()
    {
        parent::startup();

        $bank = $this->bank;

        $bank->setDownload(\Pto\Bank\Bank::DOWNLOAD_METHOD_CNB);

        $bank->loadCurrency("CZK")
                ->setRate(27.8);
        $bank->loadCurrency("USD", NULL)
                ->setProfil(array('mask' => 'S 1', 'thousand' => ',', 'point' => '.', 'zeroClear' => FALSE, 'decimal' => 2, 'symbol' => '$'));
        $bank->loadCurrency("GBP", NULL, array('mask' => 'S 1', 'thousand' => ',', 'point' => '.', 'zeroClear' => FALSE, 'decimal' => 2, 'symbol' => '£'));
    }

    public function actionDefault()
    {
        $bank = $this->bank;

        Nette\Diagnostics\Debugger::barDump($bank->change(1, "EUR", "CZK"));
        Nette\Diagnostics\Debugger::barDump($bank->change(1, "EUR", "GBP"));
        Nette\Diagnostics\Debugger::barDump($bank->change(1, "EUR", "HUF"));

        Nette\Diagnostics\Debugger::barDump($bank->format(1, "EUR"));
        Nette\Diagnostics\Debugger::barDump($bank->format(1, "EUR", "CZK"));
        Nette\Diagnostics\Debugger::barDump($bank->format(1, "EUR", "USD"));
        Nette\Diagnostics\Debugger::barDump($bank->format(1, "EUR", "GBP"));
        
        \Nette\Diagnostics\Debugger::barDump($bank->getDefault());
        \Nette\Diagnostics\Debugger::barDump($bank->getActualRate("EUR"));
        \Nette\Diagnostics\Debugger::barDump($bank->getActualRate("CZK"));
        \Nette\Diagnostics\Debugger::barDump($bank->getActualRate("GBP"));
        \Nette\Diagnostics\Debugger::barDump($bank->getActualRate("USD"));
        \Nette\Diagnostics\Debugger::barDump($bank->getActualRate("HUF"));
    }
</pre>

In Latte
<pre>
    <p>{1|currency} = {1|currencyTo:"EUR"}</p>
    <p>{1|currency} = {1|currencyTo:"CZK"}</p>
    <p>{1|currency} = {1|currencyTo:"USD"}</p>
    <p>{1|currency} = {1|currencyTo:"GBP"}</p>

    <p>{1|currency:"CZK"} = {1|currency:"CZK":"EUR"}</p>
    <p>{1|currency:"USD"} = {1|currency:"USD":"EUR"}</p>
    <p>{1|currency:"GBP"} = {1|currency:"GBP":"USD"}</p>
</pre>


-----

Repository [http://github.com/pupitooo/bank](http://github.com/pupitooo/bank).
