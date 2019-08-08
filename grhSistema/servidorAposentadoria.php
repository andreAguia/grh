<?php
/**
 * Cadastro de Tempo de Serviço
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $aposentadoria = new Aposentadoria();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
##############################################################################################################################################
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Verifica a data de saída
    $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
    $dtHoje = date("Y-m-d");                                      # Data de hoje
    $dtFinal = NULL;

    # Dados para o controle
    $disabled = FALSE;
    $autofocus = TRUE;

    # Analisa a data
    if(!vazio($dtSaida)){           // Se tem saída é a saída
        $dtFinal = date_to_bd($dtSaida);
        $disabled = TRUE;
        $autofocus = FALSE;
    }else{                          // Não tem saída então é hoje
        $dtFinal = $dtHoje;         
    }

    # Finalmente define o valor
    $parametro = $dtFinal;

    # Cria um menu
    $menu = new MenuBar();

    # Botão voltar
    $linkBotaoVoltar = new Button('Voltar','servidorMenu.php');
    $linkBotaoVoltar->set_title('Volta para a página anterior');
    $linkBotaoVoltar->set_accessKey('V');
    $menu->add_link($linkBotaoVoltar,"left");

    $imagem = new Imagem(PASTA_FIGURAS.'ajuda.png',NULL,15,15);
    $botaoHelp = new Button();
    $botaoHelp->set_imagem($imagem);
    $botaoHelp->set_title("Ajuda");
    $botaoHelp->set_url("https://docs.google.com/document/d/e/2PACX-1vSH4_OkFekLul3KY6AlTHP0WjDblvsQXdX1uA319UV4REs3d9YklhQJqSFoL_yrHfYEaSmX94RtQ47Q/pub");
    $botaoHelp->set_target("_blank");            
    #$menu->add_link($botaoHelp,"right");

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Tempo de Serviço Averbado");
    $botaoRel->set_url("../grhRelatorios/servidorAverbacao.php?data=$parametro");
    $botaoRel->set_target("_blank");
    #$menu->add_link($botaoRel,"right");

    $menu->show();

    # Exibe os dados do servidor
    get_DadosServidor($idServidorPesquisado);

##############################################################################################################################################
#   Pega os Valores
##############################################################################################################################################
    
    # Gênero do servidor
    $sexo = $pessoal->get_sexo($idServidorPesquisado);
    
    # Idade do Servidor
    $idade = $pessoal->get_idade($idServidorPesquisado);
    
    # Tempo de Serviço
    $uenf = $pessoal->get_tempoServicoUenf($idServidorPesquisado,$parametro);
    $publica = $pessoal->get_totalAverbadoPublico($idServidorPesquisado);
    $privada = $pessoal->get_totalAverbadoPrivado($idServidorPesquisado);
    $totalTempo = $uenf + $publica + $privada;
    
    # Pega o Tempo de Serviço da aposentadoria Integral
    switch ($sexo){
        case "Masculino" :
            $diasAposentadoria = $intra->get_variavel("diasAposentadoriaMasculino");
            break;
        case "Feminino" :
            $diasAposentadoria = $intra->get_variavel("diasAposentadoriaFeminino");
            break;
    }
    
    # Idade da Aposentadoria Integral
    switch ($sexo){
        case "Masculino" :
            $anosAposentadoria = $intra->get_variavel("idadeAposentadoriaMasculino");
            break;
        case "Feminino" :
            $anosAposentadoria = $intra->get_variavel("idadeAposentadoriaFeminino");
            break;
    }
    
##############################################################################################################################################
#   Tempo de Serviço
##############################################################################################################################################
    
    $painel = new Callout();
    $painel->abre();
    
    titulo("Tempo de Serviço");
    br();
    
    # Limita a tela
    $grid = new Grid();
    
    # Tempo público e privado
    $grid->abreColuna(4);
    
    # Monta o array
    $dados1 = array(
            array("UENF ",$uenf),
            array("Empresa Pública",$publica),
            array("Empresa Privada",$privada),
            array("Total",$totalTempo." dias<br/>(".dias_to_diasMesAno($totalTempo).")")
    );    

    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_titulo('Tempo de Serviço');
    $tabela->set_conteudo($dados1);
    $tabela->set_label(array("Descrição","Dias"));
    $tabela->set_align(array("left","center"));
    $tabela->set_totalRegistro(FALSE);
    $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                            'valor' => "Total",
                                            'operador' => '=',
                                            'id' => 'totalTempo')));

    $tabela->show();            
    $grid->fechaColuna();
    
#############################3

    # Ocorrências
    $grid->abreColuna(4);
    
    # Monta o select
    $reducao = "SELECT tbtipolicenca.nome as tipo,
                       SUM(numDias) as dias
                  FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                 WHERE idServidor = $idServidorPesquisado
                   AND tbtipolicenca.tempoServico IS TRUE
              GROUP BY tbtipolicenca.nome";

    $dados2 = $pessoal->select($reducao);

    # Somatório
    $totalOcorrencias = array_sum(array_column($dados2, 'dias'));

    # Adiciona na tabela
    if($totalOcorrencias == 0){
        array_push($dados2,array("Sem Ocorrências","---"));
    }else{
        array_push($dados2,array("Total",$totalOcorrencias));
    }

    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_titulo('Ocorrências');
    $tabela->set_conteudo($dados2);
    $tabela->set_label(array("Descrição","Dias"));
    $tabela->set_align(array("left","center"));
    $tabela->set_totalRegistro(FALSE);
    $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                                   'valor' => "Total",
                                                   'operador' => '=',
                                                   'id' => 'totalTempo')
        ));
    
    $tabela->show();            
    $grid->fechaColuna();

#############################3

    # Total do Tempo
    $grid->abreColuna(4); 
    
    # Calcula o tempo de serviço geral
    $totalTempoGeral = $totalTempo - $totalOcorrencias;

    # Dias que faltam
    $faltam = $diasAposentadoria - $totalTempoGeral;

    if($faltam < 0){
        $texto = "Dias Sobrando";
    }else{
        $texto = "Dias Faltando";
    }

    # Monta o array
    $dados3 = array(
              array("Tempo de Serviço ",$totalTempo),
              array("Ocorrências","($totalOcorrencias)"),
              array("Total",$totalTempoGeral),
              array("Dias para aposentadoria",$diasAposentadoria),
              array($texto,$faltam." dias<br/>(".dias_to_diasMesAno($faltam).")")
    );

    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_titulo('Resumo Geral');
    $tabela->set_conteudo($dados3);
    $tabela->set_label(array("Descrição","Dias"));
    $tabela->set_align(array("left","center"));
    $tabela->set_totalRegistro(FALSE);
    $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'totalTempo'),
                                            array('coluna' => 0,
                                                'valor' => "Ocorrências",
                                                'operador' => '=',
                                                'id' => 'ocorrencia'),
                                             array('coluna' => 0,
                                                   'valor' => "Dias Sobrando",
                                                   'operador' => '=',
                                                   'id' => 'diasSobrando'),
                                             array('coluna' => 0,
                                                   'valor' => "Dias Faltando",
                                                   'operador' => '=',
                                                   'id' => 'diasFaltando')));
    $tabela->show();            
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $painel->fecha();
    
##############################################################################################################################################
#   Previsão de Aposentadoria
##############################################################################################################################################
    
    $grid1 = new Grid();
    $grid1->abreColuna(4);
    
    # Monta o array
    $dados1 = array(
              array("Idade do Servidor ",$idade),
              array("Tempo de Serviço ",$totalTempo));
    
    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_titulo('Integral');
    $tabela->set_conteudo($dados1);
    $tabela->set_label(array("Descrição","Valor"));
    $tabela->set_align(array("left","center"));
    $tabela->set_totalRegistro(FALSE);
    $tabela->show();            
    
    $grid1->fechaColuna();
    $grid1->abreColuna(4);
    
    $grid1->fechaColuna();
    $grid1->abreColuna(4);
    
    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}