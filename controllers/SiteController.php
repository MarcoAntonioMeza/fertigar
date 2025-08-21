<?php
namespace app\controllers;

use Yii;

class SiteController extends AppController
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if(!isset(Yii::$app->user->identity))
            return $this->renderPartial('index-main');
        else
            return $this->render('index');
    }

    public function actionPermisos()
    {
        return $this->render('permisos');
    }

    public function actionAcercaDe()
    {
        return $this->render('acerca-de');
    }

    public function actionCrearFactura()
    {
      
        $params = [
            'Serie' => '01',
            'Folio' => '100',
            // 'Date' => '2022-03-30',
            'PaymentForm' => '01',
            'PaymentConditions' => 'CREDITO A SIETE DIAS',
            'Currency' => 'MXN',
            'CfdiType' => 'I',
            'PaymentMethod' => 'PUE',
            'ExpeditionPlace' => '72160',
            // 'Receiver' =>
            // [
            //     'Rfc'=> 'URE180429TM6',
            //     'CfdiUse'=> 'G03',
            //     'Name'=> 'UNIVERSIDAD ROBOTICA ESPAÑOLA',
            //     'FiscalRegime'=> '603',
            //     'TaxZipCode' => '65000',
            //     'Address'=>
            //     [
            //         'Street' => 'Guadalcazar del receptor',
            //         'ExteriorNumber' => '300',
            //         'InteriorNumber' => 'A',
            //         'Neighborhood'=> 'Las lomas',
            //         'ZipCode' => '65000',
            //         'Municipality' => 'San Luis Potosi',
            //         'State' => 'San Luis Potosi',
            //         'Country' => 'México'
            //     ]
            // ],
            "Receiver" => [
                "Rfc"=> "XAXX010101000",
                "Name"=> "PUBLICO EN GENERAL",
                "CfdiUse"=> "S01",
                "FiscalRegime"=> "616",
                "TaxZipCode" => 72160
            ],
            'Items' => [
                [
                    'ProductCode' => '10101504',
                    'IdentificationNumber' => 'EDL',
                    'Description' => 'Estudios de viabilidad',
                    'Unit' => 'NO APLICA',
                    'UnitCode' => 'MTS',
                    'UnitPrice' => 50.001000,
                    'Quantity' => 2.0,
                    'Subtotal' => 100.002000,
                    'TaxObject' => '02',
                    'Taxes' => [
                        [
                            'Total' => 16.000320,
                            'Name' => 'IVA',
                            'Base' => 100.002000,
                            'Rate' => 0.160000,
                            'IsRetention' => false,
                        ],
                    ],
                    'Total' => 116.00232,
                ],
            ],
            "GlobalInformation" => [
                "Periodicity"=> "01",
                "Months"=> "08",
                "Year"=> 2025
            ]
        ];

        try {
            
            $response = Yii::$app->facturama->createInvoice($params);

            return $this->asJson($response);
        } catch (\Exception $e) {
            return $this->asJson([
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
 * Descargar factura como PDF
 */
public function actionDescargarFactura()
{
    try {
        $id = '_reKPFkRdQ9VoaYFozy2EA2';
        $pdf = Yii::$app->facturama->downloadInvoice('pdf', $id);
        // $myfile = fopen('factura'.$id.'.'.'pdf', 'a+');
        // fwrite($myfile, base64_decode(end($pdf)));
        // fclose($myfile);
        // printf('<pre>%s<pre>', var_export(true));

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/pdf');
        Yii::$app->response->headers->add('Content-Disposition', "attachment; filename=factura-{$id}.pdf");

        return  base64_decode(end($pdf));
    } catch (\Exception $e) {
        return $this->asJson(['error' => $e->getMessage()]);
    }
}

/**
 * Enviar factura por correo electrónico
 */
public function actionEnviarFactura()
{
    try {
        $resp = Yii::$app->facturama->sendInvoiceEmail("C3pGrFTF32_2OhDPeSaLgA2", "erickgaytan53@gmail.com");
        return $this->asJson($resp);
    } catch (\Exception $e) {
        return $this->asJson(['error' => $e->getMessage()]);
    }
}

/**
 * Enviar factura por correo electrónico
 */
public function actionCfdis()
{
    try {
        $facturas = Yii::$app->facturama->getClient()->get('Cfdi');
        echo "<pre>";
        foreach ($facturas as $factura) {
            print_r($factura);
             
        }

    } catch (\Exception $e) {
        return $this->asJson(['error' => $e->getMessage()]);
    }
}


    
}
