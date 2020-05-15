﻿ <?php
	$con = bancoMysqli();	
	if(isset($_GET['p']))
	{
		$p = $_GET['p'];	
	}
	else
	{
		$p = "inicio";
	}
	switch($p)
	{
		case 'inicio':
			include "../include/menuEventoInicial.php";
?>
<section id="contact" class="home-section bg-white">
    <div class="container">
        <div class="row">    
			<div class="form-group">
				<div class="col-md-offset-2 col-md-8">
					<h5>Seus últimos eventos enviados</h5>
					<div class="left">
						<ul>
		<?php 
			$con = bancoMysqli();
			$sql_ultimo = "SELECT * FROM ig_evento WHERE (idUsuario = ".$_SESSION['idUsuario']." OR idResponsavel = ".$_SESSION['idUsuario']." OR suplente = ".$_SESSION['idUsuario'].") AND dataEnvio IS NOT NULL AND ocupacao IS NULL ORDER BY dataEnvio DESC LIMIT 0,30";
			$query_ultimo = mysqli_query($con,$sql_ultimo);
			while($evento = mysqli_fetch_array($query_ultimo))
			{
				$usuario = recuperaUsuario($evento['idUsuario']);
				$instituicao = recuperaDados("ig_instituicao",$usuario['idInstituicao'],"idInstituicao");				
		?>
							<li><p><strong><?php echo $evento['nomeEvento'] ?> </strong>(<?php echo retornaTipo($evento['ig_tipo_evento_idTipoEvento']) ?>) </p>
								<p>Enviado por: <?php echo $usuario['nomeCompleto'] ?> (<?php echo $instituicao['instituicao'] ?>) em: <?php echo exibirDataBr($evento['dataEnvio']) ?></p>
								<p><?php echo resumoOcorrencias($evento['idEvento']); ?></p>
								<br />				
							</li>
		<?php
			}
		?>
						</ul> 
					</div>
				</div>
			</div>
        </div>
    </div>
</section>
	<?php 
		break; 
		case "carregar":
			if(isset($_POST['apagar']))
			{
				$con = bancoMysqli();
				$idApagar = $_POST['apagar'];
				$sql_apagar_registro = "UPDATE ig_evento SET publicado = 0 WHERE idEvento = $idApagar";
				if(mysqli_query($con,$sql_apagar_registro)){	
					$mensagem = "Evento apagado com sucesso!";
					gravarLog($sql_apagar_registro);
				}else{
					$mensagem = "Erro ao apagar o evento...";	
				}
			}
			include "../include/menuEventoInicial.php";
	?>
<section id="list_items" class="home-section bg-white">
	<div class="container">
      	<div class="row">
			<div class="col-md-offset-2 col-md-8">
				<div class="section-heading">
					<h2>Eventos gravados mas não enviados</h2>
					<h4>Selecione o evento para carregar.</h4>
                    <h5><?php if(isset($mensagem)){echo $mensagem;} ?></h5>
				</div>
			</div>
			<div class="col-md-offset-1 col-md-10"><hr/></div>
			<div class="col-md-offset-1 col-md-10">
				<p><strong>LEGENDA DO STATUS DO EVENTO</strong></p>
				<p align="left">
					<strong>Em Elaboração:</strong> significa que o programador ainda está inserindo as informações do evento.<br/>
					<strong>Aguardando Aprovação:</strong> significa que o evento está fora do prazo e foi enviada ao setor de contratos uma solicitação para análise de aprovação do mesmo.<br/>
					<strong>Não Aprovado:</strong> a solicitação de aprovação foi recusada pelo setor de contratos.
				</p>
			</div>
			<div class="col-md-offset-1 col-md-10"><hr/></div>
		</div>  
		<div class="table-responsive list_info">
			<?php listaEventosGravados($_SESSION['idUsuario']); ?>
		</div>
	</div>
</section> <!--/#list_items-->
	<?php
		break;
		case "naoaprovado":
		
		$con = bancoMysqli();
		$idEvento = $_GET['idEvento'];
		$sql_nao_aprovado = "SELECT * FROM `igsis_argumento` WHERE `idEvento` = '$idEvento' ORDER BY data DESC";
		$query_nao_aprovado = mysqli_query($con,$sql_nao_aprovado);
		$i = 0;
		while($lista = mysqli_fetch_array($query_nao_aprovado))
		{			
			$operador = recuperaUsuario($lista['idContratos']);						
			$x[$i]['argumento']= $lista['argumento'];
			$x[$i]['idContratos'] = $operador['nomeCompleto'];
			$x[$i]['data'] = exibirDataHoraBr($lista['data']);
			$i++;				
		}
		$evento = recuperaDados("ig_evento",$idEvento,"idEvento");
	?>
		<section id="list_items">
			<div class="container">
				<div class="section-heading">
				</div>
				<div align="justify">
					<strong>Número do Evento:</strong> <?php echo $evento['idEvento'] ?> | 
					<strong>Nome do Evento:</strong> <?php echo $evento['nomeEvento'] ?>
				</div>		
				<div class="table-responsive list_info">
					<table class="table table-condensed">
						<thead>
							<tr class="list_menu">								
								<td width="65%">Argumento</td>
								<td>Operador de Contratos</td>
								<td>Data</td>								
							</tr>
						</thead>
						<tbody>
						<?php
						for($h = 0; $h < $i; $h++)
						{
							echo '<tr>';
							echo '<td class="list_description">'.$x[$h]['argumento'].'</td>';
							echo '<td class="list_description">'.$x[$h]['idContratos'].'</td>';
							echo '<td class="list_description">'.$x[$h]['data'].'</td>';
							echo'</tr>';
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</section>	
	<?php 
		break;
		case "basica":
			if(isset($_POST['carregar']))
			{
				$_SESSION['idEvento'] = $_POST['carregar'];
			}
			// Insere um novo evento em branco
			if(isset($_GET["inserir"]))
			{
				iniciaFormulario($_SESSION['idUsuario'],$_SESSION['idInstituicao']);
			}
			// Atualiza o banco com as informações do post
			if(isset($_POST['atualizar']))
			{	
				// Atualiza o banco
                $idEvento = $_SESSION['idEvento'];
				$sinopse = addslashes($_POST['sinopse']);
				$releaseCom = addslashes($_POST['releaseCom']); 
				$linksCom = addslashes($_POST['linksCom']);
				$nomeGrupo = addslashes($_POST['nomeGrupo']);
				$fichaTecnica = addslashes($_POST['fichaTecnica']); 
				$faixaEtaria = $_POST['faixaEtaria'];
				$ig_modalidade_IdModalidade = $_POST['ig_modalidade_IdModalidade'];
				$projetoEspecial = $_POST['projetoEspecial']; 
				$nomeEvento = addslashes($_POST['nomeEvento']);
				$ig_tipo_evento_idTipoEvento = $_POST['ig_tipo_evento_idTipoEvento'];
				$idResponsavel = $_POST['nomeResponsavel'];
				$idSuplente = $_POST['suplente'];

                $nApresentacao = $_POST['nApresentacao'];
                $espacoPublico = $_POST['espacoPublico'];
                $fomento = $_POST['fomento'];
                $tipoFomento = $_POST['tipoFomento'] ?? 0;
                $oficina = $_POST['oficina'];

				if(isset($_POST['subEvento']))
				{
					$subEvento = 1;	
				}
				else
				{
					$subEvento = 0;	
				}
				$sql_atualizar = "UPDATE `ig_evento` SET 
				`nomeEvento` = '$nomeEvento', 
				`projetoEspecial` = '$projetoEspecial', 
				`idResponsavel` = '$idResponsavel', 
				`suplente` = '$idSuplente', 
				`ig_modalidade_IdModalidade` = 	'$ig_modalidade_IdModalidade',
				`ig_tipo_evento_idTipoEvento` = '$ig_tipo_evento_idTipoEvento',
				`subEvento` = '$subEvento',
				`nomeGrupo` = '$nomeGrupo', 
				`fichaTecnica` = '$fichaTecnica', 
				`faixaEtaria` = '$faixaEtaria', 
				`sinopse` = '$sinopse', 
				`releaseCom` = '$releaseCom', 
				`linksCom` = '$linksCom',
				`publicado` = 1,
                numero_apresentacao = '$nApresentacao',
                espaco_publico = '$espacoPublico',
                fomento = '$fomento',
                tipo_fomento = '$tipoFomento',
                `oficina` = '$oficina',
				`statusEvento` = 'Em elaboração'
                WHERE `ig_evento`.`idEvento` = ".$_SESSION['idEvento'].";";
                $con = bancoMysqli();
                if(mysqli_query($con,$sql_atualizar))
                {
                    if (isset($_POST['linguagem'])) {
                        atualizaRelacionamentoEvento('igsis_evento_linguagem', $idEvento, $_POST['linguagem']);
                    }

                    if (isset($_POST['representatividade'])) {
                        atualizaRelacionamentoEvento('igsis_evento_representatividade', $idEvento, $_POST['representatividade']);
                    }

                    $mensagem = "Atualizado com sucesso!";
                    gravarLog($sql_atualizar);
                }
                else
                {
                    $mensagem = "Erro ao atualizar... tente novamente";
                }
			}
			// Cria um array com dados do evento
			$campo = recuperaEvento($_SESSION['idEvento']);
			if($campo['ig_tipo_evento_idTipoEvento'] == 1)
			{
				$_SESSION['cinema'] = 1;	
			}
			else
			{
				$_SESSION['cinema'] = 0;
			}
			if($campo['subEvento'] == 1)
			{
				$_SESSION['subEvento'] = 1;	
			}
			else
			{
				$_SESSION['subEvento'] = 0;
			}
			include "../include/menuEvento.php";
	?>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Evento - Informações Gerais</h3>
                    <h1><?php echo $campo["nomeEvento"] ?></h1>
                    <h4><?php if(isset($mensagem)){echo $mensagem;} ?></h4>
                </div>
            </div>
		</div> 
		<div class="row">
			<div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=basica" class="form-horizontal" role="form">
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Nome do Evento *</label>
							<input type="text" name="nomeEvento" class="form-control" id="inputSubject" value="<?php echo $campo['nomeEvento'] ?>" required/>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Tipo de relação jurídica</label>
							<select class="form-control" name="ig_modalidade_IdModalidade" id="inputSubject" >
								<option value="1"></option>
								<?php echo geraOpcao("ig_modalidade",$campo['ig_modalidade_IdModalidade'],"") ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Nome do Projeto especial</label>
							<select class="form-control" name="projetoEspecial" id="inputSubject" >
								<option value="1"></option>
								<?php echo geraOpcao("ig_projeto_especial",$campo['projetoEspecial'],$_SESSION['idInstituicao']) ?>
							</select>
						</div>
					</div>

                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-4">
                            <label for="espacoPublico">Espaço público? *</label><br>
                            <input type="radio" name="espacoPublico" id="espacoPublico"
                                   value="1" <?= $campo['espaco_publico'] == 1 ? 'checked' : NULL ?>> Sim
                            <input type="radio" name="espacoPublico" id="espacoPublico"
                                   value="0" <?= $campo['espaco_publico'] == 0 ? 'checked' : NULL ?>> Não
                        </div>

                        <div class="col-md-4">
                            <label for="nApresentacao">Quantidade de apresentação *</label>
                            <input type="number" name="nApresentacao" id="nApresentacao" class="form-control"
                                   value="<?= $campo['numero_apresentacao'] ?>" min='1' required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-offset-2 col-md-4">
                                <label for="fomento">É fomento/programa? *</label><br>
                                <input type="radio" name="fomento" class="fomento" id="sim" value="1" <?= $campo['fomento'] == 1 ? 'checked' : NULL ?>> Sim
                                <input type="radio" name="fomento" class="fomento" id="nao" value="0" <?= $campo['fomento'] == 0 ? 'checked' : NULL ?>> Não
                            </div>

                            <div class="col-md-4">
                                <label for="espacoPublico">Selecione o fomento/programa</label><br>
                                <select name="tipoFomento" id="tipoFomento" class="form-control">
                                    <option value="">Selecione o fomento/programa da SMC</option>
                                    <?php
                                    geraOpcaoPadrao('fomento', $campo['tipo_fomento']);
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Tipo de Evento *</label>
							<select class="form-control" name="ig_tipo_evento_idTipoEvento" id="inputSubject" required>
								<option value=""></option>
								<?php echo geraOpcao("ig_tipo_evento",$campo['ig_tipo_evento_idTipoEvento'],"") ?>
							</select>					
						</div>
					</div>

                    <div class="row">
                        <div class="form-group col-md-offset-3">
                            <label for="tipo">Este evento é oficina?</label> <br>
                            <label><input type="radio" name="oficina" value="1" id="simOficina" <?= $campo['oficina'] == 1 ? 'checked' : NULL ?>> Sim </label>&nbsp;&nbsp;
                            <label><input type="radio" name="oficina" value="0" <?= $campo['oficina'] == 0 ? 'checked' : NULL ?>> Não </label>
                        </div>
                    </div>

                    <div class="row">
                            <div class="col-md-offset-2 col-md-8">
                                <label>Ações (Expressões Artístico-culturais) * <i>(multipla escolha) </i></label>
                                <button class='btn btn-default' type='button' data-toggle='modal'
                                        data-target='#modalAcoes' style="border-radius: 30px;">
                                    <i class="fa fa-question-circle"></i></button>
                            </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-8">
                            <?php
                                geraCheckboxEvento('igsis_linguagem', 'linguagem', 'igsis_evento_linguagem', $_SESSION['idEvento']);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-offset-2 col-md-8">
                            <label>Público (Representatividade e Visibilidade Sócio-cultural)* <i>(multipla escolha) </i></label>
                            <button class='btn btn-default' type='button' data-toggle='modal'
                                    data-target='#modalPublico' style="border-radius: 30px;">
                                <i class="fa fa-question-circle"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-8">
                            <?php
                                geraCheckboxEvento('igsis_representatividade', 'representatividade', 'igsis_evento_representatividade', $_SESSION['idEvento']);
                            ?>
                        </div>
                    </div>

					<div class="form-group">
						<br />
						<p>O responsável e suplente devem estar cadastrados como usuários do sistema.</p>
						<div class="col-md-offset-2 col-md-8">
							<label>Primeiro responsável (Fiscal)</label>
							<select class="form-control" name="nomeResponsavel" id="inputSubject" >
								<option value="1"></option>	
								<?php echo opcaoUsuario($_SESSION['idInstituicao'],$campo['idResponsavel']) ?>
							</select>	                
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Segundo responsável (Suplente)</label>
							<select class="form-control" name="suplente" id="inputSubject" >
								<option value="1"></option>
								<?php echo opcaoUsuario($_SESSION['idInstituicao'],$campo['suplente']) ?>
							</select>	
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="subEvento" id="subEvento" <?php checar($campo['subEvento']) ?>/><label style="padding:0 10px 0 5px;"> Haverá evento(s) complementar(es) (sub-evento)?</label>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Nome do Grupo</label>
							<input type="text" name="nomeGrupo" class="form-control" maxlength="100" id="inputSubject" placeholder="Nome do coletivo, grupo teatral, etc." value="<?php echo $campo['nomeGrupo'] ?>"/>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Ficha técnica completa*</label>
							<label>Esse campo deve conter a listagem de pessoas envolvidas no espetáculo, como elenco, técnicos, e outros profissionais envolvidos na realização do mesmo.</i></strong></label>
							<p align="justify"><font color="gray"><strong><i>Elenco de exemplo:</strong><br/>Ana Cañas (voz e guitarra)<br/>Lúcio Maia (guitarra solo)<br/>Fabá Jimenez (guitarra base)</br> Fabio Sá (baixo)</br>Marco da Costa (bateria)<br/>Eloá Faria (figurinista)<br/>Leonardo Kuero (técnico de som)</font></i></p>
							<textarea name="fichaTecnica" class="form-control" rows="10"><?php echo $campo["fichaTecnica"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
                            <label>Classificação indicativa *</label> <a href="?perfil=classificacaoIndicativa" target="_blank"><i>(Confira aqui como classificar)</i></a>
							<select class="form-control" name="faixaEtaria" id="inputSubject" >
								<option value="0"></option>
								<?php echo geraOpcao("ig_etaria",$campo['faixaEtaria'],"") ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Sinopse *</label>
							<label>Esse campo deve conter uma breve descrição do que será apresentado no espetáculo.</i></strong></label>
							<p align="justify"><font color="gray"><strong><i>Texto de exemplo:</strong><br/>Ana Cañas faz o show de lançamento do seu quarto disco, “Tô na Vida” (Som Livre/Guela Records). Produzido por Lúcio Maia (Nação Zumbi) em parceria com Ana e mixado por Mario Caldato Jr, é o primeiro disco totalmente autoral da carreira da cantora e traz parcerias com Arnaldo Antunes e Dadi entre outros.</font></i></p>
							<textarea name="sinopse" class="form-control" rows="10"><?php echo $campo["sinopse"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Release *</label>
							<label>Esse campo deve abordar informações relacionadas ao artista, abordando breves marcos na carreira e ações realizadas anteriormente. </i></strong></label>
							<p align="justify"><font color="gray"><strong><i>Texto de exemplo:</strong><br/>A cantora e compositora paulistana lançou, em 2007, o seu primeiro disco, "Amor e Caos". Dois anos depois, lançou "Hein?", disco produzido por Liminha e que contou com "Esconderijo", canção composta por Ana, eleita entre as melhores do ano pela revista Rolling Stone e que alcançou repercussão nacional por integrar a trilha sonora da novela "Viver a Vida" de Manoel Carlos, na Rede Globo. 
							Ainda em 2009, grava, a convite do cantor e compositor Nando Reis, a bela canção "Pra Você Guardei o Amor". Em 2012, Ana lança o terceiro disco de inéditas, "Volta", com versões para Led Zeppelin ("Rock'n'Roll") e Edith Piaf ("La Vie en Rose"), além das inéditas autorais "Urubu Rei" (que ganhou clipe dirigido por Vera Egito) e "Será Que Você Me Ama?". Em 2013, veio o primeiro DVD, "Coração Inevitável", registrando o show que contou com a direção e iluminação de Ney Matogrosso.</font></i></p>
							<textarea name="releaseCom" class="form-control" rows="10"><?php echo $campo["releaseCom"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Links </label>
							<label>Esse campo deve conter os links relacionados ao espetáculo, ao artista/grupo que auxiliem na divulgação do evento.</i></strong></label>
							<p align="justify"><font color="gray"><strong><i>Links de exemplo:</i></strong></strong><br/> https://www.facebook.com/anacanasoficial/<br/></strong>https://www.youtube.com/user/anacanasoficial</font></i></p>
							<textarea name="linksCom" class="form-control" rows="10"><?php echo $campo["linksCom"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="atualizar" value="1" />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

    <div class="modal fade" id="modalAcoes" role="dialog" aria-labelledby="lblmodalAcoes" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Ações (Expressões Artístico-culturais)</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <table class="table table-bordered table-responsive">
                        <thead>
                            <tr>
                                <th>Ação</th>
                                <th>Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sqlConsultaLinguagens = "SELECT linguagem, descricao FROM igsis_linguagem WHERE publicado = '1' ORDER BY 1";
                            foreach ($con->query($sqlConsultaLinguagens)->fetch_all(MYSQLI_ASSOC) as $linguagem) {
                            ?>
                                <tr>
                                    <td><?=$linguagem['linguagem']?></td>
                                    <td><?=$linguagem['descricao']?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-theme" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPublico" role="dialog" aria-labelledby="lblmodalPublico" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Público (Representatividade e Visibilidade Sócio-cultural)</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <table class="table table-bordered table-responsive">
                        <thead>
                        <tr>
                            <th>Representatividade</th>
                            <th>Descrição</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sqlConsultaLinguagens = "SELECT representatividade_social, descricao FROM igsis_representatividade WHERE publicado = '1' ORDER BY 1";
                        foreach ($con->query($sqlConsultaLinguagens)->fetch_all(MYSQLI_ASSOC) as $linguagem) {
                            ?>
                            <tr>
                                <td><?=$linguagem['representatividade_social']?></td>
                                <td><?=$linguagem['descricao']?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-theme" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</section>
            <script>
                let fomento = $('.fomento');
                let linguagem = $("input[name='linguagem[]']");
                const oficinaId = "Oficinas e Formação Cultural";
                let oficinaRadio = $("input[name='oficina']");
                var oficinaOficial = linguagem[8];

                function verificaOficina() {
                    if ($('#simOficina').is(':checked')) {
                        checaCampos(oficinaOficial);
                    } else {
                        checaCampos("");
                    }
                }

                function checaCampos(obj){
                    if(obj.id == oficinaId && obj.value == '8'){

                        for(i = 0; i < linguagem.size(); i++){
                            if (!(linguagem[i] == obj)){
                                let linguagens = linguagem[i].id;

                                document.getElementById(linguagens).disabled = true;
                                document.getElementById(linguagens).checked = false;
                                document.getElementById(oficinaId).checked = true;
                                document.getElementById(oficinaId).disabled = false;

                                document.getElementById(oficinaId).readonly = true;

                            }
                        }
                    }else{
                        for(i = 0; i < linguagem.size(); i++){

                            if (!(linguagem[i] == linguagem[8])){
                                let linguagens = linguagem[i].id;

                                document.getElementById(linguagens).disabled = false;
                                document.getElementById(oficinaId).checked = false;
                                document.getElementById(oficinaId).disabled = true;

                                document.getElementById(oficinaId).readonly = false;
                            }
                        }

                    }
                }

                fomento.on("change", verificaFomento);
                oficinaRadio.on("change", verificaOficina);

                $(document).ready(
                    verificaFomento(),
                    verificaOficina()
                );

                function verificaFomento() {
                    if ($('#sim').is(':checked')) {
                        $('#tipoFomento')
                            .attr('disabled', false)
                            .attr('required', true)
                    } else {
                        $('#tipoFomento')
                            .attr('disabled', true)
                            .attr('required', false)
                    }
                }
            </script>
	<?php
		break;
		case "detalhe" :
			if(isset($_POST['atualizar']))
			{
				// Atualiza o banco
				$fichaTecnica = addslashes($_POST['fichaTecnica']);
				$faixaEtaria = $_POST['faixaEtaria'];
				$sql_atualizar = "UPDATE `ig_evento` SET
				`fichaTecnica` = '$fichaTecnica',
				`faixaEtaria` = '$faixaEtaria'
				WHERE `ig_evento`.`idEvento` = ".$_SESSION['idEvento'].";";
				$con = bancoMysqli();
				if(mysqli_query($con,$sql_atualizar))
				{
					$mensagem = "Atualizado com sucesso!";
					gravarLog($sql_atualizar);
				}
				else
				{
					$mensagem = "Erro ao atualizar... tente novamente";
				}
			}
			$campo = recuperaEvento($_SESSION['idEvento']); //carrega os dados do evento em questão
			include "../include/menuEvento.php";
	?>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Evento - Detalhamento</h3>
                    <h1><?php echo $campo["nomeEvento"] ?> </h1>
                    <h4><?php if(isset($mensagem)){echo $mensagem;} ?></h4>
                </div>
            </div>
		</div>
		<div class="row">
			<div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=detalhe" class="form-horizontal" role="form">
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Ficha técnica completa*</label>
							<label>Esse campo deve conter a listagem de pessoas envolvidas no espetáculo, como elenco, técnicos, e outros profissionais envolvidos na realização do mesmo.</i></strong></label>
							<p align="justify"><font color="gray"><strong><i>Elenco de exemplo:</strong><br/>Ana Cañas (voz e guitarra)<br/>Lúcio Maia (guitarra solo)<br/>Fabá Jimenez (guitarra base)</br> Fabio Sá (baixo) </br> Marco da Costa (bateria)</font></i></p>
							<textarea name="fichaTecnica" class="form-control" rows="10"><?php echo $campo["fichaTecnica"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
                            <label>Classificação indicativa *</label> <a href="?perfil=classificacaoIndicativa" target="_blank"><i>(Confira aqui como classificar)</i></a>
							<select class="form-control" name="faixaEtaria" id="inputSubject" >
								<option value="0"></option>
								<?php echo geraOpcao("ig_etaria",$campo['faixaEtaria'],"") ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="atualizar" value="1" />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>  
	<?php 
		break;
		case "conteudo" :
			if(isset($_POST['atualizar']))
			{		
				// Atualiza o banco
				$sinopse = addslashes($_POST['sinopse']);
				$releaseCom = addslashes($_POST['releaseCom']); 
				$linksCom = addslashes($_POST['linksCom']); 
				$sql_atualizar = "UPDATE `ig_evento` SET 
				`sinopse` = '$sinopse', 
				`releaseCom` = '$releaseCom', 
				`linksCom` = '$linksCom'
				WHERE `ig_evento`.`idEvento` = ".$_SESSION['idEvento'].";";
				if(mysqli_query($con,$sql_atualizar))
				{
					$mensagem = "Atualizado com sucesso!";
					gravarLog($sql_atualizar);	
				}
				else
				{
					$mensagem = "Erro ao atualizar... tente novamente";	
				}
			}
			$campo = recuperaEvento($_SESSION['idEvento']); //carrega os dados do evento em questão
			include "../include/menuEvento.php";
	?>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Evento - Conteúdo</h3>
                    <h1><?php echo $campo["nomeEvento"] ?></h1>
                    <h4><?php if(isset($mensagem)){echo $mensagem;} ?></h4>
                </div>
            </div>
		</div>
		<div class="row">
			<div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=conteudo" class="form-horizontal" role="form">
					 <div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Sinopse *</label>
							<label>Esse campo deve conter uma breve descrição do que será apresentado no espetáculo.</i></strong></label>
							<p align="justify"><font color="gray"><strong><i>Texto de exemplo:</strong><br/>Ana Cañas faz o show de lançamento do seu quarto disco, “Tô na Vida” (Som Livre/Guela Records). Produzido por Lúcio Maia (Nação Zumbi) em parceria com Ana e mixado por Mario Caldato Jr, é o primeiro disco totalmente autoral da carreira da cantora e traz parcerias com Arnaldo Antunes e Dadi entre outros.</font></i></p>
							<textarea name="sinopse" class="form-control" rows="10"><?php echo $campo["sinopse"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Release *</label>
							<label>Esse campo deve abordar informações relacionadas ao artista, abordando breves marcos na carreira e ações realizadas anteriormente. </i></strong></label>
							<p align="justify"><font color="gray"><strong><i>Texto de exemplo:</strong><br/>A cantora e compositora paulistana lançou, em 2007, o seu primeiro disco, "Amor e Caos". Dois anos depois, lançou "Hein?", disco produzido por Liminha e que contou com "Esconderijo", canção composta por Ana, eleita entre as melhores do ano pela revista Rolling Stone e que alcançou repercussão nacional por integrar a trilha sonora da novela "Viver a Vida" de Manoel Carlos, na Rede Globo. 
							Ainda em 2009, grava, a convite do cantor e compositor Nando Reis, a bela canção "Pra Você Guardei o Amor". Em 2012, Ana lança o terceiro disco de inéditas, "Volta", com versões para Led Zeppelin ("Rock'n'Roll") e Edith Piaf ("La Vie en Rose"), além das inéditas autorais "Urubu Rei" (que ganhou clipe dirigido por Vera Egito) e "Será Que Você Me Ama?". Em 2013, veio o primeiro DVD, "Coração Inevitável", registrando o show que contou com a direção e iluminação de Ney Matogrosso.</font></i></p>
							<textarea name="releaseCom" class="form-control" rows="10"><?php echo $campo["releaseCom"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Links </label>
							<label>Esse campo deve conter os links relacionados ao espetáculo, ao artista/grupo que auxiliem na divulgação do evento.</i></strong></label>
							<p align="justify"><font color="gray"><strong><i>Links de exemplo:</i></strong></strong><br/> https://www.facebook.com/anacanasoficial/<br/></strong>https://www.youtube.com/user/anacanasoficial</font></i></p>
							<textarea name="linksCom" class="form-control" rows="10"><?php echo $campo["linksCom"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="atualizar" value="1" />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>  
	<?php 
		break;
		case "internos" :
			if(isset($_POST['atualizar']))
			{
				//gera as variáveis
				$ig_produtor_nome = addslashes ($_POST['ig_produtor_nome']);
				$ig_produtor_telefone = $_POST['ig_produtor_telefone'];
				$ig_produtor_telefone2 = $_POST['ig_produtor_telefone2'];
				$ig_produtor_email = $_POST['ig_produtor_email'];
				$ig_producao_equipe = addslashes($_POST['ig_producao_equipe']);		
				$ig_producao_infraestrutura = addslashes($_POST['ig_producao_infraestrutura']);
				if(isset($_POST['ig_comunicacao_registroFotografia']))
				{
					$ig_comunicacao_registroFotografia = 1;
				}
				else
				{
					$ig_comunicacao_registroFotografia = 0;
				}
				if(isset($_POST['ig_comunicacao_registroVideo']))
				{
					$ig_comunicacao_registroVideo = 1;
				}
				else
				{
					$ig_comunicacao_registroVideo = 0;
				}
				if(isset($_POST['ig_comunicacao_registroAudio']))
				{
					$ig_comunicacao_registroAudio = 1;
				}
				else
				{
					$ig_comunicacao_registroAudio = 0;
				}
				$idEvento = $_SESSION['idEvento'];
				if($ig_produtor_email == '')
			{
				$mensagem = "Por favor, preencha todos os campos obrigatórios!";
			}
			else
			{
				//Produtor
				//verifica se há produtor
				$ver = recuperaEvento($_SESSION['idEvento']);
				if($ver['ig_produtor_idProdutor'] == 0)
				{
					$sql_inserir_produtor = "INSERT INTO  `ig_produtor` (`idProdutor` ,`nome` ,`email` ,`telefone` ,`telefone2` ,`idSpCultura`) VALUES ( NULL ,  '$ig_produtor_nome',  '$ig_produtor_email',  '$ig_produtor_telefone',  '$ig_produtor_telefone2',  '' )";
					if(mysqli_query($con,$sql_inserir_produtor))
					{		
						$mensagem = "Produtor inserido com sucesso! ";	
						$idProdutor = recuperaUltimo("ig_produtor"); //recupera o idProdutor inserido
						mysqli_query($con,"UPDATE ig_evento SET ig_produtor_idProdutor = '$idProdutor' WHERE idEvento = $idEvento");
						gravarLog($sql_inserir_produtor); //grava log
					}
					else
					{
						$mensagem = "Erro ao atualizar!";
					}
				}
				else
				{
					$sql_atualizar_produtor = "UPDATE ig_produtor SET `nome` = '$ig_produtor_nome' ,`email` = '$ig_produtor_email' ,`telefone` = '$ig_produtor_telefone',`telefone2` = '$ig_produtor_telefone2' WHERE idProdutor = ".$ver['ig_produtor_idProdutor'];
					if(mysqli_query($con,$sql_atualizar_produtor))
					{		
						$mensagem = "Produtor inserido com sucesso! ";	
						gravarLog($sql_atualizar_produtor); //grava log
					}
					else
					{
						$mensagem = "Erro ao atualizar!";
					}	
				}
			}
				//Produção
				//Verifica se já existe o registro na tabela
				$ver = verificaExiste("ig_producao","ig_evento_idEvento",$_SESSION['idEvento'],0);
				if($ver['numero'] == 0)
				{
					$idEvento = $_SESSION['idEvento'];
					$sql_inserir_producao = "INSERT INTO  `ig_producao` (`idProducao` ,`ig_evento_idEvento` ,`carros` ,`equipe` ,`infraestrutura`, `registroAudio`, `registroVideo`, `registroFotografia` ) VALUES ( NULL ,  '$idEvento',  '',  '$ig_producao_equipe',  '$ig_producao_infraestrutura', '$ig_comunicacao_registroAudio', '$ig_comunicacao_registroVideo', '$ig_comunicacao_registroFotografia' )";
					if(mysqli_query($con,$sql_inserir_producao))
					{
						$mensagem02 = "Informações de produção inseridas com sucesso! ";	
						gravarLog($sql_inserir_producao); //grava log
					}
					else
					{
						$mensagem02 = "Erro ao atualizar!";
					}
				}
				else
				{
					$sql_atualizar_producao = "UPDATE ig_producao SET  `equipe` = '$ig_producao_equipe' ,`infraestrutura` = '$ig_producao_infraestrutura', `registroAudio` = '$ig_comunicacao_registroAudio', `registroVideo` = '$ig_comunicacao_registroVideo' , `registroFotografia` = '$ig_comunicacao_registroFotografia' WHERE `ig_evento_idEvento` = $idEvento";
					if(mysqli_query($con,$sql_atualizar_producao))
					{		
						$mensagem02 = "Informações de produção atualizadas com sucesso! ";	
						gravarLog($sql_atualizar_producao); //grava log
					}
					else
					{
						$mensagem02 = "Erro ao atualizar!";
					}
				}
			}
			if(isset($_POST['ig_artesvisuais_identidade']))
			{
				$painel = $_POST['ig_artesvisuais_paineis'];
				$legendas = $_POST['ig_artesvisuais_legendas'];
				$identidade = $_POST['ig_artesvisuais_identidade'];
				$suporte = $_POST['ig_artesvisuais_suporte'];
				$acervo = $_POST['ig_artesvisuais_acervo'];
				$verArtes = verificaExiste("ig_artes_visuais","idEvento",$_SESSION['idEvento'],0);
				if($verArtes['numero'] == 0)
				{
					$idEvento = $_SESSION['idEvento'];
					$sql_insere_artes = "INSERT INTO `ig_artes_visuais` (`idArtes`, `idEvento`, `numero`, `tipo`, `valorTotal`, `painel`, `legendas`, `identidade`, `suporte`, `acervo`) VALUES (NULL, '$idEvento', NULL, NULL, NULL, '$painel', '$legendas', '$identidade', '$suporte', '$acervo')";
					if(mysqli_query($con,$sql_insere_artes))
					{
						gravarLog($sql_insere_artes);	
						$mensagem02 = $mensagem02." Informações expositivas inseridas com sucesso";
					}
					else
					{
						$mensagem02 = $mensagem02." Erro ao inserir informações expositivas";
					}		
				}
				else
				{
					$sql_atualiza_artes = "UPDATE ig_artes_visuais SET 
					`painel` = '$painel',
					`legendas` = '$legendas',
					`identidade` = '$identidade',
					`acervo` = '$acervo',
					`suporte` = '$suporte'
					WHERE idEvento = '$idEvento'";		
					if(mysqli_query($con,$sql_atualiza_artes))
					{
						gravarLog($sql_atualiza_artes);
						$mensagem02 = $mensagem02." Informações expositivas atualizadas com sucesso";
					}
					else
					{
						$mensagem02 = $mensagem02." Erro ao atualizar informações expositivas";
					}	
				}
			}
			$campo = recuperaEvento($_SESSION['idEvento']); //carrega os dados do evento em questão
			$interno = recuperaDados("ig_servico",$_SESSION['idEvento'],"ig_evento_idEvento"); // recupera os dados dos serviços internos do evento em questão
			$com = recuperaDados("ig_comunicacao",$_SESSION['idEvento'],"ig_evento_idEvento"); // recupera os dados de comunicação do evento em questão
			$produtor = recuperaProdutor($campo['ig_produtor_idProdutor']); // recupera dados do produtor
			$producao = recuperaDados("ig_producao",$campo['idEvento'],"ig_evento_idEvento"); // recupera dados da produção
			include "../include/menuEvento.php";
	?>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Evento - Serviços internos</h3>
                    <h1><?php echo $campo["nomeEvento"] ?></h1>
                    <h4><?php if(isset($mensagem)){echo $mensagem;} ?></h4>
                    <h4><?php if(isset($mensagem02)){echo $mensagem02;} ?></h4>
                </div>
            </div>
		</div>
		<div class="row">
			<div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=internos" class="form-horizontal" role="form">
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Nome do produtor do evento*</label>
							<input type="text" name="ig_produtor_nome" class="form-control" id="ig_produtor_nome" value="<?php echo $produtor['nome'] ?>"/>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Telefone #1*: </label>
							<input type="text" name="ig_produtor_telefone" class="form-control" id="telefone" onkeyup="mascara( this, mtel );" maxlength="15" value="<?php echo $produtor['telefone'] ?>"/>
						</div>
						<div class="col-md-6">
							<label>Telefone #2: </label>
							<input type="text" name="ig_produtor_telefone2" class="form-control" id="telefone" onkeyup="mascara( this, mtel );" maxlength="15" value="<?php echo $produtor['telefone2'] ?>"/>
						</div> 				
					</div>       		 
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Email*</label>
							<input type="text" name="ig_produtor_email" class="form-control" id="inputSubject" value="<?php echo $produtor['email'] ?>"/>
						</div> 
					</div>            
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Equipe</label>
							<textarea name="ig_producao_equipe" class="form-control" rows="10" placeholder="Profissionais envolvidos na produção e montagem do evento, tais como equipe de iluminação, figurinistas, etc."><?php echo $producao["equipe"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Infraestrutura</label>
							<textarea name="ig_producao_infraestrutura" class="form-control" rows="10" placeholder="Necessidades técnicas e materiais envolvidos na realização do evento, tais como: cadeiras, iluminação específica, slideshow, etc."><?php echo $producao["infraestrutura"] ?></textarea>
						</div> 
					</div>
					<br /><br />
					<h5>Comunicação</h5>
		<?php //artes visuais 
			$artes = recuperaDados("ig_artes_visuais",$_SESSION['idEvento'],"idEvento");
			if($campo['ig_tipo_evento_idTipoEvento'] == '2' )
			{
		?>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Criação de Identidade Visual</label>
							<select class="form-control" name="ig_artesvisuais_identidade" id="inputSubject" >
								<option value="0" <?php if(isset($artes)){if($artes['identidade'] == 0){echo "selected";}} ?> >Não</option>
								<option value="1" <?php if(isset($artes)){if($artes['identidade'] == 1){echo "selected";}} ?>>Sim</option>
							</select>
						</div>
						<div class=" col-md-6">
							<label>Confecção de painéis</label>
							<select class="form-control" name="ig_artesvisuais_paineis" id="inputSubject" >
								<option value="0" <?php if(isset($artes)){if($artes['painel'] == 0){echo "selected";}} ?> >Não</option>
								<option value="1" <?php if(isset($artes)){if($artes['painel'] == 1){echo "selected";}} ?>>Sim</option>                        
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Confecção de legendas</label>
							<select class="form-control" name="ig_artesvisuais_legendas" id="inputSubject" >
								<option value="0" <?php if(isset($artes)){if($artes['legendas'] == 0){echo "selected";}} ?> >Não</option>
								<option value="1" <?php if(isset($artes)){if($artes['legendas'] == 1){echo "selected";}} ?>>Sim</option>
							</select>
						</div>
						<div class=" col-md-6">
							<label>Suporte extra (exposição)</label>
							<select class="form-control" name="ig_artesvisuais_suporte" id="inputSubject" >
								<option value="0" <?php if(isset($artes)){if($artes['suporte'] == 0){echo "selected";}} ?> >Não</option>
								<option value="1" <?php if(isset($artes)){if($artes['suporte'] == 1){echo "selected";}} ?>>Sim</option>                        
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Acervo</label>
							<select class="form-control" name="ig_artesvisuais_acervo" id="inputSubject" >
								<option value="0" <?php if(isset($artes)){if($artes['acervo'] == 0){echo "selected";}} ?> >A exposição NÃO possui peças que fazem parte da coleção da instituição.</option>
								<option value="1" <?php if(isset($artes)){if($artes['acervo'] == 1){echo "selected";}} ?>>A exposição POSSUI peças que fazem parte da coleção da instituição.</option>
							</select>
						</div> 
					</div>
		<?php
			}//artes visuais 
		?>
					<div class="form-group">     
						<p>Pedido de documentação</p>
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="ig_comunicacao_registroFotografia" id="especial01" <?php checar($producao['registroFotografia']) ?> /><label  style="padding:0 10px 0 5px;">Fotografia</label>
							<input type="checkbox" name="ig_comunicacao_registroAudio" id="especial02" <?php checar($producao['registroAudio']) ?>/><label  style="padding:0 10px 0 5px;">Áudio</label>
							<input type="checkbox" name="ig_comunicacao_registroVideo" id="especial03" <?php checar($producao['registroVideo']) ?>/><label  style="padding:0 10px 0 5px;">Vídeo</label>
						</div>                     
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="atualizar" value="1" />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>  
	<?php
		break;
		case "area" :
			$campo = recuperaEvento($_SESSION['idEvento']); //carrega os dados do evento em questão
			include "../include/menuEvento.php";
	?>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Evento - Especificidades de Área</h3>
                    <h1><?php echo $campo["nomeEvento"] ?></h1>
                    <h4><?php if(isset($mensagem)){echo $mensagem;} ?></h4>
                </div>
            </div>
		</div>
		<div class="row">
			<div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=area" class="form-horizontal" role="form">
		<?php
			switch($campo['ig_tipo_evento_idTipoEvento'])
			{
				case 5:
					if(isset($_POST['atualizar']))
					{
						$certificado = $_POST['certificado'];
						$vagas = $_POST['vagas'];
						$publico = addslashes($_POST['publico']);
						$material = $_POST['material'];
						$forma_inscricao = $_POST['forma_inscricao'];
						$hora_aula = $_POST['hora_aula'];
						$venda = $_POST['venda'];
						$material_venda = addslashes($_POST['material_venda']);
						$idEvento = $_SESSION['idEvento'];
						$carga_horaria = $_POST['carga_horaria'];
						if($_POST['inicio_inscricao'] != '')
						{
							$inicio_inscricao = exibirDataMysql($_POST['inicio_inscricao']);
						}
						else
						{
							$inicio_inscricao = '0000-00-00';
						}
						if($_POST['encerra_inscricao'] != '')
						{	
							$encerra_inscricao = exibirDataMysql($_POST['encerra_inscricao']);
						}
						else
						{
							$encerra_inscricao = '0000-00-00';
						}
						if($_POST['divulga_inscricao'] != '')
						{	
							$divulga_inscricao = exibirDataMysql($_POST['divulga_inscricao']);
						}
						else
						{
							$divulga_inscricao = '0000-00-00';
						}
						$verifica_oficinas =  verificaExiste("ig_oficinas","idEvento",$_SESSION['idEvento'],"");
						if($verifica_oficinas['numero'] == 0)
						{
							$sql_insere_oficinas = "INSERT INTO `ig_oficinas` (`idOficinas`, `idEvento`, `certificado`, `vagas`, `publico`, `material`, `inscricao`, `valorHora`, `venda`, `divulgacao`, `cargaHoraria`) VALUES (NULL, '$idEvento', '$certificado', '$vagas', '$publico', '$material', '$forma_inscricao', '$hora_aula', '$venda', '$divulga_inscricao', '$carga_horaria');";
							$query_insere_oficinas = mysqli_query($con,$sql_insere_oficinas);
							gravarLog($sql_insere_oficinas);
							if($query_insere_oficinas)
							{
								$sql_insere_ocorrencia_inscricao = "INSERT INTO `ig_ocorrencia` (`idOcorrencia`, `idTipoOcorrencia`, `ig_comunicao_idCom`, `local`, `idEvento`, `segunda`, `terca`, `quarta`, `quinta`, `sexta`, `sabado`, `domingo`, `dataInicio`, `dataFinal`, `horaInicio`, `horaFinal`, `timezone`, `diaInteiro`, `diaEspecial`, `libras`, `audiodescricao`, `valorIngresso`, `retiradaIngresso`, `localOutros`, `lotacao`, `reservados`, `duracao`, `precoPopular`, `frequencia`, `publicado`, `idSubEvento`, `idCinema`, `virada`) VALUES (NULL, '1', NULL, NULL, '$idEvento', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$inicio_inscricao', '$encerra_inscricao', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL);";
								$query_insere_ocorrencia_inscricao = mysqli_query($con,$sql_insere_ocorrencia_inscricao);
								if($query_insere_ocorrencia_inscricao)
								{
									gravarLog($sql_insere_ocorrencia_inscricao);
									$mensagem = "Dados inseridos com sucesso!";	
								}
								else
								{
									$mensagem = "Erro(1)";	
								}
							}
							else
							{
								$mensagem =  "Erro (2)";
							}
						}
						else
						{
							$sql_atualiza_oficinas = "UPDATE ig_oficinas SET
							`certificado` = '$certificado',
							`vagas` = '$vagas',
							`publico` =  '$publico',
							`material` =  '$material',
							`inscricao` = '$forma_inscricao',
							`valorHora` = '$hora_aula',
							`venda` = '$venda',
							`cargaHoraria` = '$carga_horaria',
							`divulgacao` = '$divulga_inscricao'
							WHERE idEvento = '$idEvento'";	
							$query_atualiza_oficinas = mysqli_query($con,$sql_atualiza_oficinas);
							if($query_atualiza_oficinas)
							{
								gravarLog($sql_atualiza_oficinas);
								$sql_atualiza_ocorrencia_oficinas = "UPDATE ig_ocorrencia SET
								`dataInicio` = '$inicio_inscricao',
								`dataFinal` = '$encerra_inscricao',
								`publicado` = '1'
								WHERE idEvento = '$idEvento';
								";
								verificaMysql($sql_atualiza_ocorrencia_oficinas);
								$query_atualiza_ocorrencia_oficinas = mysqli_query($con,$sql_atualiza_ocorrencia_oficinas);
								if($query_atualiza_ocorrencia_oficinas)
								{
									gravarLog($sql_atualiza_ocorrencia_oficinas);
									$mensagem = "Atualizado com sucesso!";	
								}
								else
								{
									$mensagem = "Erro (4)";	
								}
							}
							else
							{
								$mensagem = "Erro (3)";	
							} 
							//atualiza novos dados de oficina		
						}
					}
					$oficina = recuperaDados("ig_oficinas",$_SESSION['idEvento'],"idEvento");
					$data_oficinas = recuperaDados("ig_ocorrencia",$_SESSION['idEvento'],"idEvento");
		?>
					<h3>Oficinas, Palestras e Debates</h3>
					<h4><? if(isset($mensagem)){echo $mensagem;} ?></h4>
					<div class="form-group">
                  		<div class="col-md-offset-2 col-md-6"><strong>Certificado:</strong><br/>
							<select class="form-control" id="tipoDocumento" name="certificado" >
							   <option value="0" <?php if($oficina['certificado'] == 0){ echo "selected"; }  ?> >Não</option>
							   <option value="1" <?php if($oficina['certificado'] == 1){ echo "selected"; }  ?> >Sim</option>
							</select>
						</div>				  
						<div class=" col-md-6"><strong>Vagas:</strong><br/>
							<input type="text" class="form-control" id="Estado" name="vagas" placeholder="" value = "<?php echo $oficina['vagas'] ?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Público-alvo*</label>
							<textarea name="publico" class="form-control" rows="10" placeholder=""><?php echo $oficina['publico'] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8"><strong>Material Requisitado:</strong><br/>
							<input type="text" class="form-control" id="RazaoSocial" name="material" placeholder="" value = "<?php echo $oficina['material'] ?>" >
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6"><strong>Forma de inscrição:</strong><br/>
							<select class="form-control" id="tipoDocumento" name="forma_inscricao" >
								<option value="1" <?php if($oficina['inscricao'] == 1){ echo "selected"; }  ?> >Sem necessidade</option>
								<option value="2" <?php if($oficina['inscricao'] == 2){ echo "selected"; }  ?>>Pelo site - ficha de inscrição</option>
								<option value="3" <?php if($oficina['inscricao'] == 3){ echo "selected"; }  ?>>Pelo site - por email</option>
								<option value="4" <?php if($oficina['inscricao'] == 4){ echo "selected"; }  ?>>Pessoalmente</option>
							</select>
						</div>				  
						<div class=" col-md-6"><strong>Início de inscrição:</strong><br/>
						  <input type="text" class="form-control datepicker" id="" name="inicio_inscricao" placeholder="" value = "<?php echo exibirDataBr($data_oficinas['dataInicio']) ?>">
						</div>
					</div>
				    <div class="form-group">
                  		<div class="col-md-offset-2 col-md-6"><strong>Encerramento de inscrição:</strong><br/>
							<input type="text" class="form-control datepicker" id="" name="encerra_inscricao" placeholder="" value = "<?php echo exibirDataBr($data_oficinas['dataFinal']) ?>">
						</div>				  
						<div class=" col-md-6"><strong>Divulgação de inscrição:</strong><br/>
							<input type="text" class="form-control datepicker" id="" name="divulga_inscricao" placeholder="" value = "<?php echo exibirDataBr($oficina['divulgacao']) ?>">
						</div>
					</div>
					<div class="form-group">
                  		<div class="col-md-offset-2 col-md-6"><strong>Valor hora/aula:</strong><br/>
							<input type="text" class="form-control" id="Valor" name="hora_aula" placeholder="" value = "<?php echo $oficina['valorHora'] ?>">
						</div>				  
						<div class=" col-md-6"><strong>Venda de material:</strong><br/>
							<select class="form-control" id="tipoDocumento" name="venda" >
								<option value="0" <?php if($oficina['venda'] == 0){ echo "selected"; }  ?> >Não</option>
								<option value="1" <?php if($oficina['venda'] == 1){ echo "selected"; }  ?> >Sim</option>
							</select>
						</div>
					</div>
                  	<div class="form-group">
						<div class="col-md-offset-2 col-md-8"><strong>Carga Horária (em horas):</strong><br/>
							<input type="text" class="form-control" id="RazaoSocial" name="carga_horaria" placeholder="" value = "<?php echo $oficina['cargaHoraria'] ?>" >
						</div>
					</div>
				    <div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Discrimine o material (CD, DVD, impresso, camiseta, etc)</label>
							<textarea name="material_venda" class="form-control" rows="10" placeholder=""></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="atualizar" value="1" />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>
			<?php
				break;
				case 2: // Artes Visuais
					$idTabela = "ig_artes_visuais";
					$idCampo = "idEvento";
					$idDado = $_SESSION['idEvento'];
					$st = 0;
					if(isset($_POST['atualizar']))
					{
						$ig_artesvisuais_numero = $_POST['ig_artesvisuais_numero'];
						$ig_artesvisuais_tipo = $_POST['ig_artesvisuais_tipo'];
						$ig_artesvisuais_valorTotal = dinheiroDeBr($_POST['ig_artesvisuais_valorTotal']);
						//verifica se existe um registro na tabela
						$ver = verificaExiste($idTabela,$idCampo,$idDado,$st);
						if($ver['numero'] == 0)
						{
							// insere um registro novo
							$sql_insere_artes = "INSERT INTO  `ig_artes_visuais` (`idArtes` ,`idEvento` ,`numero` ,`tipo` ,`valorTotal`)VALUES (NULL ,  '$idDado',  '$ig_artesvisuais_numero',  '$ig_artesvisuais_tipo',  '$ig_artesvisuais_valorTotal');";
							if(mysqli_query($con,$sql_insere_artes))
							{		
								$mensagem = "Atualizado com sucesso! ";	
								gravarLog($sql_insere_artes); //grava log
							}
							else
							{
								$mensagem = "Erro ao atualizar!";
							}
						}
						else
						{
							//atualiza o registro existente
							$sql_atualiza_artes = "UPDATE ig_artes_visuais SET numero = '$ig_artesvisuais_numero', tipo = '$ig_artesvisuais_tipo', valorTotal = '$ig_artesvisuais_valorTotal'";
							if(mysqli_query($con,$sql_atualiza_artes))
							{		
								$mensagem = "Atualizado com sucesso! ";	
								gravarLog($sql_atualiza_artes); //grava log
							}
							else
							{
								$mensagem = "Erro ao atualizar!";
							}
						}
					}
					$artes = recuperaDados($idTabela,$_SESSION['idEvento'],$idCampo);
			?>
<h3>Artes Visuais</h3>
<h4><? if(isset($mensagem)){echo $mensagem;} ?></h4>
<div class="form-group">
	<div class="col-md-offset-2 col-md-6">
			<label>Número de contratados</label>
			<input type="text" class="form-control" name="ig_artesvisuais_numero" value="<?php if(isset($artes)){echo $artes['numero'];} ?>" id="" placeholder="">
	</div>
	<div class=" col-md-6">
		<label>Tipo de contratação</label>
		<select class="form-control" name="ig_artesvisuais_tipo" id="inputSubject" >
			<option value="Edital" <?php if(isset($artes)){if($artes['tipo'] == "Edital"){echo "selected";}} ?> >Edital</option>
			<option value="Selecionado" <?php if(isset($artes)){if($artes['tipo'] == "Selecionado"){echo "selected";}} ?>>Selecionado</option>
			<option value="Jurado" <?php if(isset($artes)){if($artes['tipo'] == "Jurado"){echo "selected";}} ?>>Jurado</option>
		</select>
	</div>
</div>
<div class="form-group">
	<div class="col-md-offset-2 col-md-8">
		<label>Valor do cachê *</label>
		<input type="text" name="ig_artesvisuais_valorTotal" class="form-control" id="valor" value="<?php if(isset($artes)){echo dinheiroParaBr($artes['valorTotal']);} ?>"/>
	</div> 
</div>
<div class="form-group">
	<div class="col-md-offset-2 col-md-8">
		<input type="hidden" name="atualizar" value="1" />
		<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
	</div>
</div>
			<?php 
				break;
				case 11:
				case 12:
				case 19:
				case 21:
					$idTabela = "ig_musica";
					$idCampo = "ig_evento_idEvento";
					$idDado = $_SESSION['idEvento'];
					$st = 0;
					if(isset($_POST['atualizar']))
					{
						$ig_musica_genero = $_POST['ig_musica_genero'];
						$ig_musica_venda = $_POST['ig_musica_venda'];
						$ig_musica_material = addslashes($_POST['ig_musica_material']);
						//verifica se existe um registro na tabela
						$ver = verificaExiste($idTabela,$idCampo,$idDado,$st);
						if($ver['numero'] == 0)
						{
							// insere um registro novo
							$sql_insere_musica = "INSERT INTO  `ig_musica` (`idMusica` ,`ig_evento_idEvento` ,`genero` ,`venda` ,`material`)VALUES (NULL ,  '$idDado',  '$ig_musica_genero',  '$ig_musica_venda','$ig_musica_material');";
							if(mysqli_query($con,$sql_insere_musica))
							{		
								$mensagem = "Atualizado com sucesso! ";	
								gravarLog($sql_insere_musica); //grava log
							}
							else
							{
								$mensagem = "Erro ao atualizar(3)!";
							}
						}
						else
						{
							//atualiza o registro existente
							$sql_atualiza_musica = "UPDATE ig_musica SET genero = '$ig_musica_genero', venda = '$ig_musica_venda', material = '$ig_musica_material' WHERE ig_evento_idEvento = $idDado";
							if(mysqli_query($con,$sql_atualiza_musica))
							{		
								$mensagem = "Atualizado com sucesso! ";	
								gravarLog($sql_atualiza_musica); //grava log
							}
							else
							{
								$mensagem = "Erro ao atualizar(4)!";
							}
						}
					}
					$artes = recuperaDados($idTabela,$_SESSION['idEvento'],$idCampo);
			?>
<h3>Música</h3>
<h4><? if(isset($mensagem)){echo $mensagem;} ?></h4>
<div class="form-group">
	<div class="col-md-offset-2 col-md-6">
		<label>Gênero</label>
		<input type="text" class="form-control" name="ig_musica_genero" value="<?php if(isset($artes)){echo $artes['genero'];} ?>" id="" placeholder="Erudito, popular, rock, samba, experimental, etc">
	</div>
	<div class=" col-md-2">
		<label>Venda de material</label>
		<select class="form-control" name="ig_musica_venda" id="inputSubject" >
			<option value="1" <?php if(isset($artes)){if($artes['venda'] == "1"){echo "selected";}} ?> >Sim</option>
			<option value="0" <?php if(isset($artes)){if($artes['venda'] == "0"){echo "selected";}} ?>>Não</option>
		</select>
	</div>
</div>
<div class="form-group">
	<div class="col-md-offset-2 col-md-8">
		<label>Descrição o material</label>
		<textarea name="ig_musica_material" class="form-control" rows="10" placeholder="Livro, camiseta, CD, DVD, etc"><?php echo $artes["material"] ?></textarea>
	</div> 
</div>   
<div class="form-group">
	<div class="col-md-offset-2 col-md-8">
		<input type="hidden" name="atualizar" value="1" />
		<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
	</div>
</div>
			<?php
				break;
				case 3:
				case 7:
				case 9:
				case 14:
				case 17:
				case 27:
				case 29:
					$idTabela = "ig_teatro_danca";
					$idCampo = "ig_evento_idEvento";
					$idDado = $_SESSION['idEvento'];
					$st = 0;
					if(isset($_POST['atualizar']))
					{
						//Atualizar 02		
						$ig_teatro_danca_estreia = $_POST['ig_teatro_danca_estreia'];
						$ig_teatro_danca_genero = $_POST['ig_teatro_danca_genero'];
						$ig_teatro_danca_venda = $_POST['ig_teatro_danca_venda'];
						$ig_teatro_danca_material = $_POST['ig_teatro_danca_material'];
						//verifica se existe um registro na tabela
						$ver = verificaExiste($idTabela,$idCampo,$idDado,$st);
						if($ver['numero'] == 0)
						{
							// insere um registro novo 03
							$sql_insere_teatro = "INSERT INTO  `ig_teatro_danca` (`idTeatro` ,`ig_evento_idEvento` ,`estreia` ,`genero`, `venda`, `material`)VALUES (NULL ,  '$idDado',  '$ig_teatro_danca_estreia',  '$ig_teatro_danca_genero', '$ig_teatro_danca_venda', '$ig_teatro_danca_material' );";
							if(mysqli_query($con,$sql_insere_teatro))
							{
								//04		
								$mensagem = "Atualizado com sucesso! ";	
								gravarLog($sql_insere_teatro); //grava log
							}
							else
							{
								//04
								$mensagem = "Erro ao atualizar!";
							}
							//04
						}
						else
						{
							//atualiza o registro existente 03
							$sql_atualiza_teatro = "UPDATE ig_teatro_danca SET estreia = '$ig_teatro_danca_estreia', genero = '$ig_teatro_danca_genero', venda = '$ig_teatro_danca_venda', material = '$ig_teatro_danca_material' WHERE ig_evento_idEvento = $idDado";
							if(mysqli_query($con,$sql_atualiza_teatro))
							{	//05	
								$mensagem = "Atualizado com sucesso!";	
								gravarLog($sql_atualiza_teatro); //grava log
							}
							else
							{
								//05
								$mensagem = "Erro ao atualizar!";
							}//05
						}//insere um novo registro 03
					}//Atualizar 02
					$artes = recuperaDados($idTabela,$_SESSION['idEvento'],$idCampo);
			?>
<h3>Teatro / Dança</h3>
<h4><? if(isset($mensagem)){echo $mensagem;} ?></h4>
<div class="form-group">
	<div class="col-md-offset-2 col-md-2">
		<label>Estréia?</label>
		<select class="form-control" name="ig_teatro_danca_estreia" id="inputSubject" >
			<option value="1" <?php if(isset($artes)){if($artes['estreia'] == "1"){echo "selected";}} ?> >Sim</option>
			<option value="0" <?php if(isset($artes)){if($artes['estreia'] == "0"){echo "selected";}} ?>>Não</option>
		</select>
	</div>
	<div class=" col-md-6">
		<label>Gênero</label>
		<input type="text" class="form-control" name="ig_teatro_danca_genero" value="<?php if(isset($artes)){echo $artes['genero'];} ?>" id="" placeholder="">
	</div>
	<div class=" col-md-2">
		<label>Venda de material</label>
		<select class="form-control" name="ig_teatro_danca_venda" id="inputSubject" >
			<option value="1" <?php if(isset($artes)){if($artes['venda'] == "1"){echo "selected";}} ?> >Sim</option>
			<option value="0" <?php if(isset($artes)){if($artes['venda'] == "0"){echo "selected";}} ?>>Não</option>
		</select>
	</div>
</div>
<div class="form-group">
	<div class="col-md-offset-2 col-md-8">
		<label>Descrição do material</label>
		<textarea name="ig_teatro_danca_material" id="inputSubject" class="form-control" rows="10" placeholder="Livro, camiseta, CD, DVD, etc"><?php echo $artes["material"] ?></textarea>
	</div> 
</div>
<div class="form-group">
	<div class="col-md-offset-2 col-md-8">
		<input type="hidden" name="atualizar" value="1" />
		<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
	</div>
</div>
			<?php 
				break;
				default:
			?>
<div class="form-group">
	<div class="col-md-offset-2 col-md-8">
                <?php
					if($_SESSION['cinema'] == 1)
					{
						echo "<h4>Você selecionou o evento como 'Mostra de Cinema'. Por favor, acesse o Módulo Cinema no Menu.</h4>";
					}
					else
					{ 
						echo "<h4>Não há campos específicos para o tipo de evento selecionado</h4>";
					}
				?>
    </div>
</div>
			<?php
				break;
			}// fim da especificidade
		break;
		case "externos" :
			include "../include/menuEvento.php";
			$idTabela = "ig_servico";
			$idCampo = "ig_evento_idEvento";
			$idDado = $_SESSION['idEvento'];
			$st = 0;
			if(isset($_POST['atualizar']))
			{
				//carrega as variáveis
				$ig_servico_legenda = $_POST['ig_servico_legenda'];
				$ig_servico_traducao = $_POST['ig_servico_traducao'];
				$ig_servico_seguro = $_POST['ig_servico_seguro'];
				$ig_servico_transporte = $_POST['ig_servico_transporte'];
				$ig_servico_montagem = $_POST['ig_servico_montagem'];
				$ig_servico_passagens = $_POST['ig_servico_passagens'];
				$ig_servico_itinerario = $_POST['ig_servico_itinerario'];
				$ig_servico_hospedagem = $_POST['ig_servico_hospedagem'];
				$ig_servico_locacao = $_POST['ig_servico_locacao'];
				$ig_servico_bilhetagem = $_POST['ig_servico_bilhetagem'];	
				//verifica se existe um registro na tabela
				$ver = verificaExiste($idTabela,$idCampo,$idDado,$st);
				if($ver['numero'] == 0)
				{
					// insere um registro novo
					$sql_insere_ext = "INSERT INTO `ig_servico` (`idServico`, `ig_evento_idEvento`, `legenda`, `traducao`, `graficos`, `passagens`, `itinerario`, `libras`, `audiodescricao`, `montagem`, `hospedagem`, `seguro`, `transporte`, `razaoSocial`, `cpfCnpj`, `banco`, `agencia`, `conta`, `bilhetagem`, `locacao`) VALUES (NULL, '$idDado', '$ig_servico_legenda', '$ig_servico_traducao', NULL, '$ig_servico_passagens', '$ig_servico_itinerario', NULL, NULL, '$ig_servico_montagem', '$ig_servico_hospedagem', '$ig_servico_seguro', '$ig_servico_transporte', NULL, NULL, NULL, NULL, NULL, '$ig_servico_bilhetagem', '$ig_servico_locacao');";
					if(mysqli_query($con,$sql_insere_ext))
					{		
						$mensagem_s = "Atualizado com sucesso! ";	
						gravarLog($sql_insere_ext); //grava log
					}
					else
					{
						$mensagem_s = "Erro ao atualizar(1)!";
					}
				}
				else
				{
					//atualiza o registro existente
					$sql_atualiza_ext = "UPDATE `ig_servico` SET `legenda` = '$ig_servico_legenda', `traducao` = '$ig_servico_traducao', `seguro` = '$ig_servico_seguro', `transporte` = '$ig_servico_transporte',`montagem` = '$ig_servico_montagem', `passagens`= '$ig_servico_passagens', `itinerario`= '$ig_servico_itinerario',`hospedagem` = '$ig_servico_hospedagem',`locacao` = '$ig_servico_locacao',`bilhetagem` = '$ig_servico_bilhetagem'  WHERE `ig_evento_idEvento` = '$idDado'";
					if(mysqli_query($con,$sql_atualiza_ext))
					{		
						$mensagem_s = "Atualizado com sucesso! ";	
						gravarLog($sql_atualiza_ext); //grava log
					}
					else
					{
						$mensagem_s = "Erro ao atualizar(2)!";
					}
				}
			}
			$externo = recuperaDados($idTabela,$_SESSION['idEvento'],$idCampo); 
			$campo = recuperaEvento($_SESSION['idEvento']); //carrega os dados do evento em questão
			?>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Previsão de demandas de serviços externos</h3>
                    <h1><?php echo $campo["nomeEvento"] ?>  </h1>
                    <h4><?php if(isset($mensagem_s)){echo $mensagem_s;} ?></h4>
                </div>
            </div>
		</div>
		<div class="row">
			<div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=externos" class="form-horizontal" role="form">
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Legenda / legendagem </label>
							<input type="text" name="ig_servico_legenda" class="form-control" id="inputSubject" value="<?php echo $externo['legenda'] ?>"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Tradução </label>
							<input type="text" name="ig_servico_traducao" class="form-control" id="inputSubject" value="<?php echo $externo['traducao'] ?>"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Seguro </label>
							<input type="text" name="ig_servico_seguro" class="form-control" id="inputSubject" value="<?php echo $externo['seguro'] ?>"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Transporte </label>
							<input type="text" name="ig_servico_transporte" class="form-control" id="inputSubject" value="<?php echo $externo['transporte'] ?>"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Montagem fina</label>
							<input type="text" name="ig_servico_montagem" class="form-control" id="inputSubject" value="<?php echo $externo['montagem'] ?>"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Passagem aérea </label>
							<input type="text" name="ig_servico_passagens" class="form-control" id="inputSubject" value="<?php echo $externo['passagens'] ?>"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Descrição das passagens aéreas</label>
							<textarea name="ig_servico_itinerario" class="form-control" rows="10" placeholder="Descreva as datas, locais de ida e volta para as passagens aéreas."><?php echo $externo["itinerario"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Hospedagem</label>
							<input type="text" name="ig_servico_hospedagem" class="form-control" id="inputSubject" value="<?php echo $externo['hospedagem'] ?>"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Equipamentos para locação</label>
							<textarea name="ig_servico_locacao" class="form-control" rows="10" placeholder="Descreva equipamentos para locação."><?php echo $externo["locacao"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Bilhetagem</label>
							<textarea name="ig_servico_bilhetagem" class="form-control" rows="10" placeholder="Ingresso rápido: Nome/Razão Social, CPF/CNPJ, Banco, Agência, Conta"><?php echo $externo["bilhetagem"] ?></textarea>
						</div> 
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="atualizar" value="1" />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>  
	<?php 
		break;
		case "arquivos" :
			if(isset($_POST['apagar']))
			{
				$con = bancoMysqli();
				$idArquivo = $_POST['apagar'];
				$sql_apagar_arquivo = "UPDATE ig_arquivo SET publicado = '0' WHERE idArquivo = '$idArquivo'";
				if(mysqli_query($con,$sql_apagar_arquivo))
				{
					$arq = recuperaDados("ig_arquivo",$idArquivo,"idArquivo");
					$mensagem =	"Arquivo ".$arq['arquivo']."apagado com sucesso!";
					gravarLog($sql_apagar_arquivo);
				}
				else
				{
					$mensagem = "Erro ao apagar o arquivo. Tente novamente!";
				}
			}
			$campo = recuperaEvento($_SESSION['idEvento']); //carrega os dados do evento em questão
			include "../include/menuEvento.php";
	?>

	<section id="list_items" class="home-section bg-white">
		<div class="container">
			<div class="row">
				<div class="col-md-offset-2 col-md-8">
					<div class="section-heading">
						<h2>Arquivos anexados</h2>
						<?php
						if( isset( $_POST['enviar'] ) )
						{
							$pathToSave = '../uploads/';
							// A variavel $_FILES é uma variável do PHP, e é ela a responsável por tratar arquivos que sejam enviados em um formulário
							// Nesse caso agora, a nossa variável $_FILES é um array com 3 dimensoes e teremos de trata-lo, para realizar o upload dos arquivos
							// Quando é definido o nome de um campo no form html, terminado por [] ele é tratado como se fosse um array, e por isso podemos ter varios campos com o mesmo nome
							$i = 0;
							$msg = array( );
							$arquivos = array( array( ) );
							foreach(  $_FILES as $key=>$info )
							{
								foreach( $info as $key=>$dados )
								{
									for( $i = 0; $i < sizeof( $dados ); $i++ )
									{
										// Aqui, transformamos o array $_FILES de:
										// $_FILES["arquivo"]["name"][0]
										// $_FILES["arquivo"]["name"][1]
										// $_FILES["arquivo"]["name"][2]
										// $_FILES["arquivo"]["name"][3]
										// para
										// $arquivo[0]["name"]
										// $arquivo[1]["name"]
										// $arquivo[2]["name"]
										// $arquivo[3]["name"]
										// Dessa forma, fica mais facil trabalharmos o array depois, para salvar o arquivo
										$arquivos[$i][$key] = $info[$key][$i];
									}
								}
							}
							$i = 1;
							// Fazemos o upload normalmente, igual no exemplo anterior
							foreach( $arquivos as $file )
							{
								// Verificar se o campo do arquivo foi preenchido
								if( $file['name'] != '' )
								{
									$con = bancoMysqli();
									$dataUnique = date('YmdHis');
									$arquivoTmp = $file['tmp_name'];
									$arquivo = $pathToSave.$dataUnique."_".semAcento($file['name']);
									$arquivo_base = $dataUnique."_".semAcento($file['name']);
									if(file_exists($arquivo))
									{
										echo "O arquivo ".$arquivo_base." já existe! Renomeie e tente novamente<br />";
									}
									else
									{
										$idEvento = $_SESSION['idEvento'];
										//include "../include/conecta_mysql.php";
										$sql = "INSERT INTO ig_arquivo (idArquivo , arquivo , ig_evento_idEvento, publicado) VALUES( NULL , '$arquivo_base' , '$idEvento', '1' );";
										mysqli_query($con,$sql);
										gravarLog($sql);
										if( !move_uploaded_file( $arquivoTmp, $arquivo ) )
										{
											$msg[$i] = 'Erro no upload do arquivo '.$i;
										}
										else
										{
											$msg[$i] = sprintf('Upload do arquivo %s foi um sucesso!',$i);
										}
									}
								}
								$i++;
							}
							// Imprimimos as mensagens geradas pelo sistema
							foreach( $msg as $e )
							{
								echo " <div id = 'mensagem_upload'>";
								printf('%s<br>', $e);
								echo " </div>";
							}
						}
					?>
						<h5>Se na lista abaixo, o seu arquivo começar com "http://", por favor, clique, grave em seu computador, faça o upload novamente e apague a ocorrência citada.</h5>
					</div>
					<div class="table-responsive list_info">
						<?php listaArquivos($_SESSION['idEvento']); ?>
					</div>
				</div>
			</div>  
		</div>
	</section>

<section id="enviar" class="home-section bg-white">
	<div class="container">
		<div class="row">
			<div class="col-md-offset-2 col-md-8">
				<div class="section-heading">
					<h1><?php echo $campo["nomeEvento"] ?>  </h1>
					<h3>Envio de Arquivos</h3>
					<p>Nesta página você envia os arquivos como o rider, mapas de cenas e luz, logos de parceiros, programação de filmes de mostras de cinema, entre outros arquivos destinados à comunicação e produção. O tamanho máximo do arquivo deve ser 60MB.</p>
					<p>Não envie cópias de documentos nesta página. Para o envio, vá até a área de "<a href="?perfil=contratados&p=lista">Pedidos de contratação</a>" e anexe direto em cada contratado.</p>
					<p> Em caso de envio de fotografia, considerar as seguintes especificações técnicas:<br />
					- formato: horizontal <br />
					- tamanho: mínimo de 300dpi”</p>

					<br />
					<div class = "center">
						<form method='POST' action="?perfil=evento&p=arquivos" enctype='multipart/form-data'>
							<p><input type='file' name='arquivo[]'></p>
							<p><input type='file' name='arquivo[]'></p>
							<p><input type='file' name='arquivo[]'></p>
							<p><input type='file' name='arquivo[]'></p>
							<p><input type='file' name='arquivo[]'></p>
							<p><input type='file' name='arquivo[]'></p>
							<p><input type='file' name='arquivo[]'></p>
							<p><input type='file' name='arquivo[]'></p>
							<p><input type='file' name='arquivo[]'></p>
							<br>
							<input type="submit" class="btn btn-theme btn-lg btn-block" value='Enviar' name='enviar'>
						</form>
					</div>
				</div>
			</div>      
		</div>
	</div>
</section>

	<?php 
		break;
		case "ocorrencias" :
			include "../include/menuEvento.php";
			if(isset($_POST['dataInicio']))
			{
				//carrega as variaveis vindas do POST
				$dataInicio = exibirDataMysql($_POST['dataInicio']);
				if(isset($_POST['dataFinal']))
				{
					if($_POST['dataFinal'] != "")
					{
						$dataFinal = exibirDataMysql($_POST['dataFinal']);
					}
					else
					{
						$dataFinal = '0000-00-00';
					}
				}
				else
				{
					$dataFinal = NULL;
				}
				if(($dataFinal == "") OR ($dataFinal == '0000-00-00'))
				{
					$tipoOcorrencia = 3; // Tipo de Ocorrência data única
				}
				else
				{
					$tipoOcorrencia = 4; // Tipo de Ocorrência por temporada
				}
				if(isset($_POST['idSubEvento']))
				{ //Tipo de Ocorrência de Sub-evento
					if($_POST['idSubEvento'] == 0)
					{
						$idSubEvento = NULL;	
					}
					else
					{
						$tipoOcorrencia = 6;
						$idSubEvento = $_POST['idSubEvento'];	
					}
				}
				$ig_comunicao_idCom = 0;
				if(isset($_POST['segunda']))
				{
					$segunda = 1;
				}
				else
				{
					$segunda = 0;
				}		
				if(isset($_POST['terca']))
				{
					$terca = 1;
				}
				else
				{
					$terca = 0;
				}
				if(isset($_POST['quarta']))
				{
					$quarta = 1;
				}
				else
				{
					$quarta = 0;
				}
				if(isset($_POST['quinta']))
				{
					$quinta = 1;
				}
				else
				{
					$quinta = 0;
				}
				if(isset($_POST['sexta']))
				{
					$sexta = 1;
				}
				else
				{
					$sexta = 0;
				}
				if(isset($_POST['sabado']))
				{
					$sabado = 1;
				}
				else
				{
					$sabado = 0;
				}
				if(isset($_POST['domingo']))
				{
					$domingo = 1;
				}
				else
				{
					$domingo = 0;
				}
				if(isset($_POST['virada']))
				{
					$virada = 1;
				}
				else
				{
					$virada = 0;
				}
				if ($segunda == 0 AND $terca == 0 AND $quarta == 0 AND $quinta == 0 AND $sexta == 0 AND $sabado == 0 AND $domingo == 0)
				{
					$segunda = 1;
					$terca = 1;
					$quarta = 1;
					$quinta = 1;
					$sexta = 1;
					$sabado = 1;
					$domingo = 1;
				}
				if ($virada == 1) 
				{
					$segunda = 0;
					$terca = 0;
					$quarta = 0;
					$quinta = 0;
					$sexta = 0;
					$sabado = 1;
					$domingo = 1;
				}
				if(isset($_POST['libras']))
				{
					$libras = 1;
				}
				else
				{
					$libras = 0;
				}
				if(isset($_POST['audiodescricao']))
				{
					$audiodescricao = 1;
				}
				else
				{
					$audiodescricao = 0;
				}
				if(isset($_POST['diaEspecial']))
				{
					$diaEspecial = 1;
				}
				else
				{
					$diaEspecial = 0;
				}
				if(isset($_POST['precoPopular']))
				{
					$precoPopular = 1;
				}
				else
				{
					$precoPopular = 0;
				}
				if(isset($_POST['duracao']))
				{
					$duracao = $_POST['duracao'];
				}
				else
				{
					$duracao = 0;
				}
				$hora = $_POST['hora'];
				$horaInicio = $hora.":00"; //completa os segundos
				$valorIngresso = $_POST['valorIngresso'];
				$horaFinal = "00:00:00";
				$timezone = -3;
				$diaInteiro = 0;
				$localOutros = "";
				$lotacao = $_POST['ingressosDisponiveis'];
				$reservados = $_POST['ingressosReservados'];
				$retiradaIngresso = $_POST['retiradaIngresso'];
				$instituicao = $_POST['instituicao'];
				$local = $_POST['local'];
				$frequencia = 0;
				$idEvento = $_SESSION['idEvento'];
				$publicado = 1;
				$observacao = $_POST['observacao'];
                $subprefeitura = $_POST['subprefeitura'];
                $periodo = $_POST['periodo'];

            }
			if(isset($_POST['inserir']))
			{
				$sql_inserir = "INSERT INTO `ig_ocorrencia` (`idOcorrencia`, `idTipoOcorrencia`, `ig_comunicao_idCom`, `local`, `idEvento`, `segunda`, `terca`, `quarta`, `quinta`, `sexta`, `sabado`, `domingo`, `dataInicio`, `dataFinal`, `horaInicio`, `horaFinal`, `timezone`, `diaInteiro`, `diaEspecial`, `libras`, `audiodescricao`, `valorIngresso`, `retiradaIngresso`, `localOutros`, `lotacao`, `reservados`, `duracao`, `precoPopular`, `frequencia`, `publicado`,`idSubEvento`, `virada`, `observacao`, `subprefeitura_id`, `idPeriodoDia`) 
                                VALUES 
                                (NULL, '$tipoOcorrencia', NULL, '$local', '$idEvento', '$segunda', '$terca', '$quarta', '$quinta', '$sexta', '$sabado', '$domingo', '$dataInicio', '$dataFinal', '$horaInicio', '$horaFinal', '$timezone', '$diaInteiro', '$diaEspecial', '$libras', '$audiodescricao', '$valorIngresso', '$retiradaIngresso', '$localOutros', '$lotacao', '$reservados', '$duracao', '$precoPopular', '$frequencia', '$publicado', '$idSubEvento', '$virada', '$observacao', '$subprefeitura', '$periodo');";
				if (eventoOnline()) {
				    if (validaDataIntegrante($dataInicio)){
				        if (!validaPartIntegrantes()['bol']) {
                            if (mysqli_query($con, $sql_inserir)) {
                                $idOcorrencia = $con->insert_id;
                                atualizadaDataApresentacao($dataInicio, $idOcorrencia);
                                $mensagem = "Ocorrência inserida com sucesso!";
                                gravarLog($sql_inserir);
                            } else {
                                $mensagem = "Erro ao inserir. Tente novamente.";
                            }
                        }else{
                            $mensagem = "Erro! O número de ocorrências para o integrante excedeu.";
				        }
                    } else {
				        $mensagem = "Erro já existe uma ocorrência cadastrada no mesmo periodo para um dos integrantes.";
                    }
				}else {
                    if(mysqli_query($con,$sql_inserir)) {
                        $idOcorrencia = $con->insert_id;
                        $mensagem = "Ocorrência inserida com sucesso!";
                        gravarLog($sql_inserir);
                    }else
                    {
                        $mensagem = "Erro ao inserir. Tente novamente.";
                    }
                }
			}
			if(isset($_POST['atualizar']))
			{
				$idOc = $_POST['atualizar'];
				$sql_atualizar_ocorrencia = "UPDATE ig_ocorrencia SET
				`idTipoOcorrencia` = '$tipoOcorrencia',
				`local` = '$local' ,
				`segunda` = '$segunda',
				`terca` = '$terca',
				`quarta` = '$quarta',
				`quinta` = '$quinta',
				`sexta` = '$sexta',
				`sabado` = '$sabado',
				`domingo` = '$domingo',
				`dataInicio` = '$dataInicio',
				`dataFinal` = '$dataFinal',
				`horaInicio` = '$horaInicio',
				`diaEspecial` = '$diaEspecial',
				`libras` = '$libras',
				`audiodescricao` = '$audiodescricao',
				`valorIngresso` = '$valorIngresso',
				`retiradaIngresso` = '$retiradaIngresso',
				`localOutros` = '$localOutros',
				`lotacao` = '$lotacao',
				`reservados` = '$reservados',
				`duracao` = '$duracao',
				`precoPopular` = '$precoPopular',
				`idSubEvento` = '$idSubEvento',
				`virada` = '$virada',
				`observacao` = '$observacao',
                `subprefeitura_id` = '$subprefeitura',
                `idPeriodoDia` = '$periodo'  
				WHERE 	`idOcorrencia` = '$idOc'";
				$con = bancoMysqli();
				if(mysqli_query($con,$sql_atualizar_ocorrencia))
				{
                    if (eventoOnline()) {
                        atualizadaDataApresentacao($dataInicio, $idOc, true);
                    }
					$mensagem = "Ocorrência atualizada com sucesso!";
					gravarLog($sql_atualizar_ocorrencia);	
				}
				else
				{
					$mensagem = "Erro ao atualizar. Tente novamente.";
				}
			}
			if(isset($_POST['duplicar']))
			{
				$idOc = $_POST['duplicar'];
				$sql_duplicar_ocorrencia = "INSERT INTO ig_ocorrencia (`idTipoOcorrencia`, `ig_comunicao_idCom`, `local`, `idEvento`, `segunda`, `terca`, `quarta`, `quinta`, `sexta`, `sabado`, `domingo`, `dataInicio`, `dataFinal`, `horaInicio`, `horaFinal`, `timezone`, `diaInteiro`, `diaEspecial`, `libras`, `audiodescricao`, `valorIngresso`, `retiradaIngresso`, `localOutros`, `lotacao`, `reservados`, `duracao`, `precoPopular`, `frequencia`, `publicado`, `idSubEvento`, `virada`, `observacao`, `subprefeitura_id`, `idPeriodoDia`) SELECT `idTipoOcorrencia`, `ig_comunicao_idCom`, `local`, `idEvento`, `segunda`, `terca`, `quarta`, `quinta`, `sexta`, `sabado`, `domingo`, `dataInicio`, `dataFinal`, `horaInicio`, `horaFinal`, `timezone`, `diaInteiro`, `diaEspecial`, `libras`, `audiodescricao`, `valorIngresso`, `retiradaIngresso`, `localOutros`, `lotacao`, `reservados`, `duracao`, `precoPopular`, `frequencia`, `publicado`, `idSubEvento`, `virada`, `observacao`, `subprefeitura_id`, `idPeriodoDia` FROM ig_ocorrencia WHERE `idOcorrencia` = '$idOc'";
				if(mysqli_query($con,$sql_duplicar_ocorrencia))
				{
					$mensagem = "Ocorrência duplicada com sucesso!";	
					gravarLog($sql_duplicar_ocorrencia);	
				}
				else
				{
					$mensagem = "Erro ao duplicar. Tente novamente.";
				}
			}
			if(isset($_POST['apagar']))
			{
				$con = bancoMysqli();
				$idOc = $_POST['apagar'];
				$sql_apagar_ocorrencia = "UPDATE ig_ocorrencia SET publicado = '0' WHERE idOcorrencia = $idOc";
				if(mysqli_query($con,$sql_apagar_ocorrencia))
				{
                    if (eventoOnline()) {
                        apagaDataApresentacao($idOc);
                    }
                    $mensagem = "Ocorrência apagada com sucesso!";
					gravarLog($sql_apagar_ocorrencia);	
				}
				else
				{
					$mensagem = "Erro ao atualizar. Tente novamente.";
				}
			}
			// Cria um array com dados do evento
			$campo = recuperaEvento($_SESSION['idEvento']);
			$action = $_GET['action'];

            if (isset($_POST['inserirInstituicao'])) {
                $instituicao = $_POST['nomeInstituicao'];
                $sigla = $_POST['siglaInstituicao'];
                $instituicaoPai = 3;

                $sql = "INSERT INTO ig_instituicao (instituicao, instituicaoPai, sigla) VALUES ('$instituicao', '$instituicaoPai', '$sigla')";

                if (mysqli_query($con, $sql)) {
                    $mensagem = "Instituição inserida com sucesso!";
                } else {
                    $mensagem = "Ocorreu um erro ao inserir a instituição! Tente novamente.";
                }
            }

            if (isset($_POST['inserirLocal'])) {
                $sala = $_POST['nomeSala'];
                $instituicaoId = $_POST['instituicaoId'];
                $cep = $_POST['cep'];
                $logradouro = $_POST['rua'];
                $numero = $_POST['numero'];
                $bairro = $_POST['bairro'];
                $cidade = $_POST['cidade'];
                $estado = $_POST['estado'];
                $telefone = $_POST['telefone'];

                $sql = "INSERT INTO ig_local (sala, lotacao, idInstituicao, publicado, logradouro, numero, bairro, cidade, estado, cep, telefone)
            VALUES ('$sala', 0, '$instituicaoId', 1, '$logradouro', '$numero', '$bairro', '$cidade', '$estado', '$cep', '$telefone')";


                if (mysqli_query($con, $sql)) {
                    $mensagem = "Sala/Espaço inserida com sucesso!";
                } else {
                    $mensagem = "Ocorreu um erro ao inserir a Sala/Espaço! Tente novamente.";
                }
            }
			switch($action)
			{
				case "inserir":
	?>
<script type="application/javascript">
	$(function()
	{
		$('#instituicao').change(function()
		{
			if( $(this).val() )
			{
				$('#local').hide();
				$('.carregando').show();
				$.getJSON('local.ajax.php?instituicao=',{instituicao: $(this).val(), ajax: 'true'}, function(j)
				{
					var options = '<option value=""></option>';	
					for (var i = 0; i < j.length; i++)
					{
						options += '<option value="' + j[i].idEspaco + '">' + j[i].espaco + '</option>';
					}	
					$('#local').html(options).show();
					$('.carregando').hide();
				});
			}
			else
			{
				$('#local').html('<option value="">-- Escolha uma instituição --</option>');
			}
		});
	});
</script>
<script type="text/javascript">
	$(document).ready(function ()
	{
		validate();
		$('#datepicker11').change(validate);
	});
	function validate()
	{
		if ($('#datepicker11').val().length > 0)
		{
			$("#diasemana01").prop("disabled", false);
			$("#diasemana02").prop("disabled", false);
			$("#diasemana03").prop("disabled", false);
			$("#diasemana04").prop("disabled", false);
			$("#diasemana05").prop("disabled", false);
			$("#diasemana06").prop("disabled", false);
			$("#diasemana07").prop("disabled", false);
		}
		else
		{
			$("#diasemana01").prop("disabled", true);
			$("#diasemana02").prop("disabled", true);
			$("#diasemana03").prop("disabled", true);
			$("#diasemana04").prop("disabled", true);
			$("#diasemana05").prop("disabled", true);
			$("#diasemana06").prop("disabled", true);
			$("#diasemana07").prop("disabled", true);
		}
	}

    //Script CEP
    $(document).ready(function () {
        function limpa_formulário_cep() {
            // Limpa valores do formulário de cep.
            $("#rua").val("");
            $("#bairro").val("");
            $("#cidade").val("");
            $("#estado").val("");
        }

        //Quando o campo cep perde o foco.
        $("#cep").blur(function () {
            //Nova variável "cep" somente com dígitos.
            var cep = $(this).val().replace(/\D/g, '');

            //Verifica se campo cep possui valor informado.
            if (cep != "") {

                //Expressão regular para validar o CEP.
                var validacep = /^[0-9]{8}$/;

                //Valida o formato do CEP.
                if (validacep.test(cep)) {

                    //Preenche os campos com "..." enquanto consulta webservice.
                    $("#rua").val("...");
                    $("#bairro").val("...");
                    $("#cidade").val("...");
                    $("#estado").val("...");

                    //Consulta o webservice viacep.com.br/
                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {

                        if (!("erro" in dados)) {
                            //Atualiza os campos com os valores da consulta.
                            $("#rua").prop('readonly', true);
                            $("#bairro").prop('readonly', true);
                            $("#cidade").prop('readonly', true);
                            $("#estado").prop('readonly', true);

                            $("#rua").val(dados.logradouro);
                            $("#bairro").val(dados.bairro);
                            $("#cidade").val(dados.localidade);
                            $("#estado").val(dados.uf);

                            if (dados.logradouro == "") {
                                alert("Por favor preencha o formulário");
                                $("#rua").prop('readonly', false);
                                $("#bairro").prop('readonly', false);
                                $("#cidade").prop('readonly', false);
                                $("#estado").prop('readonly', false);
                            }
                        } else {
                            //CEP pesquisado não foi encontrado.
                            limpa_formulário_cep();
                            alert("CEP não encontrado.");
                        }
                    });
                } else {
                    //cep é inválido.
                    limpa_formulário_cep();
                    alert("Formato de CEP inválido.");
                }
            } else {
                //cep sem valor, limpa formulário.
                limpa_formulário_cep();
            }
        });
    });
</script>
<script type="text/javascript">
	function habilitar()
	{  
		if(document.getElementById('diaEspecial').checked)
		{  
			document.getElementById('especial01').disabled = false;  
			document.getElementById('especial02').disabled = false;  
			document.getElementById('especial03').disabled = false;  
		}
		else
		{
			document.getElementById('especial01').disabled = true;  
			document.getElementById('especial02').disabled = true;  
			document.getElementById('especial03').disabled = true;  
		} 
	}
</script>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Evento - Inserir ocorrências</h3>
                    <h1><?php echo $campo["nomeEvento"] ?> </h1>
                    <p><?php if(isset($mensagem)){echo $mensagem;} ?></p>
                </div>
            </div>
    	</div>
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=ocorrencias&action=listar" class="form-horizontal" role="form">
                    <div class="form-group">  
                        <div class="col-md-offset-2 col-md-8">
 						    <select class="form-control" name="idSubEvento" id="inputSubject" >
								<option>Selecione o sub-evento</option>
								
								<?php geraOpcaoSub($_SESSION['idEvento'],""); ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Data início *</label>
							<input type="text" name="dataInicio" class="form-control" id="datepicker10" placeholder="">
						</div>
						<div class=" col-md-6">
							<label>Data encerramento</label>
							<input type="text" name="dataFinal" class="form-control" id="datepicker11" onblur="validate()" placeholder="só preencha em caso de temporada">
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="segunda" id="diasemana01" disabled="disabled"/><label style="padding:0 10px 0 5px;"> Segunda</label>
							<input type="checkbox" name="terca" id="diasemana02" disabled="disabled"/><label  style="padding:0 10px 0 5px;"> Terça</label>
							<input type="checkbox" name="quarta" id="diasemana03" disabled="disabled"/><label style="padding:0 10px 0 5px;"> Quarta</label>
							<input type="checkbox" name="quinta" id="diasemana04" disabled="disabled"/><label style="padding:0 10px 0 5px;"> Quinta</label>
							<input type="checkbox" name="sexta" id="diasemana05" disabled="disabled"/><label  style="padding:0 10px 0 5px;"> Sexta</label>
							<input type="checkbox" name="sabado" id="diasemana06" disabled="disabled"/><label style="padding:0 10px 0 5px;"> Sábado</label>
							<input type="checkbox" name="domingo" id="diasemana07" disabled="disabled"/><label  style="padding:0 10px 0 5px;"> Domingo</label>
						</div>                     
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="diaEspecial" id="diaEspecial" onclick="habilitar()"/><label  style="padding:0 20px 0 5px;">Dia especial?</label>
							<input type="checkbox" name="audiodescricao" id="especial01" disabled="disabled"/><label  style="padding:0 10px 0 5px;">Audiodescricão</label>
							<input type="checkbox" name="libras" id="especial02" disabled="disabled"/><label  style="padding:0 10px 0 5px;">Libras</label>
							<input type="checkbox" name="precoPopular" id="especial03" disabled="disabled"/><label  style="padding:0 10px 0 5px;">Preço popular</label>
						</div>                     
					</div>
					<div class="form-group">       			
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="virada" id="virada" onclick="habilitar()"/><label  style="padding:0 20px 0 5px;">Virada 2019</label>
						</div>                     
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-2">
							<label>Horário de início *</label>
							<input type="text" name="hora" class="form-control"id="hora" placeholder="hh:mm"/>
						</div> 
						<div class="col-md-3">
							<label>Valor ingresso *</label>
							<input type="text" name="valorIngresso" class="form-control" id="valor" placeholder="em reais">
						</div>
						<div class=" col-md-3">
							<label>Duração *</label>
							<input type="text" id="duracao" name="duracao" class="form-control" id="" placeholder="em minutos">
						</div>
					</div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-8">
                            <label>Período</label>
                            <select class="form-control" name="periodo" id="periodo">
                                <option>Selecione</option>
                                <?php
                                    geraOpcao("ig_periodo_dia", $ocorrencia['idPeriodoDia'], "")
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-8">
                            <label>Subprefeitura</label>
                            <select class="form-control" name="subprefeitura" id="subprefeitura">
                                <option>Selecione</option>
                                <?php
                                geraOpcao("igsis_subprefeitura", $ocorrencia['subprefeitura_id'], "") ?>
                            </select>
                        </div>
                    </div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Sistema de retirada de ingressos</label>
							<select class="form-control" name="retiradaIngresso" id="inputSubject" >
								<option>Selecione</option>
								<?php geraOpcao("ig_retirada","","") ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Local / instituição *</label><img src="images/loading.gif" class="loading" style="display:none" />
							<select class="form-control" name="instituicao" id="instituicao" >
								<option>Selecione</option>
								<?php geraOpcao("ig_instituicao","","") ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Sala / espaço (antes selecione a instituição)</label>
                            <button class='btn btn-default' type='button' data-toggle='modal'
                                    data-target='#adicionaLocal' style="border-radius: 30px;">
                                <i class="fa fa-plus-circle"></i></button>
                            <select class="form-control" name="local" id="local" required>
                                <option value="">Selecione...</option>
							<select class="form-control" name="local" id="local" ></select>
						</div>
					</div>	
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Ingressos disponíveis</label>
							<input type="text" class="form-control" name="ingressosDisponiveis" id="" placeholder="">
						</div>
						<div class=" col-md-6">
							<label>Ingressos reservados</label>
							<input type="text" class="form-control" name="ingressosReservados" id="" placeholder="">
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8"><strong>Observações</strong><br/>
							<textarea name="observacao" class="form-control" cols="40" rows="2"></textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="inserir" value="1"  />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Inserir ocorrência"  />
						 </div>
					</div>
                </form>
            </div>
        </div>
    </div>

    <!-- ADICIONAR INSTITUIÇÃO -->
    <div class="modal fade" id="adicionaInstituicao" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id='instituicaoAdiciona' action="?perfil=evento&p=ocorrencias&action=inserir"
                      class="form-horizontal" role="form">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Cadastrar Instituição</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="instituicao">Instituição</label>
                            <input type="text" class="form-control" name="nomeInstituicao" id="nomeInstituicao" maxlength="80">
                        </div>

                        <div class="form-group">
                            <label for="instituicao">Sigla</label>
                            <input type="text" class="form-control" name="siglaInstituicao" id="siglaInstituicao" maxlength="12">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="hidden" name="idEvento" value="<?= $idEvento ?>">
                        <button type='submit' class='btn btn-info btn-sm' name="inserirInstituicao">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ADICIONAR LOCAL -->
    <div class="modal fade" id="adicionaLocal" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id='localAdiciona' action="?perfil=evento&p=ocorrencias&action=inserir"
                      class="form-horizontal" role="form">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Cadastrar Sala/Espaço</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nomeSala">Sala </label>
                            <input type="text" class="form-control" name="nomeSala" id="nomeSala">
                        </div>

                        <div class="form-group">
                            <label for="instituicaoId">Instituição</label>
                            <select class="form-control" name="instituicaoId" id="instituicaoId" required>
                                <option value="">Selecione...</option>
                                <?php
                                $inst = retornaInstituicao($ocorrencia['local']);
                                geraOpcao("ig_instituicao", $inst, "")
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cep">CEP: *</label>
                            <input type="text" class="form-control" name="cep" id="cep" maxlength="9"
                                   placeholder="Digite o CEP" required data-mask="00000-000">
                        </div>

                        <div class="form-group">
                            <label for="rua">Rua: *</label>
                            <input type="text" class="form-control" name="rua" id="rua" placeholder="Digite a rua"
                                   maxlength="200" readonly>
                        </div>
                        <div class="form-group">
                            <label for="numero">Número: *</label>
                            <input type="number" name="numero" class="form-control" placeholder="Ex.: 10" required>
                        </div>

                        <div class="form-group">
                            <label for="bairro">Bairro: *</label>
                            <input type="text" class="form-control" name="bairro" id="bairro"
                                   placeholder="Digite o Bairro" maxlength="80" readonly>
                        </div>
                        <div class="form-group">
                            <label for="cidade">Cidade: *</label>
                            <input type="text" class="form-control" name="cidade" id="cidade"
                                   placeholder="Digite a cidade" maxlength="50" readonly>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado: *</label>
                            <input type="text" class="form-control" name="estado" id="estado" maxlength="2"
                                   placeholder="Ex.: SP" readonly>
                        </div>
                        <div class="form-group">
                            <label for="estado">Telefone: *</label>
                            <input type="text" class="form-control" name="telefone" id="telefone" required data-mask="(00)0000-0000">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="hidden" name="idEvento" value="<?= $idEvento ?>">
                        <button type='submit' class='btn btn-info btn-sm' name="inserirLocal">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</section>
			<?php
				break;
				case "listar":
			?>
<section id="list_items" class="home-section bg-white">
	<div class="container">
		<div class="row">
			<div class="col-md-offset-2 col-md-8">
				<div class="section-heading">
					<h2>Ocorrências</h2>
					<h4>Selecione o evento para carregar.</h4>
					<h5><?php if(isset($mensagem)){echo $mensagem;} ?></h5>
				</div>
			</div>
		</div>
		<div class="table-responsive list_info">
			<?php listaOcorrencias($_SESSION['idEvento']); ?>
		</div>
	</div>
</section>
			<?php
				break;
				case "editar":
					$idOcorrencia = $_POST['id'];
					$ocor = recuperaDados("ig_ocorrencia",$idOcorrencia,"idOcorrencia");
			?>
<script type="application/javascript">
	$(function()
	{
		$('#instituicao').change(function()
		{
			if( $(this).val() )
			{
				$('#local').hide();
				$('.carregando').show();
				$.getJSON('local.ajax.php?instituicao=',{instituicao: $(this).val(), ajax: 'true'}, function(j)
				{
					var options = '<option value=""></option>';	
					for (var i = 0; i < j.length; i++)
					{
						options += '<option value="' + j[i].idEspaco + '">' + j[i].espaco + '</option>';
					}	
					$('#local').html(options).show();
					$('.carregando').hide();
				});
			}
			else
			{
				$('#local').html('<option value="">-- Escolha uma instituição --</option>');
			}
		});
	});
</script>
<script type="text/javascript">
	$(document).ready(function ()
	{
		validate();
		$('#datepicker11').change(validate);
	});
	function validate()
	{
		if ($('#datepicker11').val().length > 0)
		{
			$("#diasemana01").prop("disabled", false);
			$("#diasemana02").prop("disabled", false);
			$("#diasemana03").prop("disabled", false);
			$("#diasemana04").prop("disabled", false);
			$("#diasemana05").prop("disabled", false);
			$("#diasemana06").prop("disabled", false);
			$("#diasemana07").prop("disabled", false);
		}
		else
		{
			$("#diasemana01").prop("disabled", true);
			$("#diasemana02").prop("disabled", true);
			$("#diasemana03").prop("disabled", true);
			$("#diasemana04").prop("disabled", true);
			$("#diasemana05").prop("disabled", true);
			$("#diasemana06").prop("disabled", true);
			$("#diasemana07").prop("disabled", true);
		}
	}
</script>
<script type="text/javascript">
	function habilitar()
	{  
		if(document.getElementById('diaEspecial').checked)
		{  
			document.getElementById('especial01').disabled = false;  
			document.getElementById('especial02').disabled = false;  
			document.getElementById('especial03').disabled = false;  
		}
		else
		{  
			document.getElementById('especial01').disabled = true;  
			document.getElementById('especial02').disabled = true;  
			document.getElementById('especial03').disabled = true;  
		}  
	}

    //Script CEP
    $(document).ready(function () {
        function limpa_formulário_cep() {
            // Limpa valores do formulário de cep.
            $("#rua").val("");
            $("#bairro").val("");
            $("#cidade").val("");
            $("#estado").val("");
        }

        //Quando o campo cep perde o foco.
        $("#cep").blur(function () {
            //Nova variável "cep" somente com dígitos.
            var cep = $(this).val().replace(/\D/g, '');

            //Verifica se campo cep possui valor informado.
            if (cep != "") {

                //Expressão regular para validar o CEP.
                var validacep = /^[0-9]{8}$/;

                //Valida o formato do CEP.
                if (validacep.test(cep)) {

                    //Preenche os campos com "..." enquanto consulta webservice.
                    $("#rua").val("...");
                    $("#bairro").val("...");
                    $("#cidade").val("...");
                    $("#estado").val("...");

                    //Consulta o webservice viacep.com.br/
                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {

                        if (!("erro" in dados)) {
                            //Atualiza os campos com os valores da consulta.
                            $("#rua").prop('readonly', true);
                            $("#bairro").prop('readonly', true);
                            $("#cidade").prop('readonly', true);
                            $("#estado").prop('readonly', true);

                            $("#rua").val(dados.logradouro);
                            $("#bairro").val(dados.bairro);
                            $("#cidade").val(dados.localidade);
                            $("#estado").val(dados.uf);

                            if (dados.logradouro == "") {
                                alert("Por favor preencha o formulário");
                                $("#rua").prop('readonly', false);
                                $("#bairro").prop('readonly', false);
                                $("#cidade").prop('readonly', false);
                                $("#estado").prop('readonly', false);
                            }
                        } else {
                            //CEP pesquisado não foi encontrado.
                            limpa_formulário_cep();
                            alert("CEP não encontrado.");
                        }
                    });
                } else {
                    //cep é inválido.
                    limpa_formulário_cep();
                    alert("Formato de CEP inválido.");
                }
            } else {
                //cep sem valor, limpa formulário.
                limpa_formulário_cep();
            }
        });
    });
</script>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Evento - Editar ocorrências</h3>
                    <h1><?php echo $campo["nomeEvento"] ?><?php echo $idOcorrencia; ?></h1>
                    <p><?php if(isset($mensagem)){echo $mensagem;} ?></p>
                </div>
            </div>
    	</div>
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=ocorrencias&action=listar" class="form-horizontal" role="form">
                    <div class="form-group">  
                        <div class="col-md-offset-2 col-md-8">
 						    <select class="form-control" name="idSubEvento" id="inputSubject" >
								<option>Selecione o sub-evento</option>
								<option value="0">Não é sub-evento</option>
								<?php geraOpcaoSub($_SESSION['idEvento'],$ocor['idSubEvento']); ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Data início *</label>
							<input type="text" name="dataInicio" class="form-control" id="datepicker10" value="<?php echo exibirDataBr($ocor['dataInicio']) ?>" placeholder="">
						</div>
						<div class=" col-md-6">
							<label>Data encerramento</label>
							<input type="text" name="dataFinal" class="form-control" id="datepicker11" onblur="validate()" value="<?php if($ocor['dataFinal'] != '0000-00-00'){echo exibirDataBr($ocor['dataFinal']);} ?>"placeholder="só preencha se for temporada">
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="segunda" id="diasemana01" disabled="disabled" <?php checar($ocor['segunda']) ?> /><label style="padding:0 10px 0 5px;"> Segunda</label>
							<input type="checkbox" name="terca" id="diasemana02" disabled="disabled" <?php checar($ocor['terca']) ?>/><label  style="padding:0 10px 0 5px;"> Terça</label>
							<input type="checkbox" name="quarta" id="diasemana03" disabled="disabled" <?php checar($ocor['quarta']) ?>/><label style="padding:0 10px 0 5px;"> Quarta</label>
							<input type="checkbox" name="quinta" id="diasemana04" disabled="disabled" <?php checar($ocor['quinta']) ?> /><label style="padding:0 10px 0 5px;"> Quinta</label>
							<input type="checkbox" name="sexta" id="diasemana05" disabled="disabled" <?php checar($ocor['sexta']) ?>/><label  style="padding:0 10px 0 5px;"> Sexta</label>
							<input type="checkbox" name="sabado" id="diasemana06" disabled="disabled" <?php checar($ocor['sabado']) ?>/><label style="padding:0 10px 0 5px;"> Sábado</label>
							<input type="checkbox" name="domingo" id="diasemana07" disabled="disabled" <?php checar($ocor['domingo']) ?>/><label  style="padding:0 10px 0 5px;"> Domingo</label>
						</div>                     
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="diaEspecial" id="diaEspecial" onclick="habilitar()" <?php checar($ocor['diaEspecial']) ?>/><label  style="padding:0 20px 0 5px;">Dia especial?</label>
							<input type="checkbox" name="audiodescricao" id="especial01" disabled="disabled" <?php checar($ocor['audiodescricao']) ?>/><label  style="padding:0 10px 0 5px;">Audiodescricão</label>
							<input type="checkbox" name="libras" id="especial02" disabled="disabled" <?php checar($ocor['libras']) ?>/><label  style="padding:0 10px 0 5px;">Libras</label>
							<input type="checkbox" name="precoPopular" id="especial03" disabled="disabled" <?php checar($ocor['precoPopular']) ?>/><label  style="padding:0 10px 0 5px;">Preço popular</label>
						</div>                     
					</div>
					<div class="form-group">         			
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="virada" id="virada" onclick="habilitar()" <?php checar($ocor['virada']) ?>/><label  style="padding:0 20px 0 5px;">Virada 2019</label>
						</div>                     
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-2">
							<label>Horário de início</label>
							<input type="text" name="hora" class="form-control" id="hora" placeholder="hh:mm" value="<?php echo $ocor['horaInicio'] ?>"/>
						</div> 
						<div class="col-md-3">
							<label>Valor ingresso *</label>
							<input type="text" name="valorIngresso" class="form-control" id="valor" value="<?php echo $ocor['valorIngresso'] ?>" placeholder="em reais">
						</div>
						<div class=" col-md-3">
							<label>Duração *</label>
							<input type="text" id="duracao" name="duracao" class="form-control" value="<?php echo $ocor['duracao'] ?>" placeholder="em minutos">
						</div>
					</div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-8">
                            <label>Período</label>
                            <select class="form-control" name="periodo" id="periodo">
                                <option>Selecione</option>
                                <?php
                                geraOpcao("ig_periodo_dia", $ocor['idPeriodoDia'], "") ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-8">
                            <label>Subprefeitura</label>
                            <select class="form-control" name="subprefeitura" id="subprefeitura">
                                <option>Selecione</option>
                                <?php
                                geraOpcao("igsis_subprefeitura", $ocor['subprefeitura_id'], "") ?>
                            </select>
                        </div>
                    </div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Sistema de retirada de ingressos</label>
							<select class="form-control" name="retiradaIngresso" id="inputSubject" >
								<option>Selecione</option>
								<?php geraOpcao("ig_retirada",$ocor['retiradaIngresso'],"") ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Local / instituição *</label><img src="images/loading.gif" class="loading" style="display:none" />
                            <button class='btn btn-default' type='button' data-toggle='modal'
                                    data-target='#adicionaInstituicao' style="border-radius: 30px;">
                                <i class="fa fa-plus-circle"></i></button>
							<select class="form-control" name="instituicao" id="instituicao" >
								<option>Selecione</option>
								<?php
								$inst = retornaInstituicao($ocor['local']);
								geraOpcao("ig_instituicao",$inst,"") 
								?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Sala / espaço (antes selecione a instituição)</label>
                            <button class='btn btn-default' type='button' data-toggle='modal'
                                    data-target='#adicionaLocal' style="border-radius: 30px;">
                                <i class="fa fa-plus-circle"></i></button>
							<select class="form-control" name="local" id="local" >
								<?php geraOpcao("ig_local",$ocor['local'],$inst); ?>
							</select>
						</div>
					</div>	
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Ingressos disponíveis</label>
							<input type="text" class="form-control" name="ingressosDisponiveis" value="<?php echo $ocor['lotacao'] ?>" id="" placeholder="">
						</div>
						<div class=" col-md-6">
							<label>Ingressos reservados</label>
							<input type="text" class="form-control" name="ingressosReservados" value="<?php echo $ocor['reservados'] ?>" id="" placeholder="">
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8"><strong>Observações</strong><br/>
							<textarea name="observacao" class="form-control" cols="40" rows="4"> <?php echo $ocor['observacao'] ?> </textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="atualizar" value="<?php echo $ocor['idOcorrencia']; ?>"  />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Atualizar ocorrência"  />
						</div>
					</div>
				</form>
			</div>
        </div>
    </div>

    <!-- ADICIONAR INSTITUIÇÃO -->
    <div class="modal fade" id="adicionaInstituicao" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id='instituicaoAdiciona' action="?perfil=evento&p=ocorrencias&action=editar"
                      class="form-horizontal" role="form">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Cadastrar Instituição</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="instituicao">Instituição</label>
                            <input type="text" class="form-control" name="nomeInstituicao" id="nomeInstituicao" maxlength="80">
                        </div>

                        <div class="form-group">
                            <label for="instituicao">Sigla</label>
                            <input type="text" class="form-control" name="siglaInstituicao" id="siglaInstituicao" maxlength="12">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>

                        <input type="hidden" name="id" value="<?= $ocor['idOcorrencia'] ?>">
                        <button type='submit' class='btn btn-info btn-sm' name="inserirInstituicao">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ADICIONAR LOCAL -->
    <div class="modal fade" id="adicionaLocal" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id='localAdiciona' action="?perfil=evento&p=ocorrencias&action=editar"
                      class="form-horizontal" role="form">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Cadastrar Sala/Espaço</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nomeSala">Sala </label>
                            <input type="text" class="form-control" name="nomeSala" id="nomeSala">
                        </div>

                        <div class="form-group">
                            <label for="instituicaoId">Instituição</label>
                            <select class="form-control" name="instituicaoId" id="instituicaoId" required>
                                <option value="18">Espaços abertos</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cep">CEP: *</label>
                            <input type="text" class="form-control" name="cep" id="cep" maxlength="9"
                                   placeholder="Digite o CEP" required data-mask="00000-000">
                        </div>

                        <div class="form-group">
                            <label for="rua">Rua: *</label>
                            <input type="text" class="form-control" name="rua" id="rua" placeholder="Digite a rua"
                                   maxlength="200" readonly>
                        </div>
                        <div class="form-group">
                            <label for="numero">Número: *</label>
                            <input type="number" name="numero" class="form-control" placeholder="Ex.: 10" required>
                        </div>

                        <div class="form-group">
                            <label for="bairro">Bairro: *</label>
                            <input type="text" class="form-control" name="bairro" id="bairro"
                                   placeholder="Digite o Bairro" maxlength="80" readonly>
                        </div>
                        <div class="form-group">
                            <label for="cidade">Cidade: *</label>
                            <input type="text" class="form-control" name="cidade" id="cidade"
                                   placeholder="Digite a cidade" maxlength="50" readonly>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado: *</label>
                            <input type="text" class="form-control" name="estado" id="estado" maxlength="2"
                                   placeholder="Ex.: SP" readonly>
                        </div>
                        <div class="form-group">
                            <label for="estado">Telefone: *</label>
                            <input type="text" class="form-control" name="telefone" id="telefone" required data-mask="(00)0000-0000">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="hidden" name="id" value="<?= $ocor['idOcorrencia'] ?>">
                        <button type='submit' class='btn btn-info btn-sm' name="inserirLocal">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
			<?php
				break;
				case "inserirsub":
			?>
<section id="inserir" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
                    <h3>Evento - Inserir ocorrências</h3>
                    <h1><?php echo $campo["nomeEvento"] ?> </h1>
                    <p><?php if(isset($mensagem)){echo $mensagem;} ?></p>
                </div>
            </div>
    	</div>
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
				<form method="POST" action="?perfil=evento&p=ocorrencias" class="form-horizontal" role="form">
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Selecione o sub-evento (é preciso criar antes de lhe atribuir uma ocorrência)</label>
							<select class="form-control" name="idSubEvento" id="inputSubject" >
								<option>Selecione</option>
								<?php geraOpcaoSub($_SESSION['idEvento'],$campo['idSubEvento']); ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Data início *</label>
							<input type="text" name="dataInicio" class="form-control" id="datepicker10" placeholder="">
						</div>
						<div class=" col-md-6">
							<label>Data encerramento</label>
							<input type="text" name="dataFinal" class="form-control" id="datepicker11" onblur="validate()" placeholder="só preencha se for temporada">
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="segunda" id="diasemana01" disabled="disabled"/><label style="padding:0 10px 0 5px;"> Segunda</label>
							<input type="checkbox" name="terca" id="diasemana02" disabled="disabled"/><label  style="padding:0 10px 0 5px;"> Terça</label>
							<input type="checkbox" name="quarta" id="diasemana03" disabled="disabled"/><label style="padding:0 10px 0 5px;"> Quarta</label>
							<input type="checkbox" name="quinta" id="diasemana04" disabled="disabled"/><label style="padding:0 10px 0 5px;"> Quinta</label>
							<input type="checkbox" name="sexta" id="diasemana05" disabled="disabled"/><label  style="padding:0 10px 0 5px;"> Sexta</label>
							<input type="checkbox" name="sabado" id="diasemana06" disabled="disabled"/><label style="padding:0 10px 0 5px;"> Sábado</label>
							<input type="checkbox" name="domingo" id="diasemana07" disabled="disabled"/><label  style="padding:0 10px 0 5px;"> Domingo</label>
						</div>                     
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="checkbox" name="diaEspecial" id="diaEspecial" onclick="habilitar()"/><label  style="padding:0 20px 0 5px;">Dia especial?</label>
							<input type="checkbox" name="audiodescricao" id="especial01" disabled="disabled"/><label  style="padding:0 10px 0 5px;">Audiodescricão</label>
							<input type="checkbox" name="libras" id="especial02" disabled="disabled"/><label  style="padding:0 10px 0 5px;">Libras</label>
							<input type="checkbox" name="precoPopular" id="especial03" disabled="disabled"/><label  style="padding:0 10px 0 5px;">Preço popular</label>
						</div>
						<div class="form-group">                    
							<div class="col-md-offset-2 col-md-8">
								<input type="checkbox" name="virada" id="virada" onclick="disabled()"/><label  style="padding:0 20px 0 5px;">Virada Cultural</label>          			    
							</div>  
						</div>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-2">
								<label>Horário de início</label>
								<input type="text" name="hora" class="form-control"id="hora" placeholder="hh:mm"/>
							</div> 
							<div class="col-md-3">
								<label>Valor ingresso *</label>
								<input type="text" name="valorIngresso" class="form-control" id="valor" placeholder="em reais">
							</div>
							<div class=" col-md-3">
								<label>Duração *</label>
								<input type="text" id="duracao" name="duracao" class="form-control" id="" placeholder="em minutos">
							</div>
						</div>     
						<div class="form-group">
							<div class="col-md-offset-2 col-md-8">
								<label>Sistema de retirada de ingressos</label>
								<select class="form-control" name="retiradaIngresso" id="inputSubject" >
									<option>Selecione</option>
									<?php geraOpcao("ig_retirada","","") ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-8">
								<label>Local / instituição *</label><img src="images/loading.gif" class="loading" style="display:none" />
								<select class="form-control" name="instituicao" id="instituicao" >
									<option>Selecione</option>
									<?php geraOpcao("ig_instituicao","","") ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-8">
								<label>Sala / espaço (antes selecione a instituição)</label>
								<select class="form-control" name="local" id="local" >
								</select>
							</div>
						</div>	
						<div class="form-group">
							<div class="col-md-offset-2 col-md-6">
								<label>Ingressos disponíveis</label>
								<input type="text" class="form-control" name="ingressosDisponiveis" id="" placeholder="">
							</div>
							<div class=" col-md-6">
								<label>Ingressos reservados</label>
								<input type="text" class="form-control" name="ingressosReservados" id="" placeholder="">
							</div>
						</div>
						<div class="form-group">
						<div class="col-md-offset-2 col-md-8"><strong>Observações</strong><br/>
							<textarea name="observacao" cols="40" rows="2"></textarea>
						</div>
					</div>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-8">
								<input type="hidden" name="inserir" value="1"  />
								<input type="submit" class="btn btn-theme btn-lg btn-block" value="Inserir ocorrência"  />
							 </div>
						</div>
					</div>
				</form>
            </div>
        </div>
	</div>
</section>
			<?php
				break;
			}// fecha a switch action
		break;
		case "subEvento":
			if(isset($_GET['action']))
			{
				$action = $_GET['action'];
			}
			else
			{
				$action = "listar";
			}
			include "../include/menuEvento.php";
			switch($action)
			{
				case "inserir": 
					$novo = 1;
					if(isset($_POST['inserir']))
					{
						//insere
						$ig_sub_evento_titulo = addslashes($_POST['ig_sub_evento_titulo']);
						$ig_sub_evento_idTipo  = $_POST['ig_sub_evento_idTipo'];
						$ig_sub_evento_descricao  = addslashes($_POST['ig_sub_evento_descricao']);
						$idEvento = $_SESSION['idEvento'];
						$publicado = 1;
						$sql_inserir_sub = "INSERT INTO `ig_sub_evento` (`idSubEvento`, `idTipo`, `ig_evento_idEvento`, `titulo`, `descricao`, `publicado`) VALUES (NULL, '$ig_sub_evento_idTipo', '$idEvento', '$ig_sub_evento_titulo', '$ig_sub_evento_descricao', '$publicado')";
						$query_inserir_sub = mysqli_query($con,$sql_inserir_sub);
						if($query_inserir_sub)
						{
							gravarLog($sql_inserir_sub);
							$mensagem = "Sub-evento inserido com sucesso";
							$ultimo = recuperaUltimo("ig_sub_evento");	
						}
						$novo = 0;
					}
					if(isset($_POST['atualizar']))
					{
						$idSubEvento = $_POST['atualizar'];
						$ig_sub_evento_titulo = addslashes($_POST['ig_sub_evento_titulo']);
						$ig_sub_evento_idTipo  = $_POST['ig_sub_evento_idTipo'];
						$ig_sub_evento_descricao  = addslashes($_POST['ig_sub_evento_descricao']);
						$sql_atualizar_sub = "UPDATE `ig_sub_evento` SET `idTipo` = '$ig_sub_evento_idTipo', `titulo` = '$ig_sub_evento_titulo', `descricao` = '$ig_sub_evento_descricao' WHERE `idSubEvento` = '$idSubEvento'";
						$query_atualizar_sub = mysqli_query($con,$sql_atualizar_sub);	
						if($query_atualizar_sub)
						{
							gravarLog($sql_atualizar_sub);
							$mensagem = "Sub-evento atualizado com sucesso";
						}
						else
						{
							$mensagem = "Erro ao atualizar. Tente novamente.";
						}
						$novo = 0;
						$ultimo = $idSubEvento;
					}
					if(isset($_POST['editar']))
					{
						$novo = 0;
						$ultimo = $_POST['editar'];	
					}
					$sub = recuperaDados("ig_sub_evento",$ultimo,"idSubEvento");
					$campo = recuperaEvento($_SESSION['idEvento']); //carrega os dados do evento em questão
				?>
<div class="form-group">
    <div class="col-md-offset-2 col-md-8">
        <br /><br />
    </div> 
</div>
<div class="container">
    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="text-hide">
				<h3>Inserir sub-evento</h3>
				<h1><?php echo $campo["nomeEvento"] ?></h1>
				<h4><?php if(isset($mensagem)){echo $mensagem;} ?></h4>
			</div>
		</div>
    </div>
    <div class="row">
        <div class="col-md-offset-1 col-md-10">
			<form method="POST" action="?perfil=evento&p=subEvento&action=inserir" class="form-horizontal" role="form">
				<h3>Sub-evento</h3>
                <h4><? if(isset($mensagem_s)){echo $mensagem_s;} ?></h4>
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8">
						<label>Nome do Sub-evento</label>
						<input type="text" name="ig_sub_evento_titulo" class="form-control" id="inputSubject" value="<?php echo $sub['titulo'] ?>"/>
					</div> 
				</div>
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8">
						<label>Tipo de Evento do Sub-evento</label>
						<select class="form-control" name="ig_sub_evento_idTipo" id="inputSubject" >
							<option value="1"></option>
							<?php echo geraOpcao("ig_tipo_evento",$sub['idTipo'],"") ?>
						</select>					
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8">
						<label>Descrição</label>
						<textarea name="ig_sub_evento_descricao" class="form-control" rows="10" placeholder="Descreva a atividade complementar ao evento."><?php echo $sub["descricao"] ?></textarea>
					</div> 
				</div>
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8">
						<?php if($novo == 1){ ?>         
						<input type="hidden" name="inserir" value="1" />
						<?php }else { ?>
						<input type="hidden" name="atualizar" value="<?php echo $sub['idSubEvento']; ?>" />
						<?php } ?>
						<input type="submit" class="btn btn-theme btn-lg btn-block" value="<?php if($novo == 1){echo "Inserir";}else{echo "Atualizar";}?>">
					</div>
				</div>
            </form>
        </div>
    </div>
</div>
<br /><br />  
			<?php
				break;
				case "listar":
					if(isset($_POST['apagar']))
					{
						$idSubEvento = $_POST['apagar'];
						$sql_apagar_sub = "UPDATE ig_sub_evento SET publicado = '0' WHERE idSubEvento = '$idSubEvento'";
						$query_apagar_sub = mysqli_query($con,$sql_apagar_sub);
						if($query_apagar_sub)
						{
							gravarLog($sql_apagar_sub);
							$mensagem = "Sub-evento apagado!";	
						}
						else
						{
							$mensagem = "Erro ao apagar o sub-evento";	
						}
					}
				?>
<section id="list_items" class="home-section bg-white">
	<div class="container">
		<div class="row">
			<div class="col-md-offset-2 col-md-8">
				<div class="section-heading">
					<h2>Sub-Eventos</h2>
					<h4>Selecione o evento para carregar.</h4>
					<h5><?php if(isset($mensagem)){echo $mensagem;} ?></h5>
				</div>
			</div>
		</div>  
		<div class="table-responsive list_info">
			<?php listaSubEventos($_SESSION['idEvento']); ?>
		</div>
	</div>
</section> 
		<?php
			} // fim da switch do subEvento
		break; 
		case "enviar":
			$evento = recuperaEvento($_SESSION['idEvento']);
			if(isset($_GET['action']))
			{
				$action = $_GET['action'];
			}
			else
			{
				$action = "evento";
			}
			include "../include/menuEvento.php";
		?>
<section id="list_items" class="home-section bg-white">
	<div class="container">
		<div class="row">
			<div class="col-md-offset-2 col-md-8">
				<div class="section-heading">
					<h2>Finalizar / enviar</h2>
					<h4></h4>
                    <h5><?php if(isset($mensagem)){echo $mensagem;} ?></h5>
                </div>
			</div>
		</div>  
		<?php
			switch($action)
			{
				case "evento":
		?>
		<h5>Dados do evento | <a href="?perfil=evento&p=enviar&action=servicos">Solicitação de serviços</a> | <a href="?perfil=evento&p=enviar&action=pedidos">Pedidos de contratação</a> |  <a href="?perfil=evento&p=enviar&action=pendencias">Pendências</a></h5>
		<div class="table-responsive list_info" >
            <h4><?php echo $evento['nomeEvento'] ?></h4>
            <div align="left">
				<?php descricaoEvento($_SESSION['idEvento']); ?>

                <?php $idEvento = ($_SESSION['idEvento']);
                $evento = recuperaDados('ig_evento', $idEvento, 'idEvento');
                $fomento = recuperaDados('fomento', $evento['tipo_fomento'], 'id'); ?>
                <?php
                if($evento['fomento'] == 1){?>
                    <span>
                                <?= "<b>Fomento:</b><br />".nl2br($fomento['fomento'])."<br /><br />" ?>
                            </span>
                    <?php
                }
                ?>

                <span>
                        <?= "<b>Espaço Publico: </b><br />".nl2br($evento['espaco_publico'] == 1 ? 'Sim' : 'Não')."<br /><br />" ?>
                    </span>

                <span>
                        <?= "<b>Número de apresentações:</b><br />".nl2br($evento['numero_apresentacao'])."<br /><br />" ?>
                    </span>
            </div>
            <h5>Ocorrências</h5>
            <?php echo resumoOcorrencias($_SESSION['idEvento']); ?><br /><br />
            <?php listaOcorrenciasTexto($_SESSION['idEvento']); ?>
			<h5>Especificidades</h5>
			<div class="left">
				<?php descricaoEspecificidades($_SESSION['idEvento'],$evento['ig_tipo_evento_idTipoEvento']); ?>
			</div>
			<?php
				break;
				case "pedidos":
					require "../funcoes/funcoesSiscontrat.php";
					$pedido = listaPedidoContratacao($_SESSION['idEvento']);
			?>
			<h5> <a href="?perfil=evento&p=enviar&action=evento">Dados do evento </a>| <a href="?perfil=evento&p=enviar&action=servicos">Solicitação de serviços</a> | Pedidos de contratação  |  <a href="?perfil=evento&p=enviar&action=pendencias">Pendências</a></h5>
			<div class="table-responsive list_info" >
				<?php
					if($pedido != NULL)
					{
				?>
				<h4><?php echo $evento['nomeEvento'] ?></h4>
					<?php
						for($i = 0; $i < count($pedido); $i++)
						{
							$dados = siscontrat($pedido[$i]);
							$pessoa = siscontratDocs($dados['IdProponente'],$dados['TipoPessoa']);
					?>
				<p align="left">
					Nome ou Razão Social: <b><?php echo $pessoa['Nome'] ?></b><br />
					Tipo de pessoa: <b><?php echo retornaTipoPessoa($dados['TipoPessoa']);?></b><br />
					Dotação: <b><?php echo retornaVerba($dados['Verba']);?></b><br />
					Valor:<b>R$ <?php echo dinheiroParaBr($dados['ValorGlobal']);?></b><br />		
				</p>      
					<?php
						}// fechamento do for 
					}
					else
					{
					?>
				<h5> Não há pedidos de contratação. </h5>
				<?php	
					}
				break;
				case "servicos":
				?>    
				<h5> <a href="?perfil=evento&p=enviar&action=evento">Dados do evento </a>| Solicitação de serviços | <a href="?perfil=evento&p=enviar&action=pedidos">Pedidos de contratação</a>  |  <a href="?perfil=evento&p=enviar&action=pendencias">Pendências</a></h5>
				<div class="table-responsive list_info" >
					<h4><?php echo $evento['nomeEvento'] ?></h4>
					<div class="left">
						<h5>Previsão de serviços externos</h5>
						<?php listaServicosExternos($_SESSION['idEvento']); ?><br /><br />
						<h5>Serviços Internos</h5>
						<?php listaServicosInternos($_SESSION['idEvento']) ?>
					</div>
			<?php
				break;
				case "pendencias":
					require_once("../funcoes/funcoesVerifica.php");
					require_once("../funcoes/funcoesSiscontrat.php");
					$evento = recuperaDados("ig_evento",$_SESSION['idEvento'],"idEvento");
					$campos = verificaCampos($_SESSION['idEvento']);
					$ocorrencia = verificaOcorrencias($_SESSION['idEvento']);
					$prazo = prazoContratos($_SESSION['idEvento']);

			?>   
					<h5> <a href="?perfil=evento&p=enviar&action=evento">Dados do evento </a>| <a href="?perfil=evento&p=enviar&action=servicos">Solicitação de serviços</a> | <a href="?perfil=evento&p=enviar&action=pedidos">Pedidos de contratação</a>  |  Pendências</h5>
					<div class="table-responsive list_info" >
						<h4><?php echo $evento['nomeEvento'] ?></h4>
						<div class="left">
				<?php //print_r($evento);
					if($campos['total'] > 0)
					{
						echo "<h5>Há campos obrigatórios não preenchidos.</h5>";	
						echo "<strong>".substr($campos['campos'],1)."</strong>";
					}
					else
					{
						echo "<h5>Todos os campos obrigatórios foram preenchidos.</h5>";
					}
				?>
							</p>
							<br/>
					
							<h6>Parecer Artístico</h6>
							<p>
						<?php
						$pedido = listaPedidoContratacao($_SESSION['idEvento']);
						for($i = 0; $i < count($pedido); $i++)
						{
							$dados = siscontrat($pedido[$i]);
							$pessoa = siscontratDocs($dados['IdProponente'],$dados['TipoPessoa']);
							$parecer = verificaParecer($pedido[$i]);
						?>
							<p align="left">
								Pedido nº: <b><?php echo $pedido[$i] ?></b><br />
								<?php echo $parecer;?><br/>
							
							</p>      
						<?php
						}// fechamento do for 						
						?>
						</p>
						<br/>
		
						<p>
				<?php //print_r($evento);
					if($ocorrencia > 0)
					{
						echo "<h5>Há ocorrências cadastradas.</h5>";	
						echo "<br />";
					}
					else
					{
						echo "<strong><h5>Não há ocorrências cadastradas.</h5></strong>";
					}
				?>
							</p>
						</div>
						<br />
						<p><?php echo $prazo['mensagem'];?><p>
				<?php
                    $sqlConsultaPedido = "SELECT `idPedidoContratacao`, `valor` FROM `igsis_pedido_contratacao` WHERE `idEvento` = '".$_SESSION['idEvento']."' AND `publicado` = '1'";
                    $queryConsultaPedido = $con->query($sqlConsultaPedido);
					if($evento['ig_produtor_idProdutor'] == 0)
					{
						echo "<h6>Preencha os dados do produtor para habilitar o botão de envio!</h6>";
					}
					else
					{
                        if ($queryConsultaPedido->num_rows > 0)
                        {
                            $erroRegiao = false;
                            while ($pedidos = $queryConsultaPedido->fetch_assoc())
                            {
                                if ($pedidos['valor'] > 0)
                                {
                                    $sqlValoresRegiao = "SELECT * FROM `igsis_valor_regiao` WHERE `idPedido` = '".$pedidos['idPedidoContratacao']."'";
                                    $registrosRegiao = $con->query($sqlValoresRegiao)->num_rows;

                                    if ($registrosRegiao == 0)
                                    {
                                        $erroRegiao = true;
                                    }
                                }
                            }
                            if ($erroRegiao)
                            {
                                echo "<h6>Preencha os valores por região no pedido de contratação para habilitar o botão de envio!</h6>";
                            }
                            else
                            {
                                $pedido = listaPedidoContratacao($_SESSION['idEvento']);
                                if($prazo['fora'] == 1) //Tem pedido e está fora do prazo
                                {
                                ?>
                                    <div class="form-group">
                                        <div class="col-md-offset-2 col-md-8">
                                            <form method='POST' action='?perfil=aprovacao_evento'>
                                                <input type='hidden' name='aprovacao_evento' value='".$campo['idEvento']."' />
                                                <br />
                                                <input type ='submit' class='btn btn-theme btn-lg btn-block' value='Solicitar Envio' onclick="this.disabled = true; this.value = 'Enviando…'; this.form.submit();">
                                            </form>
                                        </div>
                                    </div>
                                <?php
                                }
                                else if($prazo['fora'] == 0) //Tem pedido e está dentro do prazo
                                {
                                    ?>
                                    <?php
                                    if (eventoOnline()):
                                        $participacoes = validaPartIntegrantes();
                                        ?>
                                    <?php if ($participacoes['bol']): ?>
                                        <h6>Os seguintes CPFs já participaram de 6 apresentações</h6>
                                            <?=$participacoes['msg']?>
                                    <?php else: ?>
                                        <div class="form-group">
                                            <div class="col-md-offset-2 col-md-8">
                                                <form method='POST' action='?perfil=aprovacao_evento'>
                                                    <input type='hidden' name='aprovacao_evento' value='".$campo[' idEvento']."'
                                                    />
                                                    <br/>
                                                    <input type='submit' class='btn btn-theme btn-lg btn-block'
                                                           value='Solicitar Envio'
                                                           onclick="this.disabled = true; this.value = 'Enviando…'; this.form.submit();">
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                <?php else: ?>
                                    <div class="form-group">
                                        <div class="col-md-offset-2 col-md-8">
                                            <form method='POST' action='?perfil=aprovacao_evento'>
                                                <input type='hidden' name='aprovacao_evento' value='".$campo[' idEvento']."'
                                                />
                                                <br/>
                                                <input type='submit' class='btn btn-theme btn-lg btn-block'
                                                       value='Solicitar Envio'
                                                       onclick="this.disabled = true; this.value = 'Enviando…'; this.form.submit();">
                                            </form>
                                        </div>
                                    </div>
                                <?php endif ?>
                                    <!-- /* Código comentado devido ao bloqueio de envio direto mesmo dentro do prazo por questão de fechamento do SOF */
                                    <div class="form-group">
                                        <div class="col-md-offset-2 col-md-8">
                                            <form method='POST' action='?perfil=evento&p=finalizar'>
                                                <input type='hidden' name='carregar' value='".$campo['idEvento']."' />
                                                <input type ='submit' class='btn btn-theme btn-lg btn-block' value='Enviar'>
                                            </form>
                                        </div>
                                    </div>
                                    -->
                                    <?php
                                }
                                else if($pedido =! null) //Tem pedido
                                {
                                    ?><div class="col-md-offset-1 col-md-10">
                                    <h4><font color="red">Sistema fechado para envio de programação com pedido de contratação.</font></h4>
                                    <p><strong>Dúvidas entrar em contato com a Débora através do e-mail dsbueno@prefeitura.sp.gov.br</strong></p>
                                </div>


                                    <?php
                                }
                            }
                        }
                        else
                        {
                            $pedido = listaPedidoContratacao($_SESSION['idEvento']);
                            if($prazo['fora'] == 1) //Não tem pedido e está fora do prazo
                            {
                                ?>
                                <div class="form-group">
                                    <div class="col-md-offset-2 col-md-8">
                                        <form method='POST' action='?perfil=aprovacao_evento'>
                                            <input type='hidden' name='aprovacao_evento' value='".$campo['idEvento']."' />
                                            <br />
                                            <input type ='submit' class='btn btn-theme btn-lg btn-block' value='Solicitar Envio' onclick="this.disabled = true; this.value = 'Enviando…'; this.form.submit();">
                                        </form>
                                    </div>
                                </div>
                                <?php
                            }
                            else if($prazo['fora'] == 0) //Não tem pedido e está dentro do prazo
                            {
                                ?>
                                <div class="form-group">
                                    <div class="col-md-offset-2 col-md-8">
                                        <form method='POST' action='?perfil=evento&p=finalizar'>
                                            <input type='hidden' name='carregar' value='".$campo['idEvento']."' />
                                            <input type ='submit' class='btn btn-theme btn-lg btn-block' value='Enviar'>
                                        </form>
                                    </div>
                                </div>
                                <?php
                            }
                            else if($pedido =! null) //Tem pedido
                            {
                                ?><div class="col-md-offset-1 col-md-10">
                                <h4><font color="red">Sistema fechado para envio de programação com pedido de contratação.</font></h4>
                                <p><strong>Dúvidas entrar em contato com a Débora através do e-mail dsbueno@prefeitura.sp.gov.br</strong></p>
                            </div>


                                <?php
                            }
                        }
					}
				break;
			} // fecha a switch action */?>	
					</div>
				</div>
			</div>
		</div>
	</div>
</section> 
	<?php 
		break;
		case "finalizar":
			include "../include/menuEvento.php";
			require_once("../funcoes/funcoesVerifica.php");
			require_once("../funcoes/funcoesSiscontrat.php");
			$verifica = verificaPendencias($_SESSION['idEvento']);
			if($verifica == 0)
			{
	?>
<section id="contact" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
	                <h2>O pedido será enviado!</h2>
                </div>
            </div>
			<div class="form-group">
				<div class="col-md-offset-2 col-md-8">
					<p>Uma vez enviado o formulário, não poderá mais editá-lo.</p>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-offset-2 col-md-8">
			<?php 
				$con = bancoMysqli();
				$idUser = $_SESSION['idUsuario'];
				$sql_botao = "SELECT * FROM igsis_lista_usuarios WHERE idUsuario = '$idUser'";
				$query_botao = mysqli_query($con,$sql_botao);
				$num_botao = mysqli_num_rows($query_botao);
				$data1 = strtotime(date('Y-m-d H:i:s'));
				$data2 = strtotime('2016-06-01 00:00:00');
				$data3 = strtotime('2016-06-15 00:00:00');
				if($data1 > $data2 AND $data1 < $data3)
				{
					if($num_botao > 0)
					{
			?>
					<font color="#FFF">1</font>
					<form method='POST' action='?perfil=finalizar'>
						<input type='hidden' name='finalizar' value='<?php echo $campo['idEvento'] ?>' />
						<input type ='submit' class='btn btn-theme btn-lg btn-block' value='Enviar' onclick="this.disabled = true; this.value = 'Enviando…'; this.form.submit();">
					</form>
			<?php
					}
					else
					{
			?>
					<p>O sistema está fechado para envios até 15/06/2016</p>	
			<?php
					}
				}
				else
				{
			?>
					<font color="#FFF">2</font>
					<form method='POST' action='?perfil=finalizar'>
						<input type='hidden' name='finalizar' value='".$campo['idEvento']."' />  
						<input type ='submit' class='btn btn-theme btn-lg btn-block' value='Enviar' onclick="this.disabled = true; this.value = 'Enviando…'; this.form.submit();">
					</form>
			<?php
				}
			?>			
				</div>
			</div>
        </div>
    </div>
</section>      
		<?php
			}
			else
			{
		?>
<section id="contact" class="home-section bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="text-hide">
	                <h2>Não é possível enviar o formulário!</h2>
                </div>
            </div>
			<div class="form-group">
				<div class="col-md-offset-2 col-md-8">
					<p>Há pendências que necessitam ser resolvidas. </p>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-offset-2 col-md-8">
					<form method='POST' action='?perfil=finalizar'>
						<input type='hidden' name='finalizar' value='".$campo['idEvento']."' />
						<input type ='submit' class='btn btn-theme btn-lg btn-block' value='Enviar'>
					</form>
				</div>
			</div>
		</div>
    </div>
</section>    
		<?php
			}
		break;
		case "busca":
			if(isset($_POST['busca']))
			{
				$resultado = busca($_POST['busca'],1);
				$mensagem = "Foram encontradas ".$resultado['numReg']." eventos com o termo ".$_POST['busca'].".";
			}
		?>
<section id="services" class="home-section bg-white">
	<div class="container">
		<div class="row">
			<div class="col-md-offset-2 col-md-8">
				<div class="section-heading">
					<h2>Contratados - Pessoa Jurídica</h2>
				</div>
			</div>
		</div> 
	    <div class="row">
            <div class="form-group">
				<div class="col-md-offset-2 col-md-8">
					<h5><?php if(isset($mensagem)){ echo $mensagem; } ?>
					<form method="POST" action="?perfil=evento&p=busca" class="form-horizontal" role="form">
						<label>Busca por palavras</label>
						<input type="text" name="busca" class="form-control" id="palavras" placeholder="" ><br />
					</form>
				</div>
            </div>
			<br />             
	        <div class="form-group">
		        <div class="col-md-offset-2 col-md-8">
					<input type="hidden" name="pesquisar" value="1" />
    		        <input type="submit" class="btn btn-theme btn-lg btn-block" value="Pesquisar"> 
        	    </div>
        	</div>
        </div>
	</div>
</section>
	<?php //var_dump($resultado);
		break;
		case "enviados":
			include "../include/menuEventoInicial.php";
	?>	
<section id="list_items" class="home-section bg-white">
	<div class="container">
      	<div class="row">
			<div class="col-md-offset-2 col-md-8">
				<div class="section-heading">
					<h2>IGSIS enviadas</h2>
					<h4>Selecione o evento para carregar.</h4>
                    <h5><?php if(isset($mensagem)){echo $mensagem;} ?></h5>
				</div>
			</div>
		</div>  
		<div class="table-responsive list_info">
            <?php listaEventosEnviados($_SESSION['idUsuario']); ?>
		</div>
	</div>
</section> <!--/#list_items-->
	<?php
		break;
		case "pedidos_enviados":
			require "../funcoes/funcoesSiscontrat.php";
			$_SESSION['idPedido'] = ""; //zera a session pedido
			// não precisa chamar a funcao porque o index contrato já chama.
			$lista_pedido = siscontratListaEvento("todos",$_SESSION['idInstituicao'],50,1,"DESC","todos",$_SESSION['idUsuario']); //esse gera uma array com os pedidos
			$url = urlAtual();
			$link = "http://".$_SERVER['HTTP_HOST']."/igsis/pdf/pedido_pdf.php";
			//$link="frm_edita_pedidocontratacaopj.php";
			include "../include/menuEventoInicial.php";
	?>
<section id="list_items" class="home-section bg-white">
	<div class="container">
        <div class="row">
			<div class="col-md-offset-2 col-md-8">
				<div class="section-heading">
					<h2>Pedidos enviados</h2>
				</div>
			</div>
		</div>  
		<div class="table-responsive list_info">
			<table class="table table-condensed">
				<thead>
					<tr class="list_menu">
						<td>Codigo do Pedido</td>
						<td>Número de Processo</td>
						<td>Proponente</td>
						<td>Objeto</td>
						<td>Local</td>
						<td>Valor (R$)</td>
						<td>Periodo</td>
						<td>Status</td>
						<td>Contratos</td>
					</tr>
				</thead>
				<tbody>
		<?php
			$data=date('Y');
			for($i = 0; $i < count($lista_pedido); $i++)
			{	
				$operador = recuperaDados("ig_usuario",$lista_pedido[$i]['Contratos'],"idUsuario");
				$status = recuperaDados("sis_estado",$lista_pedido[$i]['Status'],"idEstado");
				$lista_pf = siscontratDocs($lista_pedido[$i]['IdProponente'],$lista_pedido[$i]['TipoPessoa']);
				echo "<tr><td class='lista'> <a target='_blank' href='?perfil=detalhe_pedido&id_ped=".$lista_pedido[$i]['idPedido']."'>".$lista_pedido[$i]['idPedido']."</a></td>";
				echo '<td class="list_description">'.$lista_pedido[$i]['NumeroProcesso'].'</td> ';
				echo '<td class="list_description">'.$lista_pf['Nome'].'</td> ';
				echo '<td class="list_description">'.$lista_pedido[$i]['Objeto'].'</td> ';
				echo '<td class="list_description">'.$lista_pedido[$i]['Local'].'</td> ';
				echo '<td class="list_description">'.dinheiroParaBr($lista_pedido[$i]['ValorGlobal']).'</td> ';
				echo '<td class="list_description">'.$lista_pedido[$i]['Periodo'].'</td> ';
				echo '<td class="list_description">'.$status['estado'].'</td>';
				echo '<td class="list_description">'.$operador['nomeCompleto'].'</td> </tr>';
			}
		?>
				</tbody>
			</table>
		</div>
	</div>
</section>
<!--fim_list-->
	<?php
		break;
	} // fim eventos
	?>
