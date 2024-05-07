<?php

/**
 * Cadastro de Histórico de Vagas de Docentes
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
    $vaga = new Vaga();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de bancos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    $idVaga = get_session('idVaga');

    # Pega o status da vaga
    $statusVaga = $vaga->get_status($idVaga);

    # Pega os dados dessa vaga    
    $vagaDados = $vaga->get_dados($idVaga);
    $numConcursos = $vaga->get_numConcursoVaga($idVaga);

    $centro = $vagaDados['centro'];
    $idCargo = $vagaDados['idCargo'];
    $nomeCargo = $pessoal->get_nomeCargo($idCargo);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo
    $objeto->set_nome("Concursos Desta Vaga");

    # Botão de voltar da lista
    $objeto->set_voltarLista('areaVagasDocentes.php');

    # select da lista
    $objeto->set_selectLista("SELECT concat(tbconcurso.anoBase,' - Edital: ',DATE_FORMAT(tbconcurso.dtPublicacaoEdital,'%d/%m/%Y')) as concurso,
                                      concat(IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) as lotacao,
                                      area,
                                      idServidor,
                                      tbvagahistorico.obs,
                                      idVagaHistorico
                                 FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                                      JOIN tblotacao USING (idLotacao)
                                WHERE idVaga = {$idVaga} ORDER BY tbconcurso.dtPublicacaoEdital desc");

    # select do edita
    $objeto->set_selectEdita("SELECT idVaga,
                                     idConcurso,
                                     idLotacao,
                                     area,
                                     idServidor,
                                     obs
                                FROM tbvagahistorico
                               WHERE idVagaHistorico = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    $objeto->set_botaoIncluirNome("Incluir concurso nessa vaga");

    # Esconde o botão iniciar para usar um diferente na rotina de listar
    $objeto->set_botaoIncluir(false);
    $objeto->set_botaoVoltarLista(false);

    $objeto->set_numeroOrdem(true);
    $objeto->set_numeroOrdemTipo('d');

    # Parametros da tabela
    $objeto->set_label(["Concurso", "Laboratório", "Área", "Servidor", "Obs"]);
    $objeto->set_funcao([null, null, null]);
    $objeto->set_align(["left", "left", "left", "left", "left"]);
    #$objeto->set_width([15, 30, 15, 20, 15]);

    $objeto->set_classe([null, null, null, "Vaga"]);
    $objeto->set_metodo([null, null, null, "get_Nome"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbvagahistorico');

    # Nome do campo id
    $objeto->set_idCampo('idVagaHistorico');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    ###############
    # Pega os dados da combo de vagas
    $vagas = $pessoal->select("SELECT idVaga,
                                      concat(idVaga,' - ',
                                      (SELECT CONCAT('(Origem: ',tblotacao.DIR,'-',tblotacao.GER,')') 
                                         FROM tbvagahistorico AS B 
                                         JOIN tbconcurso USING (idConcurso)
                                         JOIN tblotacao USING (idLotacao)
                                        WHERE A.idVaga = B.idVaga 
                                     ORDER BY tbconcurso.dtPublicacaoEdital LIMIT 1),
                                     ' - ',centro,' / ',tbcargo.nome)
                                 FROM tbvaga AS A LEFT JOIN tbcargo USING (idCargo)
                                 WHERE idCargo = {$idCargo} AND centro = '{$centro}'");

    array_unshift($vagas, array(0, null));

    ###############
    # Pega os dados para combo concurso 
    $concurso = $pessoal->select("SELECT idconcurso,
                                         concat(anoBase,' - Edital: ',DATE_FORMAT(dtPublicacaoEdital,'%d/%m/%Y')) as concurso
                                    FROM tbconcurso
                                    WHERE tipo = 2
                                ORDER BY dtPublicacaoEdital desc");

    array_unshift($concurso, array(0, null));

    ###############
    # Pega os dados da combo lotacao
    $selectLotacao = "SELECT idlotacao, 
                             concat(IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) as lotacao                       
                        FROM tblotacao 
                        WHERE tblotacao.DIR = '{$centro}'  
                        ORDER BY ativo desc, lotacao";

    $result = $pessoal->select($selectLotacao);
    array_unshift($result, array(null, null)); # Adiciona o valor de nulo
    ###############
    # Pega o cargo dessa vaga
    $idCargo = $vaga->get_idCargoVaga($idVaga);

    # Pega os dados da combo idServidor
    $select = "SELECT idServidor,
                      CONCAT('(',date_format(dtAdmissao,'%d/%m/%Y'),') - ',tbpessoa.nome)
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa) 
                WHERE idCargo = {$idCargo} 
                  AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)";

    # Se for inclusão
    if (empty($id)) {
        $select .= "AND idServidor NOT IN (SELECT idServidor FROM tbvagahistorico WHERE idServidor IS NOT null) ";

        # Pega o último ocupante
        $idUltimo = $vaga->get_idServidorOcupante($idVaga);

        # Se não for vazio coloca no select
        if (!empty($idUltimo)) {

            # Pega a data de demissão desse servidor
            $dtSaida = $pessoal->get_dtSaida($idUltimo);

            if (!empty($dtSaida)) {
                $select .= "AND (dtAdmissao > '" . date_to_bd($dtSaida) . "') ";
            }
        }
    } else { # Se for edição
        $select .= "AND idServidor NOT IN (SELECT idServidor FROM tbvagahistorico WHERE idServidor IS NOT null AND idVagaHistorico <> {$id}) ";

        # Pega os servidores que já ocuparam essa vaga
        $ocupantes = $vaga->get_idServidoresOcupantes($idVaga);

        # Pega o servidor desse concurso nessa vaga
        $ss = "SELECT idServidor
                 FROM tbvagahistorico
                WHERE idVagaHistorico = {$id}";

        $essaVaga = $pessoal->select($ss, false);

        if (!empty($essaVaga)) {

            # Pega a posição no array desse servidor
            $idArray = array_search($essaVaga, $ocupantes);

            # Verifica se é o primeiro: 0
            if ($idArray > 0) {
                # Pega o idServidor anterior
                $idAnterior = $ocupantes[$idArray - 1]["idServidor"];

                # Verifica se não está em branco
                if (!empty($idAnterior)) {
                    # Pega a data de demissão do servidor anterior
                    $dtSaida = $pessoal->get_dtSaida($idAnterior);

                    # Joga no select
                    if (!empty($dtSaida)) {
                        $select .= "AND (dtAdmissao > '" . date_to_bd($dtSaida) . "') ";
                    }
                }
            }
        }
    }

    $select .= ' ORDER BY tbpessoa.nome';
    $docente = $pessoal->select($select);
    array_unshift($docente, array(null, null)); # Adiciona o valor de nulo
    ###############
    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'idVaga',
            'label' => 'Vaga',
            'tipo' => 'combo',
            'array' => $vagas,
            'col' => 9,
            'padrao' => $idVaga,
            'required' => true,
            'size' => 50),
        array('linha' => 1,
            'nome' => 'idConcurso',
            'label' => 'Concurso:',
            'tipo' => 'combo',
            'array' => $concurso,
            'col' => 3,
            'required' => true,
            'autofocus' => true,
            'size' => 30),
        array('nome' => 'idLotacao',
            'label' => 'Laboratório:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $result,
            'size' => 50,
            'col' => 6,
            'title' => 'Em qual setor o servidor está lotado',
            'linha' => 2),
        array('nome' => 'area',
            'label' => 'Área:',
            'tipo' => 'texto',
            'size' => 255,
            'col' => 6,
            'title' => 'Área de atuação.',
            'linha' => 2),
        array('nome' => 'idServidor',
            'label' => 'Docente Empossado:',
            'tipo' => 'combo',
            'array' => $docente,
            'size' => 50,
            'col' => 12,
            'title' => 'Docente ocupante dessa vaga',
            'linha' => 4),
        array('linha' => 5,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))
    ));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    switch ($fase) {
        case "" :
        case "listar" :
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaVagasDocentes.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório");
            $botaoRel->set_url("../grhRelatorios/vagas.historico.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            # Incluir
            $botaoVoltar = new Link("Incluir Concurso", "?fase=editar");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Inclui um concurso nessa vaga.');
            $menu1->add_link($botaoVoltar, "right");
            # Retirada a limitação de inclusão somente quando vaga estivesse disponível
            # a pedido de ana terezinha
//            if ($statusVaga == "Disponível") {
//                $botaoVoltar = new Link("Incluir Concurso", "?fase=editar");
//                $botaoVoltar->set_class('button');
//                $botaoVoltar->set_title('Inclui um concurso nessa vaga.');
//                $menu1->add_link($botaoVoltar, "right");
//            }

            $menu1->show();

            # Exibe dados da vaga
            $vaga->exibeDadosVaga($idVaga);

            # Alerta de laboratório
            $msn = $vaga->verificaProblemaVaga($idVaga);
            callout($msn);

            $objeto->listar();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}