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
    $classeDependente = new Dependente();

    # Pega o idDependente
    $idDependente = get_session("idDependente");
    $nomeDependente = $classeDependente->get_nome($idDependente);

    # Faz os cálculos dos valores padrão para quando for inclusão    
    if (empty($id)) {
        $dtInicio = date_to_bd($classeDependente->get_dtInicialAuxEducacaoControle($idDependente));
        $dtTermino = date_to_bd($classeDependente->get_dtFinalAuxEducacaoControle($idDependente));
    } else {
        $dtInicio = null;
        $dtTermino = null;
    }

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do comprovante para Auxílio Educação de {$nomeDependente}";
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
    $objeto->set_nome("Comprovantes de Escolaridade para o Auxílio Educação Recebidos");

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorDependentes.php');

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
                                     obs,
                                     idDependente
                                FROM tbauxeducacao
                               WHERE idAuxEducacao = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

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

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

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

    # Extra
    if (!empty($idDependente)) {

        function exibeDadosDependentes($id) {
            $dependente = new Dependente();
            $dados = $dependente->get_dados($id);

            if ($dados["idParentesco"] == 2 OR $dados["idParentesco"] == 8 OR $dados["idParentesco"] == 9) {

                $dtNasc = date_to_php($dados["dtNasc"]);
                $anos21 = get_dataIdade($dtNasc, 21);
                $anos24 = get_dataIdade($dtNasc, 24);
                $idade = idade($dtNasc);

                # Dados do Servidor
                $idPessoa = $dados["idPessoa"];
                $pessoal = new Pessoal();
                $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);
                $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

                $intra = new Intra();
                $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

                $grid = new Grid("center");
                $grid->abreColuna(12);

                $nomeDependente = $dependente->get_nome($id);
                tituloTable("Dependente: {$nomeDependente}");
                br();

                $grid->fechaColuna();
                $grid->abreColuna(4);

                # monta a tabela
                $array = array(
                    array("Data de Nascimento:", $dtNasc),
                    array("Admissão do Servidor:", $dtAdmissao),
                    array("Publicação da Portaria", $dataHistoricaInicial));

                $tabela = new Tabela();
                $tabela->set_titulo("Datas Possíveis do Inicio do Auxílio");
                $tabela->set_conteudo($array);
                $tabela->set_label(["Descrição", "Valor"]);
                $tabela->set_width([50, 50]);
                $tabela->set_align(["left"]);
                $tabela->set_totalRegistro(false);
                $tabela->set_formatacaoCondicional(array(
                    array('coluna' => 1,
                        'valor' => $dependente->get_dtInicialAuxEducacao($id),
                        'operador' => '=',
                        'id' => 'alerta')));

                $tabela->show();

                $grid->fechaColuna();
                $grid->abreColuna(4);

                # monta a tabela
                $array = array(
                    array("Inicia em:", $dependente->get_dtInicialAuxEducacao($id)),
                    array("Somente com Declaração", $anos21),
                    array("Encerra o direito", $anos24));

                $tabela = new Tabela();
                $tabela->set_titulo("Período do Auxílio Educação");
                $tabela->set_conteudo($array);
                $tabela->set_label(["Descrição", "Valor"]);
                $tabela->set_width([50, 50]);
                $tabela->set_align(["left"]);
                $tabela->set_totalRegistro(false);
                $tabela->show();

                $grid->fechaColuna();
                $grid->abreColuna(4);

                # monta a tabela
                $array = array(
                    array("Idade Atual:", "{$idade} anos"),
                    array("Faz 21 anos:", $anos21),
                    array("Faz 24 anos:", $anos24));

                $tabela = new Tabela();
                $tabela->set_titulo("Idades");
                $tabela->set_conteudo($array);
                $tabela->set_label(["Descrição", "Valor"]);
                $tabela->set_width([50, 50]);
                $tabela->set_align(["left"]);
                $tabela->set_totalRegistro(false);
                $tabela->show();

                $grid->fechaColuna();
                $grid->fechaGrid();
            }
        }

        $dependente = new Dependente();
        $dados = $dependente->get_dados($idDependente);

        if ($dados["idParentesco"] == 2 OR $dados["idParentesco"] == 8 OR $dados["idParentesco"] == 9) {

            $objeto->set_rotinaExtraListar("exibeDadosDependentes");
            $objeto->set_rotinaExtraListarParametro($idDependente);
            
            $objeto->set_rotinaExtraEditar("exibeDadosDependentes");
            $objeto->set_rotinaExtraEditarParametro($idDependente);
        }
    }
    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id,"servidorCompAuxEducaExtra.php");
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


            # Exclui o registro
            $objeto->excluir($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}