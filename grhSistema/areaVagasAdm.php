<?php

/**
 * Cadastro de Banco
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

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de vagas de Administrativo e Técnico";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }
    
    # Define a função usada em dois momentos nesse codigo
    function botaoServidoresAtivosVagas($sigla = null) {
        # Ver servidores ativos
        $servAtivos = new Link(null, "../grhRelatorios/geral.concursados.ativos.admTec.php?sigla={$sigla}");
        $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
        $servAtivos->set_target("_blank");
        $servAtivos->set_title("Exibe os servidores ativos");
        $servAtivos->show();
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    $menu = new MenuBar();

    # Voltar
    $botao = new Link("Voltar", "areaConcursoAdm.php");
    $botao->set_class('button');
    $botao->set_title('Voltar a página anterior');
    $botao->set_accessKey('V');
    $menu->add_link($botao, "left");
    $menu->show();

    tituloTable("Vacância para Cargos Administrativos e Técnicos");
    br();

    # Menu de Abas
    $tab = new Tab([
        "Considerando as Vagas Autorizadas para Concurso",
        "Considerando as Vagas Ocupadas",
    ]);

    $tab->abreConteudo();

    $grid1 = new Grid();
    $grid1->abreColuna(12);

    tituloTable("Considerando as Vagas Autorizadas nos Concursos");

    $texto = "Observações Importantes:<br/>"
            . " - Para essa análise serão consideradas somente as vagas novas (vagas reais) e descartadas as vagas de reposição.<br/>"
            . " - Antes de aceitar estes dados como corretos, deve-se verificar no menu de vagas de cada concurso se não há problemas detectados.";
    callout($texto);

    $grid1->fechaColuna();
    $grid1->abreColuna(8);

    # Exibe as vagas 
    $select = "SELECT CONCAT(tbconcurso.anobase,'<br/>',tbconcurso.regime),
                              tbtipocargo.sigla,
                              vagasNovas,
                              idConcursoVaga,
                              idConcursoVaga
                         FROM tbconcursovaga JOIN tbtipocargo USING (idTipoCargo)
                                             JOIN tbconcurso USING (idConcurso)
                        WHERE tbconcurso.tipo = 1
                     ORDER BY tbconcurso.anobase DESC, tbtipocargo.sigla DESC";

    $conteudo = $pessoal->select($select);
    $numConteudo = $pessoal->count($select);

    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_conteudo($conteudo);
    $tabela->set_titulo("Quantidade de Vagas Novas por Concurso");
    $tabela->set_subtitulo("Desde o Concurso de 1997");
    $tabela->set_label(["Concurso", "Cargo", "Vagas Novas", "Servidores Ativos", "Vagas Disponíveis"]);
    $tabela->set_width([20, 20, 20, 20, 20]);

    $tabela->set_colunaSomatorio([2, 3, 4]);
    $tabela->set_textoSomatorio("Total:");
    $tabela->set_totalRegistro(false);

    $tabela->set_rowspan(0);
    $tabela->set_grupoCorColuna(0);

    $tabela->set_classe([null, null, null, "VagaAdm", "VagaAdm"]);
    $tabela->set_metodo([null, null, null, "get_numServidoresAtivosVaga", "get_vagasDisponiveis"]);
    $tabela->show();

    $grid1->fechaColuna();
    $grid1->abreColuna(4);

    # Exibe as vagas 
    $select = "SELECT tbtipocargo.sigla,
                      idTipoCargo,
                      idTipoCargo,
                      tbtipocargo.sigla,
                      idTipoCargo
                 FROM tbtipocargo
                WHERE tipo = 'Adm/Tec'
             ORDER BY 1 DESC";

    $conteudo = $pessoal->select($select);
    $numConteudo = $pessoal->count($select);

    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_conteudo($conteudo);
    $tabela->set_titulo("Total Geral");
    $tabela->set_subtitulo("Desde o Concurso de 1997");
    $tabela->set_label(["Cargo", "Vagas Novas", "Vagas Ocupadas", "Ver", "Vagas Disponíveis"]);
    $tabela->set_colspanLabel([null, null, 2]);
    $tabela->set_width([20, 20, 20, 20, 20]);
    
    $tabela->set_funcao([null, null, null, "botaoServidoresAtivosVagas"]);

    $tabela->set_colunaSomatorio([1, 2, 4]);
    $tabela->set_textoSomatorio("Total:");
    $tabela->set_totalRegistro(false);

    $tabela->set_rowspan(0);
    $tabela->set_grupoCorColuna(0);

    $tabela->set_classe([null, "VagaAdm", "VagaAdm", null, "VagaAdm"]);
    $tabela->set_metodo([null, "get_numReaisCargo", "get_numServidoresAtivosCargo", null, "get_vagasDisponiveisCargo"]);

    $painel = new Callout("primary");
    $painel->abre();

    $vagasAdm = new VagaAdm();

    p("Vagas Totais:", "vagaCargo");
    p($vagasAdm->get_vagasDisponiveisCargo(6) + $vagasAdm->get_vagasDisponiveisCargo(5) + $vagasAdm->get_vagasDisponiveisCargo(4) + $vagasAdm->get_vagasDisponiveisCargo(3), "vagaCentro");
    p("PNE: {$vagasAdm->get_vagasDisponiveisCargo(6)} | PNF: {$vagasAdm->get_vagasDisponiveisCargo(5)} | PNM: {$vagasAdm->get_vagasDisponiveisCargo(4)} | PNS: {$vagasAdm->get_vagasDisponiveisCargo(3)}", "vagaCargo");

    $painel->fecha();
    $tabela->show();

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $tab->fechaConteudo();

    #################################################

    $tab->abreConteudo();

    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Reserva para as máximas
    $maxpne = 0;
    $maxpnf = 0;
    $maxpnm = 0;
    $maxpns = 0;

    $anoInicial = 1997;
    $anoFinal = date("Y");

    # Pega os valores máximos
    for ($i = $anoInicial; $i < $anoFinal + 1; $i++) {

        # Habilita os valores
        $vpne = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(6, $i);
        $vpnf = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(5, $i);
        $vpnm = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(4, $i);
        $vpns = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(3, $i);

        # PNE
        if ($maxpne < $vpne) {
            $maxpne = $vpne;
        }

        # PNF
        if ($maxpnf < $vpnf) {
            $maxpnf = $vpnf;
        }

        # PNM
        if ($maxpnm < $vpnm) {
            $maxpnm = $vpnm;
        }

        # PNS
        if ($maxpns < $vpns) {
            $maxpns = $vpns;
        }
    }

    # monta o array
    for ($i = $anoInicial; $i < $anoFinal + 1; $i++) {

        # Habilita os valores
        $vpne = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(6, $i);
        $vpnf = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(5, $i);
        $vpnm = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(4, $i);
        $vpns = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(3, $i);

        $total = $vpne + $vpnf + $vpnm + $vpns;

        # PNE
        if ($maxpne == $vpne) {
            $vpne = "<span class='label warning'>{$vpne}</span>";
        }

        # PNF
        if ($maxpnf == $vpnf) {
            $vpnf = "<span class='label warning'>{$vpnf}</span>";
        }

        # PNM
        if ($maxpnm == $vpnm) {
            $vpnm = "<span class='label warning'>{$vpnm}</span>";
        }

        # PNS
        if ($maxpns == $vpns) {
            $vpns = "<span class='label warning'>{$vpns}</span>";
        }

        $dados[] = [$i, $vpne, $vpnf, $vpnm, $vpns, $total, $i];
    }

    tituloTable("Considerando as Vagas Ocupadas");
    $texto = "Observações Importantes:<br/>"
            . " - Para essa análise é considerado o número máximo de servidores concursados ativos por ano / cargo desde {$anoInicial}.";
    callout($texto);

    $grid1->fechaColuna();
    $grid1->abreColuna(8);

    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_conteudo($dados);
    $tabela->set_titulo("Quantidade de Servidores Ativos por Ano");
    $tabela->set_subtitulo("Estatutários e Celetistas Concursados<br/>{$anoInicial} a {$anoFinal}");
    $tabela->set_label(["Ano", "PNE", "PNF", "PNM", "PNS", "Total", "Ver"]);
    $tabela->set_colspanLabel([null, null, null, null, null, 2]);
    $tabela->set_classe([null, null, null, null, null, null, "Concurso"]);
    $tabela->set_metodo([null, null, null, null, null, null, "rel_ServidoresPorAno"]);

    $tabela->set_rowspan(0);
    $tabela->set_grupoCorColuna(0);

    $tabela->show();

    $grid1->fechaColuna();
    $grid1->abreColuna(4);

    # Habilita os valores
    $vapne = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(6);
    $vapnf = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(5);
    $vapnm = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(4);
    $vapns = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(3);

    # Diferença
    $difpne = $maxpne - $vapne;
    $difpnf = $maxpnf - $vapnf;
    $difpnm = $maxpnm - $vapnm;
    $difpns = $maxpns - $vapns;

    # VagasTotais
    $vagasTotais = $difpne + $difpnf + $difpnm + $difpns;

    $dados2 = [
        ["PNS", $maxpns, $vapns, "PNS", $difpns],
        ["PNM", $maxpnm, $vapnm, "PNM", $difpnm],
        ["PNF", $maxpnf, $vapnf, "PNF", $difpnf],
        ["PNE", $maxpne, $vapne, "PNE", $difpne],
    ];

    # Monta a tabela
    $tabela = new Tabela();
    $tabela->set_conteudo($dados2);
    $tabela->set_titulo("Maiores Valores");
    $tabela->set_subtitulo("{$anoInicial} a {$anoFinal}");
    $tabela->set_label(["Cargo", "Max", "Atual", "Ver", "Diferença"]);
    
    $tabela->set_funcao([null, null, null, "botaoServidoresAtivosVagas"]);
    
    $tabela->set_colspanLabel([null, null, 2]);
    $tabela->set_width([20, 20, 20, 20, 20]);
    $tabela->set_colunaSomatorio([1, 2, 4]);
    $tabela->set_totalRegistro(false);    
    
    $tabela->set_rowspan(0);
    $tabela->set_grupoCorColuna(0);

    $painel = new Callout("primary");
    $painel->abre();

    p("Vagas Totais:", "vagaCargo");
    p($vagasTotais, "vagaCentro");
    p("PNE: {$difpne} | PNF: {$difpnf} | PNM: {$difpnm} | PNS: {$difpns}", "vagaCargo");

    $painel->fecha();
    $tabela->show();

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $tab->fechaConteudo();
    $tab->show();

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}