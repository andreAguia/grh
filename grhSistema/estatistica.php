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

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','inicial');
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
    
    # PArametros do perfil
    $parametroPerfil = post('parametroPerfil',get_session('parametroPerfil','*'));
    
    # Começa uma nova página
    $page = new Page();        
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    if($fase == "inicial"){
        $linkVoltar = new Link("Voltar","grh.php");
    }else{
        $linkVoltar = new Link("Voltar","?");
    }
    $linkVoltar->set_class('button');
    $linkVoltar->set_title('Voltar para página anterior');
    $linkVoltar->set_accessKey('V');
    $menu1->add_link($linkVoltar,"left");

    $menu1->show();
    
    titulo("Estatística");
    br();

    $grid = new Grid();

    ## Coluna do menu            
    $grid->abreColuna(12,3);

        # Número de Servidores
        $painel = new Callout();
        $painel->abre();
            $numServidores = $pessoal->get_numServidoresAtivos();
            p($numServidores,"estatisticaNumero");
            p("Servidores Ativos","estatisticaTexto");
        $painel->fecha(); 

        ###############################

        # Menu de tipos
        $menu = new Menu();
        $menu->add_item('titulo','Detalhada');
        $menu->add_item('link','Por Idade','?fase=idade');
        $menu->add_item('link','Por Faixa Etária','?fase=faixaEtaria');
        $menu->add_item('link','Por Perfil','?fase=perfil');
        $menu->add_item('link','Por Cargo - Geral','?fase=cargo');
        $menu->add_item('link','Por Cargo - Adm/Tec','?fase=cargoAdm');
        $menu->add_item('link','Por Diretoria','?fase=diretoria');
        $menu->add_item('link','Por Gerência','?fase=gerencia');
        $menu->add_item('link','Por Escolaridade','?fase=escolaridade');
        $menu->add_item('link','Por Nacionalidade','?fase=nacionalidade');
        $menu->add_item('link','Por Estado Civil','?fase=estadoCivil');
        $menu->add_item('link','Por Cidade de Moradia','?fase=cidade');
        #$menu->add_item('link','Temporal','?fase=temporalCargo');  # Retirado por imprecisão
        
        $menu->show();

    $grid->fechaColuna();
    
    ################################################################
    
    # Coluna de Conteúdo
    $grid->abreColuna(12,9);
    
    switch ($fase){
        case "inicial":
            
            break;
        
    ################################################################
        
        case "idade":
            
            # Abre um callout
            $painal = new Callout();
            $painel->abre();
            
            # Título
            tituloTable("Por Idade");
            
            # Cria as colunas
            $grid2 = new Grid();
            $grid2->abreColuna(4);
            br();

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
            #$tabela->set_titulo("por Idade");
            $tabela->set_label(array("Descrição","Idade"));
            $tabela->set_width(array(50,50));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->set_linkTituloTitle("Exibe detalhes");
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(8);
            
            # Chart
            $chart = new Chart("ColumnChart",$dados);
            $chart->set_idDiv("idade");
            $chart->set_legend(FALSE);
            $chart->set_label(array("Idade","Descrição"));
            $chart->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);            
            
            $select = 'SELECT TIMESTAMPDIFF(YEAR, tbpessoa.dtNasc, NOW()) AS idade,
                              count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                               WHERE situacao = 1
                            GROUP BY idade
                            ORDER BY 1';

            $servidores = $pessoal->select($select);
            
            # Chart
            #tituloTable("por Cada Idade");
            $chart = new Chart("ColumnChart",$servidores);
            $chart->set_idDiv("faixa");
            $chart->set_label(array("Servidores","Idade"));
            $chart->set_legend(FALSE);
            $chart->set_idDiv("cadaIdade");
            $chart->show();
            
            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_titulo("por Cada Idade");
            $tabela->set_label(array("Idade","Servidores"));
            $tabela->set_align(array("center"));
            $tabela->set_width(array(50,50));
            $tabela->set_rodape("Total de Servidores: ".$total);
            #$tabela->show();
            
            $grid2->fechaGrid();
            
            $painel->fecha();            
            break;
        
    ################################################################   
        
        case "faixaEtaria":
            
            # Abre um callout
            $panel = new Callout();
            $panel->abre();
            
            $grid3 = new Grid();
            $grid3->abreColuna(12);
            
            # Faixa Etária Geral
            $select = "SELECT CASE 
                WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 10 AND 20 THEN 'até 20'
                WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 21 AND 30 THEN '21 a 30'
                WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 31 AND 40 THEN '31 a 40'
                WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 41 AND 50 THEN '41 a 50'
                WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 51 AND 60 THEN '51 a 60'
                WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 61 AND 70 THEN '61 a 70'
                WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 71 AND 80 THEN '71 a 80'
                WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 81 AND 90 THEN '81 a 90'
            END,
            COUNT(idPessoa),
            ROUND((COUNT(idPessoa)*100)/".$numServidores.",1)
            FROM tbpessoa JOIN tbservidor USING (idPessoa)
           WHERE situacao = 1
            GROUP BY 1 ORDER BY 1";
            
            $servidores = $pessoal->select($select);
            
            # Chart
            tituloTable("por Faixa Etária");
            $chart = new Chart("ColumnChart",$servidores);
            $chart->set_idDiv("faixa");
            $chart->set_label(array("Servidores","Faixa"));
            $chart->set_legend(FALSE);
            $chart->show();
            
            br(2);
            $grid4 = new Grid();
            $grid4->abreColuna(5);
            
            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            #$tabela->set_titulo("por Faixa Etária");
            $tabela->set_label(array("Faixa","Servidores","%"));
            $tabela->set_align(array("center"));
            $tabela->set_rodape("Total de Servidores: ".$numServidores);
            $tabela->show();
            
            $grid4->fechaColuna();      
            $grid4->abreColuna(7);
            
            # Chart            
            $chart = new Chart("Pie",$servidores);
            $chart->set_idDiv("faixaPie");
            $chart->set_tamanho($largura = 400,$altura = 400);
            $chart->set_legend(FALSE);
            $chart->show();
            
            
            $grid4->fechaColuna();            
            $grid4->fechaGrid();            
            $panel->fecha();
            break;
            
    ################################################################   
        
        case "perfil":
            
            # Abre um callout
            $panel = new Callout();
            $panel->abre();
            
            # Título
            tituloTable("por Perfil");

            $grid3 = new Grid();
            $grid3->abreColuna(4);
            br();
            
            # Geral - Por Perfil
            $selectGrafico = 'SELECT tbperfil.nome, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbperfil.nome
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            
            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Tabela
            $tabela = new Tabela();
            #$tabela->set_titulo("por Perfil");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Perfil","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);                
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(2);

            $grid3->fechaColuna();
            $grid3->abreColuna(6);

            # Gráfico
            $chart = new Chart("Pie",$servidores);
            $chart->set_idDiv("perfil");
            $chart->set_legend(FALSE);
            $chart->set_tamanho($largura = 300,$altura = 300);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();
            $panel->fecha();
            break;
            
    ################################################################        

            case "cargo":
            
            # Abre um callout
            $panel = new Callout();
            $panel->abre();
            
            # Título
            tituloTable("por Cargo - Geral");

            $grid3 = new Grid();
            $grid3->abreColuna(4);
            br();

            # Geral - Por Cargo
            $selectGrafico = 'SELECT tbtipocargo.tipo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                                                LEFT JOIN tbtipocargo USING (idTipoCargo)
                               WHERE situacao = 1
                            GROUP BY tbtipocargo.tipo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            $total = array_sum(array_column($servidores, "jj"));

            # Exemplo de tabela simples
            $tabela = new Tabela();                
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Tipo do Cargo","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();
            
            $grid3->fechaColuna();
            $grid3->abreColuna(2);

            $grid3->fechaColuna();
            $grid3->abreColuna(6);

            #tituloTable("por Cargo");
            $chart = new Chart("Pie",$servidores);
            $chart->set_idDiv("cargo");
            $chart->set_legend(FALSE);
            $chart->set_tamanho($largura = 300,$altura = 300);
            $chart->show();
            
            $grid3->fechaColuna();
            $grid3->abreColuna(6);
            
            # Adm/Tec
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                                 AND tbtipocargo.tipo = "Adm/Tec" GROUP BY tbtipocargo.cargo
                        ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            tituloTable("Administrativos e Técnicos");
            $chart = new Chart("Pie",$servidores);
            $chart->set_idDiv("administrativos");
            #$chart->set_legend(FALSE);
            $chart->show();

            # Tabela
            $selectGrafico = 'SELECT tbtipocargo.cargo, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                               AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tbtipocargo.cargo, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = NULL;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];                    
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if($escolaridade <> $escolaridadeAnterior){
                    if(is_null($escolaridadeAnterior)){
                        $escolaridadeAnterior = $escolaridade;
                    }else{
                        $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi+$masc);
                    }
                }

                if($sexo == 'Masculino'){
                   $masc = $contagem;
                   $totalMasc += $masc;
                }else{
                   $femi = $contagem;
                   $totalFemi += $femi; 
                }   
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));          

            $arrayEscolaridade[] = array("Total",$totalFemi,$totalMasc,$total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            #$tabela->set_titulo("Adm/Tec");
            $tabela->set_label(array("Cargo","Feminino","Masculino","Total"));
            $tabela->set_width(array(55,15,15,15));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(6);

            # Professores
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                                 AND tbtipocargo.tipo = "Professor" GROUP BY tbtipocargo.cargo
                            ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            tituloTable("Professores");
            $chart = new Chart("Pie",$servidores);
            $chart->set_idDiv("professores");
            #$chart->set_legend(FALSE);
            $chart->show();

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Tebela
            $selectGrafico = 'SELECT tbtipocargo.cargo, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                               AND tbtipocargo.tipo = "Professor"
                            GROUP BY tbtipocargo.cargo, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = NULL;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];                    
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if($escolaridade <> $escolaridadeAnterior){
                    if(is_null($escolaridadeAnterior)){
                        $escolaridadeAnterior = $escolaridade;
                    }else{
                        $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi+$masc);
                    }
                }

                if($sexo == 'Masculino'){
                   $masc = $contagem;
                   $totalMasc += $masc;
                }else{
                   $femi = $contagem;
                   $totalFemi += $femi; 
                }   
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));          

            $arrayEscolaridade[] = array("Total",$totalFemi,$totalMasc,$total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            #$tabela->set_titulo("Professor");
            $tabela->set_label(array("Cargo","Feminino","Masculino","Total"));
            $tabela->set_width(array(55,15,15,15));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'estatisticaTotal')));

            $tabela->show();    
                
            $grid3->fechaColuna();
            $grid3->fechaGrid();
            $panel->fecha();
            break;
            
    ################################################################        

            case "cargoAdm":
            
            # Abre um callout
            $panel = new Callout();
            $panel->abre();
            
            # Título
            tituloTable("por Cargo - Administrativo e Tecnicos");
            br();
           
            $grid3 = new Grid();
            $grid3->abreColuna(12);
            
            #############################
            
            # Adm/Tec
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                                 AND tbtipocargo.tipo = "Adm/Tec" GROUP BY tbtipocargo.cargo
                        ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            $chart = new Chart("Pie",$servidores);
            $chart->set_idDiv("administrativos");
            $chart->set_tamanho(800,500);
            $chart->show();

            #$grid3->fechaColuna();
            #$grid3->abreColuna(6);

            # Tabela
            $selectGrafico = 'SELECT tbtipocargo.cargo, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                               AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tbtipocargo.cargo, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = NULL;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];                    
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if($escolaridade <> $escolaridadeAnterior){
                    if(is_null($escolaridadeAnterior)){
                        $escolaridadeAnterior = $escolaridade;
                    }else{
                        $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi+$masc);
                    }
                }

                if($sexo == 'Masculino'){
                   $masc = $contagem;
                   $totalMasc += $masc;
                }else{
                   $femi = $contagem;
                   $totalFemi += $femi; 
                }   
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));          

            $arrayEscolaridade[] = array("Total",$totalFemi,$totalMasc,$total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            #$tabela->set_titulo("Geral");
            $tabela->set_label(array("Cargo","Feminino","Masculino","Total"));
            $tabela->set_width(array(55,15,15,15));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'estatisticaTotal')));

            #$tabela->show();
            $grid3->fechaColuna();
            
            #############################
           
            # Pega os Cargos Administrativos
            $selectCargo = "SELECT idTipoCargo, cargo"
                         . "  FROM tbtipocargo"
                         . " WHERE tipo = 'Adm/Tec'";
            
            $dadosCargo = $pessoal->select($selectCargo);
            
            foreach($dadosCargo as $cc){
            
                $grid3->abreColuna(6);

                # Tabela
                $selectGrafico = 'SELECT tbcargo.nome, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                    FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                                  JOIN tbcargo USING (idCargo)
                                   WHERE tbservidor.situacao = 1
                                   AND idTipoCargo = '.$cc[0].'
                                GROUP BY tbcargo.nome, tbpessoa.sexo
                                ORDER BY 1';
                
                $servidores = $pessoal->select($selectGrafico);

                # Novo array 
                $arrayEscolaridade = array();

                # Valores anteriores
                $escolaridadeAnterior = NULL;

                # inicia as variáveis
                $masc = 0;
                $femi = 0;
                $totalMasc = 0;
                $totalFemi = 0;
                $total = 0;

                # Modelar o novo array
                foreach ($servidores as $value) {
                    # Carrega as variáveis
                    $escolaridade = $value[0];
                    $sexo = $value[1];                    
                    $contagem = $value[2];

                    # Verifica se mudou de escolaridade
                    if($escolaridade <> $escolaridadeAnterior){
                        if(is_null($escolaridadeAnterior)){
                            $escolaridadeAnterior = $escolaridade;
                        }else{
                            $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);
                            $masc = 0;
                            $femi = 0;
                            $escolaridadeAnterior = $escolaridade;
                            $total += ($femi+$masc);
                        }
                    }

                    if($sexo == 'Masculino'){
                       $masc = $contagem;
                       $totalMasc += $masc;
                    }else{
                       $femi = $contagem;
                       $totalFemi += $femi; 
                    }   
                }

                $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));          

                $arrayEscolaridade[] = array("Total",$totalFemi,$totalMasc,$total);

                # Tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($arrayEscolaridade);
                $tabela->set_titulo($cc[1]);
                $tabela->set_label(array("Cargo","Feminino","Masculino","Total"));
                $tabela->set_width(array(55,15,15,15));
                $tabela->set_align(array("left","center"));
                $tabela->set_totalRegistro(FALSE);
                $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                    'valor' => "Total",
                                                    'operador' => '=',
                                                    'id' => 'estatisticaTotal')));

                $tabela->show();

                $grid3->fechaColuna();
                
            }
            
            $grid3->fechaGrid();
            $panel->fecha();
            break;
            
    ################################################################################################################################       
            
        case "diretoria":
            
            # Abre um callout
            $panel = new Callout();
            $panel->abre();
            
            # Título
            tituloTable("por Diretoria");
            br();

            $grid2 = new Grid();
            $grid2->abreColuna(4);
            
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
            #$tabela->set_titulo("por Lotação");
            $tabela->set_label(array("Diretoria","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(8);
           
                # Chart
                #tituloTable($item[0]);
                $chart = new Chart("Pie",$servidores);
                $chart->set_idDiv('porLotacao');
                $chart->set_legend(FALSE);
                $chart->set_tamanho($largura = 500,$altura = 500);
                $chart->show();
            
            $grid2->fechaColuna();            
            $grid2->abreColuna(6);

            # Adm/Tec
            $selectGrafico = 'SELECT tblotacao.dir, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                     AND situacao = 1
                                     AND ativo
                                     AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tblotacao.dir, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = NULL;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];                    
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if($escolaridade <> $escolaridadeAnterior){
                    if(is_null($escolaridadeAnterior)){
                        $escolaridadeAnterior = $escolaridade;
                    }else{
                        $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi+$masc);
                    }
                }

                if($sexo == 'Masculino'){
                   $masc = $contagem;
                   $totalMasc += $masc;
                }else{
                   $femi = $contagem;
                   $totalFemi += $femi; 
                }   
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));          

            $arrayEscolaridade[] = array("Total",$totalFemi,$totalMasc,$total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            $tabela->set_titulo("Adm/Tec");
            $tabela->set_label(array("Lotação","Feminino","Masculino","Total"));
            $tabela->set_width(array(55,15,15,15));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'estatisticaTotal')));

            $tabela->show();
            
            $grid2->fechaColuna();
            
            ##########################################
                
            $grid2->abreColuna(6);

            # Professor
            $selectGrafico = 'SELECT tblotacao.dir, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                     AND situacao = 1
                                     AND ativo
                                     AND tbtipocargo.tipo = "Professor"
                            GROUP BY tblotacao.dir, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = NULL;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];                    
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if($escolaridade <> $escolaridadeAnterior){
                    if(is_null($escolaridadeAnterior)){
                        $escolaridadeAnterior = $escolaridade;
                    }else{
                        $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi+$masc);
                    }
                }

                if($sexo == 'Masculino'){
                   $masc = $contagem;
                   $totalMasc += $masc;
                }else{
                   $femi = $contagem;
                   $totalFemi += $femi; 
                }   
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));          

            $arrayEscolaridade[] = array("Total",$totalFemi,$totalMasc,$total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            $tabela->set_titulo("Professor");
            $tabela->set_label(array("Lotação","Feminino","Masculino","Total"));
            $tabela->set_width(array(55,15,15,15));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid2->fechaColuna();
            
            
            ###
            
            $grid2->abreColuna(12);
            
            # Numero de Servidores por Diretoria / Cargo 
            # Pega as diretorias ativas
            $select1 ='SELECT DISTINCT dir
                        FROM tblotacao
                       WHERE ativo';

            $diretorias = $pessoal->select($select1);
            
            # Pega os cargos
            $select2 = 'SELECT idtipocargo, cargo
                        FROM tbtipocargo
                    ORDER BY idTipoCargo';

            $cargos = $pessoal->select($select2);
            $numeroCargos = $pessoal->count($select2);
            
            # Cria um array onde terá os resultados
            $resultado = array();
            
            # Cria e preenche o array do total da coluna
            $totalColuna = array();
            $totalColuna = array_fill(0, $numeroCargos+2, 0);
            
            # Cria e preenche o array do label
            $label = array("Diretoria");
            foreach($cargos as $cc){
                $label[]=$cc[1];
            }
            $label[] = "Total";
            
            # Zera o contador de linha
            $linha = 0;
            
            # Trata o parametro do perfil transformanto * em nulo
            if($parametroPerfil == "*"){
                $idPerfil = NULL;
            }else{
                $idPerfil = $parametroPerfil;
            }
            
            # Percorre as diretorias
            foreach($diretorias as $dd){
                $resultado[$linha][0] = $dd[0];     // Sigoa da Diretoria 
                $coluna = 1;                        // Inicia a coluna
                $totalLinha = 0;                    // Zera totalizador de cada linha
                
                # Percorre as colunas / Cargos
                foreach($cargos as $cc){
                    $quantidade = $pessoal->get_numServidoresAtivosCargoLotacao($cc[0], $dd[0],$idPerfil);    // Pega a quantidade de servidores
                    $resultado[$linha][$coluna] = $quantidade;                                                  // Joga para o array de exibição
                    $totalLinha = $totalLinha + $quantidade;                                                    // Soma o total da linha a quantidade da coluna
                    $totalColuna[$coluna] += $quantidade;                                                       // Soma o total da coluna a quantidade da linha
                    $coluna++;                                                                                  // Incrementa a coluna
                }
                # Faz a última coluna com o total da linha
                $resultado[$linha][$coluna] = $totalLinha;
                $totalColuna[$coluna] += $totalLinha;
                $linha++;
            }
            # Faz a última lina com os totais das colunas
            $resultado[$linha][0] = "Total";
            $coluna = 1;
            foreach($cargos as $cc){
                $resultado[$linha][$coluna] = $totalColuna[$coluna];
                $coluna++;
            }
            $resultado[$linha][$coluna] = $totalColuna[$coluna];
            
            $tabela = new Tabela();
            $tabela->set_titulo("Número de Servidores por Diretoria / Cargo");
            $tabela->set_conteudo($resultado);
            $tabela->set_label($label);
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_align(array("left","center"));            
            $tabela->show();
            
            ###
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $panel->fecha();
            break;
            
    ################################################################################################################################    
            
        case "gerencia":
            
            # Abre um callout
            $panel = new Callout();
            $panel->abre();
            
            # Título
            tituloTable("por Gerência");
            br();

            $grid2 = new Grid();
            
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
            
            foreach ($servidores as $item){
                $grid2->abreColuna(4);

                # exibe a tabela
                $selectGrafico2 = 'SELECT tblotacao.ger, count(tbservidor.idServidor) as jj
                                        FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                         AND situacao = 1
                                         AND ativo
                                         AND tblotacao.dir="'.$item[0].'" 
                                    GROUP BY tblotacao.ger
                                    ORDER BY 2 desc';
                
                $servidores = $pessoal->select($selectGrafico2);
                
                # Chart
                tituloTable($item[0]);
                $chart = new Chart("Pie",$servidores);
                $chart->set_idDiv($item[0]);
                $chart->set_legend(FALSE);
                $chart->show();

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                # Exemplo de tabela simples
                $tabela = new Tabela();
                #$tabela->set_titulo($item[0]);
                $tabela->set_conteudo($servidores);
                $tabela->set_label(array("Lotação","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);
                $tabela->show();

                $grid2->fechaColuna();
            }


           
            $grid2->fechaGrid();
            break;
            
    ################################################################################################################################
            
            case "escolaridade":
            
            # Sexo por Lotação
            $painel = new Callout();
            $painel->abre();

            titulotable("por Escolaridade");
            br(); 

            $grid3 = new Grid();
            $grid3->abreColuna(6);
                
            # Adm/Tec
            $selectGrafico = 'SELECT tbescolaridade.escolaridade, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                                JOIN tbformacao USING (idPessoa)
                                                JOIN tbescolaridade USING (idEscolaridade)
                                                JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                               AND idEscolaridade <> 12
                               AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tbescolaridade.idEscolaridade, tbpessoa.sexo
                            ORDER BY tbescolaridade.idEscolaridade';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = NULL;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];                    
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if($escolaridade <> $escolaridadeAnterior){
                    if(is_null($escolaridadeAnterior)){
                        $escolaridadeAnterior = $escolaridade;
                    }else{
                        $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi+$masc);
                    }
                }

                if($sexo == 'Masculino'){
                   $masc = $contagem;
                   $totalMasc += $masc;
                }else{
                   $femi = $contagem;
                   $totalFemi += $femi; 
                }   
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));          

            $arrayEscolaridade[] = array("Total",$totalFemi,$totalMasc,$total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            $tabela->set_titulo("Adm/Tec");
            $tabela->set_label(array("Escolaridade","Feminino","Masculino","Total"));
            $tabela->set_width(array(55,15,15,15));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid3->fechaColuna();

    ##########################################

            $grid3->abreColuna(6);               


            # Professor
            $selectGrafico = 'SELECT tbescolaridade.escolaridade, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                                JOIN tbformacao USING (idPessoa)
                                                JOIN tbescolaridade USING (idEscolaridade)
                                                JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1
                               AND idEscolaridade <> 12
                               AND tbtipocargo.tipo = "Professor"
                            GROUP BY tbescolaridade.idEscolaridade, tbpessoa.sexo
                            ORDER BY tbescolaridade.idEscolaridade';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = NULL;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];                    
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if($escolaridade <> $escolaridadeAnterior){
                    if(is_null($escolaridadeAnterior)){
                        $escolaridadeAnterior = $escolaridade;
                    }else{
                        $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi+$masc);
                    }
                }

                if($sexo == 'Masculino'){
                   $masc = $contagem;
                   $totalMasc += $masc;
                }else{
                   $femi = $contagem;
                   $totalFemi += $femi; 
                }   
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior,$femi,$masc,$femi+$masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));          

            $arrayEscolaridade[] = array("Total",$totalFemi,$totalMasc,$total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            $tabela->set_titulo("Professor");
            $tabela->set_label(array("Escolaridade","Feminino","Masculino","Total"));
            $tabela->set_width(array(55,15,15,15));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid3->fechaColuna();                
            $grid3->fechaGrid();
        $painel->fecha();
        break;
            
####################################################################################################

    case "nacionalidade":
            
        # Sexo por Lotação
        $painel = new Callout();
        $painel->abre();

        titulotable("por Nacionalidade");
        br(); 

        $grid = new Grid();
        $grid->abreColuna(4);

        # Geral - Por Nacionalidade
        $selectGrafico = 'SELECT tbnacionalidade.nacionalidade, count(tbservidor.idServidor) as jj
                            FROM tbnacionalidade JOIN tbpessoa ON(tbnacionalidade.idnacionalidade = tbpessoa.nacionalidade)
                                                 JOIN tbservidor USING (idPessoa)
                           WHERE situacao = 1
                        GROUP BY tbnacionalidade.nacionalidade
                        ORDER BY 2 desc';

        $servidores = $pessoal->select($selectGrafico);

        # Soma a coluna do count
        $total = array_sum(array_column($servidores, "jj"));            

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($servidores);
        #$tabela->set_titulo("por Nacionalidade");
        $tabela->set_label(array("Nacionalidade","Servidores"));
        $tabela->set_width(array(80,20));
        $tabela->set_align(array("left","center"));
        $tabela->set_rodape("Total de Servidores: ".$total);
        $tabela->show();

        $grid->fechaColuna();
        $grid->abreColuna(8);

        # Chart
        #tituloTable("por Nacionalidade");
        $chart = new Chart("Pie",$servidores);
        $chart->set_idDiv("nacionalidade");
        $chart->set_legend(FALSE);
        $chart->set_tamanho($largura = 500,$altura = 500);
        $chart->show();

        $grid->fechaColuna();                
        $grid->fechaGrid();
        $painel->fecha();
        break;

####################################################################################################            
            
    case "estadoCivil":
            
        # Sexo por Lotação
        $painel = new Callout();
        $painel->abre();

        titulotable("por Estado Civil");
        br(); 

        $grid = new Grid();
        $grid->abreColuna(4);

        # Geral - Por Nacionalidade
        $selectGrafico = 'SELECT tbestciv.estciv, count(tbservidor.idServidor) as jj
                            FROM tbestciv RIGHT JOIN tbpessoa ON (tbestciv.idEstCiv = tbpessoa.estCiv)
                                          JOIN tbservidor USING (idPessoa)
                           WHERE situacao = 1
                        GROUP BY tbestciv.estciv
                        ORDER BY 2 desc';

        $servidores = $pessoal->select($selectGrafico);

        # Soma a coluna do count
        $total = array_sum(array_column($servidores, "jj"));            

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($servidores);
        #$tabela->set_titulo("por Nacionalidade");
        $tabela->set_label(array("Estado Civil","Servidores"));
        $tabela->set_width(array(80,20));
        $tabela->set_align(array("left","center"));
        $tabela->set_rodape("Total de Servidores: ".$total);
        $tabela->show();

        $grid->fechaColuna();
        $grid->abreColuna(8);

        # Chart
        #tituloTable("por Nacionalidade");
        $chart = new Chart("Pie",$servidores);
        $chart->set_idDiv("estCiv");
        $chart->set_legend(FALSE);
        $chart->set_tamanho($largura = 500,$altura = 500);
        $chart->show();

        $grid->fechaColuna();                
        $grid->fechaGrid();
        $painel->fecha();
        break;

####################################################################################################            
            
    case "cidade":
            
        # Sexo por Lotação
        $painel = new Callout();
        $painel->abre();

        titulotable("por Cidade de Moradia");
        br(); 

        $grid = new Grid();
        $grid->abreColuna(5);

        # Geral - Por Nacionalidade
        $selectGrafico = 'SELECT CONCAT(tbcidade.nome," (",tbestado.uf,")"), count(tbservidor.idServidor) as jj
                            FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                          JOIN tbcidade USING (idCidade)
                                          JOIN tbestado USING (idEstado)
                           WHERE situacao = 1
                        GROUP BY 1
                        ORDER BY jj desc';

        $servidores = $pessoal->select($selectGrafico);

        # Soma a coluna do count
        $total = array_sum(array_column($servidores, "jj"));            

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($servidores);
        $tabela->set_label(array("Cidade","Servidores"));
        $tabela->set_width(array(80,20));
        $tabela->set_align(array("left","center"));
        $tabela->set_rodape("Total de Servidores: ".$total);
        $tabela->show();

        $grid->fechaColuna();
        $grid->abreColuna(7);

        # Chart
        #tituloTable("por Nacionalidade");
        $chart = new Chart("Pie",$servidores);
        $chart->set_idDiv("cidade");
        $chart->set_legend(FALSE);
        $chart->set_tamanho($largura = 500,$altura = 500);
        $chart->show();

        $grid->fechaColuna();                
        $grid->fechaGrid();
        $painel->fecha();
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
    }
    
    # Fecha o grid
    $grid1->fechaColuna();
    $grid1->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}