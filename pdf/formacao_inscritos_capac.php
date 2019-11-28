<?php

// Incluimos a classe PHPExcel
require_once("../include/phpexcel/Classes/PHPExcel.php");
require_once("../funcoes/funcoesConecta.php");
require_once("../funcoes/funcoesGerais.php");


// Instanciamos a classe
$objPHPExcel = new PHPExcel();


// Podemos renomear o nome das planilha atual, lembrando que um único arquivo pode ter várias planilhas
$objPHPExcel->getProperties()->setCreator("Sistema IGSIS");
$objPHPExcel->getProperties()->setLastModifiedBy("Sistema IGSIS");
$objPHPExcel->getProperties()->setTitle("Relatório de Controle de Fotos");
$objPHPExcel->getProperties()->setSubject("Relatório de Controle de Fotos");
$objPHPExcel->getProperties()->setDescription("Gerado automaticamente a partir do Sistema IGSIS");
$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
$objPHPExcel->getProperties()->setCategory("Inscritos");

// Criamos as colunas
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'Número Inscrição' )
    ->setCellValue('B1', "Nome" )
    ->setCellValue("C1", "CPF" )
    ->setCellValue("D1", "Data de Nascimento" )
    ->setCellValue("E1", "Função")
    ->setCellValue("F1", "Linguagem")
    ->setCellValue("G1", "Etnia")
    ->setCellValue("H1", "Região Preferencial");

// Definimos o estilo da fonte
$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

//Colorir a primeira linha
$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray
(
    array
    (
        'fill' => array
        (
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'E0EEEE')
        ),
    )
);


//Consulta
$con = bancoMysqliProponente();

$ano = $_POST['ano'];

$idTipoFormacao = $_POST['idTipoFormacao'];


switch ($idTipoFormacao)
{
    case 0:
        $sqlFormacao = "";
        break;
    case 1:
        $sqlFormacao = "AND pf.tipo_formacao_id = 1";
        break;
    case 2:
        $sqlFormacao = "AND pf.tipo_formacao_id = 2";
        break;
    default:
        $sqlFormacao = "";
        break;
}

$sql= "SELECT
          pf.id,
          pf.nome,
          pf.rg,
          pf.cpf,
          pf.ccm,
          pf.dataNascimento,
          tf.descricao,
          fl.linguagem,
          ff.funcao,
          et.etnia,
          pf.formacao_ano,
          re.regiao
        FROM pessoa_fisica AS pf
               INNER JOIN tipo_formacao as tf ON pf.tipo_formacao_id = tf.id
               INNER JOIN formacao_linguagem as fl ON pf.formacao_linguagem_id = fl.id
               INNER JOIN formacao_funcoes as ff ON pf.formacao_funcao_id = ff.id
               INNER JOIN etnias as et ON et.id = pf.etnia_id
               LEFT JOIN regioes as re ON re.id = pf.formacao_regiao_preferencial
               INNER JOIN (SELECT pessoa_fisica_id FROM formacao_validacao WHERE validado = 1) AS fv ON fv.pessoa_fisica_id = pf.id
        WHERE pf.tipo_formacao_id > 0
          AND pf.formacao_ano = '$ano'
          AND pf.formacao_funcao_id IS NOT NULL
          AND pf.publicado = '1' {$sqlFormacao}";

        echo $sql;

$query = (mysqli_query($con,$sql));

$i = 2;
while($pf = mysqli_fetch_array($query))
{
    $a = "A".$i;
    $b = "B".$i;
    $c = "C".$i;
    $d = "D".$i;
    $e = "E".$i;
    $f = "F".$i;
    $g = "G".$i;
    $h = "H".$i;
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue($a, $pf['id'])
        ->setCellValue($b, $pf['nome'])
        ->setCellValue($c, $pf['cpf'])
        ->setCellValue($d, exibirDataBr($pf['dataNascimento']))
        ->setCellValue($e, $pf['funcao'])
        ->setCellValue($f, $pf['linguagem'])
        ->setCellValue($g, $pf['etnia'])
        ->setCellValue($h, $pf['regiao']);
    $i++;
}

// Renomeia a guia
$objPHPExcel->getActiveSheet()->setTitle('Inscritos');

foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col)
{
    $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
}


$objPHPExcel->setActiveSheetIndex(0);
ob_end_clean();
ob_start();

$nome_arquivo = date("Y-m-d")."_capac_inscritos.xls";


// Cabeçalho do arquivo para ele baixar(Excel2007)
header('Content-Type: text/html; charset=ISO-8859-1');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$nome_arquivo.'"');
header('Cache-Control: max-age=0');
// Se for o IE9, isso talvez seja necessário
header('Cache-Control: max-age=1');

// Acessamos o 'Writer' para poder salvar o arquivo
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

// Salva diretamente no output, poderíamos mudar arqui para um nome de arquivo em um diretório ,caso não quisessemos jogar na tela
$objWriter->save('php://output');

exit;
