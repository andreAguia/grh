<?php

/**
 * Cadastro de Feriados
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de feriados";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    
    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Feriados');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php?fase=acumulacao');

    # select da lista
    $objeto->set_selectLista("SELECT anoReferencia,
                                      dtEntrega,
                                      idServidor,
                                      processo,
                                      idEntrega
                                 FROM tbacumulacao_entrega
                                WHERE anoReferencia LIKE '%$parametroAno%'
                             ORDER BY anoReferencia, dtEntrega");

    # select do edita
    $objeto->set_selectEdita('SELECT anoReferencia,
                                     dtEntrega,
                                     processo,
                                     idServidor                                     
                                FROM tbacumulacao_entrega
                               WHERE idEntrega = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Ano", "Data", "Servidor", "Processo"));
    #$objeto->set_width(array(10, 20, 60));
    $objeto->set_align(array("center", "center", "left"));
    $objeto->set_funcao(array(null, "date_to_php"));

    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbacumulacao_entrega');

    # Nome do campo id
    $objeto->set_idCampo('idEntrega');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # servidor
    $servidor = $pessoal->select('SELECT idServidor, tbpessoa.nome
                                    FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                   WHERE idPerfil = 1
                                ORDER BY tbpessoa.nome');
    array_unshift($servidor, [null, null]);

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoAtual + 2, $anoInicial, "d");

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha'     => 1,
            'nome'      => 'anoReferencia',
            'label'     => 'Ano Referência:',
            'tipo'      => 'combo',
            'array'     => $anoExercicio,
            'valor'     => $parametroAno,
            'required'  => true,
            "autofocus" => true,
            'col'       => 4,
            'size'      => 5),
        array('nome'     => 'dtEntrega',
            'label'    => 'Data da Entrega:',
            'tipo'     => 'date',
            'size'     => 20,
            'required' => true,
            'title'    => 'Data da entega',
            'col'      => 4,
            'linha'    => 1),
        array('linha'    => 1,
            'nome'     => 'processo',
            'label'    => 'Processo:',
            'tipo'     => 'sei',
            'required' => true,
            'col'      => 4,
            'size'     => 50),
        array(
            'linha'    => 2,
            'nome'     => 'idServidor',
            'label'    => 'Servidor:',
            'tipo'     => 'combo',
            'array'    => $servidor,
            'col'      => 12,
            'required' => true,
            'size'     => 30)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    switch ($fase) {

        case "" :
        case "listar" :
            /*
             *  Formulário de Pesquisa
             */
            $form = new Form('?');

            # AnoReferencia
            $comboAno = $pessoal->select('SELECT DISTINCT anoReferencia
                                            FROM tbacumulacao_entrega
                                         ORDER BY anoReferencia');
var_dump($comboAno);
            # Ano
            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(5);
            $controle->set_title('Ano do início do serviço');
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_array($comboAno[0]);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            $objeto->set_formExtra($form);
            $objeto->listar();
            break;

        case "editar" :
        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}