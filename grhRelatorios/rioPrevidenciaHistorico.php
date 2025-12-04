<?php

/**
 * Sistema GRH
 * 
 * Ficha Cadastral
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Verifica qual será o id
if (empty($idServidorPesquisado)) {
    alert("É necessário informar o id do Servidor.");
}

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica a fase do programa
    $fase = get('fase');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Limita a tela
    $grid = new Grid();
    $grid->abreColuna(12);

    switch ($fase) {

        case "":
            # Pega os dados
            # Monta o formulário
            #tituloTable("Histórico Funcional", null, "Para o Rio Previdência");
            tituloTable("Histórico Funcional");
            br();

            # Monta o formulário
            $form = new Form("?fase=relatorio");

            # Nome
            $controle = new Input('nome', 'texto', 'Nome do Servidor:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_readonly(true);
            $controle->set_valor($pessoal->get_nome($idServidorPesquisado));
            $controle->set_title('O nome do servidor.');
            $form->add_item($controle);

            # idFuncional
            $controle = new Input('idFuncional', 'texto', 'IdFuncional:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_readonly(true);
            $controle->set_valor($pessoal->get_idFuncional($idServidorPesquisado));
            $controle->set_title('O idfuncional do servidor.');
            $form->add_item($controle);

            # matrúcula
            $controle = new Input('matricula', 'texto', 'Matrícula:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_readonly(true);
            $controle->set_valor($pessoal->get_matricula($idServidorPesquisado));
            $controle->set_title('A Matrícula do servidor.');
            $form->add_item($controle);

            # vinculo
            $controle = new Input('vinculo', 'texto', 'Vínculo:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_title('O Vínculo do servidor.');
            $form->add_item($controle);

            # Órgão
            $controle = new Input('orgao', 'texto', 'Órgão:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor("UNIVERSIDADE ESTADUAL NORTE FLUMINENSE DARCY RIBEIRO");
            $controle->set_title('O órgão do servidor.');
            $form->add_item($controle);

            # Data de Admissão
            $controle = new Input('dtAdmissao', 'data', 'Data de Admissão:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_readonly(true);
            $controle->set_valor(date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado)));
            $controle->set_title('A data de admissão do servidor.');
            $form->add_item($controle);
            
            # Forma de Ingresso
            $controle = new Input('fIngresso', 'texto', 'Forma de Ingresso:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(8);
            $controle->set_valor("Concurso Público");
            $controle->set_title('A Forma de Ingresso do servidor.');
            $form->add_item($controle);
            
            # Data da investidura
            $controle = new Input('dtInvestidura', 'data', 'Data da Investidura:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_valor();
            $controle->set_title('A data de admissão do servidor.');
            $form->add_item($controle);
            
            # Fundamentação Legal
            $controle = new Input('fLegal', 'texto', 'Fundamentação Legal:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(8);
            $controle->set_valor();
            $controle->set_title('A Fundamentação Legal.');
            $form->add_item($controle);
            
            # Cargo Inicial
            $controle = new Input('cargoInicial', 'texto', 'Cargo Inicial:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor($pessoal->get_nomenclaturaOriginal($idServidorPesquisado));
            $controle->set_title('O cargo inicial do servidor.');
            $form->add_item($controle);
            
            # Cargo Atual
            $controle = new Input('cargoAtual', 'texto', 'Cargo Atual:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor($pessoal->get_tipoCargo($idServidorPesquisado));
            $controle->set_title('O cargo atual do servidor.');
            $form->add_item($controle);
            
            # Data do cargo atuaç
            $controle = new Input('dtCargoAtual', 'data', 'Data do Cargo Atual:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_valor();
            $controle->set_title('A data do Cargo Atual.');
            $form->add_item($controle);
            
            # Fundamentação Legal
            $controle = new Input('fLegalCargo', 'texto', 'Fundamentação Legal da Alteração:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(8);
            $controle->set_valor();
            $controle->set_title('A Fundamentação Legal.');
            $form->add_item($controle);
            
            # Regime Previdenciário
            $controle = new Input('regimePrevidenciario', 'combo', 'Plano Previdenciário:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_array([null, "RIOPREV FINANCEIRO", "RIOPREV PREVIDENCIÁRIO"]);
            $controle->set_valor($pessoal->get_planoPevidenciario($idServidorPesquisado));
            $controle->set_title('Regime/Plano Previdenciário.');
            $form->add_item($controle);
            
            # Acumula Cargo
            $controle = new Input('acumulaCargo', 'simnao', 'Acumula Cargo?:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(2);
            $controle->set_title('Acumula Cargo?');
            $form->add_item($controle);
            
            # Qual Cargo
            $controle = new Input('qualCargo', 'texto', 'Se Sim Qual Cargo:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(7);
            $controle->set_valor();
            $form->add_item($controle);
            
            # Ativo ou inativo
            $controle = new Input('acumulaCargoAtivo', 'combo', 'Ativo ou Inativo?', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(3);
            $controle->set_array([null, "Ativo", "Inativo"]);
            $controle->set_title('Acumula Cargo?');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "relatorio":
            br(7);
            p("Este Relatório Ainda Não Está Pronto","center","f20");
            break;
    }


    $grid->fechaColuna();
    $grid->fechaGrid();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou o Histórico Funcional - Rio Previdência';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}