<?php

//require '../include/';
require_once("../funcoes/funcoesConecta.php");
require_once("../funcoes/funcoesGerais.php");
require_once("../funcoes/funcoesSiscontrat.php");

//CONEXÃO COM BANCO DE DADOS
$conexao = bancoMysqli();

//CONSULTA
$id_ped=$_GET['id'];
$idUsuario = $_GET['idUsuario'];
$pedido = siscontrat($id_ped);
$pf = siscontratDocs($pedido['IdProponente'],1);
$usuario = $conexao->query("SELECT nomeCompleto, email FROM ig_usuario WHERE idUsuario = '$idUsuario'")->fetch_assoc();

switch ($idUsuario){
    case "1389":
        $email = "andersonpagamentosartisticos@gmail.com";
        break;
    case "1125":
        $mail = "tomcontratos@gmail.com";
        break;
    case "1393":
        $email = "brunamotacontratos@gmail.com";
        break;
    case "1392":
        $email = "danielbarbosacontratos@gmail.com";
        break;
    case "1391":
        $email = "marianaoliveiracontratos@gmail.com";
        break;
    default:
        $email = "smc.pagamentosartisticos@gmail.com";
}

dataPagamento($id_ped);

$id = $pedido['idEvento'];
$Objeto = $pedido["Objeto"];
$Periodo = $pedido["Periodo"];
$NumeroProcesso = $pedido["NumeroProcesso"];
$dataAtual = date('d/m/Y');

// GERANDO O WORD:
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=$dataAtual - Processo SEI $NumeroProcesso - Email.doc");

?>

<html>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">
<body>

<p style="text-align:justify">Prezado(a) Senhor(a) <?= $pf['Nome']?>,</p>
<p>&nbsp;</p>
<p style="text-align:justify">Tendo em vista a apresentação <?= $Objeto?>, na data/período de <?= $Periodo?>, <?= $pf['Email'] ?>, encaminho em anexo, para fins de pagamento, os itens abaixo relacionados:</p>
<p style="text-align:justify">a) Recibo da nota de empenho (para ser assinado);</p>
<p style="text-align:justify">b) Pedido de pagamento (para ser assinado);</p>
<p style="text-align:justify">c) Recibo de pagamento (para ser assinado).</p>
<p style="text-align:justify">Para fins de arquivamento, segue também o Anexo e a Nota de Empenho da referida contratação.  </p>
<p style="text-align:justify">Informo que a documentação acima citada deverá ser devolvida digitalizada, <strong>somente através do e-mail <?= $email ?>, em até 48 horas, impreterivelmente.</strong></p>
<p>&nbsp;</p>
<p style="text-align:justify">Atenciosamente,</p>
<p>&nbsp;</p>
<p><?=$usuario['nomeCompleto']?><br>
SMC / Pagamentos Artísticos</p>
<!--<p>Tel: (11) 3397-0191</p>-->
<p>&nbsp;</p>
</body>
</html>