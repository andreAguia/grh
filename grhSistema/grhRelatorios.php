<?php
/**
 * Rotina do menu de relatório
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
    $fase = get('fase','listar');
		
    # Verifica a fase do programa
    $fase = get('fase','menu');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    switch ($fase){	
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
                $menu = new Menu();
                $menu->add_item('titulo','Categorias de Relatórios');
                $menu->add_item('linkAjax','Aposentadoria & Abono Permanencia','?fase=aposentadoria','','','divMenuRelatorioGrh');  
                $menu->add_item('linkAjax','Atestado','?fase=atestado','','','divMenuRelatorioGrh');  
                $menu->add_item('linkAjax','Cargo Efetivo','?fase=cargoEfetivo','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Cargo em Comissão','?fase=cargoEmComissao','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Cedidos','?fase=cedidos','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Concursos','?fase=concursos','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Contatos','?fase=contatos','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Dependentes & Auxílio Creche','?fase=dependentes','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Diárias','?fase=diarias','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Estatutários','?fase=estatutarios','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Endereço','?fase=endereco','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Faltas','?fase=faltas','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Férias','?fase=ferias','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Financeiro','?fase=financeiro','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Geral','?fase=geral','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Licença e Afastamentos','?fase=licenca','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Lotação','?fase=lotacao','','','divMenuRelatorioGrh');                
                $menu->add_item('linkAjax','Movimentação de Pessoal (SigFis)','?fase=sigFis','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Professores','?fase=professores','','','divMenuRelatorioGrh'); 
                $menu->add_item('linkAjax','Recadastramento 2018','?fase=recad2018','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Sispatri','?fase=sispatri','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Triênio','?fase=trienio','','','divMenuRelatorioGrh');
                #$menu->add_item('linkAjax','TRE','?fase=tre','','','divMenuRelatorioGrh');
                $menu->add_item('linkAjax','Histórico','?fase=historico','','','divMenuRelatorioGrh');
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

        case "aposentadoria";        
            $menu = new Menu();
            $menu->add_item('titulo','Aposentadoria');
            $menu->add_item('linkWindow','Relatório de Estatutários com Idade para Aposentadoria','../grhRelatorios/servIdadeAposent.php');
            $menu->add_item('linkWindow','Relatório de Estatutários que Atingiram Idade para Aposentadoria','../grhRelatorios/servidoresComIdadeParaAposentar.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Aposentados - Com Email e Telefone','../grhRelatorios/geralServidoresAposentados.php');
            $menu->add_item('linkWindow','Relatório Geral de Estatutarios com Abono Permanencia Deferido','../grhRelatorios/geralAbonoDeferido.php');
            $menu->add_item('linkWindow','Relatório Geral de Estatutarios com Abono Permanencia Indeferido','../grhRelatorios/geralAbonoIndeferido.php');
            
            $menu->show();
            break;

        ######################################

        case "cargoEfetivo";        
            $menu = new Menu();
            $menu->add_item('titulo','Cargos');
            $menu->add_item('linkWindow','Relatório de Cargos - Agrupados por Nível','../grhRelatorios/cargoNivel.php');
            $menu->add_item('linkWindow','Relatório Numero de Servidores Ativos por Diretoria / Cargo','../grhRelatorios/cargoNivelLotacao.php');
            $menu->add_item('linkWindow','Relatório de Estatutários - Por Cargo','../grhRelatorios/cargoEstatutarios.php');
            $menu->add_item('linkWindow','Relatório de Estatutários Administrativos e Técnicos por Lotação','../grhRelatorios/admTecporLotacao.php');
            $menu->add_item('linkWindow','Relatório de Estatutários Administrativos e Técnicos por Sexo','../grhRelatorios/admTecporSexo.php');
            $menu->add_item('linkWindow','Relatório de Estatutários Administrativos e Técnicos por Escolaridade do Cargo','../grhRelatorios/admTecporEscolaridadeCargo.php');
            $menu->add_item('linkWindow','Relatório de Professores por Lotação','../grhRelatorios/professorporLotacao.php');
            
            $menu->show();
            break;

        ######################################

        case "cargoEmComissao";        
            $menu = new Menu();
            $menu->add_item('titulo','Cargos');
            $menu->add_item('linkWindow','Relatório dos Cargos em Comissão Ativos','../grhRelatorios/cargoComissaoAtivos.php');
            $menu->add_item('linkWindow','Relatório dos Cargos em Comissão Inativos','../grhRelatorios/cargoComissaoInativos.php');
            $menu->add_item('linkWindow','Relatório de Servidores com Cargos em Comissão - Agrupados por Cargo','../grhRelatorios/cargosComissionados.php');
            $menu->add_item('linkWindow','Relatório de Servidores com Cargos em Comissão Ativos - Histórico','../grhRelatorios/cargosComissionadosAtivosHistorico.php');
            $menu->add_item('linkWindow','Relatório de Servidores com Cargos em Comissão Inativos - Histórico','../grhRelatorios/cargosComissionadosInativosHistorico.php');

            $menu->show();
            break;

        ######################################

        case "cedidos";
            $menu = new Menu();
            $menu->add_item('titulo','Cedidos');
            $menu->add_item('linkWindow','Relatório de Estatutários Cedidos','../grhRelatorios/estatutariosCedidos.php');
            $menu->add_item('linkWindow','Histórico de Estatutários Cedidos - Agrupados por Ano da Cessão','../grhRelatorios/estatutariosCedidosHistorico.php');            
            $menu->add_item('linkWindow','Relatório de Estatutários Cedidos - Agrupados por Órgão','../grhRelatorios/estatutariosCedidosOrgao.php');
            #$menu->add_item('linkWindow','Escala Anual de Férias - Servidores Técnicos Estatutários Cedidos','../grhRelatorios/escalaAnualFeriasTecnicosSandraCedidos.php');
            $menu->add_item('linkWindow','Relatório de Cedidos de Outros Órgãos - Agrupados por Órgão','../grhRelatorios/cedidosporOrgao.php');

            $menu->show();
            break;

        ######################################

        case "concursos";
            $menu = new Menu();
            $menu->add_item('titulo','Concursos');
            $menu->add_item('linkWindow','Relatório de Estatutários Ativos - Agrupados por Concurso','../grhRelatorios/estatutariosConcurso.php');

            $menu->show();
            break;

        ######################################

        case "dependentes";
            $menu = new Menu();
            $menu->add_item('titulo','Dependentes');        
            $menu->add_item('linkWindow','Relatório Geral de Auxílio Creche','../grhRelatorios/servidoresAtivoAuxilioCreche.php');
            $menu->add_item('linkWindow','Relatório Servidores Ativos com Dependente (Filhos)','../grhRelatorios/servidoresAtivoComFilhos.php');
            $menu->add_item('linkWindow','Relatório Mensal de Vencimento de Auxilio Creche','../grhRelatorios/vencimentoMensalAuxilioCreche.php');
            $menu->add_item('linkWindow','Relatório Anual de Vencimento de Auxilio Creche','../grhRelatorios/vencimentoAnualAuxilioCreche.php');

            $menu->show();
            break;

        ######################################
    
        case "diarias";
            $menu = new Menu();
            $menu->add_item('titulo','Diárias');
            $menu->add_item('linkWindow','Relatório Mensal pela Data do Processo','../grhRelatorios/diariasMensalDataProcesso.php');
            $menu->add_item('linkWindow','Relatório Anual pela Data do Processo','../grhRelatorios/diariasAnualDataProcesso.php');
            $menu->add_item('linkWindow','Relatório Mensal por Data de Saída','../grhRelatorios/diariasMensal.php');
            $menu->add_item('linkWindow','Relatório Anual por Data de Saída','../grhRelatorios/diariasAnual.php');

            $menu->show();
            break;

        ######################################

        case "tre";
            $menu = new Menu();
            $menu->add_item('titulo','TRE');
            $menu->add_item('linkWindow','Relatório Mensal de Afastamentos para Serviço Eleitoral (TRE)','../grhRelatorios/treAfastamentoMensal.php');
            $menu->add_item('linkWindow','Relatório Anual de Afastamentos para Serviço Eleitoral (TRE)','../grhRelatorios/treAfastamentoAnual.php'); 
            $menu->add_item('linkWindow','Relatório Mensal de Folgas Fruídas (TRE)','../grhRelatorios/treFolgaMensal.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Folgas Fruídas (TRE)','../grhRelatorios/treFolgaAnual.php'); 

            $menu->show();
            break;

        ######################################

        case "ferias";
            $menu = new Menu();
            $menu->add_item('titulo','Férias');
            $menu->add_item('linkWindow','Escala Anual de Férias de Servidores Tecnicos Estatutarios','../grhRelatorios/ferias.escalaAnual.TecnicosEstatutarios.php');
            $menu->add_item('linkWindow','Escala Anual de Férias de Docentes Estatutarios com Cargo de Comissao','../grhRelatorios/ferias.escalaAnual.DocentesComCargo.php');
            $menu->add_item('linkWindow','Escala Anual de Férias de Docentes Com Regencia de Turma','../grhRelatorios/ferias.escalaAnual.DocentesComRegencia.php');
            #$menu->add_item('linkWindow','Escala Anual de Férias UENF - Servidores Cedidos','../grhRelatorios/escalaAnualFeriasTecnicosSandraCedidos.php');
            
            #$menu->add_item('linkWindow','Escala Mensal Geral de Férias','../grhRelatorios/escalaMensalFeriasGeral.php');
            #$menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação','../grhRelatorios/escalaMensalFeriasGeralPorLotacao.php');
            #$menu->add_item('linkWindow','Escala Mensal Geral de Férias Agrupados por Lotação - Assinatura','../grhRelatorios/escalaMensalFeriasGeralPorLotacaoComAssinatura.php');
            #$menu->add_item('linkWindow','Escala Semestral de Férias (Fevereiro - Agosto)','../grhRelatorios/escalaSemestralFeriasGeralFevereiroAgosto.php');
            #$menu->add_item('linkWindow','Escala Semestral de Férias (Setembro - Janeiro)','../grhRelatorios/escalaSemestralFeriasGeralSetembroJaneiro.php');
            #$menu->add_item('linkWindow','Total de Férias por Ano do Exercício','../grhRelatorios/totalFeriasAnual.php');
            #$menu->add_item('linkWindow','Relatório Mensal de Servidores em Férias','../grhRelatorios/servidorEmFerias.php');
            
            $menu->add_item('linkWindow','Servidores Em Férias Por Ano de Fruição','../grhRelatorios/ferias.fruicao.anual.porMes.emFerias.php');
            $menu->show();
            break;

        ######################################

         case "trienio";
            $menu = new Menu();
            $menu->add_item('titulo','Triênio');
            $menu->add_item('linkWindow','Relatório Geral de Triênio','../grhRelatorios/geralTrienio.php');
            $menu->add_item('linkWindow','Relatório Mensal de Vencimento de Triênios','../grhRelatorios/vencimentoMensalTrienio.php');
            $menu->add_item('linkWindow','Relatório Anual de Vencimento de Triênios','../grhRelatorios/vencimentoAnualTrienio.php');

            $menu->show();
            break;

        ######################################

        case "geral";
            $menu = new Menu();
            $menu->add_item('titulo','Geral');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos','../grhRelatorios/geralServidoresAtivos.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos - Agrupados por Lotação','../grhRelatorios/geralServidoresAtivoLotacao.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos - Com CPF - Agrupados por Lotação','../grhRelatorios/geralServidoresAtivoCpf.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos - Assinatura','../grhRelatorios/geralServidoresAtivosAssinatura.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos - Check','../grhRelatorios/geralServidoresAtivosCheck.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos e Inativos - Agrupados por Lotação','../grhRelatorios/geralServidoresLotacao.php');
            $menu->add_item('linkWindow','Relatório Geral de Servidores Ativos e Inativos - Com CPF','../grhRelatorios/geralServidoresCPF.php'); 
            $menu->add_item('linkWindow','Relatório Geral de Servidores Inativos','../grhRelatorios/geralServidoresInativos.php');
            $menu->show();
            break;    

        ######################################

        case "licenca";
            $menu = new Menu();
            $menu->add_item('titulo','Licença e Afastamentos');
            $menu->add_item('linkWindow','Relatório Mensal de Servidores em Licença Por Lotação','../grhRelatorios/licencaMensal.php');
            $menu->add_item('linkWindow','Relatório Anual de Servidores em Licença Por Lotação','../grhRelatorios/licencaAnualLotacao.php');
            $menu->add_item('linkWindow','Relatório Mensal de Servidores em Licença Agrupados','../grhRelatorios/licencaMensalAgrupado.php');
            $menu->add_item('linkWindow','Relatório Mensal de Servidores em Licença Sem Duplicidade','../grhRelatorios/licencaMensalAgrupadoSemDuplicidade.php');
            $menu->add_item('linkWindow','Relatório Mensal de Término de Licença','../grhRelatorios/licencaVencimentoMensal.php');
            $menu->add_item('linkWindow','Relatório Anual de Término de Licença','../grhRelatorios/licencaVencimentoAnual.php');
            $menu->add_item('linkWindow','Relatório Anual de Licença Prêmio','../grhRelatorios/licencaPremioAnual.php');
            $menu->show();
            break;    

        ######################################

        case "lotacao";
            $menu = new Menu();
            $menu->add_item('titulo','Lotação');
            $menu->add_item('linkWindow','Relatório de Lotações Ativas','../grhRelatorios/lotacao.php');
            $menu->add_item('linkWindow','Relatório de Aniversariantes - Por Lotação','../grhRelatorios/lotacaoAniversariantes.php');
            $menu->add_item('linkWindow','Relatório de Servidores Ativos - Por Lotação','../grhRelatorios/lotacaoServidoresAtivos.php');
            #$menu->add_item('linkWindow','Lista de Telefones e Ramais - Agrupados por Diretoria','../grhRelatorios/ramais.php');
            $menu->show();
            break;    

        ######################################

        case "atestado";
            $menu = new Menu();
            $menu->add_item('titulo','Atestado');
            $menu->add_item('linkWindow','Relatório Mensal de Servidores com Atestado','../grhRelatorios/atestadoMensal.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Servidores com Atestado','../grhRelatorios/atestadoAnual.php'); 

            $menu->show();
            break;    

        ######################################

         case "faltas";
            $menu = new Menu();
            $menu->add_item('titulo','Faltas');
            $menu->add_item('linkWindow','Relatório Mensal de Faltas','../grhRelatorios/faltasMensal.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Faltas','../grhRelatorios/faltasAnual.php'); 

            $menu->show();
            break;

        ######################################

         case "sigFis";
            $menu = new Menu();
            $menu->add_item('titulo','Movimentação de Pessoal (SigFis)');
            $menu->add_item('linkWindow','Relatório Anual de Servidores Admitidos','../grhRelatorios/sigFisAnualAdmitidos.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Servidores Demitidos e Exonerados','../grhRelatorios/sigFisAnualDemitidos.php'); 
            $menu->add_item('linkWindow','Relatório Anual de Servidores Nomeados','../grhRelatorios/sigFisAnualNomeados.php'); 
            $menu->show();
            break; 
        
        ######################################

         case "sispatri";
            $menu = new Menu();
            $menu->add_item('titulo','Sispatri');
            $menu->add_item('linkWindow','CI dos Servidores que Nao Entregaram o Sispatri','../grhRelatorios/sispatriLotacao.php'); 
            $menu->show();
            break; 
        
        ######################################

         case "estatutarios";
            $menu = new Menu();
            $menu->add_item('titulo','Estatutários');
            $menu->add_item('linkWindow','Estatutários Ativos com Assinatura','../grhRelatorios/estatutariosAtivosAssinatura.php');
            $menu->add_item('linkWindow','Estatutários Agrupados pela Lotação','../grhRelatorios/estatutariosLotacao.php'); 
            $menu->add_item('linkWindow','Estatutários Agrupados pelo Cargo','../grhRelatorios/estatutariosCargo.php'); 
            $menu->add_item('linkWindow','Estatutários Com CPF e Data de Nascimento','../grhRelatorios/estatutariosCpfNascimento.php'); 
            
            $menu->show();
            break;  
        
        ######################################

         case "financeiro";
            $menu = new Menu();
            $menu->add_item('titulo','Financeiro');
            $menu->add_item('linkWindow','Financeiro','../grhRelatorios/financeiro.php'); 

            $menu->show();
            break;  
        
        ######################################

         case "contatos";
            $menu = new Menu();
            $menu->add_item('titulo','Contatos');
            $menu->add_item('linkWindow','Email dos Servidores','../grhRelatorios/email.php');            
            $menu->add_item('linkWindow','Telefones dos Servidores','../grhRelatorios/telefone.php');

            $menu->show();
            break;
        
        ######################################

         case "nacionalidade";
            $menu = new Menu();
            $menu->add_item('titulo','Censo');
            $menu->add_item('linkWindow','Relatório de Servidores Agrupados por Nacionalidade','../grhRelatorios/geralServidoresNacionalidade.php');
            $menu->add_item('linkWindow','Relatório de Professores Agrupados por Nacionalidade','../grhRelatorios/professorNacionalidade.php'); 
            $menu->show();
            break;
        
        ######################################

        case "professores";        
            $menu = new Menu();
            $menu->add_item('titulo','Professores');
            $menu->add_item('linkWindow','Relatório de Professores Agrupados por Nacionalidade','../grhRelatorios/professorNacionalidade.php'); 
            $menu->add_item('linkWindow','Relatório de Professores Com Data de Nascimento e Sexo','../grhRelatorios/professorIdadeSexo.php'); 
            $menu->add_item('linkWindow','Relatório de Professores Agrupados por Lotaçao','../grhRelatorios/professorporLotacao.php'); 
            $menu->show();
            break;
        
        ######################################

        case "historico";        
            $menu = new Menu();
            $menu->add_item('titulo','Histórico');
            $menu->add_item('linkWindow','Relatório de Servidores Ativos Ex-Fenorte','../grhRelatorios/servidoresAtivosExFenorte.php');
            $menu->add_item('linkWindow','Servidores Por Ano de Admissão Com Email e CPF Por Tipo de Cargo','../grhRelatorios/servidoresPorAnoAdmissaoComEmailCpf.php');
            $menu->add_item('linkWindow','Servidores Por Ano de Saída Com Email e CPF Por Tipo de Cargo','../grhRelatorios/servidoresPorAnoDemissaoComEmailCpf.php');
            $menu->add_item('linkWindow','Servidores Ativo em um Determinado Ano','../grhRelatorios/servidoresAtivosPorAno.php');
            $menu->show();
            break;

        ######################################

         case "endereco";
            $menu = new Menu();
            $menu->add_item('titulo','Endereço');
            $menu->add_item('linkWindow','Relatório de Servidores Agrupado por Cidade','../grhRelatorios/enderecoPorCidade.php');
            $menu->add_item('linkWindow','Relatório de Servidores com Endereço, Emails e Telefones Agrupado por Lotaçao','../grhRelatorios/enderecoEmailLotacao.php');
            $menu->add_item('linkWindow','Relatório de Ativos e Aposentados Com Endereço','../grhRelatorios/enderecoAtivoAposentado.php');
            $menu->show();
            break;

        ######################################

         case "recad2018";
            
            $menu = new Menu();
            $menu->add_item('titulo','Recadastramento 2018');
            $menu->add_item('titulo1','por Lotaçao');
            $menu->add_item('linkWindow','Servidores Ativos Recadastrados','../grhRelatorios/recadastramentoLotacao.php');
            $menu->add_item('linkWindow','Servidores Ativos NÃO Recadastrados','../grhRelatorios/recadastramentoFaltamLotacao.php');
            $menu->add_item('linkWindow','Servidores Inativos Recadastrados','../grhRelatorios/recadastramentoLotacaoInativos.php');
            $menu->add_item('titulo1','por Cargo');
            $menu->add_item('linkWindow','Servidores Ativos Recadastrados','../grhRelatorios/recadastramentoCargo.php');
            $menu->add_item('linkWindow','Servidores Ativos NÃO Recadastrados','../grhRelatorios/recadastramentoFaltamCargo.php');
            $menu->add_item('linkWindow','Servidores Inativos Recadastrados','../grhRelatorios/recadastramentoCargoInativos.php');
            $menu->add_item('titulo1','por Sisgen (Docentes Ativos)');
            $menu->add_item('linkWindow','Realizou Sisgen','../grhRelatorios/recadastramentoSisgen.php?sisgen=1');
            $menu->add_item('linkWindow','Nao Realizou Sisgem','../grhRelatorios/recadastramentoSisgen.php?sisgen=0');
            $menu->add_item('linkWindow','Nao Responderam o Anexo III','../grhRelatorios/recadastramentoSisgen.php?sisgen=2');
            $menu->show();
            
            #######
            
            $grid2 = new Grid();
            $grid2->abreColuna(7);
            
            # Inicia o array
            $resumo = array();
            
            # Total de Servidores Ativos
            # Geral
            $select = "SELECT idServidor "
                    . "  FROM tbservidor "
                    . " WHERE situacao = 1";
            $totalServidores = $pessoal->count($select);
            
            # Total de Professores
            $select = "SELECT idServidor "
                    . "  FROM tbservidor JOIN tbcargo USING (idCargo)"
                    . "                  JOIN tbtipocargo USING (idTipoCargo)"
                    . " WHERE situacao = 1 "
                    . "   AND tbtipocargo.tipo = 'Professor'";
            $totalProfessores = $pessoal->count($select);
            
            # Total de Adm/Tec
            $select = "SELECT idServidor "
                    . "  FROM tbservidor JOIN tbcargo USING (idCargo)"
                    . "                  JOIN tbtipocargo USING (idTipoCargo)"
                    . " WHERE situacao = 1 "
                    . "   AND tbtipocargo.tipo = 'Adm/Tec'";
            $totalAdm = $pessoal->count($select);
            
            #####
            
            # Total Recadastrados Ativos
            $select = "SELECT idRecadastramento "
                    . "   FROM tbrecadastramento LEFT JOIN tbservidor USING (idServidor)"
                    . "  WHERE situacao = 1";            
            $recadastradosAtivosTotal = $pessoal->count($select);
            
            # Professores Recadastrados Ativos
            $select = "SELECT idRecadastramento "
                    . "   FROM tbrecadastramento LEFT JOIN tbservidor USING (idServidor) "
                    . "                               JOIN tbcargo USING (idCargo) "
                    . "                               JOIN tbtipocargo USING (idTipoCargo)"
                    . " WHERE situacao = 1 "
                    . "   AND tbtipocargo.tipo = 'Professor'";
            $recadastradosProfessoresAtivos = $pessoal->count($select);
            
            # Adm/Tec Recadastrados Ativos
            $select = "SELECT idRecadastramento "
                    . "   FROM tbrecadastramento LEFT JOIN tbservidor USING (idServidor) "
                    . "                               JOIN tbcargo USING (idCargo) "
                    . "                               JOIN tbtipocargo USING (idTipoCargo)"
                    . " WHERE situacao = 1 "
                    . "   AND tbtipocargo.tipo = 'Adm/Tec'";
            $recadastradosAdmAtivos = $pessoal->count($select);
            
            #####
            
            # Total Recadastrados Inativos
            $select = "SELECT idRecadastramento "
                    . "   FROM tbrecadastramento LEFT JOIN tbservidor USING (idServidor)"
                    . "  WHERE situacao <> 1";            
            $recadastradosInativosTotal = $pessoal->count($select);
            
            # Professores Recadastrados Inativos
            $select = "SELECT idRecadastramento "
                    . "   FROM tbrecadastramento LEFT JOIN tbservidor USING (idServidor) "
                    . "                               JOIN tbcargo USING (idCargo) "
                    . "                               JOIN tbtipocargo USING (idTipoCargo)"
                    . " WHERE situacao <> 1 "
                    . "   AND tbtipocargo.tipo = 'Professor'";
            $recadastradosProfessoresInativos = $pessoal->count($select);
            
            # Adm/Tec Recadastrados Inativos
            $select = "SELECT idRecadastramento "
                    . "   FROM tbrecadastramento LEFT JOIN tbservidor USING (idServidor) "
                    . "                               JOIN tbcargo USING (idCargo) "
                    . "                               JOIN tbtipocargo USING (idTipoCargo)"
                    . " WHERE situacao <> 1 "
                    . "   AND tbtipocargo.tipo = 'Adm/Tec'";
            $recadastradosAdmInativos = $pessoal->count($select);
            
            $resumo[] = array("Servidores Ativos",$totalAdm,$totalProfessores,$totalServidores);
            $resumo[] = array("Recadastrados Ativos",$recadastradosAdmAtivos,$recadastradosProfessoresAtivos,$recadastradosAtivosTotal);
            $resumo[] = array("Recadastrados Inativos",$recadastradosAdmInativos,$recadastradosProfessoresInativos,$recadastradosInativosTotal);
            $resumo[] = array("NÃO Recadastrados Ativos",($totalAdm - $recadastradosAdmAtivos),($totalProfessores - $recadastradosProfessoresAtivos),($totalServidores - $recadastradosAtivosTotal));
            

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Descrição","Adm/Tec","Professores","Total"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_align(array("left","center"));
            $tabela->set_titulo("Resumo Geral");
            #$tabela->set_rodape("Total de Servidores: ".$totalServidores3);
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(5);
            
            # Sisgem
            
            # Calcula quantos realizaram
            $select5 = "SELECT idRecadastramento FROM tbrecadastramento LEFT JOiN tbservidor USING (idServidor) JOIN tbcargo USING (idCargo) WHERE situacao = 1 AND (idTipoCargo = 1 OR idTipoCargo = 2) AND sisgen = 1";
            $realizaram = $pessoal->count($select5);
            
            # Calcula quantos nao realizaram
            $select6 = "SELECT idRecadastramento FROM tbrecadastramento LEFT JOiN tbservidor USING (idServidor) JOIN tbcargo USING (idCargo) WHERE situacao = 1 AND (idTipoCargo = 1 OR idTipoCargo = 2) AND sisgen = 0";
            $naoRealizaram = $pessoal->count($select6);
            
            # Calcula quantos nao responderam
            $select7 = "SELECT idRecadastramento FROM tbrecadastramento LEFT JOiN tbservidor USING (idServidor) JOIN tbcargo USING (idCargo) WHERE situacao = 1 AND (idTipoCargo = 1 OR idTipoCargo = 2) AND sisgen = 2";
            $naoResponderam = $pessoal->count($select7);
                        
            $resumo = array();
            
            $resumo[] = array("Realizaram",$realizaram);
            $resumo[] = array("Nao Realizaram",$naoRealizaram);
            $resumo[] = array("Nao Responderam",$naoResponderam);
            $total = $realizaram + $naoRealizaram+$naoResponderam;
            $resumo[] = array("Total",$total);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Descrição","Nº de Servidores"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_align(array("left","center"));
            $tabela->set_titulo("Sisgen (Ativos)");
            #$tabela->set_rodape("Total de Servidores: ".$totalServidores3);
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ######################################
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}