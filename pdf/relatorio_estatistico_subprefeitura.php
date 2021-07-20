<?php
//require '../include/';
require_once("../funcoes/funcoesConecta.php");
require_once("../funcoes/funcoesGerais.php");
require_once("../funcoes/funcoesSiscontrat.php");
$con = bancoMysqli();

$dataAtual = date('Y:m:d H:i:s');

$sql = "SELECT * FROM igsis_subprefeitura";
$subprefieturas = $con->query($sql)->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT id, representatividade_social AS 'pauta'
        FROM igsis_representatividade
        WHERE publicado = 1 ORDER BY representatividade_social";
$pautas = $con->query($sql)->fetch_all(MYSQLI_ASSOC);

header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel");
header ("Content-Disposition: attachment; filename=$dataAtual pauta_social_subprefeitura.xls" );

?>
<html>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">
<body>
<table class="table table-condensed" border="1">
    <thead>
    </thead>
    <tbody>
    <?php
    foreach ($subprefieturas as $subprefietura) {
        ?>
        <tr>
            <td colspan='2' style="font-weight: bold">
                <?= $subprefietura['id'] . ' - ' . $subprefietura['subprefeitura'] ?>
            </td>
        </tr>

        <?php foreach ($pautas as $pauta) {
            $sql = "SELECT COUNT(*)
                FROM ig_ocorrencia AS oc
                LEFT JOIN ig_evento AS ev ON oc.idEvento = ev.idEvento
                LEFT JOIN igsis_evento_representatividade AS er ON ev.idEvento = er.idEvento
                WHERE oc.publicado = 1 AND ev.publicado = 1 AND oc.subprefeitura_id = {$subprefietura['id']} AND er.idRepresentatividade = {$pauta['id']}";
            $valor = $con->query($sql)->fetch_row();
            ?>
            <tr>
                <td><?= $pauta['pauta'] ?></td>
                <td><?= $valor[0] ?></td>
            </tr>
        <?php }
    }
    ?>
    </tbody>
</table>
</body>
</html>