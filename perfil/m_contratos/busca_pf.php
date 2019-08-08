<?php
include 'includes/menu.php';
$con = bancoMysqli();

$idPedido = $_SESSION['idPedido'];

if (isset($_POST['pesquisar'])) {
    $cpf = $_POST['cpf'];
    $validacao = validaCPF($cpf);
    if ($validacao) {
        $sql = "SELECT * FROM sis_pessoa_fisica WHERE Nome LIKE '%$cpf%' OR CPF like '%$cpf%' ORDER BY Nome";
        $query = mysqli_query($con, $sql);
        $num_arrow = mysqli_num_rows($query);
    } else {
        $mensagem = "CPF INVÁLIDO!";
    }
}
?>

<section id="list_items" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="section-heading">
                    <?php
                    if (isset($mensagem)) {
                        ?>
                        <h4 style="color: red"><?= $mensagem ?></h4>
                        <?php
                    }
                    ?>
                    <h3>Procurar Pessoa Física</h3>
                </div>
                <p></p>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-md-offset-2 col-md-8">
                    <form method="POST" action="?perfil=contratos&p=busca_pf" class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="cpf">Insira o CPF* </label>
                            <input type="text" name="cpf" id="cpf" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" name="pesquisar">Pesquisar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    if (isset($num_arrow)) {
        if ($num_arrow > 0) {
            ?>
            <br>
            <div class="container">
                <div class="table-responsive list_info">
                    <table class="table table-condensed">
                        <thead>
                        <tr class="list_menu">
                            <td>Nome</td>
                            <td>CPF</td>
                            <td width="30%"></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while ($descricao = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td class="list_description">
                                    <b>
                                        <?= $descricao['Nome'] ?>
                                    </b>
                                </td>

                                <td class="list_description">
                                    <?= $descricao['CPF'] ?>
                                </td>

                                <td class="list_description">
                                    <form action="?perfil=contratos&p=busca_pf" method="POST">
                                        <input type="hidden" id="idPreponente" name="idPreponente"
                                               value="<?= $descricao['Id_PessoaFisica'] ?>">
                                        <button class="btn btn-primary btn-medium" name="selecionar">Selecionar</button>
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
            <?php
        } else {
            ?>
            <br>
            <div class="container">
                <div class="form-group">
                    <h3>CADASTRO DE PESSOA FÍSICA</h3>
                    <p style="color: red">Não foram encontradas nenhuma pessoa física com o cpf:
                        <strong><?= $cpf ?></strong></p>
                </div>

                <div class="row">
                    <div class="col-md-offset-1 col-md-10">
                        <form class="form-horizontal" role="form" action="?perfil=contratos&p=busca_pf" method="post">
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-8"><strong>Nome *:</strong><br/>
                                    <input type="text" class="form-control" id="Nome" name="Nome" placeholder="Nome" >
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-8"><strong>Nome Artístico:</strong><br/>
                                    <input type="text" class="form-control" id="NomeArtistico" name="NomeArtistico" placeholder="Nome Artístico" >
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>Tipo de documento *:</strong><br/>
                                    <select class="form-control" id="tipoDocumento" name="tipoDocumento" >
                                        <?php geraOpcao("igsis_tipo_documento","",""); ?>
                                    </select>
                                </div>
                                <div class=" col-md-6"><strong>Documento *:</strong><br/>
                                    <input type="text" class="form-control" id="RG" name="RG" placeholder="Documento" >
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>CPF *:</strong><br/>
                                    <input type="text" class="form-control" id="cpf" name="CPF" placeholder="CPF" readonly value="<?= isset($cpf) ? $cpf : null ?> ">
                                </div>
                                <div class=" col-md-6"><strong>CCM *:</strong><br/>
                                    <input type="text" class="form-control" id="CCM" name="CCM" placeholder="CCM" >
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>Data de nascimento:</strong><br/>
                                    <input type="text" class="form-control" id="datepicker01" name="DataNascimento" placeholder="Data de Nascimento" >
                                </div>
                                <div class="col-md-6"><strong>Nacionalidade:</strong><br/>
                                    <input type="text" class="form-control" id="Nacionalidade" name="Nacionalidade" placeholder="Nacionalidade">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-8"><strong>CEP:</strong><br/>
                                    <input type="text" class="form-control" id="CEP" name="CEP" placeholder="CEP">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-8"><strong>Endereço *:</strong><br/>
                                    <input type="text" class="form-control" id="Endereco" name="Endereco" placeholder="Endereço">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>Número *:</strong><br/>
                                    <input type="text" class="form-control" id="Numero" name="Numero" placeholder="Numero">
                                </div>
                                <div class=" col-md-6"><strong>Bairro:</strong><br/>
                                    <input type="text" class="form-control" id="Bairro" name="Bairro" placeholder="Bairro">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-8"><strong>Complemento *:</strong><br/>
                                    <input type="text" class="form-control" id="Complemento" name="Complemento" placeholder="Complemento">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>Cidade *:</strong><br/>
                                    <input type="text" class="form-control" id="Cidade" name="Cidade" placeholder="Cidade">
                                </div>
                                <div class=" col-md-6"><strong>Estado *:</strong><br/>
                                    <input type="text" class="form-control" id="Estado" name="Estado" placeholder="Estado">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>E-mail *:</strong><br/>
                                    <input type="text" class="form-control" id="Email" name="Email" placeholder="E-mail" >
                                </div>
                                <div class=" col-md-6"><strong>Telefone #1 *:</strong><br/>
                                    <input type="text" class="form-control" id="telefone" onkeyup="mascara( this, mtel );" maxlength="15" name="Telefone1" placeholder="Exemplo: (11) 98765-4321" >
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>Telefone #2:</strong><br/>
                                    <input type="text" class="form-control" id="telefone" onkeyup="mascara( this, mtel );" maxlength="15" name="Telefone2" placeholder="Exemplo: (11) 98765-4321" >
                                </div>
                                <div class="col-md-6"><strong>Telefone #3:</strong><br/>
                                    <input type="text" class="form-control" id="telefone" onkeyup="mascara( this, mtel );" maxlength="15" name="Telefone3" placeholder="Exemplo: (11) 98765-4321" >
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>DRT:</strong><br/>
                                    <input type="text" class="form-control" id="DRT" name="DRT" placeholder="DRT" >
                                </div>
                                <div class=" col-md-6"><strong>Função:</strong><br/>
                                    <input type="text" class="form-control" id="Funcao" name="Funcao" placeholder="Função">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-6"><strong>Inscrição do INSS ou PIS/PASEP:</strong><br/>
                                    <input type="text" class="form-control" id="InscricaoINSS" name="InscricaoINSS" placeholder="Inscrição no INSS ou PIS/PASEP" >
                                </div>
                                <div class=" col-md-6"><strong>OMB:</strong><br/>
                                    <input type="text" class="form-control" id="OMB" name="OMB" placeholder="OMB" >
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-8"><strong>Observação:</strong><br/>
                                    <textarea name="Observacao" class="form-control" rows="10" placeholder=""></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-2 col-md-8">
                                    <input type="submit" value="GRAVAR" name="gravar" class="btn btn-theme btn-lg btn-block">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
</section>
