<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# nome das lotações
$lotacaoOrigem = "Gerência de Recursos Humanos e Pagamento - DPAF/GRH";
$lotacaoDestino = "Diretoria de Planejamento, Administração e Finanças - DPAF";
$lotacaoCi = 'FENORTE/DPAF/GRHPAG';

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

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
    $assunto = $dados[2];
    $valor = $dados[3];        

    ## Monta o Relatório 
    # Menu
    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->set_botaoVoltar(NULL);
    $menuRelatorio->show();
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cabeçalho do Relatório (com o logotipo)
    $relatorio = new Relatorio();
    $relatorio->exibeCabecalho(); 

    $valor = str_replace('.','',$valor);            // retira o ponto do milhar p/não dar problema na rotina de extenso
    $valor = str_replace(',','.',$valor);           // passa a vírgula dos centavos para ponto (padrão americano)
    $extenso = moedaExtenso($valor);                // pega o extenso
    $valor = number_format($valor, 2, '.', ',');    // passa para o formato de moeda (padrão americano)
    $valor = str_replace('.','*',$valor);           // passa o ponto (dos centavos) para *
    $valor = str_replace(',','.',$valor);           // passa a vírgula (do milhar) para ponto (do milhar)
    $valor = str_replace('*',',',$valor);           // passa o * para vírgula dos centavos

    # Pega os dados do servidor
    $select = 'SELECT tbservidor.idFuncional,
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
             FROM tbservidor
             JOIN tbpessoa on (tbservidor.idPessoa = tbpessoa.idPessoa)
             JOIN tbdocumentacao on (tbpessoa.idPessoa = tbdocumentacao.idpessoa)
        WHERE tbservidor.idServidor = '.$idServidorPesquisado;
    
    $row = $pessoal->select($select,FALSE); 
    
    br(2);
    
    # Assunto
    p($assunto,'pDiaria');
    
    # CI
    p('CI '.$lotacaoCi.' nº '.$ci,'pDiaria');
    
    # Data
    p('Campos dos Goytacazes,'.dataExtenso($data),'pDiariaData');
    br(2);
    
    # Origem
    p('De: '.$lotacaoOrigem,'pDiaria');
    br();
    
    # Destino
    p('Para: '.$lotacaoDestino,'pDiaria');
    br(2);
    
    # Texto
    p('Encaminhamos o presente Processo referente a diária(s), no valor total de R$ '.$valor.' ('.$extenso.'), do servidor(a) abaixo relacionado(a), para as providências que fizerem necessárias.','pDiaria');
    br(3);
    
    # Tabela
    echo '<table id="tableDiaria">';
    echo '<col style="width:30%">';
    echo '<col style="width:70%">';
   
    # Matrícula e Nome
    echo '<tr><th>';
    echo 'IdFuncional';
    echo '</th><td>';
    echo $row[0];    
    echo '</td></tr>';

    echo '<tr><th>';
    echo 'Servidor(a)';
    echo '</th><td>';
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
    p('Atenciosamente','pDiaria');
    br(4);
    
    # Assinatura
    p('____________________________________________________','pDiariaAssinatura');
    p('Gerente','pDiariaAssinatura');

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
}