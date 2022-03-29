<?php

/**
 * Cadastro de Classes e Padrões (Tabela Salarial)
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
    $plano = new PlanoCargos();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    
    # Pega o plano
    $parametroPlano = get_session('parametroPlano',$plano->get_planoAtual());

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de tabela salarial";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
        $voltarLista = "grh.php";
    }else{
        $voltarLista = "cadastroPlanoCargos.php?fase=exibeTabela&id={$parametroPlano}";
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));    

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Tabela Salarial');

    # botão de voltar da lista
    $objeto->set_voltarLista($voltarLista);

    # select da lista
    $objeto->set_selectLista('SELECT idClasse,
                                     tbclasse.nivel,
                                     tbtipocargo.cargo,
                                     faixa,
                                     valor,
                                     idClasse
                                FROM tbclasse JOIN tbplano USING (idPlano)
                                         LEFT JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbplano.idPlano LIKE "%' . $parametroPlano . '%"   
                            ORDER BY 
                              CASE WHEN tbclasse.nivel = "Doutorado" THEN "zzz" END,
                                     tbclasse.nivel,
                                     SUBSTRING(faixa, 1, 1), 
                                     valor');

    # select do edita
    $objeto->set_selectEdita('SELECT nivel,
                                     idTipoCargo,
                                     faixa,
                                     valor,
                                     idPlano
                                FROM tbclasse
                               WHERE idClasse = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    $objeto->set_linkExcluir('?fase=excluir');

    # Parametros da tabela
    $objeto->set_label(array("id", "Nível", "Cargo", "Faixa", "Valor"));
    $objeto->set_width(array(10,20,20,15,15));
    $objeto->set_funcao(array(null, null, null, null, "formataMoeda"));
    $objeto->set_rowspan([1]);
    $objeto->set_grupoCorColuna(1);
    
    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbclasse');

    # Nome do campo id
    $objeto->set_idCampo('idClasse');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de cargo
    $cargo = $pessoal->select('SELECT idTipoCArgo, 
                                      cargo
                                 FROM tbtipocargo
                             ORDER BY cargo desc');
    array_unshift($cargo, array(0, null));

    # Pega os dados da combo de Plano e Cargos
    $result = $pessoal->select('SELECT idPlano, 
                                       numDecreto
                                  FROM tbplano
                                 WHERE idPlano = '.$parametroPlano.'  
                              ORDER BY dtPublicacao desc');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 6,
            'nome' => 'idPlano',
            'label' => 'Plano de Cargos:',
            'tipo' => 'combo',
            'array' => $result,
            'required' => true,
            'autofocus' => true,
            'padrao' => $parametroPlano,
            'size' => 20),
        array('linha' => 1,
            'col' => 4,
            'nome' => 'nivel',
            'label' => 'Nível:',
            'tipo' => 'combo',
            'array' => array(null, "Doutorado", "Superior", "Médio", "Fundamental", "Elementar"),
            'required' => true,
            'size' => 20),
        array('linha' => 3,
            'col' => 6,
            'nome' => 'idTipoCargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'array' => $cargo,
            'required' => true,
            'size' => 20),
        array('linha' => 3,
            'col' => 4,
            'nome' => 'faixa',
            'label' => 'Faixa:',
            'tipo' => 'texto',
            'required' => true,
            'size' => 20),
        array('linha' => 4,
            'col' => 5,
            'nome' => 'valor',
            'label' => 'Valor:',
            'tipo' => 'moeda',
            'required' => true,
            'size' => 10)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    switch ($fase) {
        case "" :
        case "listar" :
            
            function exibePlano($idPlano = null) {
                # Exibe quadro do plano
                $plano = new PlanoCargos();
                $plano->exibeDadosPlano($idPlano);
            }

            $objeto->set_rotinaExtraListar('exibePlano');
            $objeto->set_rotinaExtraListarParametro($parametroPlano);
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