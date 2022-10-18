<?php

/**
 * Cadastro de Dependentes de um servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Parentes";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    $jscript = '$("#parentesco").change(function(){
                    var t1 = $("#parentesco").val();
                    switch (t1) {
                        case "2":
                        case "8":
                        case "9":
                            $("#labelauxCreche").show();
                            $("#auxCreche").show();
                            
                            var t2 = $("#auxCreche").val();
                            switch (t2) {
                                case "Sim":
                                    $("#div7").show();
                                    break;

                                default:
                                    $("#div7").hide();
                                    break;
                            }
                            break;
                            
                        default:
                            $("#labelauxCreche").hide();
                            $("#auxCreche").hide();
                            $("#div7").hide();
                            break;
                    }
                    
                });
                
                $("#auxCreche").change(function(){
                    var t2 = $("#auxCreche").val();
                    switch (t2) {
                        case "Sim":
                            $("#div7").show();
                            break;
                            
                        default:
                            $("#div7").hide();
                            break;
                    }
                    
                });
                
                var t1 = $("#parentesco").val();
                switch (t1) {
                    case "2":
                    case "8":
                    case "9":
                        $("#labelauxCreche").show();
                        $("#auxCreche").show();
                        break;

                    default:
                        $("#labelauxCreche").hide();
                        $("#auxCreche").hide();
                        $("#div7").hide();
                        break;
                }
                
                var t2 = $("#auxCreche").val();
                switch (t2) {
                    case "Sim":
                        $("#div7").show();
                        break;

                    default:
                        $("#div7").hide();
                        break;
                }
                
                    
                    ';

    # Começa uma nova página
    $page = new Page();
    $page->set_ready($jscript);
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    #$objeto->set_rotinaExtraEditar("exibeColloutDependente");
    #$objeto->set_rotinaExtraEditarParametro($idServidorPesquisado);     
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Parentes');

    # Define nome do Form
    $objeto->set_id('Dependente');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT nome,
                                     cpf,
                                     dtNasc,
                                     tbparentesco.parentesco,
                                     CASE sexo
                                        WHEN "F" THEN "Feminino"
                                        WHEN "M" THEN "Masculino"
                                     end,
                                     TIMESTAMPDIFF(YEAR,dtNasc,CURDATE()),
                                     dependente,
                                     auxCreche,
                                     dtTermino,
                                     idDependente
                                FROM tbdependente LEFT JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
                          WHERE idPessoa=' . $idPessoa . '
                       ORDER BY dtNasc desc');

    # select do edita
    $objeto->set_selectEdita('SELECT nome,
                                     dtNasc,
                                     CPF,
                                     parentesco,
                                     sexo,
                                     dependente,
                                     auxCreche,
                                     dtTermino,
                                     processo,                                 
                                     ciExclusao,  
                                     obs,
                                     idPessoa
                                FROM tbdependente
                               WHERE idDependente = ' . $id);

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
    $objeto->set_label(["Nome", "CPF", "Nascimento", "Parentesco", "Sexo", "Idade", "Dependente no IR", "Auxílio Creche", "Término do Aux. Creche"]);
    #$objeto->set_width(array(20,10,10,10,10,10,10,10));	
    $objeto->set_align(["left"]);
    $objeto->set_funcao([null, null, "date_to_php", null, null, null, null, null, "date_to_php"]);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbdependente');

    # Nome do campo id
    $objeto->set_idCampo('idDependente');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo parentesco
    $parentesco = new Pessoal();
    $result = $parentesco->select('SELECT idParentesco, 
                                          parentesco
                                     FROM tbparentesco
                                 ORDER BY idParentesco');
    array_push($result, array(null, null)); # Adiciona o valor de nulo
    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'nome',
            'label' => 'Nome do Parente:',
            'tipo' => 'texto',
            'size' => 50,
            'required' => true,
            'plm' => true,
            'autofocus' => true,
            'title' => 'Nome do Parente.',
            'col' => 6,
            'linha' => 1),
        array('nome' => 'dtNasc',
            'label' => 'Data de Nascimento:',
            'tipo' => 'data',
            'size' => 12,
            'maxLength' => 20,
            'required' => true,
            'title' => 'Data de Nascimento.',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'CPF',
            'label' => 'CPF (quando houver):',
            'tipo' => 'cpf',
            'size' => 20,
            'title' => 'CPF do Parente',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'parentesco',
            'label' => 'Parentesco:',
            'tipo' => 'combo',
            'array' => $result,
            'required' => true,
            'size' => 20,
            'title' => 'Parentesco do Parente',
            'col' => 3,
            'linha' => 2),
        array('nome' => 'sexo',
            'label' => 'Sexo:',
            'tipo' => 'combo',
            'array' => array("", "M", "F"),
            'required' => true,
            'size' => 20,
            'col' => 2,
            'title' => 'Gênero do Parente.',
            'linha' => 2),
        array('nome' => 'dependente',
            'label' => 'Dependente no IR:',
            'tipo' => 'combo',
            'array' => array("Não", "Sim"),
            'required' => true,
            'size' => 20,
            'col' => 2,
            'title' => 'Dependente no Imposto de Renda.',
            'linha' => 2),
        array('nome' => 'auxCreche',
            'label' => 'Auxílio Creche:',
            'tipo' => 'combo',
            'array' => array("Não", "Sim"),
            'size' => 20,
            'title' => 'Dependente tem Auxílio Creche.',
            'col' => 2,
            'linha' => 2),
        array('nome' => 'dtTermino',
            'label' => 'Data de Término:',
            'tipo' => 'data',
            'size' => 12,
            'fieldset' => 'Auxílio Creche',
            'title' => 'Data de Termino do Auxílio Creche.',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'size' => 20,
            'title' => 'Processo de exclusão do auxílio Creche.',
            'col' => 4,
            'linha' => 3),
        array('nome' => 'ciExclusao',
            'label' => 'Documento de Exclusão:',
            'tipo' => 'texto',
            'size' => 30,
            'title' => 'Documento de Exclusão do auxílio Creche.',
            'col' => 4,
            'linha' => 3),
        array('linha' => 4,
            'fieldset' => 'fecha',
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idPessoa',
            'label' => 'idPessoa:',
            'tipo' => 'hidden',
            'padrao' => $idPessoa,
            'size' => 5,
            'title' => 'idPessoa',
            'linha' => 5)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Parente");
    $botaoRel->set_url("../grhRelatorios/servidorDependentes.php");
    $botaoRel->set_target("_blank");
    $objeto->set_botaoListarExtra(array($botaoRel));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Paginação
    #$objeto->set_paginacao(true);
    #$objeto->set_paginacaoInicial($paginacao);
    #$objeto->set_paginacaoItens(20);
    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :
            $objeto->editar($id);
            break;

        case "gravar" :
            $objeto->gravar($id, 'servidorDependentesExtra.php');
            break;

        case "excluir" :
            $objeto->excluir($id);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}