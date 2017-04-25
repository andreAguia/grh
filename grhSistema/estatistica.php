<?php
/**
 * Estatística
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase');
    $diretoria = get('diretoria');
    $grafico = get('grafico');
    
    # Começa uma nova página
    $page = new Page();
    $page->set_jscript('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);
    
    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $linkVoltar = new Link("Voltar","grh.php");
    $linkVoltar->set_class('button');
    $linkVoltar->set_title('Voltar para página anterior');
    $linkVoltar->set_accessKey('V');
    $menu1->add_link($linkVoltar,"left");

    # Por Perfil
    $linkRel = new Link("por Perfil","?");
    $linkRel->set_class('button');
    $linkRel->set_title('Estatística por Perfil');
    #$linkRel->set_accessKey('R');
    $menu1->add_link($linkRel,"right");
    
    # Por Lotação
    $linkRel = new Link("por Lotação","?fase=lotacao");
    $linkRel->set_class('button');
    $linkRel->set_title('Estatística por Lotação');
    #$linkRel->set_accessKey('R');
    $menu1->add_link($linkRel,"right");
    
    # Por CArgo
    $linkRel = new Link("por Cargo","?fase=cargo");
    $linkRel->set_class('button');
    $linkRel->set_title('Estatística por Cargo');
    #$linkRel->set_accessKey('R');
    $menu1->add_link($linkRel,"right");

    $menu1->show();
    
    # Dados do gráfico
    $largura = 800;
    $altura = 400;
    
    ################################################################
    
    switch ($fase)
    {
        case "":
            titulo("Servidores por Perfil");
            
            $grid2 = new Grid();
            $grid2->abreColuna(4);
            
            # Pega os dados
            $selectGrafico = 'SELECT tbperfil.nome, count(tbservidor.matricula) 
                                FROM tbservidor LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbperfil.nome
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            $numServidores = $pessoal->count($selectGrafico);
            
            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Perfil","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(8);
            
            $chart = new Chart("Pie",$servidores);
            $chart->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            hr();
            break;
            
            #########################################
            
            case "lotacao":
            $grid2 = new Grid();
            $grid2->abreColuna(12);
            
            titulo("Servidores por Lotação");
            
            # Pega os dados
            $selectGrafico = 'SELECT tblotacao.dir, count(tbservidor.matricula)
                                FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                 AND situacao = 1
                                 AND ativo
                            GROUP BY tblotacao.dir
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            $numServidores = $pessoal->count($selectGrafico);
            
            # Cria um menu
            $menu2 = new MenuBar();
            
            $botao = new Button("Todos","?fase=lotacao");
            $botao->set_class('button secondary');
            $botao->set_title("Todos");
            if(is_null($diretoria)){
                   $botao->set_disabled(TRUE);
                }
            $menu2->add_link($botao,"right");
                
            foreach ($servidores as $item){
                $botao = new Button($item[0],"?fase=lotacao&diretoria=".$item[0]);
                $botao->set_class('button secondary');
                $botao->set_title($item[0]);
                if($diretoria == $item[0]){
                   $botao->set_disabled(TRUE);
                }
                
                $menu2->add_link($botao,"right");
            }
            $menu2->show();
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            switch ($diretoria)
            {
                case "":
                    $grid2 = new Grid();
                    $grid2->abreColuna(4);

                    # Tabela
                    $tabela = new Tabela();
                    $tabela->set_conteudo($servidores);
                    $tabela->set_label(array("Diretoria","Servidores"));
                    $tabela->set_width(array(80,20));
                    $tabela->set_align(array("left","center"));    
                    $tabela->show();

                    $grid2->fechaColuna();
                    $grid2->abreColuna(8);

                    $chart = new Chart("Pie",$servidores);
                    #$chart->set_tresd(TRUE);
                    $chart->show();

                    $grid2->fechaColuna();
                    $grid2->fechaGrid();
                    break;
            
                ##############################################
            
                default: 
                    $grid2 = new Grid();
                    $grid2->abreColuna(12);
                    
                    # Cria um menu
                    $menu3 = new MenuBar();

                    # Grafico
                    $imagem1 = new Imagem(PASTA_FIGURAS.'pie.png',NULL,15,15);
                    $botaoGra = new Button();
                    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
                    $botaoGra->set_url("?fase=lotacao&diretoria=$diretoria");
                    $botaoGra->set_imagem($imagem1);
                    if($grafico == ""){
                        $botaoGra->set_disabled(TRUE);
                    }
                    $menu3->add_link($botaoGra,"right");
                    
                    # Organograma
                    $imagem3 = new Imagem(PASTA_FIGURAS.'organograma2.png',NULL,15,15);
                    $botaoOrg = new Button();                    
                    $botaoOrg->set_title("Exibe o Organograma da UENF");
                    $botaoOrg->set_imagem($imagem3);
                    $botaoOrg->set_url("?fase=lotacao&diretoria=$diretoria&grafico=org");
                    if($grafico == "org"){
                        $botaoOrg->set_disabled(TRUE);
                    }
                    $menu3->add_link($botaoOrg,"right");
                    
                    $menu3->show();
                    
                    $grid2->fechaColuna();
                    $grid2->fechaGrid();
                    
                    $grid2 = new Grid();
                    $grid2->abreColuna(4);

                    # Pega os dados
                    $selectGrafico2 = 'SELECT tblotacao.ger, count(tbservidor.matricula),tblotacao.nome
                                        FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                         AND situacao = 1
                                         AND ativo
                                         AND tblotacao.dir="'.$diretoria.'" 
                                    GROUP BY tblotacao.ger
                                    ORDER BY 2 DESC ';

                    $servidores2 = $pessoal->select($selectGrafico2);

                    # Tabela
                    $tabela = new Tabela();
                    $tabela->set_titulo($diretoria);
                    $tabela->set_conteudo($servidores2);
                    $tabela->set_label(array("Gerência","Servidores"));
                    $tabela->set_width(array(80,20));
                    $tabela->set_align(array("left","center"));
                    $tabela->show(); 

                    $grid2->fechaColuna();
                    $grid2->abreColuna(8);
                    
                    if($grafico == "org"){
                        $org = new OrganogramaUenf($diretoria);
                        $org->set_ignore("SECR");
                        $org->show();
                    }else{
                        $chart2 = new Chart("Pie",$servidores2);
                        $chart2->set_idDiv($item[0]);
                        #$chart2->set_pieHole(TRUE);
                        #$chart2->set_legend(FALSE);
                        $chart2->set_tamanho(700,400);
                        $chart2->show();
                    }

                    $grid2->fechaColuna();
                    $grid2->fechaGrid();
                    break;
            }
            break;
        
            #########################################
            
            case "cargo":
                titulo("Servidores por Cargo");
                $grid2 = new Grid();
                $grid2->abreColuna(4);

                # Pega os dados
                $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.matricula) 
                                    FROM tbservidor JOIN tbcargo USING (idCargo)
                                                    JOIN tbtipocargo USING (idTipoCargo)
                                   WHERE tbservidor.situacao = 1
                                GROUP BY tbtipocargo.cargo
                                ORDER BY 2 DESC ';

                $servidores = $pessoal->select($selectGrafico);
                $numServidores = $pessoal->count($selectGrafico);

                # Exemplo de tabela simples
                $tabela = new Tabela();
                $tabela->set_conteudo($servidores);
                $tabela->set_label(array("Cargo","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->show();

                $grid2->fechaColuna();
                $grid2->abreColuna(8);

                $chart = new Chart("Pie",$servidores);
                $chart->show();

                $grid2->fechaColuna();
                $grid2->fechaGrid();

                hr();
                break;
    }
    
    # Fecha o grid
    $grid1->fechaColuna();
    $grid1->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}