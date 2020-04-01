<?php
/**
 * Área de Afastamentos da GRH
 *
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno',get_session('parametroAno',date('Y')));
    $parametroMes = post('parametroMes',get_session('parametroMes',date('m')));
    $parametroLotacao = 66;

    # Joga os parâmetros par as sessions
    set_session('parametroAno',$parametroAno);
    set_session('parametroMes',$parametroMes);    

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de afastamentos da GRH";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }

################################################################

    switch ($fase){
        case "" :
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
                p("Aguarde...","center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;

################################################################

        case "exibeLista" :
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar","grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar,"left");

            $menu1->show();

        ################################################################

            # Formulário de Pesquisa
            $form = new Form('?');

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $anoExercicio = arrayPreenche($anoInicial,$anoAtual);

            $controle = new Input('parametroAno','combo','Ano:',1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anoExercicio);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            # Mês
            $controle = new Input('parametroMes','combo','Mês:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

        ################################################################

            $grid = new Grid();
            $grid->abreColuna(4);

            $painel = new Callout();
            $painel->abre();

            $cal = new Calendario($parametroMes,$parametroAno);
            $cal->show();

            $painel->fecha();

            $grid->fechaColuna();

        ################################################################

            $grid->abreColuna(8);

            # Exibe a tabela de Servidores afastados
            $afast = new Afastamento();
            $afast->set_ano($parametroAno);
            $afast->set_mes($parametroMes);
            $afast->set_lotacao($parametroLotacao);
            $afast->set_idFuncional(FALSE);
            $afast->exibeTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

    ################################################################

    }

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
