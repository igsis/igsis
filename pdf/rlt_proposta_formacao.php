<?php

// INSTALAÇÃO DA CLASSE NA PASTA FPDF.
require_once("../include/lib/fpdf/fpdf.php");
require_once("../funcoes/funcoesConecta.php");
require_once("../funcoes/funcoesGerais.php");
require_once("../funcoes/funcoesSiscontrat.php");
require_once("../funcoes/funcoesFormacao.php");

//CONEXÃO COM BANCO DE DADOS
$conexao = bancoMysqli();

// logo da instituição
session_start();

// esse código limpa algum print errada da session
while (ob_get_level())
ob_end_clean();
header("Content-Encoding: None", true);

class PDF extends FPDF
{
   // Page header
   function Header()
   {
       // Move to the right
       $this->Cell(80);
       $this->Image('../visual/img/logo_smc.jpg',170,10);
       // Line break
       $this->Ln(20);
   }

   //INSERIR ARQUIVOS
   function ChapterBody($file)
   {
       // Read text file
       $txt = file_get_contents($file);
       // Arial 10
       $this->SetFont('Arial','',8);
       // Output justified text
       $this->MultiCell(0,4,$txt);
       // Line break
       $this->Ln();
   }

   function PrintChapter($file)
   {
       $this->ChapterBody($file);
   }
}


//CONSULTA
$id_ped=$_GET['id'];
$idPenalidade = $_GET['penal'];
dataProposta($id_ped);
gravaPenalidade($id_ped,$idPenalidade);
$penal = recuperaDados("sis_penalidades",$idPenalidade,"idPenalidades");
$Observacao = "Todas as atividades dos programas da Supervisão de Formação são inteiramente gratuitas e é terminantemente proibido cobrar por elas sob pena de multa e rescisão de contrato.";
$txtPenalidade = $penal['txt'];
$ano=date('Y');

$pedido = siscontrat($id_ped);
$pessoa = siscontratDocs($pedido['IdProponente'],1);

$id = $pedido['idEvento'];
$Objeto = $pedido["Objeto"];
$Periodo = $pedido["Periodo"];
$Duracao = $pedido["Duracao"];
$CargaHoraria = $pedido["CargaHoraria"];
$Local = $pedido["Local"];
$ValorGlobal = dinheiroParaBr($pedido["ValorGlobal"]);
$ValorPorExtenso = valorPorExtenso($pedido["ValorGlobal"]);
$FormaPagamento = $pedido["FormaPagamento"];
$Justificativa = $pedido["Justificativa"];
$Fiscal = $pedido["Fiscal"];
$Suplente = $pedido["Suplente"];

$Nome = $pessoa["Nome"];
$NomeArtistico = $pessoa["NomeArtistico"];
$EstadoCivil = $pessoa["EstadoCivil"];
$Nacionalidade = $pessoa["Nacionalidade"];
$DataNascimento = exibirDataBr($pessoa["DataNascimento"]);
$RG = $pessoa["RG"];
$CPF = $pessoa["CPF"];
$CCM = $pessoa["CCM"];
$OMB = $pessoa["OMB"];
$DRT = $pessoa["DRT"];
$cbo = $pessoa["cbo"];
$Funcao = $pessoa["Funcao"];
$Endereco = $pessoa["Endereco"];
$Telefones = $pessoa["Telefones"];
$Email = $pessoa["Email"];
$INSS = $pessoa["INSS"];


/* variáveis novas a criar */
$formacao = pdfFormacao($id_ped);
$cargo = $formacao['Cargo'];
$programa = $formacao['Programa'];
$descricaoPrograma = $formacao['descricaoPrograma'];
$edital = $formacao['edital'];
$linguagem = $formacao['linguagem'];



// GERANDO O PDF:
$pdf = new PDF('P','mm','A4'); //CRIA UM NOVO ARQUIVO PDF NO TAMANHO A4
$pdf->AliasNbPages();
$pdf->AddPage();


$x=20;
$l=7; //DEFINE A ALTURA DA LINHA

   $pdf->SetXY( $x , 35 );// SetXY - DEFINE O X (largura) E O Y (altura) NA PÁGINA

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(10,5,'(A)',0,0,'L');
   $pdf->SetFont('Arial','B', 12);
   $pdf->Cell(170,5,'CONTRATADO',0,1,'C');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','I', 10);
   $pdf->Cell(10,10,utf8_decode('(Quando se tratar de grupo, o líder do grupo)'),0,0,'L');

   $pdf->Ln();

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(12,$l,'Nome:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(168,$l,utf8_decode($Nome));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(28,$l,utf8_decode('Nome Artístico:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(152,$l,utf8_decode($NomeArtistico));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(8,$l,utf8_decode('RG:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(50,$l,utf8_decode($RG),0,0,'L');
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(10,$l,utf8_decode('CPF:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(53,$l,utf8_decode($CPF),0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(36,$l,utf8_decode('Data de Nascimento:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(25,$l,utf8_decode($DataNascimento),0,0,'L');
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(28,$l,utf8_decode('Nacionalidade:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(35,$l,utf8_decode($Nacionalidade),0,0,'L');
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(10,$l,utf8_decode('CCM:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(45,$l,utf8_decode($CCM),0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(10,$l,utf8_decode('DRT:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(40,$l,utf8_decode($DRT),0,0,'L');
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(15,$l,utf8_decode('OMB:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(45,$l,utf8_decode($OMB),0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(20,$l,utf8_decode('Endereço:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(160,$l,utf8_decode($Endereco));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(17,$l,utf8_decode('Telefone:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(87,$l,utf8_decode($Telefones),0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(13,$l,utf8_decode('E-mail:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(53,$l,utf8_decode($Email),0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(64,$l,utf8_decode('Inscrição no INSS ou nº PIS / PASEP:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(50,$l,utf8_decode($INSS),0,1,'L');

   $pdf->SetX($x);
   $pdf->Cell(180,5,'','B',1,'C');

   $pdf->Ln();

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(10,10,'(B)',0,0,'L');
   $pdf->SetFont('Arial','B', 12);
   $pdf->Cell(160,10,'PROPOSTA',0,0,'C');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(10,10,$ano."-".$id_ped,0,1,'R');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(15,$l,'Objeto:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(165,5,utf8_decode($Objeto));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(27,$l,utf8_decode('Data / Período:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(50,$l,utf8_decode($Periodo),0,0,'L');
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(27,$l,utf8_decode('Carga Horária:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(80,$l,$CargaHoraria,0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(12,5,'Local:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(168,5,utf8_decode($Local));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(12,$l,'Valor:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(168,$l,utf8_decode("R$ $ValorGlobal"."  "."($ValorPorExtenso )"));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(40,$l,'Forma de Pagamento:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(140,5,utf8_decode($FormaPagamento));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(25,$l,'Justificativa:',0,0,'L');
   $pdf->SetFont('Arial','', 9);
   $pdf->MultiCell(155,5,utf8_decode($Justificativa));


//RODAPÉ PERSONALIZADO
   $pdf->SetXY($x,262);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,utf8_decode($Nome),'T',1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,"RG: ".$RG,0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,"CPF: ".$CPF,0,0,'L');


//	QUEBRA DE PÁGINA
$pdf->AddPage('','');

$pdf->SetXY( $x , 30 );// SetXY - DEFINE O X (largura) E O Y (altura) NA PÁGINA

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(10,$l,'(C)',0,0,'L');
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(160,$l,utf8_decode('OBSERVAÇÃO'),0,1,'C');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(0,4,utf8_decode($Observacao),0,'J');

   $pdf->Ln();

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 9);
   $pdf->MultiCell(0,4,utf8_decode($txtPenalidade),0,'J');

$pdf->Ln();

$pdf->SetX($x);
$pdf->SetFont('Arial','B', 10);
$pdf->Cell(22,4,'Regime emergencial:',0,1,'L');

$pdf->SetX($x);
$pdf->SetFont('Arial','', 10);
$pdf->MultiCell(180,4,utf8_decode("
Durante o período em que os equipamentos Culturais e da Educação estiverem fechados, é do intuito da SMC tornar pública e acessível a formação a partir de suas diversas linguagens artísticas, pesquisas, mantendo como destinatário o publico-alvo do Programa.
Será da atribuição dos Artistas Educadores/Orientadores:
- A criação de materiais artístico pedagógicos em diversos suportes: vídeo, imagem, som e texto; que podem ser conteúdos ao vivo ou gravados, na forma de aulas, vivências, ações artísticas alinhados aos princípios do Programa.
- A criação e apresentação prévia da programação, cronograma e periodicidade de publicação dos materiais.
- A avaliação do alcance e impacto das ações e organização de novas estratégias de publicação devidamente alinhadas com a equipe técnica e coordenação do Programa."));

   $pdf->Ln();

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(180,$l,"Data: _________ / _________ / "."$ano".".",0,0,'L');

   $pdf->Ln();
   $pdf->Ln();
   $pdf->Ln();
   $pdf->Ln();

//RODAPÉ PERSONALIZADO
   $pdf->SetXY($x,262);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,utf8_decode($Nome),'T',1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,"RG: ".$RG,0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,"CPF: ".$CPF,0,0,'L');



//	QUEBRA DE PÁGINA
$pdf->AddPage('','');
$pdf->SetXY( $x , 37 );// SetXY - DEFINE O X (largura) E O Y (altura) NA PÁGINA



$l=5; //DEFINE A ALTURA DA LINHA

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 12);
   $pdf->Cell(180,5,"CRONOGRAMA",0,1,'C');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 12);
   $pdf->Cell(180,5,utf8_decode($programa),0,1,'C');

   $pdf->Ln();
   $pdf->Ln();

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(12,$l,'Nome:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(168,$l,utf8_decode($Nome));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(12,$l,'Cargo:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(168,$l,utf8_decode($cargo));

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(22,$l,'Linguagem:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode($linguagem));

   $pdf->Ln();

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode("O prestador de serviços acima citado é contratado nos termos do Edital ".$edital.", no período de ".$Periodo. ", com carga horária total de até: ".$CargaHoraria.", distribuída no período já citado, na forma abaixo descrita:"));

   $pdf->Ln();

   //inicio cronograma
	$con = bancoMysqli();
	$sql_parcelas = "SELECT * FROM igsis_parcelas WHERE idPedido = '$id_ped' ORDER BY vigencia_inicio ASC";
	$query = mysqli_query($con,$sql_parcelas);
	while($parcela = mysqli_fetch_array($query))
   {
	   if($parcela['valor'] > 0)
      {
   		$inicio = exibirDataBr($parcela['vigencia_inicio']);
   		$fim = exibirDataBr($parcela['vigencia_final']);
   		$horas = $parcela['horas'];

   	   $pdf->SetX($x);
   	   $pdf->SetFont('Arial','', 10);
   	   $pdf->MultiCell(180,$l,utf8_decode("De $inicio a $fim - até $horas horas"));
      }
   }
	//fim cronograma

   $pdf->Ln();
   $pdf->Ln();
   $pdf->Ln();

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(180,$l,utf8_decode("São Paulo, ______ de ____________________ de "."$ano").".",0,0,'L');


//RODAPÉ PERSONALIZADO
   $pdf->SetXY($x,262);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,utf8_decode($Nome),'T',1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,"RG: ".$RG,0,1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(100,4,"CPF: ".$CPF,0,0,'L');


$pdf->Output();
?>