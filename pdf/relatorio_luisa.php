<?php
//require '../include/';
require_once("../funcoes/funcoesConecta.php");
require_once("../funcoes/funcoesGerais.php");
require_once("../funcoes/funcoesSiscontrat.php");
$con = bancoMysqli();

$dataAtual = date('Y:m:d H:i:s');

$sql = "SELECT
               pc.idPedidoContratacao,
               pc.idEvento,
               eve.nomeEvento,
               pc.tipoPessoa,
               pc.idPessoa,
               pc.integrantes,
               pc.valor,
               eve.dataEnvio,
               oco.dataEvento
        FROM igsis_pedido_contratacao AS pc
        INNER JOIN ig_evento AS eve ON eve.idEvento = pc.idEvento
        INNER JOIN (
            SELECT idEvento, MIN(dataInicio) AS dataEvento FROM ig_ocorrencia WHERE publicado = 1 GROUP BY idEvento
        ) AS oco ON eve.idEvento = oco.idEvento
        WHERE pc.publicado = 1 AND dataEnvio BETWEEN '2017-01-01' AND NOW()";
$query = $con->query($sql)->fetch_all(MYSQLI_ASSOC);

header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel");
header ("Content-Disposition: attachment; filename=$dataAtual processos_fiscal_suplente.xls" );

?>
<html>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">
<body>
<table class="table table-condensed" border="1">
    <thead>
    <tr class="list_menu">
        <th>Proponente</th>
        <th>Documento</th>
        <th>Nome do Evento</th>
        <th>Integrantes</th>
        <th>Primeira data de execução do Evento</th>
        <th>Valor Total</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($query as $dados) {
        ?>
        <tr>
            <?php if ($dados['tipoPessoa'] == 1 ):
                $proponente = recuperaDados('sis_pessoa_fisica', $dados['idPessoa'], 'Id_PessoaFisica');
                ?>
                <td><?=$proponente['Nome'] ?? ''?></td>
                <td><?=$proponente['CPF'] ?? ''?></td>
            <?php else:
                $proponente = recuperaDados('sis_pessoa_juridica', $dados['idPessoa'], 'Id_PessoaJuridica');
                ?>
                <td><?=$proponente['RazaoSocial'] ?? ''?></td>
                <td><?=$proponente['CNPJ'] ?? ''?></td>
            <?php endif; ?>
            <td><?=$dados['nomeEvento']?></td>
            <td><?=$dados['integrantes']?></td>
            <td><?=exibirDataBr($dados['dataEvento'])?></td>
            <td><?=dinheiroParaBr($dados['valor'])?></td>

        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
</body>
</html>