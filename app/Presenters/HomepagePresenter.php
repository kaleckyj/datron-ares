<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{

    public function renderDefault($ico): void
    {
        if ($ico === NULL) {
            $this->template->form = NULL;
        } else {
            $url = "https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi?ico=" . $ico;
            $xml = simplexml_load_file($url);

            $data = new \STDClass();

            if ($xml->xpath("//are:Pocet_zaznamu")[0] == 0) $data->err = "IČO nebylo nalezeno!";
            else {
                $data->ico = $ico;
                $data->name = $xml->xpath("//are:Obchodni_firma")[0];
                $data->city = $xml->xpath("//dtt:Nazev_obce")[0];
                $data->street = $xml->xpath("//dtt:Nazev_ulice")[0];
                $data->snum = $xml->xpath("//dtt:Cislo_domovni")[0];
                $data->psc = $xml->xpath("//dtt:PSC")[0];
            }

            $this->template->form = $data;
        }
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
        $this->forward('this', $data->ico);
	}

}
