<?php

/**
 * Cadastro Geral de Candidatos - Dados da Prova
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica a fase do programa
    $fase = get('fase', 'editar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do candidato";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idCandidatoPesquisado);
    }

    # Verifica de onde veio
    $origem = get_session("origem");

    # Parametros    
    $idCandidatoPesquisado = get_session("idCandidatoPesquisado");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Dados da Prova');

    # select do edita
    $selectEdita = "SELECT inscricao,
                           nome,
                           idfuncional,
                           idLotacao, 
                           classifAc, 
                           classifPcd,
                           classifNi,
                           classifHipo,
                           cargo,
                           tipoDeficiencia,
                           notaFinal,
                           resultado                
                      FROM tbcandidato
                     WHERE idCandidato = {$idCandidatoPesquisado}";

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    $objeto->set_selectEdita($selectEdita);

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('cadastroCandidatosAdm2025.php');
    $objeto->set_voltarForm('cadastroCandidatosAdm2025.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcandidato');

    # Nome do campo id
    $objeto->set_idCampo('idCandidato');

    # Pega os dados da combo cargo do concurso
    $cargo = $pessoal->select('SELECT DISTINCT cargo, cargo
                                 FROM tbcandidato
                             ORDER BY cargo');

    array_unshift($cargo, [null, null]);

    # Pega os dados da combo lotacao
    $selectLotacao = 'SELECT idlotacao, 
                             concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")),
                             tblotacao.DIR 
                        FROM tblotacao 
                        WHERE ativo = 1
                        ORDER BY ativo desc, 2';

    $result = $pessoal->select($selectLotacao);
    array_unshift($result, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array(
            'linha' => 1,
            'nome' => 'inscricao',
            'label' => 'Inscrição:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 3,
            'title' => 'Inscrição do Candidato.'),
        array(
            'linha' => 1,
            'nome' => 'nome',
            'label' => 'Nome:',
            'tipo' => 'texto',
            'title' => 'Nome do Candidato',
            'col' => 9,
            'size' => 200),
        array(
            'linha' => 2,
            'nome' => 'idfuncional',
            'label' => 'Id Funcional:',
            'tipo' => 'texto',
            'autofocus' => true,
            'size' => 15,
            'col' => 3,
            'title' => 'IdFuncional Quando já possui.'),
        array('nome' => 'idLotacao',
            'label' => 'Previsão de Lotacão:',
            'tipo' => 'combo',
            'optgroup' => true,
            'array' => $result,
            'size' => 20,
            'col' => 9,
            'title' => 'Em qual setor o candidato poderá ser lotado',
            'linha' => 2),
        array(
            'linha' => 3,
            'nome' => 'classifAc',
            'label' => 'Classificação Ampla Concorrência:',
            'tipo' => 'numero',
            'col' => 3,
            'size' => 5),
        array(
            'linha' => 3,
            'nome' => 'classifPcd',
            'label' => 'Classificação PCD:',
            'tipo' => 'numero',
            'col' => 3,
            'size' => 5),
        array(
            'linha' => 3,
            'nome' => 'classifNi',
            'label' => 'Classificação Negros e Indios:',
            'tipo' => 'numero',
            'col' => 3,
            'size' => 5),
        array(
            'linha' => 3,
            'nome' => 'classifHipo',
            'label' => 'Classificação Hipo:',
            'tipo' => 'numero',
            'col' => 3,
            'size' => 5),
        array(
            'linha' => 4,
            'nome' => 'cargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'array' => $cargo,
            'title' => 'Cargo',
            'col' => 12,
            'size' => 200),
        array(
            'linha' => 4,
            'nome' => 'tipoDeficiencia',
            'label' => 'Tipo de Deficiência:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 100),
        array(
            'linha' => 4,
            'nome' => 'notaFinal',
            'label' => 'Nota Final:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 10),
        array(
            'linha' => 4,
            'nome' => 'resultado',
            'label' => 'Resultado:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 30)
    ));
    
    # Botões
    $botao1 = new Link("Dados da Prova", "cadastroCandidatosAdm2025EditaProva.php");
    $botao1->set_class('hollow button');

    $botao2 = new Link("Dados Pessoais", "cadastroCandidatosAdm2025EditaPessoais.php");
    $botao2->set_class('button');

    $objeto->set_botaoEditarExtra([$botao2]);

    # Log
    $objeto->set_idUsuario($idUsuario);
    ################################################################

    switch ($fase) {
        case "ver" :
        case "editar" :
            $objeto->$fase($idCandidatoPesquisado);
            break;

        case "gravar" :
            $objeto->gravar($idCandidatoPesquisado);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}