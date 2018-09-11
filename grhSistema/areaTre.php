<?php
/**
 * Área de Licença Prêmio
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
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Tre";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaPremio',FALSE);
    
    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat',get_session('parametroNomeMat'));
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao'));
    $parametroProcesso = post('parametroProcesso',get_session('parametroProcesso'));
    $parametroSituacao = post('parametroSituacao',get_session('parametroSituacao',1));
    $selectRelatorio = get_session('selectRelatorio');
        
    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat',$parametroNomeMat);
    set_session('parametroLotacao',$parametroLotacao);
    set_session('parametroProcesso',$parametroProcesso);
    set_session('parametroSituacao',$parametroSituacao);
    
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

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório Tre");
            $botaoRel->set_onClick("window.open('../grhRelatorios/treGeral.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel,"right");

            $menu1->show();
            
            # Pega o time inicial
            $time_start = microtime(TRUE);
            
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT idFuncional,
                              tbpessoa.nome,
                              idServidor,
                              idServidor,
                              (SELECT IFNULL(sum(dias),0) FROM tbtrabalhotre  WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) as trabalhados,
                              (SELECT IFNULL(sum(folgas),0) FROM tbtrabalhotre WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) as concedidas,
                              (SELECT IFNULL(sum(dias),0) FROM tbfolga WHERE tbfolga.idServidor = tbservidor.idServidor) as fruidas,
                              (SELECT IFNULL(sum(folgas),0) FROM tbtrabalhotre WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) - (SELECT IFNULL(sum(dias),0) FROM tbfolga WHERE tbfolga.idServidor = tbservidor.idServidor)
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                        WHERE situacao = 1
                          AND (SELECT sum(dias) FROM tbtrabalhotre  WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) > 0
                     ORDER BY tbpessoa.nome";
            
            # Guarde o select para o relatório
            set_session('selectRelatorio',$select);
            
            $resumo = $servidor->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Id","Nome","Cargo","Lotação","Dias Trabalhados","Folgas Concedidas","Folgas Fruidas","Folgas Pendentes"));
            $tabela->set_align(array("center","left","left","left"));
            #$tabela->set_width(array(5,15,15,15,8,15,15,15));
            #$tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_cargo","get_lotacao"));
            $tabela->set_titulo("TRE");
            
            $tabela->set_editar('?fase=editaServidor&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("ver.png");
            $tabela->set_idCampo('idServidor');
            $tabela->show();
            
            # Pega o time final
            $time_end = microtime(TRUE);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',')." segundos","right","f10");
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
################################################################

        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :
            set_session('idServidorPesquisado',$id);
            set_session('areaTre',TRUE);
            loadPage('servidorTre.php');
            break; 
        
################################################################
        
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


