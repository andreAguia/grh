<?php

/**
 * Cadastro de RPA
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
    # Inicia as classes
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de RPAs";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    $sessionidPrestador = get_session('sessionidPrestador');

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    $parametroMes = post('parametroMes', get_session('parametroMes', date("m")));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMes', $parametroMes);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();
    br();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo
    $objeto->set_nome('Controle de RPAs');
    $objeto->set_subtitulo(get_nomeMes($parametroMes)." / ".$parametroAno);

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # select da lista
    $objeto->set_selectLista("SELECT LPAD(idRecibo,4,'0'),                     
                                     idPrestador,                                     
                                     idRecibo,
                                     idRecibo,
                                     idRecibo
                                     idRecibo,
                                     idRecibo
                                FROM tbrpa_recibo JOIN tbrpa_prestador USING (idPrestador)
                                WHERE YEAR(dtInicial) = '{$parametroAno}'
                                  AND MONTH(dtInicial) = '{$parametroMes}'
                             ORDER BY dtInicial desc");

    # select do edita
    $objeto->set_selectEdita("SELECT idPrestador,
                                     dtInicial,
                                     dias,
                                     valor,
                                     processo,
                                     servico,
                                     obs
                                FROM tbrpa_recibo
                                WHERE idRecibo = {$id}");

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
    $objeto->set_label(["Número", "Prestador", "Serviço / Processo", "Período", "Valores", "Rpa"]);
    $objeto->set_width([5, 25, 30, 10, 15, 5]);
    $objeto->set_align(["center", "left", "left"]);

    $objeto->set_classe([null, "RpaPrestador", "Rpa", "Rpa", "Rpa", "Rpa"]);
    $objeto->set_metodo([null, "exibePrestador2", "exibeServicoProcesso", "exibePeriodo", "exibeValores", "exibeBotaoRpa"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbrpa_recibo');

    # Nome do campo id
    $objeto->set_idCampo('idRecibo');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de prestadores
    $prestador = $pessoal->select('SELECT idPrestador,
                                          CONCAT(prestador," - ",especialidade)
                                     FROM tbrpa_prestador
                                 ORDER BY prestador');

    array_unshift($prestador, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'idPrestador',
            'label' => 'Prestador:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $prestador,
            'padrao' => $sessionidPrestador,
            'col' => 9,
            'size' => 200),
        array('linha' => 2,
            'nome' => 'dtInicial',
            'label' => 'Data do Serviço:',
            'tipo' => 'date',
            'required' => true,
            'col' => 3,
            'size' => 20),
        array('linha' => 2,
            'nome' => 'dias',
            'label' => 'Duração (em dias):',
            'tipo' => 'numero',
            'required' => true,
            'col' => 2,
            'size' => 4),
        array('linha' => 2,
            'nome' => 'valor',
            'label' => 'Valor:',
            'tipo' => 'moeda',
            'required' => true,
            'col' => 3,
            'size' => 10),
        array('linha' => 2,
            'nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 30),
        array('linha' => 3,
            'nome' => 'servico',
            'label' => 'Serviço:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 12,
            'size' => 255),
        array('linha' => 4,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    # Prestadores
    $botao1 = new Button("Prestadores", "cadastroRpaPrestador.php");
    $botao1->set_title("Acessa o cadastro de prestadores de serviços");
    $botao1->set_class("button secondary");

    # Tabelas
    $botao2 = new Button("Tabelas", "?fase=tabelas");
    $botao2->set_title("Gerencia as tabelas de desconto");
    $botao2->set_target("_blank");
    $botao2->set_class("button secondary");

    # Relatório Mensal
    $botao3 = new Button("Relatório Mensal");
    $botao3->set_title("Relatório Mensal de RPA");
    $botao3->set_target("_blank");
    $botao3->set_url('../grhRelatorios/rpa.mensal.php');

    # Relatório Anual
    $botao4 = new Button("Relatório Anual");
    $botao4->set_title("Relatório Anual de RPA");
    $botao4->set_target("_blank");
    $botao4->set_url('../grhRelatorios/rpa.anual.php');

    $objeto->set_botaoListarExtra([$botao1, $botao2, $botao3, $botao4]);

    ################################################################
    switch ($fase) {
        case "" :
        case "listar" :
            set_session('sessionidPrestador');
            set_session('sessionCpfPrestador');

            /*
             *  Formulário de Pesquisa
             */
            $form = new Form('?');

            # Pega os dados
            $comboAno = $pessoal->select('SELECT DISTINCT YEAR(dtInicial), YEAR(dtInicial)
                                            FROM tbrpa_recibo
                                           WHERE dtInicial IS NOT NULL
                                        ORDER BY YEAR(dtInicial)');

            # Ano
            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(5);
            $controle->set_title('Ano do início do serviço');
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_array($comboAno);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Mês
            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(10);
            $controle->set_title('Mês do início do serviço');
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_array($mes);
            $form->add_item($controle);

            $objeto->set_formExtra($form);

            $objeto->listar();
            break;

        case "editar" :
            # Verifica se é incluir
            if (empty($id)) {
                loadPage("?fase=incluirRpa");
                break;
            }

        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;

        case "editar2" :
            $objeto->editar();
            break;

        case "incluirRpa" :
            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('?');

            # Título
            titulo('Incluir Nova RPA');
            $callout = new Callout();
            $callout->abre();

            # Pega os dados da combo de prestadores
            $cpfPrestador = $pessoal->select('SELECT DISTINCT cpf
                                             FROM tbrpa_prestador
                                         ORDER BY cpf');

            # Inicia o formulário
            $form = new Form('?fase=validaCPF', 'novoServidor');

            # CPF
            $controle = new Input('cpf', 'cpf', 'CPF do Prestador:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_autofocus(true);
            $controle->set_datalist($cpfPrestador);
            $controle->set_title('O CPF do Prestador');
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor(' Incluir ');
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_accessKey('E');
            $form->add_item($controle);

            $form->show();
            br(2);

            $callout->fecha();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "validaCPF" :

            # flag de erro: 1 - tem erro; 0 - não tem	
            $erro = 0;

            # repositório de mensagens de erro
            $msgErro = null;

            # Pega os valores digitados
            $cpf = post('cpf');

            # Verifica se o CPF foi digitado
            if (empty($cpf)) {
                $msgErro .= 'Você tem que digitar o CPF!\n';
                $erro = 1;
            }

            # Verifica validade do CPF
            if (!validaCpf($cpf)) {
                $msgErro .= 'CPF inválido!\n';
                $erro = 1;
            }

            # Verifia se houve erro 
            if ($erro == 1) {
                alert($msgErro);
                back(1);
            } else {
                # Inicia a classe
                $rpaPrestador = new RpaPrestador();

                # Verifica se o CPF já está cadastrado
                $idPrestador = $rpaPrestador->getIdPrestador($cpf);

                if (empty($idPrestador)) {
                    $grid = new Grid("center");
                    $grid->abreColuna(8);
                    br(3);

                    # Vai para o cadastro de prestador
                    set_session('sessionCpfPrestador', $cpf);
                    callout("Este CPF não está cadastrado. <br/>
                             É necessário cadastrar primeiro este prestador, para depois cadastrar a RPA.<br/>
                             Assim sendo, será aberto a tela de cadastro de prestador.");
                    br();

                    # Cria um menu
                    $menu1 = new MenuBar();

                    # Voltar
                    $botao1 = new Link("Desistir", "?");
                    $botao1->set_class('button');
                    $botao1->set_title('Desiste e voltar a página anterior');
                    $menu1->add_link($botao1, "left");

                    # adastrar Prestador
                    $botao2 = new Link("Cadastrar Prestador", 'cadastroRpaPrestador.php?fase=editar');
                    $botao2->set_class('button');
                    $botao2->set_title("Desvia para o cadastro de Prestador");
                    $menu1->add_link($botao2, "right");

                    $menu1->show();

                    $grid->fechaColuna();
                    $grid->fechaGrid();
                } else {
                    # Vai para o cadastro de rpa
                    set_session('sessionidPrestador', $idPrestador);
                    loadPage('?fase=editar2');
                }
            }
            break;

        case "tabelas" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "cadastroRpa.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            #$menu1->show();
            # Título
            titulo("Tabelas Vigentes");
            br();

            $grid->fechaColuna();
            $grid->abreColuna(6);

            $inss = new RpaInss();
            $inss->exibeTabela();

            # Botão Editar
            if (Verifica::acesso($idUsuario, [1, 2])) {
                # Cria um menu
                $menu2 = new MenuBar("small button-group");

                # Editar
                $botao1 = new Link("Editar", "cadastroRpaInss.php");
                $botao1->set_class('button');
                $botao1->set_title('Edita a tabela de INSS');
                $menu2->add_link($botao1, "right");

                $menu2->show();
            }
            $grid->fechaColuna();
            $grid->abreColuna(6);

            $ir = new RpaIr();
            $ir->exibeTabela();

            # Botão Editar
            if (Verifica::acesso($idUsuario, [1, 2])) {
                # Cria um menu
                $menu2 = new MenuBar("small button-group");

                # Editar
                $botao1 = new Link("Editar", "cadastroRpaIr.php");
                $botao1->set_class('button');
                $botao1->set_title('Edita a tabela de IR');
                $menu2->add_link($botao1, "right");

                $menu2->show();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}