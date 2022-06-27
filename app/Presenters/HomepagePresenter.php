<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{

    public function renderDefault($res): void
    {
        if ($res === NULL) $this->template->form = NULL;
        else $this->template->form = $res;
    }

    protected function createComponentSearchForm(): Form
    {
        $form = new Form;

        $form->addText('ico', 'IČO:')
            ->setRequired()
            ->setMaxLength(8)
            ->addRule($form::PATTERN, '%label musí obsahovat pouze číslice', '[0-9]*');

        $form->addSubmit('send', 'Vyhledat firmu');

        $form->onSuccess[] = [$this, 'searchFormSucceeded'];

        return $form;
    }

    public function searchFormSucceeded(Form $form, $data): void
	{
        $url = "https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi?ico=" . $data->ico;
        $xml = simplexml_load_file($url);

        $res = new \STDClass();

        if ($xml->xpath("//are:Pocet_zaznamu")[0] == 0) $res->err = "IČO nebylo nalezeno";
        else {
            $res->ico = $data->ico;
            $res->name = $xml->xpath("//are:Obchodni_firma")[0];
            $res->city = $xml->xpath("//dtt:Nazev_obce")[0];
            $res->street = $xml->xpath("//dtt:Nazev_ulice")[0];
            $res->snum = $xml->xpath("//dtt:Cislo_domovni")[0];
            $res->psc = $xml->xpath("//dtt:PSC")[0];
        }

        $this->forward('this', $res);
	}

}
