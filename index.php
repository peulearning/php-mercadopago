<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Configurar o Access Token
MercadoPagoConfig::setAccessToken(MERCADOPAGO_ACCESS_TOKEN);// Use seu token de teste aqui
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

// Inicializar o cliente de pagamento
$client = new PaymentClient();

try {
    // Usar 'pix' como método de pagamento
    $request = [
        "transaction_amount" => 100.00,
        "description" => "Pagamento via Pix",
        "payment_method_id" => "pix", // Método de pagamento Pix
        "payer" => [
            "email" => "user@test.com",
        ],
    ];

    // Criar as opções de requisição com cabeçalho de idempotência
    $request_options = new RequestOptions();
    $request_options->setCustomHeaders(["X-Idempotency-Key: UNIQUE_ID_" . time()]); // Use um valor único aqui

    // Fazer a requisição de pagamento
    $payment = $client->create($request, $request_options);

    // Verificar o status do pagamento e exibir o QR Code
    if ($payment->status === 'pending') {
        echo "Pagamento pendente. ID do pagamento: " . $payment->id . PHP_EOL;
        echo 'Link para pagamento via Pix: ' . $payment->point_of_interaction->transaction_data->ticket_url . PHP_EOL;
        echo 'QRCode (Base64): <img src="data:image/png;base64,' . $payment->point_of_interaction->transaction_data->qr_code_base64 . '" alt="QR Code" />' . PHP_EOL;

        $access_link = $payment->point_of_interaction->transaction_data->ticket_url;
        echo 'Link de acesso ao pagamento: <a href="' . $access_link . '" target="_blank">' . $access_link . '</a>' . PHP_EOL;

    } else {
        echo 'Erro ao criar pagamento: ' . $payment->status_detail . PHP_EOL;
    }

} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Content: ";
    var_dump($e->getApiResponse()->getContent());
    echo "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}


?>