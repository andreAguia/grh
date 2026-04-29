<?php

/**
 * Estatística
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Pega o idConcurso
    $idConcurso = get_session("idConcurso");
    $concurso = new Concurso($idConcurso);

    # Verifica a fase do programa
    $fase = get('fase', 'idade');
    $diretoria = get('diretoria');
    $grafico = get('grafico');

    # Pega o ano
    $ano = post("ano", date("Y"));

    # Parametros
    $parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', '*'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', '*'));

    # Joga os parâmetros par as sessions    
    set_session('parametroPerfil', $parametroPerfil);
    set_session('parametroLotacao', $parametroLotacao);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Define o select do perfil que será usado em toda a rotina
    $selectPerfil = "SELECT DISTINCT idPerfil, 
                                     tbperfil.nome,
                                     tbperfil.tipo
                                FROM tbservidor JOIN tbperfil USING (idPerfil)
                               WHERE situacao = 1
                                 AND tbperfil.tipo <> 'Outros' 
                            ORDER BY tbperfil.tipo, tbperfil.nome";

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $linkVoltar = new Link("Voltar",  "areaConcursoAdm.php");
    $linkVoltar->set_class('button');
    $linkVoltar->set_title('Voltar para página anterior');
    $linkVoltar->set_accessKey('V');
    $menu1->add_link($linkVoltar, "left");

    $menu1->show();

    $grid = new Grid();

    ## Coluna do menu            
    $grid->abreColuna(12, 3);

    # Exibe os dados do Concurso
    $concurso->exibeDadosConcurso($idConcurso, true);

    # menu
    $concurso->exibeMenu($idConcurso, "Estatística");

    # Exibe os servidores deste concurso
    $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

    $grid->fechaColuna();

    ################################################################
    # Coluna de Conteúdo
    $grid->abreColuna(12, 9);

    switch ($fase) {

        case "idade":

            # Título
            tituloTable("Estatística");

            # Abre um callout
            $painel = new Callout();
            $painel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por idade";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Cria as colunas
            $grid2 = new Grid();
            $grid2->abreColuna(4);

            # Número de Servidores
            $painel = new Callout();
            $painel->abre();

            # Numero Geral
            $candidatoClasse = new CandidatoAdm2025();
            $numCandidatos = $candidatoClasse->get_numCandidatoAc();
            p($numCandidatos, "estatisticaNumero");
            p("Todos os Candidatos", "estatisticaTexto");

            $painel->fecha();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(8);
            
            $grid2->fechaColuna();
            $grid2->abreColuna(5);

            /*
             *  Geral - Por Idade
             */
            $selectGrafico = 'SELECT count(idCandidato) as jj,
                                     TIMESTAMPDIFF(YEAR, dtNascimento, NOW()) AS idade
                                FROM tbcandidato 
                                WHERE TIMESTAMPDIFF(YEAR,dtNascimento, NOW()) > 15
                            GROUP BY idade
                            ORDER BY 2';

            $servidores = $pessoal->select($selectGrafico);

            # Separa os arrays para analise estatística
            $idades = array();
            foreach ($servidores as $item) {
                $idades[] = $item[1];
            }

            /*
             * Pega todas as idades para calcular a moda
             */
            $selectModa = "SELECT TIMESTAMPDIFF(YEAR, dtNascimento, NOW()) AS idade
                             FROM tbcandidato
                             WHERE TIMESTAMPDIFF(YEAR,dtNascimento, NOW()) > 15";

            $servidoresModa = $pessoal->select($selectModa);
            foreach ($servidoresModa as $item) {
                $arrayModa[] = $item[0];
            }

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Dados da tabela
            $dados[] = array("Maior Idade", maiorValor($idades));
            $dados[] = array("Menor Idade", menorValor($idades));
            $dados[] = array("Média", media_aritmetica($idades));
            $dados[] = array("Moda", moda($arrayModa));

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($dados);
            $tabela->set_label(["Descrição", "Idade"]);
            $tabela->set_width([50, 50]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->set_linkTituloTitle("Exibe detalhes");
            $tabela->set_mensagemPosTabela("Foi retirado um candidato da contagem pois está com a data de nascimento errada. (menos de um ano de idade)");
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(7);
            br(3);

            # Chart
            $chart = new Chart("ColumnChart", $dados);
            $chart->set_idDiv("idade");
            $chart->set_legend(false);
            $chart->set_label(array("Descrição", "Idade"));
            $chart->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);

            $select = 'SELECT TIMESTAMPDIFF(YEAR,dtNascimento, NOW()) AS idade,
                              count(idCandidato) as jj
                                FROM tbcandidato
                                WHERE TIMESTAMPDIFF(YEAR,dtNascimento, NOW()) > 15
                            GROUP BY idade
                            ORDER BY 1';

            $servidores = $pessoal->select($select);

            # Chart
            #tituloTable("por Cada Idade");
            $chart = new Chart("ColumnChart", $servidores);
            $chart->set_idDiv("faixa");
            $chart->set_label(array("Idade", "Servidores"));
            $chart->set_legend(false);
            $chart->set_idDiv("cadaIdade");
            $chart->show();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_titulo("por Cada Idade");
            $tabela->set_label(["Idade", "Servidores"]);
            $tabela->set_align(["center"]);
            $tabela->set_width([50, 50]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            #$tabela->show();

            $grid2->fechaGrid();

            $painel->fecha();
            break;

        ################################################################

        case "vagas":

            # Abre um callout
            $painal = new Callout();
            $painel->abre();

            # Define array de Cotas
            $concurso2025 = new ConcursoAdm2025();
            $arrayCotas = $concurso2025->get_arrayCotas();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística de candidatos nas vagas por idade";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("Candidatos Nas Vagas");

            # Cria as colunas
            $grid2 = new Grid();
            $grid2->abreColuna(4);
            br();

            # Define o array da tabela
            $arrayTabela = [];
            $resultadoFinal = [];

            # Pega os cargos
            $result = $pessoal->select('SELECT DISTINCT cargoConcurso
                                          FROM tbconcursovagadetalhada
                                      ORDER BY cargoConcurso');

            # Percorre os cargos
            foreach ($result as $item) {

                foreach ($arrayCotas as $cota) {

                    switch ($cota[0]) {
                        // Ampla Concorrência
                        case "Ac":
                            $numeroVagas = $concurso->get_numVagasAcAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifAc";
                            $campoVaga = "vagas";
                            $subtitulo = "Ampla Concorrência";
                            break;

                        // Pcd
                        case "Pcd":
                            $numeroVagas = $concurso->get_numVagasPcdAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifPcd";
                            $campoVaga = "vagasPcd";
                            $subtitulo = "Cota: PCD";
                            break;

                        // Negros e Indígenas
                        case "Ni":
                            $numeroVagas = $concurso->get_numVagasNiAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifNi";
                            $campoVaga = "vagasNi";
                            $subtitulo = "Cota: Negros e Indígenas";
                            break;

                        // Hipossuficiente Econômico
                        case "Hipo":
                            $numeroVagas = $concurso->get_numVagasHipoAprovadas($idConcurso, $item["cargoConcurso"]);
                            $campo = "classifHipo";
                            $campoVaga = "vagasHipo";
                            $subtitulo = "Cota: Hipossuficiente Econômico";
                            break;
                    }

                    # Pega os candidaatos desse cargo e dessa cota
                    $select = "SELECT count(idCandidato) as jj,
                                      TIMESTAMPDIFF(YEAR, dtNascimento, NOW()) AS idade
                                 FROM tbcandidato LEFT JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                                WHERE tbcandidato.idConcurso = {$idConcurso}
                                  AND {$campo} <= tbconcursovagadetalhada.{$campoVaga}
                                  AND cargo = '{$item["cargoConcurso"]}'
                                  AND ({$campo} <> 0 AND {$campo} IS NOT NULL)
                             ORDER BY {$campo}";

                    # Passa para o array
                    $arrayTabela = array_merge($arrayTabela, $pessoal->select($select));
                }
            }

            # Separa os arrays para analise estatística
            $idades = array();
            foreach ($arrayTabela as $item) {
                $idades[] = $item[1];
            }

            # Soma a coluna do count
            $total = array_sum(array_column($arrayTabela, "jj"));

            # Dados da tabela
            $dados[] = array("Maior Idade", maiorValor($idades));
            $dados[] = array("Menor Idade", menorValor($idades));
            $dados[] = array("Média", media_aritmetica($idades));

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($dados);
            $tabela->set_label(["Descrição", "Idade"]);
            $tabela->set_width([50, 50]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->set_linkTituloTitle("Exibe detalhes");
            $tabela->set_mensagemPosTabela("Foi retirado um candidato ca contagem pois está com a data de nascimento errada. (menos de um ano de idade)");
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(8);

            # Chart
            $chart = new Chart("ColumnChart", $dados);
            $chart->set_idDiv("idade");
            $chart->set_legend(false);
            $chart->set_label(array("Descrição", "Idade"));
            $chart->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);

            $select = 'SELECT TIMESTAMPDIFF(YEAR,dtNascimento, NOW()) AS idade,
                              count(idCandidato) as jj
                                FROM tbcandidato
                                WHERE TIMESTAMPDIFF(YEAR,dtNascimento, NOW()) > 15
                            GROUP BY idade
                            ORDER BY 1';

            $servidores = $pessoal->select($select);

            # Chart
            #tituloTable("por Cada Idade");
            $chart = new Chart("ColumnChart", $servidores);
            $chart->set_idDiv("faixa");
            $chart->set_label(array("Idade", "Servidores"));
            $chart->set_legend(false);
            $chart->set_idDiv("cadaIdade");
            $chart->show();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_titulo("por Cada Idade");
            $tabela->set_label(["Idade", "Servidores"]);
            $tabela->set_align(["center"]);
            $tabela->set_width([50, 50]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            #$tabela->show();

            $grid2->fechaGrid();

            $painel->fecha();
            break;

        ################################################################
    }

    # Fecha o grid
    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
