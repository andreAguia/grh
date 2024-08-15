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
        $atividade = "Visualizou a discriminação de vagas de Administrativo e Técnico";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega os parâmetros
    $parametroCargo = post('parametroCargo', get_session('parametroCargo', 6));

    # Joga os parâmetros par as sessions
    set_session('parametroCargo', $parametroCargo);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    $menu = new MenuBar();

    # Voltar
    $botao = new Link("Voltar", "areaVagasAdm.php");
    $botao->set_class('button');
    $botao->set_title('Voltar a página anterior');
    $botao->set_accessKey('V');
    $menu->add_link($botao, "left");
    $menu->show();

    $texto = "Observações Importantes:<br/>"
            . " - Aqui temos todos os servidores concursados que ocuparam ou acupam as vagas.<br/>"
            . " - Segundo a informação inserida no campo de servidor que ocupava a vaga anteriormente.";
    callout($texto);

    ################################################################
    # Formulário de Pesquisa
    $form = new Form('?');

    # Cargo
    $result = $pessoal->select('SELECT idTipoCargo,
                                       CONCAT(sigla," - ",cargo)
                                  FROM tbtipocargo
                                 WHERE tipo = "Adm/Tec" 
                              ORDER BY cargo');

    $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Cargol');
    $controle->set_array($result);
    $controle->set_optgroup(true);
    $controle->set_valor($parametroCargo);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(6);
    $form->add_item($controle);
    $form->show();

    ################################################################

    switch ($fase) {
        case "":
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;

        ################################################################

        case "exibeLista":

            # Monta o select
            $select = "SELECT idServidor,
                              idServidor,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                        WHERE (idPerfil = 1 OR idPerfil = 4)                       
                          AND (idServidorOcupanteAnterior is null OR idServidorOcupanteAnterior = 0)
                          AND tbtipocargo.tipo = 'Adm/Tec'
                          AND tbtipocargo.idTipoCargo = {$parametroCargo}
                     ORDER BY dtAdmissao, tbpessoa.nome";

            # Pega os dados
            $row = $pessoal->select($select);

            $tipocargo = new TipoCargo();

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Vagas Discriminadas");
            $tabela->set_subtitulo($tipocargo->get_cargo($parametroCargo));
            $tabela->set_conteudo($row);
            $tabela->set_label(["Primeiro na Vaga", "Vaga Posterior", "Vaga Posterior"]);
            $tabela->set_width([33, 33, 33]);
            $tabela->set_align(["left", "left", "left"]);
            $tabela->set_numeroOrdem(true);

            $tabela->set_classe(["Concurso", "Concurso", "Concurso", "Concurso"]);
            $tabela->set_metodo(["exibeServidorEConcurso", "exibeOcupantePosteriorComBotao", "exibeOcupantePosteriorPosteriorComBotao"]);
            $tabela->show();
            break;
        
        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            set_session('idServidorPesquisado', $id);
            set_session('origem', "areaVagasDiscriminadas.php");
            loadPage('servidorConcurso.php');
            break;
        
        ################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}