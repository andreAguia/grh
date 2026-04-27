<?php

/**
 * Cadastro Geral de Candidatos
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
    $objeto->set_nome('Dados do Candidato');

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
                           resultado,
                           obs,
                           dtNascimento,
                           cpf,
                           identidade,                           
                           nomeMae,
                           nomePai,
                           email,
                           telefone,
                           celular,
                           endereco,
                           num,
                           complemento,
                           bairro,
                           cep,
                           cidade,
                           estado
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
            'autofocus' => true,
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
            'size' => 30),
        array(
            'linha' => 5,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array(
            'linha' => 6,
            'nome' => 'dtNascimento',
            'label' => 'Data de Nascimento:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 4,
            'title' => 'Data de Nascimento.'),
        array(
            'linha' => 6,
            'col' => 4,
            'nome' => 'cpf',
            'label' => 'CPF:',
            'tipo' => 'cpf',
            'required' => true,
            'title' => 'CPF do servidor',
            'size' => 20),
        array(
            'linha' => 6,
            'nome' => 'identidade',
            'label' => 'Identidade:',
            'tipo' => 'texto',
            'title' => 'Identidade do Candidato',
            'col' => 4,
            'size' => 50),
        array(
            'linha' => 3,
            'nome' => 'nomeMae',
            'label' => 'Nome da Mãe:',
            'tipo' => 'texto',
            'col' => 6,
            'size' => 200),
        array(
            'linha' => 3,
            'nome' => 'nomePai',
            'label' => 'Nome do Pai:',
            'tipo' => 'texto',
            'col' => 6,
            'size' => 200),
        array(
            'linha' => 5,
            'nome' => 'email',
            'label' => 'Email:',
            'tipo' => 'email',
            'col' => 4,
            'size' => 50),
        array(
            'linha' => 5,
            'nome' => 'telefone',
            'label' => 'telefone:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 50),
        array(
            'linha' => 5,
            'nome' => 'celular',
            'label' => 'celular:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 50),
        array(
            'linha' => 6,
            'nome' => 'endereco',
            'label' => 'Endereço:',
            'tipo' => 'texto',
            'col' => 9,
            'size' => 250),
        array(
            'linha' => 6,
            'nome' => 'num',
            'label' => 'Número:',
            'tipo' => 'texto',
            'col' => 3,
            'size' => 50),
        array(
            'linha' => 7,
            'nome' => 'complemento',
            'label' => 'Complemento:',
            'tipo' => 'texto',
            'col' => 6,
            'size' => 250),
        array(
            'linha' => 7,
            'nome' => 'bairro',
            'label' => 'Bairro:',
            'tipo' => 'texto',
            'col' => 6,
            'size' => 250),
        array(
            'linha' => 8,
            'nome' => 'cep',
            'label' => 'Cep:',
            'tipo' => 'texto',
            'col' => 3,
            'size' => 30),
        array(
            'linha' => 8,
            'nome' => 'cidade',
            'label' => 'Cidade:',
            'tipo' => 'texto',
            'col' => 7,
            'size' => 100),
        array(
            'linha' => 8,
            'nome' => 'estado',
            'label' => 'Estado:',
            'tipo' => 'texto',
            'col' => 2,
            'size' => 5),
    ));

    # Botão de Upload
    $botao = new Button("Ofício Perícia");
    $botao->set_url("?fase=oficioPericia");
    $botao->set_title("Ofício de encaminhamento do candidato à perícia");
    $botao->set_target("_blank");

    $objeto->set_botaoEditarExtra([$botao]);

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

        ################################################################   
        
        # Ofício: Encaminhamento do Candidato à Perícia
        case "oficioPericia" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();
            
            # Título
            tituloTable("Ofício: Encaminhamento do Candidato à Perícia");
            br();
            

            # Monta o formulário
            $form = new Form("../grhRelatorios/oficio.candidato.pericia.php");

            # Número do Ofício
            $controle = new Input('numero', 'texto', 'Número do Ofício:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor();
            $controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_title('O número do Ofício.');
            $form->add_item($controle);
            
            # Ano do Ofício
            $controle = new Input('ano', 'texto', 'Ano:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor(date("Y"));
            $controle->set_required(true);
            $controle->set_title('O ano do Ofício.');
            $form->add_item($controle);
            
            # Data do Exame
            $controle = new Input('data', 'date', 'Data do Exame:', 1);
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_valor();
            $controle->set_required(true);
            $controle->set_title('A data do exame.');
            $form->add_item($controle);
            
            # Hora do Exame
            $controle = new Input('hora', 'hora', 'Hora do Exame:', 1);
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_col(3);
            $controle->set_valor();
            $controle->set_required(true);
            $controle->set_title('A hora do exame.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}