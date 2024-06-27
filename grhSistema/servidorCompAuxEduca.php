<?php

/**
 * Cadastro de Comprovantes de Escolaridade para o Auxílio Educação
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    $intra = new Intra();
    $dependente = new Dependente();
    $aux = new AuxilioEducacao();

    # Pega a Origem
    $origem = get_session("origem");

    # Pega o idDependente
    $idDependente = get_session("idDependente");
    $nomeDependente = $dependente->get_nome($idDependente);
    $cpfDependente = $dependente->get_cpf($idDependente);

    # Faz os cálculos dos valores padrão para quando for inclusão    
    if (empty($id)) {
        $dtInicio = $aux->get_dataInicialFormulario($idDependente);
        $dtTermino = $aux->get_dataFinalFormulario($idDependente);
        $dataFinalCobranca = $aux->get_data25AnosMenos1Dia($idDependente);

        # Verifica se a data de inicio é maior que a de termino do direito
        if (dataMenor($dtInicio, $dataFinalCobranca) == $dataFinalCobranca) {
            $dtInicio = null;
            $dtTermino = null;
        } else {
            $dtInicio = date_to_bd($dtInicio);
            $dtTermino = date_to_bd($dtTermino);
        }
    } else {
        $dtInicio = null;
        $dtTermino = null;
    }

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do comprovante para Auxílio Educação<br/>de {$nomeDependente}";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome("Dados Cadastrados");

    # botão de voltar da lista
    if (empty($origem)) {
        $objeto->set_voltarLista('servidorDependentes.php');
    } else {
        $objeto->set_voltarLista($origem);
    }

    # select da lista
    $objeto->set_selectLista("SELECT year(dtInicio),
                                     dtInicio,                           
                                     dtTermino,
                                     idAuxEducacao,
                                     obs,
                                     idAuxEducacao
                                FROM tbauxeducacao
                               WHERE idDependente={$idDependente}
                            ORDER BY dtInicio");

    # select do edita
    $objeto->set_selectEdita("SELECT dtInicio,
                                     dtTermino,
                                     estudou,
                                     obs,
                                     idDependente
                                FROM tbauxeducacao
                               WHERE idAuxEducacao = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # subtitulo
    #$objeto->set_subtitulo($cpfDependente);
    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Ano", "Data Início", "Data Término", "Comprovante", " Observação"]);
    $objeto->set_width([10, 10, 10, 10, 50]);
    $objeto->set_align(["center", "center", "center", "center", "left"]);
    $objeto->set_funcao([null, "date_to_php", "date_to_php"]);

    $objeto->set_classe([null, null, null, "AuxilioEducacao"]);
    $objeto->set_metodo([null, null, null, "exibeComprovante"]);

    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbauxeducacao');

    # Nome do campo id
    $objeto->set_idCampo('idAuxEducacao');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'dtInicio',
            'label' => 'Data de Início:',
            'tipo' => 'data',
            'size' => 20,
            'required' => true,
            'autofocus' => true,
            'padrao' => $dtInicio,
            'col' => 4,
            'linha' => 1),
        array('nome' => 'dtTermino',
            'label' => 'Data de Término:',
            'tipo' => 'data',
            'size' => 20,
            'required' => true,
            'padrao' => $dtTermino,
            'col' => 4,
            'linha' => 1),
        array('linha' => 1,
            'col' => 2,
            'nome' => 'estudou',
            'title' => 'Estudou no Período',
            'label' => 'Estudou?',
            'tipo' => 'combo',
            'array' => array("Sim", "Não"),
            'size' => 20),
        array('nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5),
            'col' => 12,
            'title' => 'Descrição do Elogio ou Advertência.',
            'linha' => 2),
        array('nome' => 'idDependente',
            'label' => 'idDependente:',
            'tipo' => 'hidden',
            'padrao' => $idDependente,
            'size' => 5,
            'linha' => 4)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Dados da rotina de Upload
    $pasta = PASTA_COMP_AUX_EDUCA;
    $nome = "Comprovante";
    $tabela = "tbauxeducacao";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("servidorCompAuxEducaUpload.php?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload do {$nome}");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    # btn edita Dependente
    $botao2 = new Link("Edita Dependente", "servidorDependentes.php?fase=editar&id={$idDependente}");
    $botao2->set_class('button');
    $botao2->set_title('Edita os Dependentes');
    $objeto->set_botaoListarExtra([$botao2]);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :

            function exibeDadosCompAuxEduc($id) {
                # Pega os dados do dependente
                $dependente = new Dependente();
                $dados = $dependente->get_dados($id);
                $cpfDependente = $dependente->get_cpf($id);

                $aux = new AuxilioEducacao();

                # Pega os parentescos com direito au auxEducação
                $tipos = $aux->get_arrayTipoParentescoAuxEduca();

                # Verifica se tem direito
                if (in_array($dados["idParentesco"], $tipos)) {

                    # Pega os dados do dependente
                    $dtNasc = date_to_php($dados["dtNasc"]);
                    $idade = idade($dtNasc);

                    # Pega as datas limites
                    $dataInicioCobranca = $aux->get_data21Anos($id);
                    $dataFinalCobranca = $aux->get_data25AnosMenos1Dia($id);

                    # Dados do Servidor
                    $idPessoa = $dados["idPessoa"];
                    $pessoal = new Pessoal();

                    $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);
                    $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

                    $intra = new Intra();
                    $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

                    #########################################
                    # Define período SEM comprovação
                    if (dataMenor($dataHistoricaInicial, $dataInicioCobranca) == $dataInicioCobranca) {
                        $scomp = "Já tinha mais de 21 anos<br/>quando adquiriu o direito!";
                        $ccomp = "{$dataHistoricaInicial} a {$dataFinalCobranca}";
                    } else {
                        $scomp = "{$aux->get_dataInicialDireito($id)} a {$dataInicioCobranca}";
                        $ccomp = "{$dataInicioCobranca} a {$dataFinalCobranca}";
                    }

                    $array = array(
                        array(
                            $scomp,
                            $dataInicioCobranca,
                            $ccomp,
                            $dataFinalCobranca,
                            get_dataIdade(date_to_php($dados["dtNasc"]), $aux->get_idadeFinalLei())
                        )
                    );

                    $tabela = new Tabela();
                    $tabela->set_titulo($dados["nome"]);
                    $tabela->set_subtitulo($dados["cpf"]);
                    $tabela->set_conteudo($array);
                    $tabela->set_label([
                        "Período SEM Comprovação",
                        "{$aux->get_idadeInicialLei()} anos:",
                        "Período COM Comprovação",
                        "Encerra o Direito:",
                        "{$aux->get_idadeFinalLei()} anos:"
                    ]);
                    $tabela->set_width([27, 15, 27, 15, 15]);
                    #$tabela->set_align(["left"]);
                    $tabela->set_totalRegistro(false);
                    $tabela->set_formatacaoCondicional(array(
                        array('coluna' => 0,
                            'valor' => $scomp,
                            'operador' => '=',
                            'id' => 'alerta')));
                    $tabela->show();
                }
            }

            # Exibe dados do Dependente
            $objeto->set_rotinaExtraAntesTabela("exibeDadosCompAuxEduc");
            $objeto->set_rotinaExtraAntesTabelaParametro($idDependente);

            # cria a área lateral
            $objeto->set_objetoLateralListar("AuxilioEducacao");
            $objeto->set_objetoLateralListarMetodo("exibeQuadroLista");
            $objeto->set_objetoLateralListarParametro($idDependente);

        case "editar" :

            # cria a área lateral
            $objeto->set_objetoLateralEditar("AuxilioEducacao");
            $objeto->set_objetoLateralEditarMetodo("exibeQuadroEdita");
            $objeto->set_objetoLateralEditarParametro($idDependente);

            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorCompAuxEducaExtra.php");
            break;

        case "excluir" :
            # Verifica se tem arquivo vinculado
            if (file_exists("{$pasta}{$id}.pdf")) {

                # Verifica se existe a pasta dos arquivos apagados
                if (!file_exists("{$pasta}_apagados/") || !is_dir("{$pasta}_apagados/")) {
                    mkdir("{$pasta}_apagados/", 0755);
                }

                # Move o arquivo para a pasta dos arquivos apagados
                rename("{$pasta}{$id}.pdf", "{$pasta}_apagados/{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf");
            }

            $classeDependente = new Dependente();
            $classeAuxEducacao = new AuxilioEducacao();

            $dados = $classeAuxEducacao->get_dados($id);
            $dtInicio = date_to_php($dados['dtInicio']);
            $dtTermino = date_to_php($dados['dtTermino']);

            $objeto->excluir($id, "Excluiu o comprovante de Escolaridade<br/>{$dtInicio} - {$dtTermino}<br/>" . $classeDependente->get_nome($idDependente));
            break;

            # Exclui o registro
            $objeto->excluir($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}