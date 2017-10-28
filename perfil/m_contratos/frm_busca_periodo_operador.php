﻿<?php include 'includes/menu.php';

if(isset($_GET['pag']))
{
	$p = $_GET['pag'];
}
else
{
	$p = 'inicial';
}

switch($p)
{
/* =========== INICIAL ===========*/
case 'inicial':

function retornaDataInicio($idEvento)
{ //retorna o período
	$con = bancoMysqli();
	$sql_anterior = "SELECT * FROM ig_ocorrencia WHERE idEvento = '$idEvento' AND publicado = '1' ORDER BY dataInicio ASC LIMIT 0,1"; //a data inicial mais antecedente
	$query_anterior = mysqli_query($con,$sql_anterior);
	$data = mysqli_fetch_array($query_anterior);
	$data_inicio = $data['dataInicio'];
	$sql_posterior01 = "SELECT * FROM ig_ocorrencia WHERE idEvento = '$idEvento' AND publicado = '1' ORDER BY dataFinal DESC LIMIT 0,1"; //quando existe data final
	$sql_posterior02 = "SELECT * FROM ig_ocorrencia WHERE idEvento = '$idEvento' AND publicado = '1' ORDER BY dataInicio DESC LIMIT 0,1"; //quando há muitas datas únicas
	$query_anterior01 = mysqli_query($con,$sql_posterior01);
	$data = mysqli_fetch_array($query_anterior01);
	$num = mysqli_num_rows($query_anterior01);

	if(($data['dataFinal'] != '0000-00-00') OR ($data['dataFinal'] != NULL))
	{  //se existe uma data final e que é diferente de NULO
		$dataFinal01 = $data['dataFinal'];
	}

	$query_anterior02 = mysqli_query($con,$sql_posterior02); //recupera a data única mais tarde
	$data = mysqli_fetch_array($query_anterior02);
	$dataFinal02 = $data['dataInicio'];

	if(isset($dataFinal01))
	{ //se existe uma temporada, compara com a última data única
		if($dataFinal01 > $dataFinal02)
		{
			$dataFinal = $dataFinal01;
		}
		else
		{
			$dataFinal = $dataFinal02;
		}
	}
	else
	{
		$dataFinal = $dataFinal02;
	}

	if($data_inicio == $dataFinal)
	{
		return $data_inicio;
	}
	else
	{
		return $data_inicio;
	}
}


if(isset($_POST['periodo']))
{
	$inicio = exibirDataMysql($_POST['inicio']);
	$final = exibirDataMysql($_POST['final']);
	$idContratos = $_POST['operador'];

	if($idContratos == 0)
	{
		$operador = " ";
	}
	else
	{
		$operador = "AND ped.idContratos = '$idContratos'";
	}

	$con = bancoMysqli();
	$sql_evento = "SELECT DISTINCT age.idEvento, ped.idPedidoContratacao FROM igsis_agenda AS age
		INNER JOIN igsis_pedido_contratacao AS ped ON ped.idEvento = age.idEvento
		WHERE data BETWEEN '$inicio' AND '$final' $operador AND ped.estado IN (1,2,3,4,5,8,9,10,13,14,15) ORDER BY data ASC ";
	$query_evento = mysqli_query($con,$sql_evento);
	$num = mysqli_num_rows($query_evento);
	$i = 0;
	while($evento = mysqli_fetch_array($query_evento))
	{
		$pedido = recuperaDados("igsis_pedido_contratacao",$evento['idPedidoContratacao'],"idPedidoContratacao");
		$event = recuperaDados("ig_evento",$evento['idEvento'],"idEvento");
		$usuario = recuperaDados("ig_usuario",$event['idUsuario'],"idUsuario");
		$instituicao = recuperaDados("ig_instituicao",$event['idInstituicao'],"idInstituicao");
		$local = listaLocais($pedido['idEvento']);
		$periodo = retornaPeriodo($pedido['idEvento']);
		$operador = recuperaUsuario($pedido['idContratos']);

		$x[$i]['id']= $pedido['idPedidoContratacao'];
		$x[$i]['NumeroProcesso']= $pedido['NumeroProcesso'];
		$x[$i]['objeto'] = retornaTipo($event['ig_tipo_evento_idTipoEvento'])." - ".$event['nomeEvento'];
		if($pedido['tipoPessoa'] == 1)
		{
			$pessoa = recuperaDados("sis_pessoa_fisica",$pedido['idPessoa'],"Id_PessoaFisica");
			$x[$i]['proponente'] = $pessoa['Nome'];
			$x[$i]['tipo'] = "Física";
		}
		else
		{
			$pessoa = recuperaDados("sis_pessoa_juridica",$pedido['idPessoa'],"Id_PessoaJuridica");
			$x[$i]['proponente'] = $pessoa['RazaoSocial'];
			$x[$i]['tipo'] = "Jurídica";
		}
		$x[$i]['local'] = substr($local,1);
		$x[$i]['instituicao'] = $instituicao['sigla'];
		$x[$i]['periodo'] = $periodo;
		$x[$i]['pendencia'] = $pedido['pendenciaDocumento'];
		$x[$i]['status'] = $pedido['estado'];
		$x[$i]['operador'] = $operador['nomeCompleto'];
		$i++;
	}
	$x['num'] = $i;
	if($num > 0)
	{
?>

	<br />
	<br />
	<section id="list_items">
		<div class="container">
			<h3>Resultado da busca</3>
            <h5>Foram encontrados <?php echo $x['num']; ?> pedidos de contratação.</h5>
            <h5><a href="?perfil=contratos&p=frm_busca_periodo_operador">Fazer outra busca</a></h5>
			<div class="table-responsive list_info">
				<table class="table table-condensed">
					<thead>
						<tr class="list_menu">
							<td>Codigo do Pedido</td>
							<td>Número Processo</td>
							<td>Proponente</td>
							<td>Tipo</td>
							<td>Objeto</td>
							<td width="20%">Local</td>
                            <td>Instituição</td>
							<td>Periodo</td>
							<td>Pendências</td>
							<td>Status</td>
   							<td>Operador</td>
						</tr>
					</thead>
					<tbody>

					<?php
						$data=date('Y');
						for($h = 0; $h < $x['num']; $h++)
						{
							 $status = recuperaDados("sis_estado",$x[$h]['status'],"idEstado");
							if($x[$h]['tipo'] == 'Física')
							{
								echo "<tr><td class='lista'> <a href='?perfil=contratos&p=frm_edita_propostapf&id_ped=".$x[$h]['id']."'>".$x[$h]['id']."</a></td>";
							}
							else
							{
								echo "<tr><td class='lista'> <a href='?perfil=contratos&p=frm_edita_propostapj&id_ped=".$x[$h]['id']."'>".$x[$h]['id']."</a></td>";
							}
							echo '<td class="list_description">'.$x[$h]['NumeroProcesso'].'</td> ';
							echo '<td class="list_description">'.$x[$h]['proponente'].'</td> ';
							echo '<td class="list_description">'.$x[$h]['tipo'].'</td> ';
							echo '<td class="list_description">'.$x[$h]['objeto'].'</td> ';
							echo '<td class="list_description">'.$x[$h]['local'].'</td> ';
							echo '<td class="list_description">'.$x[$h]['instituicao'].'</td> ';
							echo '<td class="list_description">'.$x[$h]['periodo'].'</td> ';
							echo '<td class="list_description">'.$x[$h]['pendencia'].'</td> ';
							echo '<td class="list_description">'.$status['estado'].'</td> ';
							echo '<td class="list_description">'.$x[$h]['operador'].'</td> </tr>';
						}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</section>

<?php
	}
	else
	{
?>
		<section id="services" class="home-section bg-white">
			<div class="container">
				<div class="row">
					<div class="col-md-offset-2 col-md-8">
						<h5>| Busca por período | <a href="?perfil=contratos&p=frm_busca_periodo&pag=relatorio">Relatório por período</a> | </h5>
						<div class="section-heading">
							<h2>Busca por período / operador</h2>
							<p><?php if(isset($mensagem)){ echo $num; }?></p>
							<p>É preciso ao menos um critério de busca ou você pesquisou por um pedido inexistente. Tente novamente.</p>
						</div>
					</div>
				</div>
				<div class="row">
				<form method="POST" action="?perfil=contratos&p=frm_busca_periodo_operador" class="form-horizontal" role="form">
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<h5><?php if(isset($mensagem)){ echo $mensagem; } ?>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-6">
							<label>Data início *</label>
								<input type="text" name="inicio" class="form-control" id="datepicker01" placeholder="">
						</div>
						<div class=" col-md-6">
							<label>Data encerramento *</label>
								<input type="text" name="final" class="form-control" id="datepicker02"  placeholder="">
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<label>Operador do Contrato</label>
							<select class="form-control" name="operador" id="inputSubject" >
								<option value='0'></option>
								<?php  geraOpcaoContrato(""); ?>
							</select>
						</div>
					</div>
					<br />
					<div class="form-group">
						<div class="col-md-offset-2 col-md-8">
							<input type="hidden" name="periodo" value="1" />
							<input type="submit" class="btn btn-theme btn-lg btn-block" value="Pesquisar">
						</div>
					</div>
				</form>
				</div>
			</div>
		</section>
<?php
	}
}
else
{
?>
	<section id="services" class="home-section bg-white">
		<div class="container">
			<div class="row">
				<h5>| Busca por período | <a href="?perfil=contratos&p=frm_busca_periodo&pag=relatorio">Relatório por período</a> | </h5>
				<div class="col-md-offset-2 col-md-8">
					<div class="section-heading">
						<h2>Busca por período / operador</h2>
						<p><?php if(isset($mensagem)){ echo $num; }?></p>
					</div>
				</div>
			</div>
			<div class="row">
			<form method="POST" action="?perfil=contratos&p=frm_busca_periodo_operador" class="form-horizontal" role="form">
				<div class="form-group">
					<div class="col-md-offset-2 col-md-6">
						<label>Data início *</label>
							<input type="text" name="inicio" class="form-control" id="datepicker01" placeholder="">
					</div>
					<div class=" col-md-6">
						<label>Data encerramento *</label>
							<input type="text" name="final" class="form-control" id="datepicker02"  placeholder="">
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8">
						<label>Operador do Contrato</label>
						<select class="form-control" name="operador" id="inputSubject" >
							<option value='0'></option>
							<?php  geraOpcaoContrato(""); ?>
						</select>
					</div>
				</div>
				<br />
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8">
						<input type="hidden" name="periodo" value="1" />
						<input type="submit" class="btn btn-theme btn-lg btn-block" value="Pesquisar">
					</div>
				</div>
			</form>
			</div>
		</div>
	</section>
<?php
}
 /* =========== INICIAL ===========*/ break;

} //fim da switch
?>