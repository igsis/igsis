<?php 
	session_start();
	   @ini_set('display_errors', '1');
	error_reporting(E_ALL); 	
   
   // INSTALA��O DA CLASSE NA PASTA FPDF.
	require_once("../include/lib/fpdf/fpdf.php");
   require_once("../funcoes/funcoesConecta.php");
   require_once("../funcoes/funcoesGerais.php");
   require_once("../funcoes/funcoesSiscontrat.php");

   //CONEX�O COM BANCO DE DADOS 
   $conexao = bancoMysqli(); 
   

class PDF extends FPDF
{
// Page header
function Header()
{
	$inst = recuperaDados("ig_instituicao",$_SESSION['idInstituicao'],"idInstituicao");	$logo = "img/".$inst['logo']; // Logo
    $this->Image($logo,20,20,50);
    // Move to the right
    $this->Cell(80);
    $this->Image('../visual/img/logo_smc.jpg',170,10);
    // Line break
    $this->Ln(20);
}

// Simple table
function Cabecalho($header, $data)
{
    // Header
    foreach($header as $col)
        $this->Cell(40,7,$col,1);
    $this->Ln();
    // Data

}

// Simple table
function Tabela($header, $data)
{
    //Data
    foreach($data as $col)
        $this->Cell(40,7,$col,1);
    $this->Ln();
    // Data

}


// Page footer
/*
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
*/

//INSERIR ARQUIVOS

function ChapterBody($file)
{
    // Read text file
    $txt = file_get_contents($file);
    // Arial 10
    $this->SetFont('Arial','',10);
    // Output justified text
    $this->MultiCell(0,5,$txt);
    // Line break
    $this->Ln();
}

function PrintChapter($file)
{
    $this->ChapterBody($file);
}

}


//CONSULTA  (copia inteira em todos os docs)
$id_ped=$_GET['id'];

$ano=date('Y');
$dataAtual = date("d/m/Y");

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
$rfFiscal = $pedido["RfFiscal"];
$Suplente = $pedido["Suplente"];
$rfSuplente = $pedido["RfSuplente"];
$NumeroProcesso = $pedido["NumeroProcesso"];
$notaempenho = $pedido["NotaEmpenho"];
$data_entrega_empenho = exibirDataBr($pedido['EntregaNE']);
$data_emissao_empenho = exibirDataBr($pedido['EmissaoNE']);
$ingresso = dinheiroParaBr($pedido['ingresso']);
$ingressoExtenso = valorPorExtenso($pedido['ingresso']);

$grupo = grupos($id_ped);
$integrantes = $grupo["texto"];

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

// GERANDO O PDF:
$pdf = new PDF('P','mm','A4'); //CRIA UM NOVO ARQUIVO PDF NO TAMANHO A4
$pdf->AliasNbPages();
$pdf->AddPage();

   
$x=20;
$l=6; //DEFINE A ALTURA DA LINHA   

	//Executante
   
   $pdf->SetXY( $x , 20 );// SetXY - DEFINE O X (largura) E O Y (altura) NA PÁGINA
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 12);
   $pdf->Cell(180,$l,utf8_decode('PREFEITURA DO MUNICÍPIO DE S�O PAULO'),0,1,'C');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 12);
   $pdf->Cell(180,$l,utf8_decode('SECRETARIA MUNICIPAL DE CULTURA'),0,1,'C');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 12);
   $pdf->Cell(180,$l,utf8_decode('PROCESSO SEI N� ' .$NumeroProcesso),0,1,'C');
     
   $pdf->Ln();

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 12);
   $pdf->Cell(180,$l,utf8_decode('Ordem de execução de servi�o n� ___/2017'),0,1,'C');
   
   $pdf->Ln();
      
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(23,$l,utf8_decode('Emanada de:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(170,$l,utf8_decode('Divisião de Administra��o'),0,1,'L');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(27,$l,utf8_decode('Suporte Legal:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(153,$l,utf8_decode('Artigo 25, inciso III, da Lei Federal n� 8.666/93 e altera��es posteriores e artigo 1� da Lei Municipal n� 13.278/02, nos termos dos artigos 16 e 17 do Decreto n� 44.279/03.'));
   
   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(180,$l,utf8_decode('Prestador e/ou executor do servi�o'),0,1,'C');
      
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(13,$l,'Nome:',0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(155,$l,utf8_decode($Nome));
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(8,$l,utf8_decode('CPF:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(70,$l,utf8_decode($CPF),0,0,'L');
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(10,$l,utf8_decode('RG:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(65,$l,utf8_decode($RG),0,1,'L');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(20,$l,utf8_decode('Endere�o:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(160,$l,utf8_decode($Endereco));
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(26,$l,utf8_decode('Representante:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(160,$l,utf8_decode($Nome));
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(23,$l,utf8_decode('Estado Civil:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(65,$l,utf8_decode($EstadoCivil),0,0,'L');
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(10,$l,utf8_decode('Nacionalidade:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(65,$l,utf8_decode($Nacionalidade),0,1,'L');
   
  
   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(180,$l,utf8_decode('Servi�o'),0,1,'C');
     
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode("Especifica��es: Contrata��o dos servi�os profissionais de natureza art�stica de ".$Objeto.", atrav�s dos integrantes mencionados na Declara��o de Exclusividade, por ".$Nome.", CPF: ".$CPF.", para realiza��o de evento no "."$Local".", no per�odo "."$Periodo"." conforme proposta e cronograma."));		
   
   	$pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode("Fica designado como fiscal do contrato ".$Fiscal.", RF ".$rfFiscal." e como suplente ".$Suplente.", RF ".$rfSuplente."."));
   
   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode("Valor do Ingresso: ".$ingresso." ( ".$ingressoExtenso." )."));    
   
   $pdf->Ln();
     
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(180,$l,utf8_decode('Pagamento'),0,1,'C');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode($FormaPagamento));
   
//	QUEBRA DE PÁGINA
$pdf->AddPage('','');
$pdf->SetXY( $x , 40 );// SetXY - DEFINE O X (largura) E O Y (altura) NA PÁGINA  
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(180,$l,utf8_decode('Penalidades'),0,1,'C');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode("- Multa de 10% (dez por cento) sobre o valor do contrato ou sobre o valor integral da venda de todos os ingressos dispon�veis por atraso de at� 30 (trinta) minutos no evento. Ultrapassado esse tempo, e independentemente da aplica��o de penalidade, fica a crit�io do equipamento da Secretaria Municipal de Cultura autorizar a realiza��o do evento, visando evitar preju�zos � grade de programa��o. N�O sendo autorizada a realiza��o do evento, ser� considerada inexecu��o total do contrato, com aplica��o de multa prevista por inexecu��o total.
   
- Multa de 10% (dez por cento) para casos de infra��o de cl�usula contratual e/ou inexecu��o parcial do ajuste e de 30% (trinta por cento) para casos de inexecu��o total do ajuste. O valor da multa ser� calculado sobre o valor do contrato ou sobre o valor integral da venda de todos os ingressos dispon�veis.
   
- Multa de 10% (dez por cento) sobre o valor do contrato ou sobre o valor integral da venda de todos os ingressos dispon�veis, em fun��o da falta de regularidade fiscal do contratado, bem como, pela verifica��o de que possui pend�ncias junto ao Cadastro Informativo Municipal (CADIN).
   
- As penalidades ser�o aplicadas sem preju�zo das demais san��es previstas na legisla��o que rege a mat�ria."));

   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(180,$l,utf8_decode('Cancelamento'),0,1,'C');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode("Esta O.E.S. poder� ser cancelada no interesse da administra��o, devidamente justificada ou em virtude da inexecu��o total ou parcial do servi�o sem preju�zo de multa."));
   
   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(180,$l,utf8_decode('Foro'),0,1,'C');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode("Fica eleito o foro desta comarca para todo e qualquer procedimento judicial oriundo desta ordem de execu��o de servi�os."));
   
   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(180,$l,utf8_decode('Observa��es'),0,1,'C');
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode(" - Compete � contratada a realiza��o do espet�culo, e a fazer constar o cr�dito � � PMSP/SECRETARIA MUNICIPAL DE CULTURA, em toda divulga��o escrita ou falada, realizada sobe o espet�culo programado.

- A empresa contratada fica sujeita ao atendimento no disposto nas Leis Municipais n� 10.973/9, regulamentada pelo DM 30.730/91; 11.113/91; 11.357/93; 12.975/2000 e portaria 66/SMC/2007; Leis Estaduais n� 7.844/92; Medida Provis�ria Federal 12.933/2013 e Lei Federal 10.741/2013.

- A contratada � respons�vel por qualquer preju�zo ou dano causado ao patrim�nio municipal ou a bens de terceiros que estejam sob a guarda do equipamento local de realiza��o do evento.

- Quaisquer outras despesas n�o ressalvadas aqui ser�o de responsabilidade da contratada, que se compromete a adotar as provid�ncias necess�rias junto � OMB.

- As provid�ncias administrativas para libera��o da autoriza��o do ECAD ser�o de responsabilidade da contratada, sendo que eventuais pagamento ser�o efetuados pela SMC.

- A Municipalidade n�o � respons�vel por qualquer material ou equipamento que n�o lhe perten�a utilizado no espet�culo, devendo esse material ser retirado no seu t�rmino.

- A Cia. dever� designar uma pessoa para atuar na Bilheteria durante toda a temporada, cabendo a esta a responsabilidade exclusiva pela venda dos ingressos.

- A Bilheteria dever� abrir 1 (uma) hora antes do in�cio de cada espet�culo.

- Ap�s o t�rmino de cada espet�culo um servidor designado pela Coordena��o do Teatro efetuar� o fechamento do border� com o bilheteiro respons�vel.

- Caber� a Cia. efetuar o repasse do percentual do FEPAC e a Coordena��o do Teatro caber� o recolhimento do valor.

- Em havendo contrata��o pela Secretaria de Cultura de empresa prestadora de servi�os de gerenciamento da bilheteria, caber� a esta efetuar a venda dos ingressos.

- Compete, ainda, � Municipalidade, o fornecimento da sonoriza��o necess�ria � realiza��o de espet�culos e dos equipamentos de ilumina��o dispon�veis no local do evento, assim como provid�ncias quanto � divulga��o de praxe (confec��o de cartaz a ser afixado no equipamento cultural e encaminhamento de release � m�dia impressa e televisiva).

- A Coordenadoria dos Centros Culturais reserva-se o direito de disponibilizar 6 (seis) ingressos por apresenta��o, que n�o poder�o ser comercializados pela Cia. Sendo que haver� comunicado com anteced�ncia quando da utiliza��o desses ingressos. Caso n�o haja manifesta��o por parte da SMC a comercializa��o desses ingressos ser� livre.

- A/o contratada/o se compromete a realizar o espet�culo para um n�mero m�nimo de 10 (dez) pagantes.

Aceito as condi��es dessa O.E.S para todos os efeitos de direito."));
   
   $pdf->Ln();
     
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(25,$l,utf8_decode('Local e data:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(160,$l,utf8_decode('S�o Paulo, '.$dataAtual),0,1,'L');
   
   $pdf->Ln();
   $pdf->Ln();
   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(120,$l,utf8_decode($Nome),'T',1,'L');
   $pdf->SetX($x);
   $pdf->Cell(120,$l,utf8_decode('CPF '.$CPF),0,0,'L');
   
   $pdf->Ln();
   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','', 10);
   $pdf->MultiCell(180,$l,utf8_decode("Determino a execu��o do servi�o na forma desta O.E.S."));
   
    $pdf->Ln();
	  
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(25,$l,utf8_decode('Local e data:'),0,0,'L');
   $pdf->SetFont('Arial','', 10);
   $pdf->Cell(160,$l,utf8_decode('São Paulo, '.$dataAtual),0,1,'L');
   
   $pdf->Ln();
   $pdf->Ln();
   $pdf->Ln();
   
   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(120,$l,utf8_decode('Giovanna M. R. Lima'),'T',1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(120,$l,utf8_decode('Chefe de Gabinete'),'',1,'L');

   $pdf->SetX($x);
   $pdf->SetFont('Arial','B', 10);
   $pdf->Cell(120,$l,utf8_decode('Secretaria Municipal de Cultura'),'',1,'L');
    
   
   $pdf->Ln();
    

  


   

//for($i=1;$i<=20;$i++)
   // $pdf->Cell(0,10,'Printing line number '.$i,0,1);
$pdf->Output();


?>