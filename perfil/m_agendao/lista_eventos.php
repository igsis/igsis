<?php
$con = bancoMysqli();
$idUsuario = $_SESSION['idUsuario'];
$nomeUsuario = recuperaUsuario($idUsuario)['nomeCompleto'];

$sqlConsultaEventos = "SELECT * FROM ig_evento
                        WHERE publicado = 1
                        AND (idUsuario = '$idUsuario') 
                        AND dataEnvio IS NULL ORDER BY idEvento DESC";
$eventos = $con->query($sqlConsultaEventos)->fetch_all(MYSQLI_ASSOC);

if(isset($_POST['apagar']))
{
    $idApagar = $_POST['apagar'];
    $sqlApagarEvento = "UPDATE ig_evento SET publicado = 0 WHERE idEvento = $idApagar";

    if($con->query($sqlApagarEvento)) {
        $mensagem = "Evento apagado com sucesso!";
        gravarLog($sqlApagarEvento);
    } else {
        $mensagem = "Erro ao apagar o evento...";
    }
}

$sqlConsultaEventos = "SELECT * FROM ig_evento
                        WHERE publicado = 1
                        AND (idUsuario = '$idUsuario') 
                        ORDER BY idEvento DESC";
$eventos = $con->query($sqlConsultaEventos)->fetch_all(MYSQLI_ASSOC);

foreach ($eventos as $evento) {
    if (strtolower($evento['statusEvento']) == "em elaboração") {
        $eventoCadastrados[] = $evento;
    } else {
        $eventoEnviados[] = $evento;
    }
}

include "include/menu.php";
?>

<section id="list_items" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="text-hide">
                    <h2>Eventos cadastrados por <?= $nomeUsuario ?>
                        <br><small>Eventos não cadastrados no módulo "Agendão" não podem ser editados</small>
                    </h2>
                    <h5><?php if(isset($mensagem)){echo $mensagem;} ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-offset-1 col-md-10"><hr/></div>
        <div class="row">
            <div class="col-md-offset-1 col-md-10">

                <ul class="nav nav-tabs">
                    <li class="nav active"><a href="#gravados" data-toggle="tab">Eventos Gravados</a></li>
                    <li class="nav"><a href="#enviados" data-toggle="tab">Eventos Enviados</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade in active" id="gravados">
                        <div class="table-responsive list_info">
                            <table class='table table-condensed'>
                                <thead>
                                <tr class='list_menu'>
                                    <td>Nome do evento</td>
                                    <td>Tipo de evento</td>
                                    <td>Data/Período</td>
                                    <td>Status do evento</td>
                                    <td width='10%'></td>
                                    <td width='10%'></td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($eventoCadastrados as $eventoCadastrado) {
                                    $tipoEvento = recuperaDados('ig_tipo_evento', $eventoCadastrado['ig_tipo_evento_idTipoEvento'], 'idTipoEvento')['tipoEvento']
                                    ?>
                                    <tr>
                                        <td class="list-description"><?=$eventoCadastrado['nomeEvento']?></td>
                                        <td class="list-description"><?=$tipoEvento?></td>
                                        <td class="list-description"><?= retornaPeriodo($eventoCadastrado['idEvento']) ?></td>
                                        <td class="list-description"><?=$eventoCadastrado['statusEvento']?></td>
                                        <?php
                                        if ($eventoCadastrado['ocupacao'] == 1) {
                                        ?>
                                            <td class='list_description'>
                                                <form method='POST' action='?perfil=agendao&p=evento_cadastra'>
                                                    <input type='hidden' name='idEvento' value='<?=$eventoCadastrado['idEvento']?>'>
                                                    <input type ='submit' class='btn btn-theme btn-block' value='carregar' <?= strtolower($eventoCadastrado['statusEvento']) == "em elaboração" ? "" : "disabled"?>>
                                                </form>
                                            </td>
                                            <td class='list_description'>
                                                <button id="btnApagar" class='btn btn-theme' type='button' data-toggle='modal' data-target='#confirmApagar' onclick="confirmApagar('<?=$eventoCadastrado['idEvento']?>', '<?=$eventoCadastrado['nomeEvento']?>');">
                                                    Apagar
                                                </button>
                                            </td>
                                        <?php
                                        } else {
                                        ?>
                                            <td class='list_description'>
                                                <a href='?perfil=agendao&p=evento_resumo&idEvento=<?=$eventoCadastrado['idEvento']?>' class='btn btn-theme'>Resumo</a>
                                            </td>
                                        <?php
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="enviados">
                        <div class="table-responsive list_info">
                            <table class='table table-condensed'>
                                <thead>
                                <tr class='list_menu'>
                                    <td width='5%'>ID evento</td>
                                    <td>Nome do evento</td>
                                    <td>Tipo de evento</td>
                                    <td>Data/Período</td>
                                    <td>Status do evento</td>
                                    <td>Data de Envio</td>
                                    <td width='10%'></td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($eventoEnviados as $eventoEnviado) {
                                    $tipoEvento = recuperaDados('ig_tipo_evento', $eventoEnviado['ig_tipo_evento_idTipoEvento'], 'idTipoEvento')['tipoEvento']
                                    ?>
                                    <tr>
                                        <td class="list-description"><?=$eventoEnviado['idEvento']?></td>
                                        <td class="list-description"><?=$eventoEnviado['nomeEvento']?></td>
                                        <td class="list-description"><?=$tipoEvento?></td>
                                        <td class="list-description"><?= retornaPeriodo($eventoEnviado['idEvento']) ?></td>
                                        <td class="list-description"><?=$eventoEnviado['statusEvento']?></td>
                                        <td class="list-description"><?=exibirDataBr($eventoEnviado['dataEnvio'])?></td>
                                        <td class='list_description'>
                                            <form method='POST' action='?perfil=agendao&p=evento_resumo&idEvento=<?=$eventoEnviado['idEvento']?>'>
                                                <input type ='submit' class='btn btn-theme btn-block' value='resumo'>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmação de Exclusão -->
    <div class="modal fade" id="confirmApagar" role="dialog" aria-labelledby="confirmApagarLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Aeoo?</h4>
                </div>
                <div class="modal-body">
                    <p>Confirma?</p>
                </div>
                <div class="modal-footer">
                    <form action="?perfil=agendao&p=lista_eventos" method="post">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="hidden" name="apagar" id="idEvento">
                        <button type="submit" class="btn btn-danger" id="confirm">Remover</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Fim Confirmação de Exclusão -->
</section>

<script>
    function confirmApagar(id, nomeEvento) {
        document.querySelector('#idEvento').value = id;

        var titulo = document.querySelector('.modal-title');
        titulo.innerHTML = "Remover o Evento";

        var mensagem = document.querySelector('.modal-body p');
        mensagem.innerHTML = "Deseja realmente remover o evento " + nomeEvento + "?";
    }
</script>