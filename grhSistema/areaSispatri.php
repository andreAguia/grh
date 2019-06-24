<?php
/**
 * Área de Licença Prêmio
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){   
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase');
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Formação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros    
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao','Todos'));
    
    # Joga os parâmetros par as sessions   
    set_session('parametroLotacao',$parametroLotacao);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    $grid = new Grid();
    $grid->abreColuna(12);
            
################################################################
    
    switch ($fase){
        case "" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar","grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar,"left");
            
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/sispatriLotacao.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel,"right");
            
            $menu1->show();
    
            # Titulo
            titulo("Área do Sispatri");
            br();
            
        ##############
            
            # Pega os dados
            $select ='SELECT tbservidor.idfuncional,
                             tbpessoa.nome,
                             tbservidor.idServidor,
                             tbservidor.idServidor
                        FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                             JOIN tbpessoa USING (idPessoa)
                                             JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
            # lotacao
            if($parametroLotacao <> "Todos"){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")'; 
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")';
                }
            }

            $select .= ' ORDER BY 1';
                        
        #########
            
            $numSispatri = $pessoal->count($select);
            
            $grid = new Grid();

            ## Coluna do menu            
            $grid->abreColuna(12,3);

                # Número de Servidores
                $painel = new Callout();
                $painel->abre();
                    if($parametroLotacao == "Todos"){
                        $numServidores = $pessoal->get_numServidoresAtivos();
                        #p($numServidores,"estatisticaNumero");
                        p("$numServidores Servidores Ativos","estatisticaTexto");
                    }else{
                        $numServidores = $pessoal->get_numServidoresAtivos($parametroLotacao);
                        #p($numServidores,"estatisticaNumero");
                        p("$numServidores Servidor(es) Ativo(s) <br/> Nesta Lotação","estatisticaTexto");
                    }
                    hr();
                    
                    switch ($numSispatri){
                        
                        case 0 :
                            p("Todos Fizeram o Sispatri !!<br/>Fantástico !!!","estatisticaTexto");
                            break;
                        
                        case 1 :
                            p("Somente 1 Servidor <br/> Não Fez o Sispatri","estatisticaTexto");
                            break;
                        
                        case ($numSispatri == $numServidores) :
                            p("Ninguém Fez o Sispatri !!!<br/> Que Loucura !!","estatisticaTexto");
                            break;
                        
                        case ($numSispatri>1) :
                            p("$numSispatri Servidores <br/> Não Fizeram o Sispatri","estatisticaTexto");
                            break;
                    }
                    
                    hr();

                    # Chart
                    $chart = new Chart("Pie",array(array("Fez Sispatri",$numServidores-$numSispatri),array("Não Fez Sispatri",$numSispatri)));
                    $chart->set_idDiv("sispatri");
                    $chart->set_legend(FALSE);
                    $chart->set_tamanho($largura = 250,$altura = 250);
                    $chart->show();
                
                $painel->fecha();
            
            $grid->fechaColuna();

        ################################################################

            # Coluna de Conteúdo
            $grid->abreColuna(12,9);  
            
            # Formulário de Pesquisa
            $form = new Form('?');
            
            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                          FROM tblotacao
                                         WHERE ativo) UNION (SELECT distinct DIR, DIR
                                          FROM tblotacao
                                         WHERE ativo)
                                      ORDER BY 2');
            array_unshift($result,array('*','-- Todos --'));

            $controle = new Input('parametroLotacao','combo','Lotação:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

        ##############
           
            # Pega os dados
            $select ='SELECT tbservidor.idfuncional,
                             tbpessoa.nome,
                             tbservidor.idServidor,
                             tbservidor.idServidor
                        FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                             JOIN tbpessoa USING (idPessoa)
                                             JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
            # lotacao
            if($parametroLotacao <> "Todos"){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")'; 
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")';
                }
            }

            $select .= ' ORDER BY 1';
                        
            #echo $select;

            $result = $pessoal->select($select);

            $tabela = new Tabela();   
            $tabela->set_titulo('Relação de Servidores que Ainda NÃO Fizeram o Sispatri');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(array("IdFuncional","Nome","Cargo","Lotação"));
            $tabela->set_conteudo($result);
            $tabela->set_align(array("center","left","left","left"));
            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao"));
            
            #$tabela->set_idCampo('idServidor');
            #$tabela->set_editar('?fase=editaServidor');
            $tabela->show();
            
            # Fecha o grid
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
        
        case "editaServidor" :
            br(8);
            aguarde();
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);
            
            # Informa a origem
            set_session('origem','areaFormacao');
            
            # Carrega a página específica
            loadPage('servidorFormacao.php');
            break; 
        
    ################################################################
        
        # Relatório
        case "relatorio" :
                
                $subTitulo = NULL;
                
                # Pega os dados
                $select ='SELECT tbservidor.idfuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbescolaridade.escolaridade,
                          idFormacao
                     FROM tbformacao JOIN tbpessoa USING (idPessoa)
                                     JOIN tbservidor USING (idPessoa)
                                     JOIN tbescolaridade USING (idEscolaridade)
                                     LEFT JOIN tbcargo USING (idCargo)
                                     LEFT JOIN tbtipocargo USING (idTipoCargo)
                     WHERE situacao = 1
                       AND idPerfil = 1';

                if($parametroNivel <> "Todos"){
                    $select .= ' AND tbtipocargo.nivel = "'.$parametroNivel.'"';
                    $subTitulo .= 'Cargo Efetivo de Nível '.$parametroNivel.'<br/>';
                }

                if($parametroEscolaridade <> "*"){
                    $select .= ' AND tbformacao.idEscolaridade = '.$parametroEscolaridade;
                    $subTitulo .= 'Curso de Nível '.$pessoal->get_escolaridade($parametroEscolaridade).'<br/>';
                }

                if(!vazio($parametroCurso)){
                    $select .= ' AND tbformacao.habilitacao like "%'.$parametroCurso.'%"';
                    $subTitulo .= 'Filtro : '.$parametroCurso.'<br/>';
                }

                $select .= ' ORDER BY tbpessoa.nome, tbformacao.anoTerm';
                
                # Monta o Relatório
                $relatorio = new Relatorio();
                $relatorio->set_titulo('Relatório Geral de Formação Servidores');
                
                if(!is_null($subTitulo)){
                    $relatorio->set_subtitulo($subTitulo);
                }
                
                $result = $pessoal->select($select);
                
                $relatorio->set_label(array("IdFuncional","Nome","Cargo","Lotação","Escolaridade","Curso"));
                $relatorio->set_conteudo($result);
                $relatorio->set_align(array("center","left","left","left","left","left"));
                $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal",NULL,"Formacao"));
                $relatorio->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao",NULL,"get_curso"));
                $relatorio->show();
                break;
                
    }
            
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


