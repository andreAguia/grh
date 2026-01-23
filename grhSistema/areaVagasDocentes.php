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
    $vaga = new Vaga();
    $concurso = new Concurso();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de vagas de docentes";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega os parâmetros
    $parametroCentro = post('parametroCentro', get_session('parametroCentro', "CCH"));
    $parametroLab = post('parametroLab', get_session('parametroLab', "*"));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', "Disponível"));
    $parametroCargo = post('parametroCargo', get_session('parametroCargo', 128));
    $parametroNome = post('parametroNome', get_session('parametroNome'));
    $parametroVaga = post('parametroVaga', get_session('parametroVaga'));

    if ($parametroCentro == "*") {
        $parametroCentro = "CBB";
    }

    # Joga os parâmetros par as sessions
    set_session('parametroCentro', $parametroCentro);
    set_session('parametroLab', $parametroLab);
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroCargo', $parametroCargo);
    set_session('parametroNome', $parametroNome);
    set_session('parametroVaga', $parametroVaga);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    switch ($fase) {
        case "":
            # Zera a origem
            set_session('origem');
            
            /*
             * Menu
             */
            $menu = new MenuBar();

            # Voltar
            $botao = new Link("Voltar", "areaConcursoProf.php?origem=vagas");

            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('V');
            $menu->add_link($botao, "left");

            # Incluir
            $botaoInserir = new Button("Incluir Nova Vaga", "cadastroVagas.php?fase=editar");
            $botaoInserir->set_title("Incluir");
            $menu->add_link($botaoInserir, "right");

            # Por nome
            $botao = new Link("por Nome", "?fase=porNome");
            $botao->set_class('button');
            $botao->set_title('Pesquisar por Nome do Professor');
            $menu->add_link($botao, "right");

            # Por vaga
            $botao = new Link("por Vaga", "?fase=porVaga");
            $botao->set_class('button');
            $botao->set_title('Pesquisar por Vaga do Professor');
            $menu->add_link($botao, "right");

            # Dados Gerais
            $botao = new Link("Dados Gerais", "?fase=geral");
            $botao->set_class('button');
            $botao->set_title('ResumoGeral');
            $menu->add_link($botao, "right");

            $menu->show();

            $grid->fechaColuna();
            $grid->abreColuna(8);

            /*
             *  Formulário de Pesquisa
             */

            tituloTable("Área de Vagas de Concurso para Cargos de Professores");
            br();
            $form = new Form('?');

            # Centros Possíveis
            $centros = array("CBB", "CCH", "CCT", "CCTA");

            $controle = new Input('parametroCentro', 'combo', 'Centro:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Centro');
            $controle->set_array($centros);
            $controle->set_valor($parametroCentro);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(4);
            $form->add_item($controle);

            # Situação
            $situacao = array("Todas", "Disponível", "Ocupada");

            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($situacao);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # Cargo
            $cargos = [
                [128, "Professor Associado"],
                [129, "Professor Titular"],
            ];

            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($cargos);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # Laboratório
            $result = $pessoal->select("SELECT idlotacao, concat(IFnull(tblotacao.GER,''),' - ',IFnull(tblotacao.nome,'')) lotacao
                                                      FROM tblotacao
                                                     WHERE ativo
                                                     AND tblotacao.DIR = '{$parametroCentro}' 
                                                  ORDER BY 2");
            array_unshift($result, array("*", 'Todos'));

            $controle = new Input('parametroLab', 'combo', 'Laboratório de Origem:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Laboratório');
            $controle->set_array($result);
            $controle->set_valor($parametroLab);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();

            $grid->abreColuna(4);
            $vaga->exibeVagasGerais($parametroCentro);
            $grid->fechaColuna();
            $grid->abreColuna(12);

            $vagaDocente = new VagaDocentes();
            $vagaDocente->setCargo($parametroCargo);
            $vagaDocente->setCentro($parametroCentro);
            $vagaDocente->setLaboratorio($parametroLab);
            $vagaDocente->setSituacao($parametroSituacao);
            $vagaDocente->show();
            break;

        case "editarConcurso" :
            set_session('idVaga', $id);
            loadPage("cadastroVagaHistorico.php");
            break;

        case "geral":

            /*
             * Geral
             */

            # Menu
            $menu = new MenuBar();

            # Voltar
            $botao = new Link("Voltar", "?");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('V');
            $menu->add_link($botao, "left");

            $menu->show();

            titulotable("Dados Gerais");
            br();

            $grid->fechaColuna();
            $grid->abreColuna(3);

            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Docentes Ativos com Vaga', '../grhRelatorios/vagas.professores.ativos.php');
            $menu->add_item('linkWindow', 'Docentes Inativos com Vaga', '../grhRelatorios/vagas.professores.inativos.php');
            $menu->add_item('linkWindow', 'Vagas Geral', '../grhRelatorios/vagas.geral.php');
            $menu->add_item('linkWindow', 'Vagas Resumo', '../grhRelatorios/vagas.resumo.php');
            $menu->add_item('linkWindow', 'Vagas Resumo Por Lotação', '../grhRelatorios/vagas.resumo.lotacao.php');
            $menu->show();

            $grid->fechaColuna();
            $grid->abreColuna(9);
            $vaga->exibeDashboard();
            break;

        case "porNome":

            # Menu
            $menu = new MenuBar();

            # Voltar
            $botao = new Link("Voltar", "?");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('V');
            $menu->add_link($botao, "left");

            $menu->show();

            $form = new Form('?fase=porNome');

            $controle = new Input('parametroNome', 'texto', 'Nome Ou Número da Vaga:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Nome Ou Número da Vaga');
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            $select = 'SELECT tbvaga.idVaga,
                              tbservidor.idFuncional,
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao,
                              tbperfil.nome,
                              tbservidor.dtAdmissao,
                              tbservidor.dtDemissao,                              
                              tbvaga.idVaga
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                         LEFT JOIN tbvagahistorico USING (idServidor)
                                              JOIN tbvaga USING (idVaga)
                        WHERE (tbpessoa.nome like "%' . $parametroNome . '%"  OR idVaga = "' . $parametroNome . '")
                          AND (tbservidor.idCargo = 128 OR tbservidor.idCargo = 129)
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     ORDER BY tbservidor.dtAdmissao DESC';

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Cadastro de Vagas');
            $tabela->set_subtitulo('Por Nome de Servidor');
            $tabela->set_label(["Vaga", "IdFuncional", 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Saída', 'Editar']);
            $tabela->set_align(["center", "center", "left", "left", "left"]);
            $tabela->set_funcao(["ressaltaVaga", null, null, null, null, null, "date_to_php", "date_to_php"]);

            $tabela->set_classe([null, null, null, "pessoal"]);
            $tabela->set_metodo([null, null, null, "get_Cargo"]);

            # Botão de Editar concursos
            $botao1 = new BotaoGrafico();
            $botao1->set_label('');
            $botao1->set_title('Editar o Concurso');
            $botao1->set_url("?fase=editarNome&id=");
            $botao1->set_imagem(PASTA_FIGURAS . 'olho.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, null, null, null, $botao1]);
            $tabela->set_idCampo('idVaga');

            $tabela->set_conteudo($result);
            $tabela->show();
            break;

        case "editarNome" :
            set_session('idVaga', $id);
            set_session('origem', "areaVagasDocentes.php?fase=porNome");            
            loadPage("cadastroVagaHistorico.php");
            break;

        case "porVaga":

            # Menu
            $menu = new MenuBar();

            # Voltar
            $botao = new Link("Voltar", "?");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('V');
            $menu->add_link($botao, "left");

            $menu->show();

            $form = new Form("?fase=editarVaga");

            $controle = new Input('parametroVaga', 'texto', 'Número da Vaga:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Nome Ou Número da Vaga');
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            tituloTable('Cadastro de Vagas', null, 'Pelo Número da Vaga');
            $callout = new Callout();
            $callout->abre();
            p("Nenhum registro encontrado", 'f14', 'center');
            $callout->fecha();
            br();
            break;

        case "editarVaga" :
            set_session('idVaga', $parametroVaga);
            set_session('origem', "areaVagasDocentes.php?fase=porVaga");  
            loadPage("cadastroVagaHistorico.php");
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}