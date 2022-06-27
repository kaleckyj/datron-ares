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
        date_default_timezone_set('Europe/Prague');
        $date = date("Y-m-d") . 'T' . date("H:i:s");

        try {
            $client = new \SoapClient('https://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/basic.wsdl');

            $xml = new \SimpleXMLElement("<Ares_dotazy></Ares_dotazy>");

            $xml->addAttribute('dotaz_datum_cas', $date);
            $xml->addAttribute('dotaz_pocet', '1');
            $xml->addAttribute('dotaz_typ', 'Basic');
            $xml->addAttribute('vystup_format', 'XML');
            $xml->addAttribute('validation_XSLT', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer/v_1.0.0/ares_answer.xsl');
            $xml->addAttribute('user_mail', 'honza.kalecky@centrum.cz');
            $xml->addAttribute('answerNamespaceRequired', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3');
            $xml->addAttribute('Id', 'Ares_dotaz');

            $query = $xml->addChild('Dotaz');
            $query->addChild('Pomocne_ID', '1');
            $query->addChild('ICO', $data->ico);

            $xml->asXML("filename.xml");

            //$str = '<are:Ares_dotazy xmlns:are="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0 http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0/ares_request_orrg.xsd" dotaz_datum_cas="' . $date .'" dotaz_pocet="1" dotaz_typ="Basic" vystup_format="XML" validation_XSLT="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer/v_1.0.0/ares_answer.xsl" user_mail="honza.kalecky@centrum.cz" answerNamespaceRequired="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3" Id="Ares_dotaz"><Dotaz><Pomocne_ID>1</Pomocne_ID><ICO>00025500</ICO></Dotaz></are:Ares_dotazy>';

            //$response = $client->GetXmlFile($str);
            $response = $client->GetXmlFile($xml->asXML());

            var_dump($response);

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        //$this->forward('this', $res);
	}

}
