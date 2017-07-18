<?php
	if(isset($_GET['id_ped']))
	{
		$idPedidoContratacao = $_GET['id_ped'];
	}
	$con = bancoMysqli();
	if(isset($_POST['action']))
	{
		switch($_POST['action'])
		{
			case "novo": //caso seja um novo pedido
				$id_formacao = $_POST['idFormacao'];
				$formacao = recuperaDados("sis_formacao",$_POST['idFormacao'],"Id_Formacao");
				$proponente = recuperaDados("sis_pessoa_fisica",$formacao['IdPessoaFisica'],"Id_PessoaFisica");
				$proponenteFormacao = recuperaDados("sis_pessoa_fisica_formacao",$formacao['IdPessoaFisica'],"IdPessoaFisica");
				$vigencia = $formacao['IdVigencia'];
				$idPrograma = $formacao['IdPrograma'];
				$idPessoa = $formacao['IdPessoaFisica'];
				$idVerba = "";
				$instituicao = $_SESSION['idInstituicao'];
				$justificativa = addslashes($cargo['justificativa']);
				//$justificativa = "Texto do Edital";
				$parecer = addslashes($proponenteFormacao['Curriculo']);
				$mensagem = "";
				// insere um novo pedido pf com pessoa = 4
				$sql_pedido = "INSERT INTO `igsis_pedido_contratacao` (`idEvento`, `tipoPessoa`, `idPessoa`, `instituicao`, `justificativa`, `parecerArtistico`, `publicado`) VALUES ('$idPrograma', '4', '$idPessoa',  '19', '$justificativa', '$parecer', '1')";
				$query_pedido = mysqli_query($con,$sql_pedido);
				if($query_pedido)
				{
					$idPedidoContratacao = mysqli_insert_id($con);
					$mensagem = "Pedido Criado.";
					// atualiza o sis_formacao com o idPedidoContratacao
					$sql_atualiza_formacao = "UPDATE sis_formacao SET idPedidoContratacao = '$idPedidoContratacao' WHERE Id_Formacao = '$id_formacao'";
					$query_atualiza_formacao = mysqli_query($con,$sql_atualiza_formacao);
					if($query_atualiza_formacao)
					{
						$mensagem = $mensagem."<br /> Tabela formação atualizado com o número de Pedido de Contratacao";	
					}
					else
					{
						$mensagem = $mensagem."<br /> Erro ao atualizar tabela formação com o número de Pedido de Contratacao";	
					}
					//cria as parcela e atualiza a tabela pedido com os valores
					$sql_cria_parcelas = "SELECT * FROM sis_formacao_parcelas WHERE Id_Vigencia = '$vigencia' ORDER BY N_Parcela ASC";
					$query_cria_parcelas = mysqli_query($con,$sql_cria_parcelas);
					$i = 1;
					while($parcela = mysqli_fetch_array($query_cria_parcelas))
					{
						//idPedido, numero, valor, vencimento, vigencia_inicio, vigencia_final, horas
						$numero = $parcela['N_Parcela'];
						$valor = $parcela['Valor'];
						$pagamento = $parcela['pagamento'];
						$vigencia_inicio = $parcela['dataInicio'];
						$vigencia_final = $parcela['dataFinal'];
						$horas = $parcela['horas'];
						$sql_insere_parcelas = "INSERT INTO `igsis_parcelas` (`idParcela`, `idPedido`, `numero`, `valor`, `vencimento`, `publicado`, `descricao`, `vigencia_inicio`, `vigencia_final`, `horas`) VALUES (NULL, '$idPedidoContratacao', '$numero', '$valor', '$pagamento', NULL, NULL, '$vigencia_inicio', '$vigencia_final', '$horas')";
						if($valor != 0)
						{
							$i++;	 
						}		
						$query_insere_parcelas = mysqli_query($con,$sql_insere_parcelas);
						if($query_insere_parcelas)
						{
							$mensagem = $mensagem."<br /> Parcela $numero inserida.";
						}
						else
						{
							$mensagem = $mensagem."<br /> Erro.";	
						}	
					}
					$valor_total = somaParcela($idPedidoContratacao,$i);
					//atualizamos a tabela prinicpal com os valores e o número de parcelas
					$sql_atualiza_parcela = "UPDATE igsis_pedido_contratacao SET parcelas = '$i',
					valor = '$valor_total' WHERE idPedidoContratacao = '$idPedidoContratacao'";
					$query_atualiza_parcela = mysqli_query($con,$sql_atualiza_parcela);
					if($query_atualiza_parcela)
					{
						$mensagem .= "<br />Valor e parcelas atualizados";	
					}
					else
					{
						$mensagem .= "<br />Erro ao atualizar parcelas e valor";	
					}
				}
				else
				{
					$mensagem = "Erro ao criar pedido";	
				}
			break;
			case "atualizar":
				$idPedidoContratacao = $_POST['idPedido'];
				$Observacao = addslashes($_POST['Observacao']);
				$Suplente  = $_POST['Suplente']; 
				$Fiscal  = $_POST['Fiscal'];
				$Parecer  = addslashes($_POST['Parecer']);
				$Justificativa  = addslashes($_POST['Justificativa']);
				$sql_atualiza_pedido = "UPDATE igsis_pedido_contratacao SET
					observacao = '$Observacao',
					parecerArtistico = '$Parecer',
					justificativa = '$Justificativa'
					WHERE idPedidoContratacao = '$idPedidoContratacao'";
				$query_atualiza_pedido = mysqli_query($con,$sql_atualiza_pedido);
				//verificaMysql($sql_atualiza_pedido);
				if($query_atualiza_pedido)
				{
					$sql_atualiza_formacao = "UPDATE sis_formacao SET
						fiscal = '$Fiscal',
						suplente = '$Suplente'
						WHERE idPedidoContratacao = '$idPedidoContratacao'";
					$query_atualiza_formacao = mysqli_query($con,$sql_atualiza_formacao);
					if($query_atualiza_formacao)
					{
						$mensagem = "Pedido Atualizado";
					}
					else
					{
						$mensagem = "Erro ao atualizar pedido (I)";	
					}
				}
				else
				{
					$mensagem = "Erro ao atualizar pedido(II)";	
				}
				//suplente Suplente 
				//fiscal Fiscal
			break;
		}	
	}
	if(isset($_POST['enviar']))
	{
		$dataEnvio = date('Y-m-d');
		$idPedidoContracao = $_POST['idPedido'];
		$sql_enviar = "UPDATE igsis_pedido_contratacao SET estado = '2'WHERE idPedidoContratacao = '$idPedidoContratacao'";
		$query_enviar = mysqli_query($con,$sql_enviar);
		if($query_enviar)
		{
			$sql_formacao = "UPDATE sis_formacao SET dataEnvio = '$dataEnvio' WHERE idPedidoContratacao = '$idPedidoContratacao'";
			$query_formacao = mysqli_query($con,$sql_formacao);
			if($query_formacao)
			{
				$mensagem = "Pedido enviado à area de contratos";
			}
			else
			{
				$mensagem = "Erro ao enviar pedido(1)";
			}
		}
		else
		{
			$mensagem = "Erro ao enviar pedido(2)";	
		}
	}
	$ano=date('Y');
	$id_ped = $idPedidoContratacao;
	$pedido = recuperaDados("igsis_pedido_contratacao",$id_ped,"idPedidoContratacao");
	$proponente = recuperaDados("sis_pessoa_fisica",$pedido['idPessoa'],"Id_PessoaFisica");
	$formacao = recuperaDados("sis_formacao",$pedido['idPedidoContratacao'],"idPedidoContratacao");
	$cargo = recuperaDados("sis_formacao_cargo",$formacao['IdCargo'],"Id_Cargo");
	$programa = recuperaDados("sis_formacao_programa",$formacao['IdPrograma'],"Id_Programa");
	$verba = recuperaDados("sis_verba",$programa['verba'],"Id_Verba");
	$objeto = "CONTRATAÇÃO COMO ".$cargo['Cargo']." DO ".$programa['Programa']." NOS TERMOS DO EDITAL ".$programa['edital']." - PROGRAMAS DA DIVISÃO DE FORMAÇÃO.";
	if($cargo['coordenador'] == 1)
	{
		$local = "SMC e equipamentos sobre sua supervisão";
	}
	else
	{
		$local = retornaLocal($formacao['IdEquipamento01'])."\n".retornaLocal($formacao['IdEquipamento02'])."\n".retornaLocal($formacao['IdEquipamento03']);
	}
	$carga = retornaCargaHoraria($pedido['idPedidoContratacao'],$pedido['parcelas']);
	$periodo = retornaPeriodoVigencia($pedido['idPedidoContratacao'],$pedido['parcelas']);
	$justif = $cargo['justificativa'];
	//MENU
	include 'includes/menu.php';
?>
<section id="contact" class="home-section bg-white">
	<div class="container">
		<div class="form-group">
			<h2>PEDIDO DE CONTRATAÇÃO DE PESSOA FÍSICA</h2>
			<h6><?php if(isset($mensagem)){ echo $mensagem; } ?></h6>
		</div>
		<div class="row">
			<div class="col-md-offset-1 col-md-10">
				<form class="form-horizontal" role="form" action="#" method="post">
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8"><strong>Código de Dados para Contratação:</strong><br/>
							<input  readonly name="Id_Formacao"  type="text" class="form-control" id="Id_Formacao" value="<?php echo $formacao['Id_Formacao'] ?>">
						</div>
					</div>
					<div class="form-group"> 
						<div class="col-md-offset-2 col-md-8"><strong>Proponente:</strong><br/>
							<input type='text' readonly class='form-control' name='nome' id='nome' value="<?php echo $proponente['Nome']." (".$proponente['CPF'].")"; ?>">                    	
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8"><strong>Objeto:</strong><br/>
							<textarea readonly="readonly"  class="form-control" rows="5"><?php echo $objeto; ?> </textarea>
						</div>
					</div>
                  	<div class="form-group">
						<div class="col-md-offset-2 col-md-8"><strong>Local:</strong><br/>
							<textarea readonly="readonly"  class="form-control" rows="5"><?php echo $local; ?> </textarea>
						</div>
					</div>       
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6"><strong>Período:</strong><br/>
							<input type='text' readonly name="Periodo" class='form-control' value="<?php echo $periodo ?>">
						</div>
						<div class="col-md-6"><strong>Carga Horária:</strong><br/>
							<input type='text' readonly name="CargaHoraria" class='form-control' value="<?php echo $carga ?>">
						</div>
					</div>
					<form class="form-horizontal" role="form" action="#" method="post">
						<div class="form-group">
							<div class="col-md-offset-2 col-md-8"><strong>Valor:</strong><br/>
								<input type='text' disabled name="valor_parcela" id='valor' class='form-control' value="<?php echo dinheiroParaBr($pedido['valor']) ?>" >
							</div>	
						</div>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-8"><strong>Forma de Pagamento:</strong><br/>
								<textarea  disabled name="FormaPagamento" class="form-control" cols="40" rows="5"><?php echo txtParcelas($pedido['idPedidoContratacao'],$pedido['parcelas']); ?> 
								</textarea>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-8"><strong>Verba:</strong><br/>
								<input type='text' disabled name="valor_parcela" id='valor' class='form-control' value="<?php echo $verba['Verba'] ?>" >
							</div>
						</div>
						<form class="form-horizontal" role="form" action="?perfil=formacao&p=frm_cadastra_pedidocontratacao_pf" method="post">
							<div class="form-group">
								<div class="col-md-offset-2 col-md-8"><strong>Justificativa:</strong><br/>
									<textarea name="Justificativa" cols="40" rows="5"><?php echo $justif ?></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-offset-2 col-md-6"><strong>Fiscal:</strong>
									<select class="form-control" name="Fiscal" id="Fiscal">
										<?php opcaoUsuario($_SESSION['idInstituicao'],$formacao['fiscal']); ?>
									</select>
								</div>
								<div class="col-md-6"><strong>Suplente:</strong>
								   <select class="form-control" name="Suplente" id="Fiscal">
										<?php opcaoUsuario($_SESSION['idInstituicao'],$formacao['suplente']); ?>
								   </select>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-offset-2 col-md-8"><strong>Observação:</strong><br/>
									<textarea name="Observacao" cols="40" rows="5"><?php echo $pedido['observacao'] ?></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-offset-2 col-md-8">
									<input type="hidden" name="action" value="atualizar"  />
									<input type="hidden" name="idPedido" value="<?php echo $pedido['idPedidoContratacao']; ?>"  />
									<input type="submit" class="btn btn-theme btn-lg btn-block" value="Gravar">
								</div>
							</div>
						</form>	
<?php
	if($pedido['estado'] == NULL OR $pedido['estado'] == "" )
	{
?>
						<form class="form-horizontal" role="form" action="?perfil=formacao&p=frm_cadastra_pedidocontratacao_pf&id_ped=<?php echo $idPedidoContratacao; ?>" method="post">
							<div class="form-group">
								<div class="col-md-offset-2 col-md-8">
									<input type="hidden" name="enviar"  />
									<input type="submit" class="btn btn-theme btn-lg btn-block" value="Enviar pedido para contratos">
								</div>
							</div>
						</form>
<?php
	}
	else
	{
?>			                                
	<div class="form-group">
		<div class="col-md-offset-2 col-md-8">
			<a href="../pdf/rlt_proposta_formacao.php?id=<?php echo $pedido['idPedidoContratacao']; ?>&penal=20" class="btn btn-theme btn-lg btn-block" target="_blank">Gerar proposta</a>
		</div>
	</div>
<?php
	}
?>
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8"><br/></div>
				</div>	
					
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8">
						<a href="?perfil=formacao&p=frm_edicao_parcelas&id_ped=<?php echo $pedido['idPedidoContratacao']; ?>" class="btn btn-theme btn-lg btn-block" target="_blank">Editar parcela</a>
					</div>
				</div>			
				
			</div>
		</div>
	</div>
</section>