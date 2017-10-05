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

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
	
    # Verifica a fase do programa
    $fase = get('fase','menu');
    $alerta = get('alerta');
    $parametroMes = post('parametroMes',date("m"));
		
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
    switch ($fase)
    {	
        # Exibe o Menu Inicial
        case "menu" :
            # acessa a rotina de atualizar os status das férias
            $pessoal->mudaStatusFeriasConfirmadaFruida();

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
            $atividade = 'Visualizou os Alertas do Sistema';
            $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,4);
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
            
        ##################################################################	

        case "aniversariantes" :
            br();
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(5);
            
            # Botão voltar
            botaoVoltar('?');
            
            $grid->fechaColuna();
            $grid->abreColuna(2);
            
            # Situação
            $form = new Form('?fase=aniversariantes');

            $controle = new Input('parametroMes','combo');
            $controle->set_size(30);
            $controle->set_title('O mês dos aniversários');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $form->add_item($controle);
            $form->show();
            
            $grid->fechaColuna();
            $grid->abreColuna(5);
            
            $grid->fechaColuna();
            $grid->abreColuna(12);
            
            $select ='SELECT DAY(tbpessoa.dtNasc),
                     tbpessoa.nome,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbpessoa LEFT JOIN tbservidor ON (tbpessoa.idPessoa = tbservidor.idPessoa)
                                   JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND MONTH(tbpessoa.dtNasc) = '.$parametroMes.'    
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
             ORDER BY month(tbpessoa.dtNasc), day(tbpessoa.dtNasc)';

            $result = $pessoal->select($select);
            
            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(array("Dia","Nome","Lotação","Cargo","Perfil"));
            $tabela->set_align(array("center","left","left","left"));
            $tabela->set_classe(array(NULL,NULL,NULL,'Pessoal','Pessoal'));
            $tabela->set_metodo(array(NULL,NULL,NULL,'get_cargo','get_perfil'));
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
