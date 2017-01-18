﻿<!-- BUSCA POR PEDIDO -->
<?php  
	 require_once("../funcoes/funcoesVerifica.php");
	 require_once("../funcoes/funcoesSiscontrat.php");
	 include "includes/menu.php"; 
?>
 
<?php
	if(isset($_GET['b']))
{
		$b = $_GET['b'];	
}
	else
{
	$b = 'inicial';
}

switch($b){
case 'inicial':
	if(isset($_POST['pesquisar']))
{
		$id = trim($_POST['id']);
		$evento = trim($_POST['evento']);
		$fiscal = $_POST['fiscal'];

	if($id == "" AND $evento == "" AND $fiscal == 0 AND $juridico == 0 AND $projeto == 0){ ?>
	<section id="services" class="home-section bg-white">
		<div class="container">
			<div class="row">
				<div class="col-md-offset-2 col-md-8">
					<div class="section-heading">
						<h2>Busca por evento</h2>
						<p>É preciso ao menos um critério de busca ou você pesquisou por um evento inexistente. Tente novamente.</p>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="form-group">
					<div class="col-md-offset-2 col-md-8">
						<h5><?php if(isset($mensagem)){ echo $mensagem; } ?>
						<form method="POST" action="?perfil=gestao_prazos&p=frm_busca" class="form-horizontal" role="form">				
						<label>Id do Evento</label>
						<input type="text" name="id" class="form-control" id="palavras" placeholder="Insira o Id do Evento" ><br />		
						
<?php if($_SESSION['perfil'] == 1){?>
					<label>Objeto/Evento</label>
            		<input type="text" name="evento" class="form-control" id="palavras" placeholder="Insira o objeto" ><br />
					
<?php } ?>
					
					<label>Fiscal, suplente ou usuário que cadastrou o evento</label>
					<select class="form-control" name="fiscal" id="inputSubject" >
					<option value="0"></option>	
					
<?php echo opcaoUsuario($_SESSION['idInstituicao'],"") ?>
					</select>
            	</div>
            </div> <br />  
	        <div class="form-group">
		        <div class="col-md-offset-2 col-md-8">
                	<input type="hidden" name="pesquisar" value="1" />
    		        <input type="submit" class="btn btn-theme btn-lg btn-block" value="Pesquisar">
                    </form>
        	    </div>
        	</div>
		</div>
		</div>
		
	</section>
<?php
}
else
{
		$con = bancoMysqli();
		$sql_existe = "SELECT idPedidoContratacao,idEvento,estado FROM igsis_pedido_contratacao WHERE idEvento = '$evento' AND publicado = '1' AND estado IS NOT NULL ORDER BY idEvento DESC";
		$query_existe = mysqli_query($con, $sql_existe);
		$num_registro = mysqli_num_rows($query_existe);
		
	if($id != "" AND $num_registro > 0)
{ // Foi inserido o número do pedido
		$pedido = recuperaDados("igsis_pedido_contratacao",$id,"idEvento");
		
	if($pedido['estado'] != NULL)
{
		$evento = recuperaDados("ig_evento",$pedido['idEvento'],"idEvento"); //$tabela,$idEvento,$campo
		$usuario = recuperaDados("ig_usuario",$evento['idUsuario'],"idUsuario");
		$instituicao = recuperaDados("ig_instituicao",$evento['idInstituicao'],"idInstituicao");
		$local = listaLocais($pedido['idEvento']);
		$local_juridico = listaLocaisJuridico($pedido['idEvento']);
		$periodo = retornaPeriodo($pedido['idEvento']);
		$duracao = retornaDuracao($pedido['idEvento']);
		$pessoa = recuperaPessoa($pedido['idPessoa'],$pedido['tipoPessoa']);
		$fiscal = recuperaUsuario($evento['idResponsavel']);
		$x[0]['id']= $evento['idEvento'];
		$x[0]['id_ped']= $pedido['idPedidoContratacao'];
		$x[0]['local'] = substr($local,1);
		$x[0]['periodo'] = $periodo;
		$x[0]['fiscal'] = $fiscal['nomeCompleto'];
		
	if($pedido['tipoPessoa'] == 1)
{
		$pessoa = recuperaDados("sis_pessoa_fisica",$pedido['idPessoa'],"Id_PessoaFisica");
		$x[0]['proponente'] = $pessoa['Nome'];
		$x[0]['tipo'] = "Física";
}
	else
{
		$pessoa = recuperaDados("sis_pessoa_juridica",$pedido['idPessoa'],"Id_PessoaJuridica");
		$x[0]['proponente'] = $pessoa['RazaoSocial'];
		$x[0]['tipo'] = "Jurídica";
}
		$x['num'] = 1;
		$x[0]['objeto'] = retornaTipo($evento['ig_tipo_evento_idTipoEvento'])." - ".$evento['nomeEvento'];
}
	else
{
		$x['num'] = 0;
}
}
	else
{ //Não foi inserido o número do pedido
	if($evento != ''){
		$filtro_evento = " AND nomeEvento LIKE '%$evento%' OR autor LIKE '%$evento%' ";
}
	else
{
		$filtro_evento = "";			
}
		
	if($fiscal != 0)
{
		$filtro_fiscal = " AND (idResponsavel = '$fiscal' OR suplente = '$fiscal' OR idUsuario = '$fiscal' )";	
}
	else
{
		$filtro_fiscal = "";	
}		
		$sql_evento = "SELECT * FROM ig_evento WHERE ig_evento.publicado = '1' AND dataEnvio IS NULL AND ig_evento.idEvento NOT IN (SELECT DISTINCT idEvento FROM igsis_pedido_contratacao WHERE publicado = 1) $filtro_evento $filtro_fiscal ORDER BY ig_evento.idEvento DESC";
		$query_evento = mysqli_query($con,$sql_evento);
		$i = 0;
		
	while($evento = mysqli_fetch_array($query_evento))
{
		$idEvento = $evento['idEvento'];	
		$evento = recuperaDados("ig_evento",$idEvento,"idEvento"); 			
		$usuario = recuperaDados("ig_usuario",$evento['idUsuario'],"idUsuario");
		$local = listaLocais($idEvento);
		$periodo = retornaPeriodo($idEvento);
		$fiscal = recuperaUsuario($evento['idResponsavel']);						
		$x[$i]['id']= $evento['idEvento'];
		$x[$i]['objeto'] = retornaTipo($evento['ig_tipo_evento_idTipoEvento'])." - ".$evento['nomeEvento'];
		$x[$i]['local'] = substr($local,1);
		$x[$i]['periodo'] = $periodo;
		$x[$i]['fiscal'] = $fiscal['nomeCompleto'];			
		$i++;			
}
		$x['num'] = $i;		
}
} 
		$mensagem = "Foram encontradas ".$x['num']." pedido(s) de contratação.";

?>
<br /><br />

	<section id="list_items">
		<div class="container">
			 <h3>Resultado da busca</h3>
             <h5>Foram encontrados <?php echo $x['num']; ?> eventos.</h5>
             <h5><a href="?perfil=gestao_prazos&p=frm_busca">Fazer outra busca</a></h5>
		<div class="table-responsive list_info">
			
<?php if($x['num'] == 0){ ?>
		
<?php }else{ ?>
		<table class="table table-condensed">
			<thead>
				<tr class="list_menu">
				<td>Id Evento</td>
				<td>Objeto</td>
				<td>Local</td>
				<td>Periodo</td>
                <td>Fiscal</td>
				</tr>
			</thead>
		<tbody>
		
<?php
	$link="index.php?perfil=gestao_prazos&p=detalhe_evento&id_eve=";
	$data=date('Y');
	
	for($h = 0; $h < $x['num']; $h++)
{		
		echo "<tr><td class='list_description'> <a href='".$link.$x[$h]['id']."'>".$x[$h]['id']."</a></td>";	
		echo '<td class="list_description">'.$x[$h]['objeto'].				'</td> ';
		echo '<td class="list_description">'.$x[$h]['local'].				'</td> ';
		echo '<td class="list_description">'.$x[$h]['periodo'].				'</td> ';
		echo '<td class="list_description">'.$x[$h]['fiscal'].				'</td> </tr>';
}
?>
		</tbody>
		</table>
		
<?php } ?>		
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
					<div class="section-heading">
						<h2>Busca por evento</h2>
					</div>
				</div>
			</div>
			  
	    <div class="row">
            <div class="form-group">
            	<div class="col-md-offset-2 col-md-8">
					<h5><?php if(isset($mensagem)){ echo $mensagem; } ?>
                    <form method="POST" action="?perfil=gestao_prazos&p=frm_busca" class="form-horizontal" role="form">
            		<label>Id do Evento</label>
            		<input type="text" name="id" class="form-control" id="palavras" placeholder="Insira o Id do Evento" ><br />
            		
<?php if($_SESSION['perfil'] == 1){?>					
					<label>Objeto/Evento</label>
            		<input type="text" name="evento" class="form-control" id="palavras" placeholder="Insira o objeto" ><br />

<?php } ?>
            			          
					<label>Fiscal, suplente ou usuário que cadastrou o evento</label>
					<select class="form-control" name="fiscal" id="inputSubject" >
					<option value="0"></option>	
					
<?php echo opcaoUsuario($_SESSION['idInstituicao'],"") ?>
					</select>

            	</div>
             </div>
		</div><br /> 
        <div class="form-group">
            <div class="col-md-offset-2 col-md-8">
               	<input type="hidden" name="pesquisar" value="1" />
   		        <input type="submit" class="btn btn-theme btn-lg btn-block" value="Pesquisar">
					</form>
        	</div>
        </div>
        </div>
	</section>               

<?php } ?>

<?php
break;
?>

<?php
} // fim da switch
?>