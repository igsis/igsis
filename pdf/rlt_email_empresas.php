<?php

//require '../include/';
require_once("../funcoes/funcoesConecta.php");
require_once("../funcoes/funcoesGerais.php");
require_once("../funcoes/funcoesSiscontrat.php");

//CONEXÃO COM BANCO DE DADOS
$conexao = bancoMysqli();

//CONSULTA
$id_ped=$_GET['id'];
$modeloEmail = $_GET['modelo'];
$idUsuario = $_GET['idUsuario'];

switch ($modeloEmail) {
    case 'empresas':
        $item4 = "Declaração do Simples Nacional (para ser assinada pelo(a) representante legal, somente em caso de Empresa optante pelo Simples Nacional).";
        break;
    case 'cooperativas':
        $item4 = "Documento comprobatório quanto a isenção ou imunidade de impostos.";
        break;
    case 'associacoes':
        $item4 = "Declaração de Associação sem fins lucrativos.";
        break;
}

$pedido = siscontrat($id_ped);
$pj = siscontratDocs($pedido['IdProponente'],2);
$rep01 = siscontratDocs($pj['Representante01'],3);
$pj = siscontratDocs($pedido['IdProponente'],2);
$usuario = $conexao->query("SELECT nomeCompleto,email FROM ig_usuario WHERE idUsuario = '$idUsuario'")->fetch_assoc();

dataPagamento($id_ped);

$id = $pedido['idEvento'];
$Objeto = $pedido["Objeto"];
$Periodo = $pedido["Periodo"];
$NumeroProcesso = $pedido["NumeroProcesso"];


$ano=date('Y');

$codPed = "";

$dataAtual = date('d/m/Y');

// Representante01

$rep01Nome = $rep01["Nome"];


// GERANDO O WORD:
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=$dataAtual - Processo SEI $NumeroProcesso - Email". ucfirst($modeloEmail) .".doc");

?>

<html>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">
<body>

<p style="text-align:justify">Prezado(a) Senhor(a) <?= $rep01Nome?>,</p>
<p>&nbsp;</p>
<p style="text-align:justify">Tendo em vista a apresentação <?= $Objeto?>, na data/período de <?= $Periodo?>, encaminho em anexo, para fins de pagamento, os itens abaixo relacionados:</p>
<p style="text-align:justify">a) Recibo da nota de empenho (para ser assinado pelo(a) representante legal da Empresa);</p>
<p style="text-align:justify">b) Pedido de pagamento (para ser assinado pelo(a) representante legal);</p>
<p style="text-align:justify">c) Instruções para Emissão da Nota Fiscal Eletrônica;</p>
<p style="text-align:justify">d) <?=$item4?></p>
<p style="text-align:justify">Para fins de arquivamento da empresa, segue também o Anexo e a Nota de Empenho da referida contratação.  </p>
<p style="text-align:justify">Informo que a documentação acima citada deverá ser devolvida digitalizada, <strong>somente através do e-mail <?= $usuario['email'] ?>, em até 48 horas, impreterivelmente.</strong></p>
<p>&nbsp;</p>
<p style="text-align:justify">Atenciosamente,</p>
<p>&nbsp;</p>
<p><?=$usuario['nomeCompleto']?><br>
SMC / Pagamentos Artísticos</p>
<!--<p>Tel: (11) 3397-0191</p>-->
<p>&nbsp;</p>
</body>
</html>