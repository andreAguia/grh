<?php
/**
 * Rotina do menu de relatório
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');
		
    # Verifica a fase do programa
    $fase = get('fase','menu');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    switch ($fase)
    {	
        # Exibe o Menu Inicial

        case "menu" :
            # Cabeçalho da Página
            AreaServidor::cabecalho();
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('grh.php');

            # Título do menu
            titulo('Menu de Relatórios');
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            br();

            # Área do Menu
            $grid = new Grid();
            $grid->abreColuna(5,4);
            
            $divMenu2 = new Div("divMenuRelatorioGrhCategoria");
            $divMenu2->abre();
            
            # Cria uma borda
            $callout = new Callout('primary');
            $callout->abre();

                # Menu de tipos de relatórios
                $menu = new Menu('menuInicial');
                $menu->add_item('titulo','Categorias de Relatórios','#','');
                $menu->add_item('linkAjax','Atestado','?fase=atestado','','','divMenuRelatorioGrh');  
                $menu->add_item('linkAjax','Cargos','?fase=cargos','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Cedidos','?fase=cedidos','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Concursos','?fase=concursos','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Dependentes & Auxílio Creche','?fase=dependentes','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Diárias','?fase=diarias','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Estatutários','?fase=estatutarios','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Faltas','?fase=faltas','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Férias','?fase=ferias','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Financeiro','?fase=financeiro','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Geral','?fase=geral','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Licença','?fase=licenca','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','SigFis','?fase=sigFis','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Triênio','?fase=trienio','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','TRE','?fase=tre','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Contatos','?fase=contatos','','','divMenuRelatorioGrh'); 
                $menu->show();
            $callout->fecha();
            $divMenu2->fecha();
            
            $grid->fechaColuna();
        
            ##########################################################
            
            # Menu dos Relatórios
            $grid->abreColuna(7,8);
            
            # Cria uma borda
            $callout = new Callout("success");
            $callout->abre();

            # div principal - onde o menu dos relatórios aparecem
            $divPrincipal = new Div("divMenuRelatorioGrh");
            $divPrincipal->abre();
            
            # Conteúdo
            br(4);
            p("Escolha uma categoria de relatório","center");
            br(6);
            
            $divPrincipal->fecha();
            
            # Fecha a borda
            $callout->fecha();   
            
            # Fecha o grid
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ######################################

        case "cargos";        
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Cargos','#','');
            $menu->add_item('linkWindow','Relatório de Cargos - Agrupados por Nível','../grhRelatorios/cargoNivel.php');
            $menu->add_item('linkWindow','Relatório de Estatutários - Agrupados por Cargo','../grhRelatorios/estatutariosCargo.php');
            $menu->add_item('linkWindow','Relatório dos Cargos em Comissão - Agrupados por Instituição','../grhRelatorios/cargoComissao.php');
            $menu->add_item('linkWindow','Relatório de Servidores com Cargos em Comissão - Agrupados por Cargo','../grhRelatorios/cargosComissionados.php');

            $menu->show();
            break;

        ######################################

        case "cedidos";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Cedidos','#','');
            $menu->add_item('linkWindow','Histórico de Estatutários Cedidos - Agrupados por Ano da Cessão','../grhRelatorios/estatutariosCedidosHistorico.php');
            $menu->add_item('linkWindow','Relatório de Estatutários Cedidos','../grhRelatorios/estatutariosCedidos.php');

            $menu->show();
            break;

        ######################################

        case "concursos";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Concursos','#','');
            $menu->add_item('linkWindow','Relatório de Estatutários Ativos - Agrupados por Concurso','../grhRelatorios/estatutariosConcurso.php');

            $menu->show();
            break;

        ######################################

        case "dependentes";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Dependentes','#','');        
            $menu->add_item('linkWindow','Relatório Geral de Auxílio Creche','../grhRelatorios/servidoresAtivoAuxilioCreche.php');
            $menu->add_item('linkWindow','Relatório Servidores Ativos com Dependente (Filhos)','../grhRelatorios/servidoresAtivoComFilhos.php');
            $menu->add_item('linkWindow','Relatório Mensal de Vencimento de Auxilio Creche','../grhRelatorios/vencimentoMensalAuxilioCreche.php');
            $menu->add_item('linkWindow','Relatório Anual de Vencimento de Auxilio Creche','../grhRelatorios/vencimentoAnualAuxilioCreche.php');

            $menu->show();
            break;

        ######################################
    
        case "diarias";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Diárias','#','');
            $menu->add_item('linkWindow','Relatório Mensal pela Data do Processo','../grhRelatorios/diariasMensalDataProcesso.php');
            $menu->add_item('linkWindow','Relatório Anual pela Data do Processo','../grhRelatorios/diariasAnualDataProcesso.php');
            $menu->add_item('linkWindow','Relatório Mensal por Data de Saída','../grhRelatorios/diariasMensal.php');
            $menu->add_item('linkWindow','Relatório Anual por Data de Saída','../grhRelatorios/diariasAnual.php');

            $menu->show();
            break;

        ######################################

        case "tre";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','TRE','#','');
            $menu->add_item('linkWindow','Relatório Mensal de Afastamentos para Serviço Eleitoral (TRE)','../grhRelatorios/treAfastamentoMensal.php'); 
            $menu->add_item('linkWindow','Relatório Mensal de Folgas Fruídas (TRE)','../grhRelatorios/treFolgaMensal.php'); 

            $menu->show();
            break;

        ######################################

        case "ferias";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Férias','#','');
            $menu->add_item('linkWindow','Escala Anual de Férias Fruídas','../grhRelatorios/escalaAnualFeriasFruidas.php');
            $menu->add_item('linkWindow','Escala Anual de Férias Solicitadas','../grhRelatorios/escalaAnualFeriasSolicitadas.php');
            $menu->add_item('linkWindow','Escala Anual de Férias Confirmadas','../grhRelatorios/escalaAnualFeriasConfirmadas.php');
            $menu->add_item('linkWindow','Escala Mensal de Férias Fruídas','../grhRelatorios/escalaMensalFeriasFruidas.php');
            $menu->add_item('linkWindow','Escala Mensal de Férias Solicitadas','../grhRelatorios/escalaMensalFeriasSolicitadas.php');
            $menu->add_item('linkWindow','Escala Mensal de Férias Confirmadas','../grhRelatorios/escalaMensalFeriasConfirmadas.php');
            $menu->add_item('linkWindow','Escala Mensal Geral de Férias','../grhRelatorios/escalaMensalFeriasGeral.php');
            $menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação','../grhRelatorios/escalaMensalFeriasGeralPorLotacao.php');
            $menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação - Assinatura','../grhRelatorios/escalaMensalFeriasGeralPorLotacaoComAssinatura.php');
            $menu->add_item('linkWindow','Escala Semestral de Férias (Fevereiro - Agosto)','../grhRelatorios/escalaSemestralFeriasGeralFevereiroAgosto.php');
            $menu->add_item('linkWindow','Escala Semestral de Férias (Setembro - Janeiro)','../grhRelatorios/escalaSemestralFeriasGeralSetembroJaneiro.php');
            $menu->add_item('linkWindow','Total de Férias por Ano do Exercício','../grhRelatorios/totalFeriasAnual.php');
            $menu->add_item('linkWindow','Relatório Mensal de Servidores em Férias','../grhRelatorios/servidorEmFerias.php');
            $menu->show();
            break;

        ######################################

         case "trienio";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Triênio','#','');
            $menu->add_item('linkWindow','Relatório Geral de Triênio','../grhRelatorios/geralTrienio.php');
            $menu->add_item('linkWindow','Relatório Mensal de Vencimento de Triênios','../grhRelatorios/vencimentoMensalTrienio.php');
            $menu->add_item('linkWindow','Relatório Anual de Vencimento de Triênios','../grhRelatorios/vencimentoAnualTrienio.php');

            $menu->show();
            break;

        ######################################

        case "geral";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Geral','#','');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos - Agrupados por Lotação','../grhRelatorios/geralServidoresAtivoLotacao.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos - Com CPF - Agrupados por Lotação','../grhRelatorios/geralServidoresAtivoCpf.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos - Assinatura','../grhRelatorios/geralServidoresAtivosAssinatura.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos - Check','../grhRelatorios/geralServidoresAtivosCheck.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores - Agrupados por Lotação','../grhRelatorios/geralServidoresLotacao.php');
            $menu->add_item('linkWindow','Lista de Telefones e Ramais - Agrupados por Diretoria','../grhRelatorios/ramais.php');
            $menu->add_item('linkWindow','Relatório de Aniversariantes - Agrupados por Lotação','../grhRelatorios/aniversariantesLotacao.php');
            
            $menu->show();
            break;    

        ######################################

        case "licenca";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Licença','#','');
            $menu->add_item('linkWindow','Relatório Mensal de Servidores em Licença','../grhRelatorios/licencaMensal.php');
            $menu->add_item('linkWindow','Relatório Mensal de Término de Licença','../grhRelatorios/licencaVencimentoMensal.php');
            $menu->add_item('linkWindow','Relatório Anual de Término de Licença','../grhRelatorios/licencaVencimentoAnual.php');
            $menu->add_item('linkWindow','Relatório Anual de Licença Prêmio','../grhRelatorios/licencaPremioAnual.php');
            $menu->add_item('linkWindow','Relatório de Licença Prêmio','../grhRelatorios/licencaPremio.php');
            $menu->show();
            break;    

        ######################################

        case "atestado";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Atestado','#','');
            $menu->add_item('linkWindow','Relatório Mensal de Servidores com Atestado','../grhRelatorios/atestadoMensal.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Servidores com Atestado','../grhRelatorios/atestadoAnual.php'); 

            $menu->show();
            break;    

        ######################################

         case "faltas";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Faltas','#','');
            $menu->add_item('linkWindow','Relatório Mensal de Faltas','../grhRelatorios/faltasMensal.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Faltas','../grhRelatorios/faltasAnual.php'); 

            $menu->show();
            break;

        ######################################

         case "sigFis";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','SigFis','#','');
            $menu->add_item('linkWindow','Relatório Anual de Servidores Admitidos','../grhRelatorios/sigFisAnualAdmitidos.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Servidores Demitidos e Exonerados','../grhRelatorios/sigFisAnualDemitidos.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Servidores Nomeados','../grhRelatorios/sigFisAnualNomeados.php'); 
            $menu->show();
            break; 
        
        ######################################

         case "estatutarios";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Estatutários','#','');
            $menu->add_item('linkWindow','Estatutários Ativos com Assinatura','../grhRelatorios/estatutariosAtivosAssinatura.php');
            $menu->add_item('linkWindow','Estatutários Agrupados pelo Cargo','../grhRelatorios/estatutariosCargo.php'); 
            $menu->add_item('linkWindow','Estatutários Com CPF e Data de Nascimento','../grhRelatorios/estatutariosCpfNascimento.php'); 

            $menu->show();
            break;  
        
        ######################################

         case "financeiro";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Financeiro','#','');
            $menu->add_item('linkWindow','Financeiro','../grhRelatorios/financeiro.php'); 

            $menu->show();
            break;  
        
        ######################################

         case "contatos";
            $menu = new Menu('menuInicial');
            $menu->add_item('titulo','Contatos','#','');
            $menu->add_item('linkWindow','Email dos Servidores','../grhRelatorios/email.php'); 

            $menu->show();
            break;
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}