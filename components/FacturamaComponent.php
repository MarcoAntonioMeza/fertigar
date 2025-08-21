<?php
namespace app\components;

use Yii;
use yii\base\Component;
use Facturama\Client;

class FacturamaComponent extends Component
{
    public $username;
    public $password;

    private $_client;

    public function init()
    {
        parent::init();

        if (!$this->username || !$this->password) {
            throw new \Exception("Debes configurar usuario y contraseña de Facturama");
        }

        $this->_client = new Client($this->username, $this->password);
    }

    /**
     * Retorna el cliente de Facturama
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Ejemplo: obtener clientes de Facturama
     */
    public function getClients()
    {
        return $this->_client->get('clients');
    }

    /**
     * Ejemplo: crear cliente en Facturama
     */
    public function createClient($data)
    {
        return $this->_client->post('clients', $data);
    }
    /**
     * Crear una factura CFDI
     */
    public function createInvoice($data)
    {
        return $this->_client->post('3/cfdis', $data);
    }
    /**
     * Descargar factura en formato PDF o XML
     * 
     * @param string $format   'pdf' | 'xml' | 'html'
     * @param string $id     ID o UUID de la factura
     * @return mixed
     */
    public function downloadInvoice($format, $id)
    {
        // return $this->_client->get("cfdis/issued/{$type}/{$id}");
        // return $this->_client->get('cfdi/issued/'.$type.'/'.$id);
        
        $format = 'pdf';  //Formato del archivo a obtener(pdf,Xml,html)
        $type = 'issued'; // Tipo de comprobante a obtener (payroll | received | issued)
        // $id = 'ZP3zyOttPOEkC2DST-jjqQ2'; // Identificador unico de la factura
        
        $params = [];

        return $this->_client->get('cfdi/'.$format.'/'.$type.'/'.$id, $params);

    }
    // {"error":"Client error: `GET https://apisandbox.facturama.mx//cfdi/pdf/issued/OwMgofF7ZDEM60gerUXudw2` resulted in a `404 Not Found` response"}
                                //  https://apisandbox.facturama.mx//cfdi/pdf/issued/ZP3zyOttPOEkC2DST-jjqQ2
    /**
     * Enviar factura por correo
     * 
     * @param string $id
     * @param string $email
     * @return mixed
     */
    public function sendInvoiceEmail($id, $email)
    {
        $body = [];
        $params = [
        'cfdiType' => 'issued',
        'cfdiId' => $id,
        'email' => $email,
        ];

        // return $this->_client->post("cfdis/{$id}/email", [
        //     "email" => $email
        // ]);
        return $this->_client->post('Cfdi', $body, $params);
    }


}

