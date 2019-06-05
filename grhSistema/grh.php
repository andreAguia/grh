<?php
/**
 * Sistema do GRH
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
    $pessoal = new Pessoal();
    $intra = new Intra();
	
    # Verifica a fase do programa
    $fase = get('fase','menu');
    $alerta = get('alerta');
    $parametroMes = post('parametroMes',date("m"));
    $parametroLotacao = post('parametroLotacao','*');
		
    # Define a senha padrão de acordo com o que está nas variáveis
    #define("SENHA_PADRAO",$config->get_variavel('senha_padrao'));    

    # Começa uma nova página
    $page = new Page();
    $page->set_bodyOnLoad("ajaxLoadPage('grh.php?fase=resumoAlertas','divAlertas',null);");
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> 'resumoAlertas'){  
        AreaServidor::cabecalho();
    }
    
    # Zera sessions
    set_session('areaTre');
    set_session('comissao');
    set_session('areaReducao');
    set_session('sessionParametroPlano');
    set_session('sessionParametroNivel');
    set_session('parametroNomeMat');
    set_session('parametroCargo');
    set_session('parametroCargoComissao');
    set_session('parametroLotacao');
    set_session('parametroCurso');
    set_session('parametroNivel');
    set_session('parametroEscolaridade');
    set_session('parametroPerfil');
    set_session('parametroSituacao');
    set_session('parametroPaginacao');   
    set_session('parametroOrdenacao');   
    set_session('parametroDescricao');
    set_session('sessionSelect');                      // Select para gerar relatório
    set_session('sessionTítulo');                      // Título do relatório
    set_session('sessionSubTítulo');                   // SubTítulo do relatório
    set_session('parametroAno');
    set_session('parametroMes');
    set_session('idCategoria');
    set_session('idProcedimento');
    
    set_session('sessionParametro');	# Zera a session do parâmetro de pesquisa da classe modelo
    set_session('sessionPaginacao');	# Zera a session de paginação da classe modelo
    set_session('sessionLicenca');      # Zera a session do tipo de licença
    set_session('matriculaGrh');        # Zera a session da pesquisa do sistema grh
     
    # Menu
    if(($fase <> 'alertas') AND ($fase <> 'resumoAlertas') AND ($fase <> 'sobre') AND ($fase <> 'atualizacoes') AND ($fase <> 'aniversariantes')){       
        p(SISTEMA,'grhTitulo');
        p("Versão: ".VERSAO,"versao");
    
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        # Cria um menu
        $menu = new MenuBar();

        # Voltar
        $linkVoltar = new Link("Sair","../../areaServidor/sistema/login.php");
        $linkVoltar->set_class('button');
        $linkVoltar->set_title('Sair do Sistema');
        $linkVoltar->set_confirma('Tem certeza que deseja sair do sistema?');
        $linkVoltar->set_accessKey('i');
        $menu->add_link($linkVoltar,"left");

        # Relatórios
        $imagem1 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_url("grhRelatorios.php");
        $botaoRel->set_title("Relatórios dos Sistema");
        $botaoRel->set_imagem($imagem1);
        $menu->add_link($botaoRel,"right");
        
        # Área do Servidor
        $linkArea = new Link("Área do Servidor","../../areaServidor/sistema/areaServidor.php");
        $linkArea->set_class('button');
        $linkArea->set_title('Área do Servidor');
	$menu->add_link($linkArea,"right");

        $menu->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }
    
##################################################################
    
    # Menu
    switch ($fase){	
        # Exibe o Menu Inicial
        case "menu" :
            # acessa a rotina de atualizar os status das férias
            if($intra->get_variavel('dataVerificaFeriasSolicitada') <> date("d/m/Y")){
                $pessoal->mudaStatusFeriasSolicitadaFruida();                       // muda as férias solicitadas na data de hoje para fruídas
                $intra->set_variavel('dataVerificaFeriasSolicitada',date("d/m/Y")); // muda a variável para hoje
                $intra->registraLog($idUsuario,date("Y-m-d H:i:s"),'Rotina de verificação de férias executada.',NULL,NULL,6);
            }

            # monta o menu principal
            Grh::menu($idUsuario);
    
            # Zera a session de alertas
            set_session('alertas');
            
            # Exibe o rodapé da página
            br();
            Grh::rodape($idUsuario);
            break;

##################################################################	

        case "resumoAlertas" :
            titulo('Alertas');
            br();                
            $checkup = New Checkup(FALSE);
            
            echo "<ul class='checkupResumo'>";
            $checkup->get_all();
            echo "</ul>";
            break;

##################################################################

        case "alertas" :
            # Botão voltar
            botaoVoltar('?');
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Exibe o título
            titulo('Alertas do Sistema');
            br();
            
            # executa o checkup
            $checkup = New Checkup();
            
            if(is_null($alerta)){
                $checkup->get_all();
            }else{
                $checkup->$alerta();
            }
            
            # Grava no log a atividade
            $data = date("Y-m-d H:i:s");
            $atividade = 'Visualizou o método: '.$alerta.' da classe Checkup.';
            $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
            
        ##################################################################	

        case "aniversariantes" :
            br();
            
            # Grava no log a atividade
            $atividade = "Visualizou os anivesariantes de ".get_nomeMes($parametroMes);
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Botão voltar
            botaoVoltar('?');
            
            # Mês 
            $form = new Form('?fase=aniversariantes');

            $controle = new Input('parametroMes','combo',"Mês",1);
            $controle->set_size(30);
            $controle->set_title('O mês dos aniversários');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_col(3);
            $controle->set_linha(1);
            $form->add_item($controle);
            
            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                          FROM tblotacao
                                         WHERE ativo) UNION (SELECT distinct DIR, DIR
                                          FROM tblotacao
                                         WHERE ativo)
                                      ORDER BY 2');
            array_unshift($result,array('*','-- Todos --'));

            $controle = new Input('parametroLotacao','combo','Lotação:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_col(9);
            $controle->set_linha(1);
            $form->add_item($controle);
            $form->show();
            
            if($parametroLotacao == "*"){
                $parametroLotacao = NULL;
            }
            
            # Exibe a tabela            
            $select ='SELECT DAY(tbpessoa.dtNasc),
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbpessoa LEFT JOIN tbservidor ON (tbpessoa.idPessoa = tbservidor.idPessoa)
                                   JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND MONTH(tbpessoa.dtNasc) = '.$parametroMes.'
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
            
            # lotacao
            if(!is_null($parametroLotacao)){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")'; 
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")'; 
                }
            }
            
            $select .= ' ORDER BY month(tbpessoa.dtNasc), day(tbpessoa.dtNasc)';

            $result = $pessoal->select($select);
            
            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(array("Dia","Nome","Lotação","Cargo","Perfil"));
            $tabela->set_align(array("center","left","left","left"));
            $tabela->set_classe(array(NULL,NULL,'Pessoal','Pessoal','Pessoal'));
            $tabela->set_metodo(array(NULL,NULL,'get_lotacao','get_cargo','get_perfil'));
            $tabela->set_titulo("Aniversariantes de ".get_nomeMes($parametroMes));
            if(date("m") == $parametroMes){
                $tabela->set_formatacaoCondicional(array(array('coluna' => 0,'valor' => date("d"),'operador' => '=','id' => 'aniversariante')));
            }
            $tabela->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

##################################################################
    
    }

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
