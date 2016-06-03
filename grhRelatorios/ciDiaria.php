<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado
$matriculaGrh = null;		  # Reservado para a matrícula pesquisada

# nome das lotações
$lotacaoOrigem = "Gerência de Recursos Humanos e Pagamento - DPAF/GRH";
$lotacaoDestino = "Diretoria de Planejamento, Administração e Finanças - DPAF";
$lotacaoCi = 'FENORTE/DPAF/GRHPAG';

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Pega o id da diaria
    $id = get('id');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # pega os dados
    $dados = $pessoal->get_dadosDiaria($id);

    $data = $dados[0];
    $ci = $dados[1];
    $assunto = $intra->get_codigoAssuntoPorId($dados[2]);
    $valor = $dados[3];        

    ## Monta o Relatório 
    # Menu
    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->set_botaoVoltar(NULL);
    $menuRelatorio->show();

    # Cabeçalho do Relatório (com o logotipo)
    $cabecalho = new Cabecalho(); 

    $valor = str_replace('.','',$valor);            // retira o ponto do milhar p/não dar problema na rotina de extenso
    $valor = str_replace(',','.',$valor);           // passa a vírgula dos centavos para ponto (padrão americano)
    $extenso = extenso($valor);                     // pega o extenso
    $valor = number_format($valor, 2, '.', ',');    // passa para o formato de moeda (padrão americano)
    $valor = str_replace('.','*',$valor);           // passa o ponto (dos centavos) para *
    $valor = str_replace(',','.',$valor);           // passa a vírgula (do milhar) para ponto (do milhar)
    $valor = str_replace('*',',',$valor);           // passa o * para vírgula dos centavos

    # Pega os dados do servidor
    $select = 'SELECT tbfuncionario.matricula,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.endereco,
                      tbpessoa.complemento,
                      tbpessoa.bairro,
                      tbpessoa.cidade,
                      tbpessoa.UF,
                      tbpessoa.cep,
                      tbpessoa.agencia,
                      tbpessoa.conta
             FROM tbfuncionario
             JOIN tbpessoa on (tbfuncionario.idPessoa = tbpessoa.idPessoa)
             JOIN tbdocumentacao on (tbpessoa.idPessoa = tbdocumentacao.idpessoa)
        WHERE tbfuncionario.matricula = '.$matriculaGrh;
    
    $row = $pessoal->select($select,false); 
    
    br(2);
    
    # Assunto
    $p = new P($assunto,'pDiaria');
    $p->show();
    
    # CI
    $p = new P('CI '.$lotacaoCi.' nº '.$ci,'pDiaria');
    $p->show();
    
    # Data
    $p = new P('Campos dos Goytacazes,'.Data::porExtenso($data),'pDiariaData');
    $p->show();
    br(2);
    
    # Origem
    $p = new P('De: '.$lotacaoOrigem,'pDiaria');
    $p->show();
    br();
    
    # Destino
    $p = new P('Para: '.$lotacaoDestino,'pDiaria');
    $p->show();
    br(2);
    
    # Texto
    $p = new P('Encaminhamos o presente Processo referente a diária(s), no valor total de R$ '.$valor.' ('.$extenso.' ), do servidor(a) abaixo relacionado(a), para as providências que fizerem necessárias.','pDiaria');
    $p->show();
    br(3);
    
    # Tabela
    echo '<table id="tableDiaria">';
    echo '<col style="width:30%">';
    echo '<col style="width:70%">';
   
    # Matrícula e Nome
    echo '<tr><th>';
    echo 'Matrícula';
    echo '</th><th>';
    echo 'Servidor(a)';
    echo '</th></tr>';

    echo '<tr><td>';
    echo $row[0];
    echo '</td><td>';
    echo $row[1];
    echo '</td></tr>';    
    
    # CPF
    echo '<tr><th>';
    echo 'CPF';
    echo '</th><td>';
    echo $row[2];
    echo '</td></tr>';

    # Cargo Em Comissão
    echo '<tr><th>';
    echo 'Cargo em Comissão';
    echo '</th><td>';
    echo $pessoal->get_cargoComissao($row[0]);
    echo '</td></tr>';

    # Endereço
    echo '<tr><th>';
    echo 'Endereço';
    echo '</th><td>';
    echo $row[3]." ".$row[4]." ".$row[5]." ".$row[6]." ".$row[7]." CEP:".$row[8];
    echo '</td></tr>';

    # Conta Corrente
    echo '<tr><th>';
    echo 'Conta Corrente';
    echo '</th><td>';
    echo $row[9]." / ".$row[10];
    echo '</td></tr>';

    echo '</table>';

    br();
    
    # Atenciosamente
    $p = new P('Atenciosamente','pDiaria');
    $p->show();
    br(4);
    
    # Assinatura
    $p = new P('____________________________________________________','pDiariaAssinatura');
    $p->show();
    $p = new P('Gerente','pDiariaAssinatura');
    $p->show();

    $page->terminaPagina();
}
?>