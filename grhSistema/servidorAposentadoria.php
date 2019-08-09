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
    
    $linkBotaoHistorico = new Button("Tempo de Serviço");
    $linkBotaoHistorico->set_title('Exibe o tempo de Serviço desse Servidor');    
    $linkBotaoHistorico->set_onClick("abreFechaDivId('divTempoServicoAposentadoria');");
    $linkBotaoHistorico->set_class('success button');
    $menu->add_link($linkBotaoHistorico,"right");
    
    $linkRegras = new Button("Regras");
    $linkRegras->set_title('Exibe as regras da aposentadoria');
    $linkRegras->set_onClick("abreFechaDivId('divRegrasAposentadoria');");
    $linkRegras->set_class('success button');
    $menu->add_link($linkRegras,"right");

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
    
    # Aposentadoria Integral
    $dtAposentadoriaIntegralIdade = $aposentadoria->get_dataAposentadoriaIntegralIdade($idServidorPesquisado);
    $dtAposentadoriaIntegralTempo = $aposentadoria->get_dataAposentadoriaIntegralTempo($idServidorPesquisado);
    $dtAposentadoriaIntegral = $aposentadoria->get_dataAposentadoriaIntegral($idServidorPesquisado);
    
    # Aposentadoria Proporcional
    $totalPublico = $publica + $uenf;
    $regraTempoProporcionalDias = 3650;
    $dtAposentadoriaProporcional = $aposentadoria->get_dataAposentadoriaProporcional($idServidorPesquisado);
    $dtAposentadoriaProporcionalIdade = $aposentadoria->get_dataProporcionalIdade($idServidorPesquisado);
    $dtAposentadoriaProporcionalTempo = $aposentadoria->get_dataProporcionalTempo($idServidorPesquisado);
    
    # Aposentadoria Compulsória
    $dtAposentadoriaCompulsoria = $aposentadoria->get_dataAposentadoriaCompulsoria($idServidorPesquisado);
    $idadeAposentadoriaCompulsoria = $intra->get_variavel("idadeAposentadoriaCompulsoria");
    
    # Pega o Tempo de Serviço da aposentadoria integral
    switch ($sexo){
        case "Masculino" :
            $diasAposentadoriaIntegral = $intra->get_variavel("diasAposentadoriaMasculino");
            break;
        case "Feminino" :
            $diasAposentadoriaIntegral = $intra->get_variavel("diasAposentadoriaFeminino");
            break;
    }
    
    # Idade da Aposentadoria Integral e proporcional
    switch ($sexo){
        case "Masculino" :
            $anosAposentadoria = $intra->get_variavel("idadeAposentadoriaMasculino");
            $idadeProporcional = 65;
            break;
        case "Feminino" :
            $anosAposentadoria = $intra->get_variavel("idadeAposentadoriaFeminino");
            $idadeProporcional = 60;
            break;
    }
    
##############################################################################################################################################
#   Regras
##############################################################################################################################################
    
    echo '<div id="divRegrasAposentadoria">';   
    $aposentadoria->exibeRegras();
    echo '</div>';
    
##############################################################################################################################################
#   Tempo de Serviço
##############################################################################################################################################
    
    echo '<div id="divTempoServicoAposentadoria">';   
    $painel = new Callout();
    $painel->abre();
    
    titulo("Tempo de Serviço");
    br();
    
    # Limita a tela
    $grid = new Grid();
    
    ################################################################
    
    $grid->abreColuna(12);
            
    # Verifica se tem Tempo averbado sobreposto

    # Pega as averbações desse servidor
    $selectSobreposicao = 'SELECT dtInicial, dtFinal, idAverbacao FROM tbaverbacao WHERE idServidor = '.$idServidorPesquisado.' ORDER BY dtInicial';
    $resultSobreposicao = $pessoal->select($selectSobreposicao);

    # Acrescenta o tempo de UENF
    $dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado));
    $resultSobreposicao[] = array($dtAdmissao,$dtFinal,NULL);

    # Inicia a variável que informa se tem sobreposicao
    $sobreposicao = FALSE;

    # Inicia o array que guarda os períodos problemáticos
    $idsProblemáticos[] = NULL;

    # Percorre os registros
    foreach($resultSobreposicao as $periodo){
        $dtInicial1 = date_to_php($periodo[0]);
        $dtFinal1 = date_to_php($periodo[1]);
        $idAverbado1 = $periodo[2];

        # Percorre a mesma listagem novamente
        foreach($resultSobreposicao as $periodoVerificado){

            $dtInicial2 = date_to_php($periodoVerificado[0]);
            $dtFinal2 = date_to_php($periodoVerificado[1]);
            $idAverbado2 = $periodoVerificado[2];

            # Evita que seja comparado com ele mesmo
            if($idAverbado1 <> $idAverbado2){
                if(verificaSobreposicao($dtInicial1,$dtFinal1,$dtInicial2,$dtFinal2)){
                    $sobreposicao = TRUE;
                    $idsProblemáticos[] = $idAverbado1;
                    $idsProblemáticos[] = $idAverbado2;
                }
            }
        }
    }

    if($sobreposicao){

        $painel = new Callout("alert","center");
        $painel->abre();
            echo "Atenção - Períodos com Sobreposição de Dias !!!";
            p("Verifique se não há dias sobrepostos entre os períodos averbados<br/>ou se algum período averbado ultrapassa a data de admissão na UENF: ".date_to_php($dtAdmissao),"center","f11");
        $painel->fecha();
    }
    
    $grid->fechaColuna();

    #############################################################
    
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
    $faltam = $diasAposentadoriaIntegral - $totalTempoGeral;

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
              array("Dias para aposentadoria",$diasAposentadoriaIntegral),
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
    echo '</div>';
    
##############################################################################################################################################
#   Previsão de Aposentadoria
##############################################################################################################################################
    
    $grid1 = new Grid();
    $grid1->abreColuna(4);
    
    $painel = new Callout();
    $painel->abre();
    
    # Aposentadoria Integral
    
    # Monta o array
    $dados1 = array(
              array("Idade",$anosAposentadoria,$idade),
              array("Tempo de Serviço",$diasAposentadoriaIntegral,$totalTempo));
    
    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_titulo('Aposentadoria Integral');
    $tabela->set_conteudo($dados1);
    $tabela->set_label(array("Descrição","Regra","Valor"));
    $tabela->set_align(array("left"));
    $tabela->set_totalRegistro(FALSE);
    $tabela->show();
    
    # Análise por idade
    if($anosAposentadoria > $idade){
        p("O servidor ainda não alcançou os <b>$anosAposentadoria</b> anos de idade de para solicitar aposentadoria integral. Somente em $dtAposentadoriaIntegralIdade.","f14");
    }else{
        p("O servidor já alcançou a idade para solicitar aposentadoria integral.","f14");
    }
    
    # Análise por Tempo de Serviço
    if($diasAposentadoriaIntegral > $totalTempoGeral){
        p("Ainda faltam <b>$faltam</b> dias para o servidor alcançar os <b>$diasAposentadoriaIntegral</b> dias de serviço necessários para solicitar a aposentadoria integral. Somente em $dtAposentadoriaIntegralTempo.","f14");
    }else{
        p("O servidor já alcançou os <b>$diasAposentadoriaIntegral</b> dias de tempo de serviço para solicitar aposentadoria integral.","f14");
    }
    
    if($sobreposicao){

        $painel = new Callout("alert","center");
        $painel->abre();
            echo "Atenção - Períodos com Sobreposição de Dias !!!";
            p("Verifique se não há dias sobrepostos entre os períodos averbados<br/>ou se algum período averbado ultrapassa a data de admissão na UENF: ".date_to_php($dtAdmissao),"center","f11");
        $painel->fecha();
    }
    
    $painel->fecha();    
    $grid1->fechaColuna();
    
    #############################################
    
    # Aposentadoria Proporcional
    $grid1->abreColuna(4);
    
    $painel = new Callout();
    $painel->abre();
    
    # Monta o array
    $dados1 = array(
              array("Idade",$idadeProporcional,$idade),
              array("Tempo de Serviço Público",$regraTempoProporcionalDias,$totalPublico));
    
    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_titulo('Aposentadoria Proporcional');
    $tabela->set_conteudo($dados1);
    $tabela->set_label(array("Descrição","Regra","Valor"));
    $tabela->set_align(array("left"));
    $tabela->set_totalRegistro(FALSE);
    $tabela->show();
    
    # Dias que faltam
    $faltamProporcional = $regraTempoProporcionalDias - $totalPublico;
        
    # Análise por idade
    if($idadeProporcional > $idade){
        p("O servidor ainda não alcançou os <b>$idadeProporcional</b> anos de idade de para solicitar aposentadoria proporcional. Somente em $dtAposentadoriaProporcionalIdade.","f14");
    }else{
        p("O servidor já alcançou a idade para solicitar aposentadoria proporcional.","f14");
    }
    
    # Análise por Tempo de Serviço
    if($regraTempoProporcionalDias > $totalPublico){
        p("Ainda faltam <b>$faltamProporcional</b> dias para o servidor alcançar os <b>$regraTempoProporcionalDias</b> dias de serviço necessários para solicitar a aposentadoria proporcional. Somente em $dtAposentadoriaProporcionalTempo.","f14");
    }else{
        p("O servidor já alcançou os <b>$regraTempoProporcionalDias</b> dias de tempo serviço público para solicitar aposentadoria proporcional.","f14");
    }
    
    $painel->fecha();
    $grid1->fechaColuna();
    
    #############################################
    
    # Aposentadoria Compulsória
    $grid1->abreColuna(4);
    
    $painel = new Callout();
    $painel->abre();
    
    # Monta o array
    $dados1 = array(
              array("Idade",$idadeAposentadoriaCompulsoria,$idade));
    
    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_titulo('Aposentadoria Compulsória');
    $tabela->set_conteudo($dados1);
    $tabela->set_label(array("Descrição","Regra","Valor"));
    $tabela->set_align(array("left"));
    $tabela->set_totalRegistro(FALSE);
    $tabela->show();
    
    # Análise por idade
    if(75 > $idade){
        p("O servidor ainda não alcançou os <b>$idadeAposentadoriaCompulsoria</b> anos de idade de para a aposentadoria compulsória. Somente em $dtAposentadoriaCompulsoria.","f14");
    }else{
        p("O servidor já alcançou a idade para a aposentadoria compulsória.","f14");
    }
    
    $painel->fecha();
    $grid1->fechaColuna();
    
    # Conclusão
    
    $grid1->abreColuna(4);
    
        if(jaPassou($dtAposentadoriaIntegral)){
            callout("Desde $dtAposentadoriaIntegral o servidor já pode solicitar Aposentadoria Integral!","success");
        }else{
            callout("Aposentadoria Integral somente em: $dtAposentadoriaIntegral.","warning");
        }
    
    $grid1->fechaColuna();
    $grid1->abreColuna(4);
    
        if(jaPassou($dtAposentadoriaProporcional)){
            callout("Desde $dtAposentadoriaProporcional o servidor já pode solicitar Aposentadoria Proporcional!","success");
        }else{
            callout("Aposentadoria Proporcional somente em: $dtAposentadoriaProporcional.","warning");
        }
    
    $grid1->fechaColuna();
    $grid1->abreColuna(4);
    
        if(jaPassou($dtAposentadoriaCompulsoria)){
            callout("Desde $dtAposentadoriaCompulsoria o servidor terá que se aposentar compulsoriamente!","success");
        }else{
            callout("Aposentadoria Compulsória somente em: $dtAposentadoriaCompulsoria.","warning");
        }
   
    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}