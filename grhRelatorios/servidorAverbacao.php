<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    $parametro = retiraAspas(get('data'));
    
    ######
    
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Tempo de Serviço');
    
    ####
    # Tempo de Serviço

    # Pega o sexo do servidor
    $sexo = $pessoal->get_sexo($idServidorPesquisado);

    # Tempo de Serviço
    $uenf = $pessoal->get_tempoServicoUenf($idServidorPesquisado,$parametro);
    $publica = $pessoal->get_totalAverbadoPublico($idServidorPesquisado);
    $privada = $pessoal->get_totalAverbadoPrivado($idServidorPesquisado);
    $totalAverbado = $publica + $privada;
    $totalTempo = $uenf + $totalAverbado;

    $dados1 = array(
              array("Tempo de Serviço na UENF ",$uenf),
              array("Tempo Averbado Empresa Pública",$publica),
              array("Tempo Averbado Empresa Privada",$privada),
              array("Total",$totalTempo." dias<br/>(".dias_to_diasMesAno($totalTempo).")")
    );
    
    ####
    # Ocorrências
    $reducao = "SELECT tbtipolicenca.nome as tipo,
                      SUM(numDias) as dias
                 FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                WHERE idServidor = $idServidorPesquisado
                  AND tbtipolicenca.tempoServico IS TRUE
               GROUP BY tbtipolicenca.nome";

    $dados2 = $pessoal->select($reducao);

    # Somatório
    $totalOcorrencias = array_sum(array_column($dados2, 'dias') );

    ####
    # Resumo

    # Define a idade que dá direito para cada gênero
    switch ($sexo){
        case "Masculino" :
            $diasAposentadoria = 12775;
            break;
        case "Feminino" :
            $diasAposentadoria = 10950;
            break;
    }

    # Calcula o tempo de serviço geral
    $totalTempoGeral = $totalTempo - $totalOcorrencias;

    # Dias que faltam
    $faltam = $diasAposentadoria - $totalTempoGeral;

    if($faltam < 0){
        $texto = "Dias Sobrando";
    }else{
        $texto = "Dias Faltando";
    }

    $dados3 = array(
              array("Tempo de Serviço ",$totalTempo),
              array("Ocorrências","($totalOcorrencias)"),
              array("Total",$totalTempoGeral),
              array("Dias para aposentadoria",$diasAposentadoria),
              array($texto,$faltam." dias<br/>(".dias_to_diasMesAno($faltam).")")
    );

    ####
    # Aposentadoria
    $dtNascimento = $pessoal->get_dataNascimento($idServidorPesquisado);
    $idade = $pessoal->get_idade($idServidorPesquisado);
    $aposentadoria = $pessoal->get_dataAposentadoria($idServidorPesquisado);
    $Compulsoria = $pessoal->get_dataCompulsoria($idServidorPesquisado);

    # Define a idade que dá direito para cada gênero
    switch ($sexo){
        case "Masculino" :
            $ii = 60;
            break;
        case "Feminino" :
            $ii = 55;
            break;
    }

    $dados4 = array(
            array("Idade do Servidor ",$idade),
            array("Data de Nascimento ",$dtNascimento),
            array("Data com Direito a Aposentadoria ($ii anos)",$aposentadoria),
            array("Data da Compulsória (75 anos)",$Compulsoria)
    );
    
    ####
    # Análise
    br();
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    $painel = new Callout("secondary","center");
    $painel->abre();

        # Verifica se servidor é ativo
        $select2 = 'SELECT tbsituacao.idSituacao,
                          tbsituacao.situacao
                     FROM tbsituacao LEFT JOIN tbservidor ON (tbservidor.situacao = tbsituacao.idsituacao)
                    WHERE idServidor = '.$idServidorPesquisado;

        $situacao = $pessoal->select($select2,FALSE);

        if($situacao[0] <> 1){
            echo "Servidor $situacao[1] com $totalTempo dias registrados até a data de saída ($dtSaida)";
        }else{            
            # Análise por dia
            if($diasAposentadoria > $totalTempoGeral){
                echo "Ainda faltam <b>$faltam</b> dias para o servidor alcançar os <b>$diasAposentadoria</b> dias de serviço necessários para solicitar a aposentadoria.";
            }else{
                echo "O servidor já alcançou os <b>$diasAposentadoria</b> dias de serviço para solicitar aposentadoria.";
            }

            br();

            # Análise por idade
            if($ii > $idade){
                echo "O servidor ainda não alcançou os <b>$ii</b> anos de idade de para solicitar aposentadoria.";
            }else{
                echo "O servidor já alcançou a idade para solicitar aposentadoria.";
            }
        }

    $painel->fecha();
    $grid1->fechaColuna();
    
    #############################################################
    # Tempo de Serviço
    
    $grid1->abreColuna(6);

    # Monta a tabela
    $tabela = new Relatorio();
    $tabela->set_cabecalhoRelatorio(FALSE);
    $tabela->set_menuRelatorio(FALSE);
    $tabela->set_subTotal(FALSE);
    $tabela->set_totalRegistro(FALSE);
    $tabela->set_dataImpressao(FALSE);
    $tabela->set_subtitulo('Tempo de Serviço');
    $tabela->set_conteudo($dados1);
    $tabela->set_label(array("Descrição","Dias"));
    $tabela->set_align(array("left","center"));
    $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                            'valor' => "Total",
                                            'operador' => '=',
                                            'id' => 'totalTempo')));

    $tabela->show();            
    $grid1->fechaColuna();

    #############################################################
    # Ocorrências que reduzem do Tempo de Serviço

    $grid1->abreColuna(6);

    # Adiciona na tabela
    if($totalOcorrencias == 0){
        array_push($dados2,array("Sem Ocorrências","---"));
    }else{
        array_push($dados2,array("Total",$totalOcorrencias));
    }

    # Monta a tabela
    $tabela = new Relatorio();
    $tabela->set_cabecalhoRelatorio(FALSE);
    $tabela->set_menuRelatorio(FALSE);
    $tabela->set_subTotal(FALSE);
    $tabela->set_totalRegistro(FALSE);
    $tabela->set_dataImpressao(FALSE);
    $tabela->set_subtitulo('Ocorrências');
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
    $grid1->fechaColuna();
    br();
    #############################################################
    # Resumo

    $grid1->abreColuna(6);
    br();
    
    # Monta a tabela
    $tabela = new Relatorio();
    $tabela->set_cabecalhoRelatorio(FALSE);
    $tabela->set_menuRelatorio(FALSE);
    $tabela->set_subTotal(FALSE);
    $tabela->set_totalRegistro(FALSE);
    $tabela->set_dataImpressao(FALSE);
    $tabela->set_subtitulo('Resumo Geral');
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
    $grid1->fechaColuna();

    #############################################################
    # Aposentadoria

    $grid1->abreColuna(6);
    br();

    # Monta a tabela do resumo de tempo
    $tabela = new Relatorio();
    $tabela->set_cabecalhoRelatorio(FALSE);
    $tabela->set_menuRelatorio(FALSE);
    $tabela->set_subTotal(FALSE);
    $tabela->set_totalRegistro(FALSE);
    $tabela->set_dataImpressao(FALSE);
    $tabela->set_subtitulo('Idade para Aposentadoria');
    $tabela->set_conteudo($dados4);
    $tabela->set_label(array("Descrição","Valor"));
    $tabela->set_align(array("left","center"));
    $tabela->set_totalRegistro(FALSE);
    $tabela->show();

    $grid1->fechaColuna();
    $grid1->fechaGrid();            

    #############################################################

    br();
    $select = "SELECT dtInicial,
                        dtFinal,
                        dias,
                        empresa,
                        CASE empresaTipo
                           WHEN 1 THEN 'Pública'
                           WHEN 2 THEN 'Privada'
                        END,
                        CASE regime
                           WHEN 1 THEN 'Celetista'
                           WHEN 2 THEN 'Estatutário'
                        END,
                        cargo,
                        dtPublicacao,
                        processo,
                        idAverbacao
                   FROM tbaverbacao
                  WHERE idServidor = $idServidorPesquisado
               ORDER BY 1 desc";

    $result = $pessoal->select($select);
    #array_push($result,array(NULL,NULL,$publica + $privada,NULL,NULL,NULL,NULL,NULL,NULL));
    #array_push($result,array(NULL,NULL,$publica + $privada,NULL,NULL,NULL,NULL,NULL,NULL));

    $relatorio = new Relatorio();
    $relatorio->set_subtitulo('Tempo de Serviço Averbado');
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Data Inicial","Data Final","Dias","Empresa","Tipo","Regime","Cargo","Publicação","Processo"));
    #$relatorio->set_colunaSomatorio(2);
    #$relatorio->set_textoSomatorio("Total de Dias Averbados:");
    $relatorio->set_exibeSomatorioGeral(FALSE);
    $relatorio->set_align(array('center','center','center','left'));
    $relatorio->set_funcao(array("date_to_php","date_to_php",NULL,NULL,NULL,NULL,NULL,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Tempo de Serviço Averbado");
    $relatorio->show();
    
    #p('Total de Dias Averbados:'.$totalAverbado,'pRelatorioDataImpressao');

    $page->terminaPagina();
}