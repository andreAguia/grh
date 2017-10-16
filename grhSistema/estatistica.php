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
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou a área de estatística";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # Pega o ano
    $ano = post("ano",date("Y"));
    
    # Começa uma nova página
    $page = new Page();
    $page->set_jscript('<script type="text/javascript" src="'.PASTA_FUNCOES_GERAIS.'/loader.js"></script>');
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    br();
    
    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);
    
    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    if(is_null($fase)){
        $linkVoltar = new Link("Voltar","grh.php");
    }else{
         $linkVoltar = new Link("Voltar","?");
    }
    $linkVoltar->set_class('button');
    $linkVoltar->set_title('Voltar para página anterior');
    $linkVoltar->set_accessKey('V');
    $menu1->add_link($linkVoltar,"left");

    $menu1->show();
    
    # Dados do gráfico
    $largura = 800;
    $altura = 400;
    
    ################################################################
    
    switch ($fase)
    {   
        case "":
        case "geral":
            titulo("Estatística Geral");
            br();
                        
            # Tamanho da coluna do grid
            $x = 3;

            $grid = new Grid();
            
            ## Primeira Coluna            
            $grid->abreColuna(6,4,3);
            
                # Número de Servidores
                $painel = new Callout();
                $painel->set_title('Painel com tipo primary');
                $painel->abre();

                    $numServidores = $pessoal->get_numServidoresAtivos();
                    p($numServidores,"estatisticaNumero");
                    p("Servidores Ativos","estatisticaTexto");

                $painel->fecha(); 
                
                ###############################
            
                # Geral - Por Perfil
                $selectGrafico = 'SELECT tbperfil.nome, count(tbservidor.idServidor) as jj
                                    FROM tbservidor LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                   WHERE tbservidor.situacao = 1
                                GROUP BY tbperfil.nome
                                ORDER BY 2 DESC ';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                # Exemplo de tabela simples
                $tabela = new Tabela();
                $tabela->set_titulo("por Perfil");
                $tabela->set_conteudo($servidores);
                $tabela->set_label(array("Perfil","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->show();
                
                ###############################
                
                # Geral - Por Cargo
                $selectGrafico = 'SELECT tbtipocargo.tipo, count(tbservidor.idServidor) as jj
                                    FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                                                    LEFT JOIN tbtipocargo USING (idTipoCargo)
                                   WHERE situacao = 1
                                GROUP BY tbtipocargo.tipo
                                ORDER BY 2 DESC ';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                # Exemplo de tabela simples
                $tabela = new Tabela();
                $tabela->set_titulo("por Cargo");
                $tabela->set_conteudo($servidores);
                $tabela->set_label(array("Tipo do Cargo","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->set_linkTitulo("?fase=cargo");
                $tabela->set_linkTituloTitle("Exibe detalhes");
                $tabela->show();
            
            $grid->fechaColuna();
            
            ###############################
            
            ## Segunda Coluna            
            $grid->abreColuna(6,4,3);
            
                # Geral - Por Sexo
                $selectGrafico = 'SELECT tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                    FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                   WHERE situacao = 1
                                GROUP BY tbpessoa.sexo
                                ORDER BY 1';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));            

                # Tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($servidores);
                $tabela->set_titulo("por Sexo");
                $tabela->set_label(array("Sexo","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->set_linkTitulo("?fase=sexo");
                $tabela->set_linkTituloTitle("Exibe detalhes");
                $tabela->show();
                
            ###############################    
                
                # Geral - Por Nacionalidade
                $selectGrafico = 'SELECT tbnacionalidade.nacionalidade, count(tbservidor.idServidor) as jj
                                    FROM tbnacionalidade JOIN tbpessoa ON(tbnacionalidade.idnacionalidade = tbpessoa.nacionalidade)
                                                         JOIN tbservidor USING (idPessoa)
                                   WHERE situacao = 1
                                GROUP BY tbnacionalidade.nacionalidade
                                ORDER BY 1';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));            

                # Tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($servidores);
                $tabela->set_titulo("por Nacionalidade");
                $tabela->set_label(array("Nacionalidade","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->show();

            $grid->fechaColuna();
            
            ###############################
            
            ## Terceira Coluna 
            
            $grid->abreColuna(6,4,3);
            
                # Geral - Por Idade
                $selectGrafico = 'SELECT count(tbservidor.idServidor) as jj,
                                         TIMESTAMPDIFF(YEAR, tbpessoa.dtNasc, NOW()) AS idade
                                    FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                   WHERE situacao = 1
                                GROUP BY idade
                                ORDER BY 2';

                $servidores = $pessoal->select($selectGrafico);

                # Separa os arrays para analise estatística
                $idades = array();
                foreach ($servidores as $item){
                    $idades[] = $item[1];
                }

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));  

                # Dados da tabela
                $dados[] = array("Maior Idade",maiorValor($idades));
                $dados[] = array("Menor Idade",menorValor($idades));
                $dados[] = array("Idade Média",media_aritmetica($idades));
                
                # Tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($dados);
                $tabela->set_titulo("por Idade");
                $tabela->set_label(array("Descrição","Idade"));
                $tabela->set_width(array(50,50));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->set_linkTituloTitle("Exibe detalhes");
                $tabela->show();
                
            ###############################
                
                # Geral - Por Lotação
                $selectGrafico = 'SELECT tblotacao.dir, count(tbservidor.idServidor) as jj
                                    FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                     AND situacao = 1
                                     AND ativo
                                GROUP BY tblotacao.dir
                                ORDER BY 1';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));            

                # Tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($servidores);
                $tabela->set_titulo("por Lotação");
                $tabela->set_label(array("Diretoria","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->set_linkTitulo("?fase=lotacao");
                $tabela->set_linkTituloTitle("Exibe detalhes");
                $tabela->show();    
            
            $grid->fechaColuna();
            
            ###############################
            
            ## Quarta Coluna 
            
            $grid->abreColuna(6,4,3);
            
                # Geral - Por Cidade
                $selectGrafico = 'SELECT tbpessoa.cidade, count(tbservidor.idServidor) as jj
                                    FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                   WHERE situacao = 1
                                GROUP BY tbpessoa.cidade
                                ORDER BY 1';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));            

                # Tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($servidores);
                $tabela->set_titulo("por Cidade de Moradia");
                $tabela->set_label(array("Cidade","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->show();

            $grid->fechaColuna();            
            $grid->fechaGrid();
            
            hr();
            break;
        
####################################################################################################
        
        case "cargo":
            titulo("Estatística por Cargo Efetivo");
            br();
            
            $grid2 = new Grid();
            $grid2->abreColuna(3);
            
            # Número de Servidores
            $painel = new Callout();
            $painel->set_title('Painel com tipo primary');
            $painel->abre();

                $numServidores = $pessoal->get_numServidoresAtivos();
                p($numServidores,"estatisticaNumero");
                p("Servidores Ativos","estatisticaTexto");

            $painel->fecha(); 

            ###############################
            
            # Pega os dados
            $selectGrafico = 'SELECT tbtipocargo.tipo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                                                LEFT JOIN tbtipocargo USING (idTipoCargo)
                               WHERE situacao = 1
                            GROUP BY tbtipocargo.tipo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            
            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));
            
            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("por Cargo");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Tipo do Cargo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(9);
            
            $chart = new Chart("Pie",$servidores);
            $chart->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            $grid2 = new Grid();
            $grid2->abreColuna(6);

            # Adm/Tec
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                                 AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tbtipocargo.cargo
                            ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("Administrativos e Técnicos");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Cargo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            # Professores
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                                 AND tbtipocargo.tipo = "Professor"
                            GROUP BY tbtipocargo.cargo
                            ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("Professores");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Cargo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();            

            $grid2->fechaColuna();
            $grid2->fechaGrid();

             hr();
            ############################################################################################
            
            $grid2 = new Grid();
            
            $cargo = array("Profissional de Nível Superior","Profissional de Nível Médio","Profissional de Nível Fundamental","Profissional de Nível Elementar");
            
            foreach($cargo as $valor){
                $grid2->abreColuna(3);

                # Profissional de Nível Superior
                $selectGrafico = 'SELECT tbcargo.nome, count(tbservidor.idServidor) as jj
                                    FROM tbservidor JOIN tbcargo USING (idCargo)
                                                    JOIN tbtipocargo USING (idTipoCargo)
                                   WHERE tbservidor.situacao = 1
                                     AND tbtipocargo.cargo = "'.$valor.'"
                                GROUP BY tbcargo.nome
                                ORDER BY 2 DESC ';

                $servidores = $pessoal->select($selectGrafico);
                
                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                # Exemplo de tabela simples
                $tabela = new Tabela();
                $tabela->set_titulo($valor);
                $tabela->set_conteudo($servidores);
                $tabela->set_label(array("Cargo","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->show();

                $grid2->fechaColuna();
            }


            $grid2->fechaGrid();

            hr();
            break;
            
####################################################################################################
            
        case "sexo":
            titulo("Estatística por Sexo");
            br();
            
            $grid2 = new Grid();
            $grid2->abreColuna(3);
            
             # Número de Servidores
            $painel = new Callout();
            $painel->set_title('Painel com tipo primary');
            $painel->abre();

                $numServidores = $pessoal->get_numServidoresAtivos();
                p($numServidores,"estatisticaNumero");
                p("Servidores Ativos","estatisticaTexto");

            $painel->fecha(); 

            ###############################
            
            # Geral - Por Sexo
            $selectGrafico = 'SELECT tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                               WHERE situacao = 1
                            GROUP BY tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));            

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_titulo("por Sexo");
            $tabela->set_label(array("Sexo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(9);
            
            $chart = new Chart("Pie",$servidores);
            $chart->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            $grid2 = new Grid();
            $grid2->abreColuna(6);
            
            # Sexo por Lotação
            $selectGrafico = 'SELECT tblotacao.dir, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                     AND situacao = 1
                                     AND ativo
                            GROUP BY tblotacao.dir, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);
            
            # Novo array 
            $novoArray = array();
            
            # Valores anteriores
            $diretoriaAnterior = NULL;
            $sexoAnterior = NULL;
            $contagemAnterior = NULL;
            
            # Contador 
            $contador = 1;
            
            # Melhora a apresentação da tabela
            foreach ($servidores as $value) {
                # Carrega as variáveis de armazenamento para comparação 
                $diretoria = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];
                
                # Verifica se mudou de diretoria
                if($diretoria <> $diretoriaAnterior){
                    # O normal é ser diferente no contador 1. Significa que tem servidores dos 2 generos (msculino e feminino)
                    if($contador == 1){
                        $contador = 2;
                        
                        # passa os valores para as variaveis anteriores
                        $diretoriaAnterior = $diretoria;
                        $sexoAnterior = $sexo;
                        $contagemAnterior = $contagem;
                    }else{
                        # Se for diferente no 2 significa que só tem servidores de um único genero nessa diretoria
                        if($sexo == "feminino"){
                            array_push($novoArray,array($diretoriaAnterior,$contagemAnterior,0,$contagemAnterior+$contagem));
                        }else{
                            array_push($novoArray,array($diretoriaAnterior,0,$contagemAnterior,$contagemAnterior+$contagem));
                        }
                        
                        # passa os valores para as variaveis anteriores
                        $diretoriaAnterior = $diretoria;
                        $sexoAnterior = $sexo;
                        $contagemAnterior = $contagem;
                        $contador = 1;
                    }
                }else{
                    array_push($novoArray,array($diretoria,$contagemAnterior,$contagem,$contagemAnterior+$contagem));
                    $contador = 1;
                }
            }

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));            

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($novoArray);
            $tabela->set_titulo("por Diretoria");
            $tabela->set_label(array("Diretoria","Feminino","Masculino","Total"));
            $tabela->set_width(array(25,25,25,25));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(6);
            
            ##################################

            # Sexo por Cargo
            $selectGrafico = 'SELECT tbtipocargo.cargo, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbtipocargo.cargo, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);
            
            # Novo array 
            $novoArray2 = array();
            
            # Valores anteriores
            $cargoAnterior = NULL;
            $sexoAnterior = NULL;
            $contagemAnterior = NULL;
            
            # Contador 
            $contador = 1;
            
            # Melhora a apresentação da tabela
            foreach ($servidores as $value) {
                # Carrega as variáveis de armazenamento para comparação 
                $cargo = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];
                
                # Verifica se mudou de diretoria
                if($cargo <> $cargoAnterior){
                    # O normal é ser diferente no contador 1. Significa que tem servidores dos 2 generos (msculino e feminino)
                    if($contador == 1){
                        $contador = 2;
                        
                        # passa os valores para as variaveis anteriores
                        $cargoAnterior = $cargo;
                        $sexoAnterior = $sexo;
                        $contagemAnterior = $contagem;
                    }else{
                        # Se for diferente no 2 significa que só tem servidores de um único genero nesse cargo
                        if($sexo == "feminino"){
                            array_push($novoArray2,array($cargoAnterior,$contagemAnterior,0,$contagemAnterior+$contagem));
                        }else{
                            array_push($novoArray2,array($cargoAnterior,0,$contagemAnterior,$contagemAnterior+$contagem));
                        }
                        
                        # passa os valores para as variaveis anteriores
                        $cargoAnterior = $cargo;
                        $sexoAnterior = $sexo;
                        $contagemAnterior = $contagem;
                        $contador = 1;
                    }
                }else{
                    array_push($novoArray2,array($cargo,$contagemAnterior,$contagem,$contagemAnterior+$contagem));
                    $contador = 1;
                }
            }

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));            

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($novoArray2);
            $tabela->set_titulo("por Cargo");
            $tabela->set_label(array("Cargo","Feminino","Masculino","Total"));
            $tabela->set_width(array(55,15,15,15));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            hr();
            break;
            
####################################################################################################
            
        case "temporalCargo":
            titulo("Número de Servidores que Trabalharam na UENF em ".$ano);
            
            $grid2 = new Grid("center");
            $grid2->abreColuna(12);
            
            # Formulário do Ano
            $form = new Form('?fase=temporalCargo');
            
            # Preenche o array
            for($i = 2000;$i<=date("Y");$i++){
                $listaAnos[] = $i;
            }
            
            $controle = new Input('ano','combo','Ano:',1);
            $controle->set_size(5);
            $controle->set_title('Ano:');
            $controle->set_array($listaAnos);
            $controle->set_valor($ano);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);
            
            $form->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            $grid2 = new Grid();
            $grid2->abreColuna(4);
            
            # Pega os dados
            $selectGrafico = 'SELECT tbtipocargo.tipo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                                                LEFT JOIN tbtipocargo USING (idTipoCargo)
                               WHERE YEAR(dtadmissao) <= "'.$ano.'" 
                                 AND ((dtdemissao IS NULL) OR (YEAR(dtdemissao) >= "'.$ano.'"))
                            GROUP BY tbtipocargo.tipo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            
            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));
            
            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("por Cargo (Temporal)");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Cargo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(8);
            
            $chart = new Chart("Pie",$servidores);
            #$chart->set_tamanho(700,300);
            $chart->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            hr();
            
            ############################################################################################
            
            $grid2 = new Grid();
            $grid2->abreColuna(6);
            
            # Adm/Tec
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE YEAR(dtadmissao) <= "'.$ano.'" 
                                 AND ((dtdemissao IS NULL) OR (YEAR(dtdemissao) >= "'.$ano.'"))
                                 AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tbtipocargo.cargo
                            ORDER BY 1 DESC ';

            $admTec = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($admTec, "jj"));

            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("Administrativos e Técnicos");
            $tabela->set_conteudo($admTec);
            $tabela->set_label(array("Cargo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            # Professores
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE YEAR(dtadmissao) <= "'.$ano.'" 
                                 AND ((dtdemissao IS NULL) OR (YEAR(dtdemissao) >= "'.$ano.'"))
                                 AND tbtipocargo.tipo = "Professor"
                            GROUP BY tbtipocargo.cargo
                            ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("Professores");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Cargo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            hr();
            ############################################################################################
            
            $grid2 = new Grid();
            
            $cargo = array("Profissional de Nível Superior","Profissional de Nível Médio","Profissional de Nível Fundamental","Profissional de Nível Elementar");
            
            foreach($cargo as $valor){
                $grid2->abreColuna(3);

                # exibe a tabela
                $selectGrafico = 'SELECT tbcargo.nome, count(tbservidor.idServidor) as jj
                                    FROM tbservidor JOIN tbcargo USING (idCargo)
                                                    JOIN tbtipocargo USING (idTipoCargo)
                                   WHERE YEAR(dtadmissao) <= "'.$ano.'" 
                                     AND ((dtdemissao IS NULL) OR (YEAR(dtdemissao) >= "'.$ano.'"))
                                     AND tbtipocargo.cargo = "'.$valor.'"
                                GROUP BY tbcargo.nome
                                ORDER BY 2 DESC ';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                # Exemplo de tabela simples
                $tabela = new Tabela();
                $tabela->set_titulo($valor);
                $tabela->set_conteudo($servidores);
                $tabela->set_label(array("Cargo","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->show();

                $grid2->fechaColuna();
            }


            $grid2->fechaGrid();

            hr();
            break;
            
        #########################################
            
        case "lotacao":
            
            titulo("Estatística por Lotação");
            br();
            
            $grid2 = new Grid();
            $grid2->abreColuna(3);
            
            # Número de Servidores
            $painel = new Callout();
            $painel->set_title('Painel com tipo primary');
            $painel->abre();

                $numServidores = $pessoal->get_numServidoresAtivos();
                p($numServidores,"estatisticaNumero");
                p("Servidores Ativos","estatisticaTexto");

            $painel->fecha(); 

            ###############################
            
            # Pega os dados
            $selectGrafico = 'SELECT tblotacao.dir, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                 AND situacao = 1
                                 AND ativo
                            GROUP BY tblotacao.dir
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);
            
            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));            

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_titulo("por Lotação");
            $tabela->set_label(array("Diretoria","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(9);

            $chart = new Chart("Pie",$servidores);
            #$chart->set_tresd(TRUE);
            $chart->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            hr();
            
            $grid2 = new Grid();
            
            foreach ($servidores as $item){
                $grid2->abreColuna(3);

                # exibe a tabela
                $selectGrafico2 = 'SELECT tblotacao.ger, count(tbservidor.idServidor) as jj
                                        FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                         AND situacao = 1
                                         AND ativo
                                         AND tblotacao.dir="'.$item[0].'" 
                                    GROUP BY tblotacao.ger
                                    ORDER BY 1';
                
                $servidores = $pessoal->select($selectGrafico2);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                # Exemplo de tabela simples
                $tabela = new Tabela();
                $tabela->set_titulo($item[0]);
                $tabela->set_conteudo($servidores);
                $tabela->set_label(array("Lotação","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->show();

                $grid2->fechaColuna();
            }


            $grid2->fechaGrid();

            hr();
            break;
        #########################################
    }
    
    # Fecha o grid
    $grid1->fechaColuna();
    $grid1->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}