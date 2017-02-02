﻿<?php
include 'includes/menu.php';

$conexao = bancoMysqli();
$id_ped=$_GET['id_ped'];
$pedido = siscontrat($id_ped);
$data = date('Y-m-d H:i:s');

$server = "http://".$_SERVER['SERVER_NAME']."/igsis/";
$http = $server."/pdf/";
$link1=$http."rlt_despacho_padrao_pf.php";
$link2=$http."rlt_despacho_vocacional_pf.php";
$link3=$http."rlt_manifestacaojuridica_pf.php";
$link4=$http."rlt_despacho_padrao_pj.php";
$link5=$http."rlt_manifestacaojuridica_pj.php";

$AmparoLegal=$_POST['AmparoLegal'];
$ComplementoDotacao=$_POST['ComplementoDotacao'];
$Finalizacao=$_POST['Finalizacao'];
$update = "UPDATE igsis_pedido_contratacao SET
		   AmparoLegal = '$AmparoLegal',
		   ComplementoDotacao = '$ComplementoDotacao',
		   Finalizacao = '$Finalizacao',
		   estado = 7,
		   DataJuridico = '$data'
		   WHERE IdPedidoContratacao = '$id_ped' ";
$stmt = mysqli_prepare($conexao,$update);
if(mysqli_stmt_execute($stmt))
{
	echo $pedido['tipoPessoa'];
	$last_id = mysqli_insert_id($conexao);
	if ($pedido['tipoPessoa'] == 2)
	{
		echo "
			<p>&nbsp;</p><h4><center>Dados Inseridos com sucesso!</h4><br>
			<br><br><h6>Qual modelo de documento deseja gerar?</h6><br>
			<div class='row'>
				<div class='col-md-offset-1 col-md-10'>
				<form class='form-horizontal' role='form'>
					<div class='form-group'>
						<div class='col-md-offset-2 col-md-6'>
							<a href='$link4?id=$id_ped' class='btn btn-theme btn-lg btn-block' target='_blank'>Despacho</a>
						</div>
						<div class='col-md-6'>
							<a href='$link5?id=$id_ped' class='btn btn-theme btn-lg btn-block' target='_blank'>Manifestação Jurídica</a>
						</div>
					</div>
				</form>
				</div>
			</div><br/><br /></center>
		";
	}
	else
	{
		echo "
			<p>&nbsp;</p><h4><center>Dados Inseridos com sucesso!</h4><br>
			<br><br><h6>Qual modelo de documento deseja gerar?</h6><br>
			<div class='row'>
				<div class='col-md-offset-1 col-md-10'>
				<form class='form-horizontal' role='form'>
					<div class='form-group'>
						<div class='col-md-offset-2 col-md-6'>
							<a href='$link1?id=$id_ped' class='btn btn-theme btn-lg btn-block' target='_blank'>Despacho Padrão</a>
						</div>
						<div class='col-md-6'>
							<a href='$link2?id=$id_ped' class='btn btn-theme btn-lg btn-block' target='_blank'>Despacho Formação</a>
						</div>
					</div>
					<div class='form-group'>
						<div class='col-md-offset-2 col-md-6'>
							<a href='$link3?id=$id_ped' class='btn btn-theme btn-lg btn-block' target='_blank'>Manifestação Jurídica</a>
						</div>
					</div>
				</form>
				</div>
			</div><br/><br /></center>
		";
	}
}
?>