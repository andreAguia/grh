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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'menu');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    switch ($fase) {
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

            # Verifica se veio da fase de ferias e se tem que mudar a data de entrega
            $dataDev = post("dtEntrega");

            # Salva o novo valor
            if (!vazio($dataDev)) {
                # Arquiva a data
                $intra->set_variavel("dataDevolucaoGrh", date_to_php($dataDev));
                ajaxLoadPage('?fase=ferias', 'divMenuRelatorioGrh');
            }

            $grid->fechaColuna();
            $grid->fechaGrid();

            br();

            # Área do Menu
            $grid = new Grid();
            $grid->abreColuna(5, 4);

            $divMenu2 = new Div("divMenuRelatorioGrhCategoria");
            $divMenu2->abre();

            # Cria uma borda
            $callout = new Callout('primary');
            $callout->abre();

            # Menu de tipos de relatórios
            $menu = new Menu();
            $menu->add_item('titulo', 'Categorias de Relatórios');
            $menu->add_item('linkAjax', 'Abono Permanencia', '?fase=abono', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Aniversariantes', '?fase=aniversariantes', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Aposentadoria e Tempo Averbado', '?fase=aposentados', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Atestado', '?fase=atestado', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Cargo Efetivo', '?fase=cargoEfetivo', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Cargo em Comissão', '?fase=cargoEmComissao', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Cedidos', '?fase=cedidos', '', '', 'divMenuRelatorioGrh');
            #$menu->add_item('linkAjax','Concursos','?fase=concursos','','','divMenuRelatorioGrh'); 
            $menu->add_item('linkAjax', 'Contatos', '?fase=contatos', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Dependentes & Auxílio Creche', '?fase=dependentes', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Diárias', '?fase=diarias', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Estatutários', '?fase=estatutarios', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Endereço', '?fase=endereco', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Faltas', '?fase=faltas', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Férias', '?fase=ferias', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Financeiro', '?fase=financeiro', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Folha de Frequência', '?fase=frequencia', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Formação', '?fase=formacao', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Geral - Servidores Ativos', '?fase=geralAtivos', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Geral - Servidores Inativos', '?fase=geralInativos', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Geral - Servidores Ativos e Inativos', '?fase=geralGeral', '', '', 'divMenuRelatorioGrh');
            #$menu->add_item('linkAjax','Licença e Afastamentos','?fase=licenca','','','divMenuRelatorioGrh');
            $menu->add_item('linkAjax','Licença Prêmio','?fase=licencaPremio','','','divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Lotação', '?fase=lotacao', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Movimentação de Pessoal', '?fase=movimentacao', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Outros', '?fase=outros', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Parentes', '?fase=parentes', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Professores (Docentes)', '?fase=professores', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Processo Eleitoral', '?fase=eleitoral', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Recadastramento 2018', '?fase=recad2018', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Seguro Anual', '?fase=seguro', '', '', 'divMenuRelatorioGrh');
            #$menu->add_item('linkAjax', 'Sispatri', '?fase=sispatri', '', '', 'divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Triênio', '?fase=trienio', '', '', 'divMenuRelatorioGrh');
            #$menu->add_item('linkAjax','TRE','?fase=tre','','','divMenuRelatorioGrh');
            $menu->add_item('linkAjax', 'Histórico', '?fase=historico', '', '', 'divMenuRelatorioGrh');
            $menu->show();
            $callout->fecha();
            $divMenu2->fecha();

            $grid->fechaColuna();

            ##########################################################
            # Menu dos Relatórios
            $grid->abreColuna(7, 8);

            # Cria uma borda
            $callout = new Callout("success");
            $callout->abre();

            # div principal - onde o menu dos relatórios aparecem
            $divPrincipal = new Div("divMenuRelatorioGrh");
            $divPrincipal->abre();

            # Conteúdo
            br(4);
            p("Escolha uma categoria de relatório", "center");
            br(6);

            $divPrincipal->fecha();

            # Fecha a borda
            $callout->fecha();

            # Fecha o grid
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ######################################

        case "abono";
            $menu = new Menu();
            $menu->add_item('titulo', 'Abono Permanência');
            #$menu->add_item('linkWindow','Relatório de Estatutários com Idade para Aposentadoria','../grhRelatorios/servIdadeAposent.php');
            #$menu->add_item('linkWindow','Relatório de Estatutários que Atingiram Idade para Aposentadoria','../grhRelatorios/servidoresComIdadeParaAposentar.php');
            #$menu->add_item('linkWindow','Relatório Geral de Servidores Aposentados - Com Email e Telefone','../grhRelatorios/geralServidoresAposentados.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Estatutarios com Abono Permanencia Deferido', '../grhRelatorios/geralAbonoDeferido.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Estatutarios com Abono Permanencia Indeferido', '../grhRelatorios/geralAbonoIndeferido.php');

            $menu->show();
            break;

        ######################################

        case "aposentados";
            $menu = new Menu();
            $menu->add_item('titulo', 'Aposentadoria e Tempo Averbado');
            $menu->add_item('linkWindow', 'Relatório de Servidores Aposentados Com Tempo de Serviço Publico Averbado', '../grhRelatorios/aposentados.tempo.publico.averbado.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Aposentados Agrupados por Cargo', '../grhRelatorios/professor.aposentadoPorCargo.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Sem Tempo Averbado', '../grhRelatorios/servidores.ativos.semTempoAverbado.php');

            $menu->show();
            break;

        ######################################

        case "aniversariantes";
            $menu = new Menu();
            $menu->add_item('titulo', 'Aniversariantes');
            $menu->add_item('linkWindow', 'Relatório Anual de Aniversariantes por Lotação', '../grhRelatorios/aniversariantesAnualLotacao.php');

            $menu->show();
            break;

        ######################################

        case "cargoEfetivo";
            $menu = new Menu();
            $menu->add_item('titulo', 'Cargos');
            $menu->add_item('linkWindow', 'Relatório de Cargos - Agrupados por Nível', '../grhRelatorios/cargoNivel.php');
            $menu->add_item('linkWindow', 'Relatório Numero de Servidores Ativos por Diretoria / Cargo', '../grhRelatorios/cargoNivelLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários - Por Cargo', '../grhRelatorios/cargoEstatutarios.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Administrativos e Técnicos por Lotação', '../grhRelatorios/admTecporLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Administrativos e Técnicos por Sexo', '../grhRelatorios/admTecporSexo.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Administrativos e Técnicos por Escolaridade do Cargo', '../grhRelatorios/admTecporEscolaridadeCargo.php');
            $menu->add_item('linkWindow', 'Relatório de Professores por Lotação', '../grhRelatorios/professorporLotacao.php');

            $menu->show();
            break;

        ######################################

        case "cargoEmComissao";
            $menu = new Menu();
            $menu->add_item('titulo', 'Cargos');
            $menu->add_item('titulo1', 'Relação dos Cargos');
            $menu->add_item('linkWindow', 'Relatório dos Cargos em Comissão Ativos', '../grhRelatorios/cargoComissaoAtivos.php');
            $menu->add_item('linkWindow', 'Relatório dos Cargos em Comissão Inativos', '../grhRelatorios/cargoComissaoInativos.php');
            $menu->add_item('titulo1', 'Relação dos Servidores com Cargo em Comissão');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão Vigente e Anterior', '../grhRelatorios/cargoComissaoVigenteEAnterior.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão - Agrupados por Cargo', '../grhRelatorios/cargosComissionados.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão com Vagas - Agrupados por Cargo', '../grhRelatorios/cargosComissionadosVagas.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão - Agrupados por Lotacao', '../grhRelatorios/cargosComissionadosLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão - Agrupados por Cargo - Com CPF e RG', '../grhRelatorios/cargosComissionadosCpfRg.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão - Docentes', '../grhRelatorios/cargosComissionados.docentes.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão - Adm & Tec', '../grhRelatorios/cargosComissionados.adm.php');
            $menu->add_item('titulo1', 'Histórico');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão Ativos - Histórico', '../grhRelatorios/cargosComissionadosAtivosHistorico.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Cargos em Comissão Inativos - Histórico', '../grhRelatorios/cargosComissionadosInativosHistorico.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos com Cargos em Comissão - Docentes - Histórico por Ano', '../grhRelatorios/cargosComissionados.docentes.porAno.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos com Cargos em Comissão - Adm & Tec - Histórico por Ano', '../grhRelatorios/cargosComissionados.adm.porAno.php');

            $menu->show();
            break;

        ######################################

        case "cedidos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Cedidos');
            $menu->add_item('titulo1', 'Cedidos da Uenf');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Cedidos', '../grhRelatorios/estatutarios.cedidos.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Admin e Tecnicos Cedidos', '../grhRelatorios/estatutarios.cedidos.admin.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Professores Cedidos', '../grhRelatorios/estatutarios.cedidos.professores.php');
            $menu->add_item('linkWindow', 'Histórico de Estatutários Cedidos - Agrupados por Ano da Cessão', '../grhRelatorios/estatutarios.cedidos.historico.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Cedidos - Agrupados por Órgão', '../grhRelatorios/estatutarios.cedidos.orgao.php');
            $menu->add_item('titulo1', 'Cedidos de Fora');
            #$menu->add_item('linkWindow','Escala Anual de Férias - Servidores Técnicos Estatutários Cedidos','../grhRelatorios/escalaAnualFeriasTecnicosSandraCedidos.php');
            $menu->add_item('linkWindow', 'Relatório de Cedidos de Outros Órgãos - Agrupados por Órgão', '../grhRelatorios/cedidosporOrgao.php');

            $menu->show();
            break;

        ######################################

        case "concursos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Concursos');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Ativos - Agrupados por Concurso', '../grhRelatorios/estatutariosConcurso.php');

            $menu->show();
            break;

        ######################################

        case "dependentes";
            $menu = new Menu();
            $menu->add_item('titulo', 'Dependentes');
            $menu->add_item('linkWindow', 'Relatório Geral de Auxílio Creche', '../grhRelatorios/servidoresAtivoAuxilioCreche.php');
            $menu->add_item('linkWindow', 'Relatório Servidores Ativos com Dependente (Filhos)', '../grhRelatorios/servidoresAtivoComFilhos.php');
            $menu->add_item('linkWindow', 'Relatório Mensal de Vencimento de Auxilio Creche', '../grhRelatorios/vencimentoMensalAuxilioCreche.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Vencimento de Auxilio Creche', '../grhRelatorios/vencimentoAnualAuxilioCreche.php');
            $menu->add_item('linkWindow', 'Relatório de Dependentes Filhos de Servidores Ativos', '../grhRelatorios/dependentes.filhos.ativos.php');

            $menu->show();
            break;

        ######################################

        case "diarias";
            $menu = new Menu();
            $menu->add_item('titulo', 'Diárias');
            $menu->add_item('linkWindow', 'Relatório Mensal pela Data do Processo', '../grhRelatorios/diariasMensalDataProcesso.php');
            $menu->add_item('linkWindow', 'Relatório Anual pela Data do Processo', '../grhRelatorios/diariasAnualDataProcesso.php');
            $menu->add_item('linkWindow', 'Relatório Mensal por Data de Saída', '../grhRelatorios/diariasMensal.php');
            $menu->add_item('linkWindow', 'Relatório Anual por Data de Saída', '../grhRelatorios/diariasAnual.php');

            $menu->show();
            break;

        ######################################

        case "formacao";
            $menu = new Menu();
            $menu->add_item('titulo', 'Formação dos Servidores');
            $menu->add_item('linkWindow', 'Relatório dos Servidores por Formação', '../grhRelatorios/servidoresComFormacao.php');

            $menu->show();
            break;

        ######################################

        case "frequencia";
            $menu = new Menu();
            $menu->add_item('titulo', 'FoLha de Frequência dos Servidores');
            $menu->add_item('linkWindow', 'Folha de Frequencia de uma Lotação', '../grhRelatorios/folhaFrequenciaLotacao.php');

            $menu->show();
            break;

        ######################################

        case "tre";
            $menu = new Menu();
            $menu->add_item('titulo', 'TRE');
            $menu->add_item('linkWindow', 'Relatório Mensal de Afastamentos para Serviço Eleitoral (TRE)', '../grhRelatorios/treAfastamentoMensal.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Afastamentos para Serviço Eleitoral (TRE)', '../grhRelatorios/treAfastamentoAnual.php');
            $menu->add_item('linkWindow', 'Relatório Mensal de Folgas Fruídas (TRE)', '../grhRelatorios/treFolgaMensal.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Folgas Fruídas (TRE)', '../grhRelatorios/treFolgaAnual.php');

            $menu->show();
            break;

        ######################################

        case "ferias";
            $menu = new Menu();
            $menu->add_item('titulo', 'Férias');
            $menu->add_item('titulo1', 'Novos Relatórios');
            $menu->add_item('linkWindow', 'Relatório de Servidores por Diretoria para Solicitação de Férias', '../grhRelatorios/ferias.anual.porDiretoria.php');
            $menu->add_item('titulo1', 'Antigas Escalas');
            $menu->add_item('linkWindow', 'Escala Anual de Férias de Servidores Tecnicos Estatutarios', '../grhRelatorios/ferias.escalaAnual.TecnicosEstatutarios.php');
            $menu->add_item('linkWindow', 'Escala Anual de Férias de Docentes Estatutarios com Cargo de Comissao', '../grhRelatorios/ferias.escalaAnual.DocentesComCargo.php');
            $menu->add_item('linkWindow', 'Escala Anual de Férias de Docentes Com Regencia de Turma', '../grhRelatorios/ferias.escalaAnual.DocentesComRegencia.php');
            $menu->add_item('titulo1', 'Outros');
            $menu->add_item('linkWindow', 'Total de Dias Fruídos por Ano de Exercício - Professor Ativo', '../grhRelatorios/ferias.exercicio.porTotalDias.professor.php');
            $menu->add_item('linkWindow', 'Total de Dias Fruídos por Ano de Exercício - Adm & Tec Ativo', '../grhRelatorios/ferias.exercicio.porTotalDias.adm.php');
            $menu->add_item('linkWindow', 'Férias Fruídas por Ano de Exercício - Professor Ativo', '../grhRelatorios/ferias.exercicio.fruidas.professor.php');
            $menu->add_item('linkWindow', 'Férias Fruídas por Ano de Exercício - Adm & Tec Ativo', '../grhRelatorios/ferias.exercicio.fruidas.adm.php');

            $menu->show();

            break;

        ######################################

        case "trienio";
            $menu = new Menu();
            $menu->add_item('titulo', 'Triênio');
            $menu->add_item('linkWindow', 'Relatório Geral de Triênio', '../grhRelatorios/trienio.geral.php');
            $menu->add_item('linkWindow', 'Relatório de Triênio por Lotação', '../grhRelatorios/trienio.lotacao.php');
            $menu->add_item('linkWindow', 'Relatório Mensal de Vencimento de Triênios', '../grhRelatorios/trienio.vencimento.mensal.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Vencimento de Triênios', '../grhRelatorios/trienio.vencimento.anual.php');

            $menu->show();
            break;

        ######################################

        case "geralAtivos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Geral - Servidores Ativos');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos', '../grhRelatorios/geralServidoresAtivos.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Professores', '../grhRelatorios/geralServidoresProfessoresAtivos.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Administrativos e Técnicos', '../grhRelatorios/geralServidoresAdmTecAtivos.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Agrupados por Lotação', '../grhRelatorios/geralServidoresAtivoLotacao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Com Sexo - Agrupados por Lotação', '../grhRelatorios/geralServidoresAtivoLotacaoSexo.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Com CPF - Agrupados por Lotação', '../grhRelatorios/geralServidoresAtivoCpf.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Com CPF', '../grhRelatorios/geralServidoresAtivoCpf_1.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Assinatura', '../grhRelatorios/geralServidoresAtivosAssinatura.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Assinatura e CPF', '../grhRelatorios/geralServidoresAtivosAssinaturaCpf.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Email Uenf e CPF', '../grhRelatorios/geralServidoresAtivosEmailCpf.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Emails e CPF', '../grhRelatorios/geralServidoresAtivosEmailCpf2.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Check', '../grhRelatorios/geralServidoresAtivosCheck.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Check - Com Afastamentos', '../grhRelatorios/geralServidoresAtivosCheck.situacao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, Cpf, Cargo e Nascimento', '../grhRelatorios/geralServidoresNomeCpfNascimento.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, Cpf, Cargo, Nascimento e Admissão', '../grhRelatorios/geralServidoresNomeCpfNascimentoAdmissao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, Cpf, Cargo e Idade', '../grhRelatorios/geralServidoresNomeCpfCargoIdade.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, Cpf, Sexo e Nascimento', '../grhRelatorios/geralServidoresNomeCpfNascimentoSexo.php');
            $menu->show();
            break;

        ######################################

        case "geralInativos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Geral - Inativos');
            $menu->add_item('titulo1', 'Ordenados pela Data de Saída');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Inativos', '../grhRelatorios/geralServidoresInativos.php');
            $menu->add_item('linkWindow', 'Relatório de Concursados Inativos - Professores', '../grhRelatorios/geralServidoresInativosProfessores.php');
            $menu->add_item('linkWindow', 'Relatório de Concursados Inativos - Administrativos e Técnicos', '../grhRelatorios/geralServidoresInativosAdm.php');
            $menu->add_item('titulo1', 'Ordenados pelo Nome');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Inativos', '../grhRelatorios/geralServidoresInativos.porNome.php');
            $menu->add_item('linkWindow', 'Relatório de Concursados Inativos - Professores', '../grhRelatorios/geralServidoresInativosProfessores.porNome.php');
            $menu->add_item('linkWindow', 'Relatório de Concursados Inativos - Administrativos e Técnicos', '../grhRelatorios/geralServidoresInativosAdm.porNome.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários e Celetistas Inativos', '../grhRelatorios/servidores.estatutarios.celetistas.inativos.porNome.php');
            $menu->show();
            break;

        ######################################

        case "geralGeral";
            $menu = new Menu();
            $menu->add_item('titulo', 'Geral _ Ativos e Inativos');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos e Inativos - Agrupados por Lotação', '../grhRelatorios/geralServidoresLotacao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos e Inativos - Com CPF', '../grhRelatorios/geralServidoresCPF.php');
            $menu->show();
            break;

        ######################################
        /*
         * Retirado devido a área de afastamento já permite relatórios completos

          case "licenca";
          $menu = new Menu();
          $menu->add_item('titulo','Licença e Afastamentos');
          $menu->add_item('linkWindow','Relatório Mensal de Servidores com Afastamento Por Lotação','../grhRelatorios/afastamento.Mensal.php');
          $menu->add_item('linkWindow','Relatório Anual de Servidores com Afastamento Por Lotação','../grhRelatorios/afastamento.Anual.php');

          #$menu->add_item('linkWindow','Relatório Mensal de Servidores em Licença Por Lotação','../grhRelatorios/licencaMensal.php');
          #$menu->add_item('linkWindow','Relatório Anual de Servidores em Licença Por Lotação','../grhRelatorios/licencaAnualLotacao.php');
          #$menu->add_item('linkWindow','Relatório Mensal de Servidores em Licença Agrupados','../grhRelatorios/licencaMensalAgrupado.php');
          #$menu->add_item('linkWindow','Relatório Mensal de Servidores em Licença Sem Duplicidade','../grhRelatorios/licencaMensalAgrupadoSemDuplicidade.php');
          #$menu->add_item('linkWindow','Relatório Mensal de Término de Licença','../grhRelatorios/licencaVencimentoMensal.php');
          #$menu->add_item('linkWindow','Relatório Anual de Término de Licença','../grhRelatorios/licencaVencimentoAnual.php');
          #$menu->add_item('linkWindow','Relatório Anual de Término de Licença (Sem Prêmio)','../grhRelatorios/licencaVencimentoAnualSemPremio.php');
          #$menu->add_item('linkWindow','Relatório Anual de Licença Prêmio','../grhRelatorios/licencaPremioAnual.php');

          if(Verifica::acesso($idUsuario,1)){
          $menu->add_item('linkWindow','Relatório Geral Por Tipo','../grhRelatorios/licencaGeralporTipo.php');
          }
          $menu->show();
          break;

         * 
         */
         ######################################

          case "licencaPremio";
          $menu = new Menu();
          $menu->add_item('titulo','Licença Prêmio');
          $menu->add_item('linkWindow','Relatório de Licença Prêmio Fruídas - Anual','../grhRelatorios/licenca.premio.anual.php');
          $menu->add_item('linkWindow','Relatório de Licença Prêmio Fruídas - Período','../grhRelatorios/licenca.premio.periodo.php');
          $menu->show();
          break;
          
        ######################################

        case "lotacao";
            $menu = new Menu();
            $menu->add_item('titulo', 'Lotação');
            $menu->add_item('titulo1', 'Cadastro de Lotações');
            $menu->add_item('linkWindow', 'Relatório de Lotações Ativas', '../grhRelatorios/lotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Lotações Inativas', '../grhRelatorios/lotacaoInativa.php');

            $menu->add_item('titulo1', 'Servidores nas Lotações');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - Por Lotação', '../grhRelatorios/lotacaoServidoresAtivos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - Histórico Por Lotação', '../grhRelatorios/lotacaoServidoresAtivosHistorico.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - Pro-Reitorias', '../grhRelatorios/lotacaoServidoresAtivosProReitorias.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Inativos - Por Lotação', '../grhRelatorios/lotacaoEstatutariosInativos.php');

            $menu->add_item('titulo1', 'Movimentação de Lotação');
            $menu->add_item('linkWindow', 'Relatório Mensal de Movimentação de Lotação de Servidores', '../grhRelatorios/movimentacao.mensal.lotacao.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Movimentação de Lotação de Servidores', '../grhRelatorios/movimentacao.anual.lotacao.php');

            $menu->add_item('titulo1', 'Histórico de Servidores nas Lotações');
            $menu->add_item('linkWindow', 'Histórico de Servidores em uma Lotação', '../grhRelatorios/lotacao.historico.servidores.php');
            $menu->add_item('linkWindow', 'Histórico de Servidores Ativos em uma Lotação', '../grhRelatorios/lotacao.historico.servidores.ativos.php');
            #$menu->add_item('linkWindow','Lista de Telefones e Ramais - Agrupados por Diretoria','../grhRelatorios/ramais.php');
            $menu->show();
            break;

        ######################################

        case "atestado";
            $menu = new Menu();
            $menu->add_item('titulo', 'Atestado');
            $menu->add_item('linkWindow', 'Relatório Mensal de Servidores com Atestado', '../grhRelatorios/atestadoMensal.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Servidores com Atestado', '../grhRelatorios/atestadoAnual.php');

            $menu->show();
            break;

        ######################################

        case "faltas";
            $menu = new Menu();
            $menu->add_item('titulo', 'Faltas');
            $menu->add_item('linkWindow', 'Relatório Mensal de Faltas', '../grhRelatorios/faltasMensal.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Faltas', '../grhRelatorios/faltasAnual.php');

            $menu->show();
            break;

        ######################################

        case "movimentacao";
            $menu = new Menu();
            $menu->add_item('titulo', 'Movimentação de Pessoal');
            $menu->add_item('titulo1', 'Movimentação de Lotação');
            $menu->add_item('linkWindow', 'Relatório Mensal de Movimentação de Lotação de Servidores', '../grhRelatorios/movimentacao.mensal.lotacao.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Movimentação de Lotação de Servidores', '../grhRelatorios/movimentacao.anual.lotacao.php');

            $menu->add_item('titulo1', 'Todos os Servidores (Com CPF - Para o SigFis)');
            $menu->add_item('linkWindow', 'Relatório Anual de Servidores Admitidos', '../grhRelatorios/movimentacao.anual.geral.admitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Servidores Demitidos e Exonerados', '../grhRelatorios/movimentacao.anual.geral.demitidos.php');
            $menu->add_item('linkWindow', 'Relatório por Período de Servidores Demitidos e Exonerados', '../grhRelatorios/movimentacao.periodo.geral.demitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Servidores Nomeados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.geral.nomeados.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Servidores Exonerados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.geral.exonerados.php');

            $menu->add_item('titulo1', 'Todos os Servidores');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos Admitidos (Sem Agrupamento a partir de uma Data)', '../grhRelatorios/movimentacao.data.ativos.admitidos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Admitidos (Sem Agrupamento a partir de uma Data)', '../grhRelatorios/movimentacao.data.geral.admitidos.php');

            $menu->add_item('titulo1', 'Somente os Docentes');
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Admitidos', '../grhRelatorios/movimentacao.anual.docentes.admitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Demitidos e Exonerados', '../grhRelatorios/movimentacao.anual.docentes.demitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Nomeados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.docentes.nomeados.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Exonerados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.docentes.exonerados.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Docentes Demitidos e Exonerados (Sem Agrupamento a partir de uma Data)', '../grhRelatorios/movimentacao.data.docentes.demitidos.php');

            $menu->add_item('titulo1', 'Somente os Técnicos & Administrativos');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Admitidos', '../grhRelatorios/movimentacao.anual.administrativos.admitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Demitidos e Exonerados', '../grhRelatorios/movimentacao.anual.administrativos.demitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Nomeados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.administrativos.nomeados.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Exonerados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.administrativos.exonerados.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Administrativos & Técnicos Demitidos e Exonerados (Sem Agrupamento a partir de uma Data)', '../grhRelatorios/movimentacao.data.administrativos.demitidos.php');

            $menu->show();
            break;

        ######################################

        case "sispatri";
            $menu = new Menu();
            $menu->add_item('titulo', 'Sispatri');
            $menu->show();
            break;

        ######################################

        case "estatutarios";
            $menu = new Menu();
            $menu->add_item('titulo', 'Estatutários');
            $menu->add_item('linkWindow', 'Estatutários Ativos com Assinatura', '../grhRelatorios/estatutariosAtivosAssinatura.php');
            $menu->add_item('linkWindow', 'Estatutários Agrupados pela Lotação', '../grhRelatorios/estatutariosLotacao.php');
            $menu->add_item('linkWindow', 'Estatutários Agrupados pelo Cargo', '../grhRelatorios/estatutariosCargo.php');
            $menu->add_item('linkWindow', 'Estatutários Com CPF e Data de Nascimento', '../grhRelatorios/estatutariosCpfNascimento.php');
            $menu->add_item('linkWindow', 'Estatutários Inativos Por Lotação', '../grhRelatorios/lotacaoEstatutariosInativos.php');

            $menu->show();
            break;

        ######################################

        case "financeiro";
            $menu = new Menu();
            $menu->add_item('titulo', 'Financeiro');
            $menu->add_item('linkWindow', 'Financeiro', '../grhRelatorios/financeiro.php');
            $menu->add_item('linkWindow', 'Estatutarios Com Data de Nascimento, Faixa e Nivel do Plano de Cargos', '../grhRelatorios/financeiro.nascimento.porLotacao.php');

            $menu->show();
            break;

        ######################################

        case "contatos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Contatos');
            $menu->add_item('titulo1', 'Email');
            $menu->add_item('linkWindow', 'Email dos Servidores', '../grhRelatorios/email.php');
            $menu->add_item('linkWindow', 'Email dos Servidores 2', '../grhRelatorios/email2.php');
            $menu->add_item('linkWindow', 'Email dos Servidores 3', '../grhRelatorios/email3.php');
            $menu->add_item('linkWindow', 'Email dos Servidores 4', '../grhRelatorios/email4.php');

            $menu->add_item('titulo1', 'Telefone');
            $menu->add_item('linkWindow', 'Telefones dos Servidores', '../grhRelatorios/telefone.php');
            $menu->add_item('linkWindow', 'Telefones dos Servidores 2', '../grhRelatorios/telefone2.php');

            $menu->add_item('titulo1', 'Contatos');
            $menu->add_item('linkWindow', 'Contatos dos Servidores 1', '../grhRelatorios/contatos.php');
            $menu->add_item('linkWindow', 'Contatos dos Servidores 2', '../grhRelatorios/contatos2.php');
            $menu->add_item('linkWindow', 'Contatos dos Servidores (Formato Sispatri)', '../grhRelatorios/contatos.sispatri.php');
            #$menu->add_item('linkWindow', 'Servidores sem Algum dos Contatos', '../grhRelatorios/contatos.servidoresSemContatos.php');
            $menu->add_item('linkWindow', 'Servidores sem E-mail Institucional Cadastrado', '../grhRelatorios/contatos.servidoresSemEmail.php');
            $menu->add_item('linkWindow', 'Servidores sem E-mail Institucional ou Celular Cadastrado', '../grhRelatorios/contatos.servidoresSemEmailECelular.php');
            $menu->add_item('linkWindow', 'Servidores sem Celular Cadastrado', '../grhRelatorios/contatos.servidoresSemCelular.php');

            $menu->show();
            break;

        ######################################

        case "outros";
            $menu = new Menu();
            $menu->add_item('titulo', 'Outros');
            $menu->add_item('linkWindow', 'Relatório de Servidores Com Nacionalidade, Documentos e Telefone', '../grhRelatorios/relatorioOutro1.php');

            $menu->show();
            break;

        ######################################

        case "nacionalidade";
            $menu = new Menu();
            $menu->add_item('titulo', 'Censo');
            $menu->add_item('linkWindow', 'Relatório de Servidores Agrupados por Nacionalidade', '../grhRelatorios/geralServidoresNacionalidade.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Agrupados por Nacionalidade', '../grhRelatorios/professorNacionalidade.php');
            $menu->show();
            break;

        ######################################

        case "professores";
            $menu = new Menu();
            $menu->add_item('titulo', 'Professores');
            $menu->add_item('titulo1', 'Geral');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos', '../grhRelatorios/geralServidoresProfessoresAtivos.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos Agrupados por Cargo', '../grhRelatorios/professor.ativoPorCargo.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Aposentados Agrupados por Cargo', '../grhRelatorios/professor.aposentadoPorCargo.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Agrupados por Nacionalidade', '../grhRelatorios/professorNacionalidade.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Com Data de Nascimento e Sexo', '../grhRelatorios/professorIdadeSexo.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Agrupados por Lotaçao', '../grhRelatorios/professorporLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Com Email A Partir do Ano de Admissão', '../grhRelatorios/professores.email.porAnoAdmissao.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Com CPF, Nascimento e Email', '../grhRelatorios/professor.cpf.email.nascimento.php');
            
            $menu->add_item('titulo1', 'A Pedido da PROPPG');
            $menu->add_item('linkWindow', 'Relatório da Professores Ativos', '../grhRelatorios/professoresPROPPG.php');
            $menu->add_item('linkWindow', 'Relatório da Professores Não-Ativos', '../grhRelatorios/professoresPROPPGInativos.php');
            
            $menu->add_item('titulo1', 'A pedido da SECACAD - Censo Anual');
            $menu->add_item('linkWindow', 'Censo 2020 - Relatório da Professores', '../grhRelatorios/professoresCenso2020.php');
            $menu->add_item('linkWindow', 'Censo 2021 - Relatório da Professores Ativos', '../grhRelatorios/professoresCenso2021.php');
            $menu->add_item('linkWindow', 'Censo 2021 - Relatório da Professores Por Admissão', '../grhRelatorios/professoresCenso2021Admissao.php');
            $menu->add_item('linkWindow', 'Censo 2021 - Relatório da Professores Por Admissão 2', '../grhRelatorios/professoresCenso2021Admissao2.php');
            $menu->add_item('linkWindow', 'Censo 2021 - Relatório da Professores Por Data de Saída', '../grhRelatorios/professoresCenso2021Saida.php');

            $menu->show();
            break;

        ######################################

        case "eleitoral";
            $menu = new Menu();
            $menu->add_item('titulo', 'Processo Eleitoral');

            $menu->add_item('titulo1', 'Administrativo e Técnico');
            $menu->add_item('linkWindow', 'Relatório de Servidores Com Cargo por Locação', '../grhRelatorios/eleitoral.AdmTec.PorLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Com Cargo por Locação e Nivel do Cargo', '../grhRelatorios/eleitoral.AdmTec.NivelCargo.PorLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Com CPF e Assinatura Por Lotação', '../grhRelatorios/eleitoral.Assinatura.AdmTec.PorLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Com CPF e Assinatura Por Lotação - Polo Macaé', '../grhRelatorios/eleitoral.Assinatura.AdmTec.PoloMacae.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Com CPF e Assinatura Por Lotação - Polo Campos', '../grhRelatorios/eleitoral.Assinatura.AdmTec.PoloCampos.php');

            $menu->add_item('titulo1', 'Professores');
            $menu->add_item('linkWindow', 'Relatório de Professores Com Cargo por Locação', '../grhRelatorios/eleitoral.Professores.PorLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Com CPF e Assinatura por Locação', '../grhRelatorios/eleitoral.Assinatura.Professores.PorLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Com CPF e Assinatura - Polo Macaé', '../grhRelatorios/eleitoral.Assinatura.Professores.PoloMacae.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Com CPF e Assinatura - Polo Campos', '../grhRelatorios/eleitoral.Assinatura.Professores.PoloCampos.php');

            $menu->show();
            break;

        ######################################

        case "historico";
            $menu = new Menu();
            $menu->add_item('titulo', 'Histórico');
            $menu->add_item('titulo1', 'Servidores Ex-Fenorte');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Ativos', '../grhRelatorios/historico.exFenorte.ativos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Inativos', '../grhRelatorios/historico.exFenorte.inativos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Por Concurso', '../grhRelatorios/historico.exFenorte.porConcurso.php');

            $menu->add_item('titulo1', 'Anuais');
            $menu->add_item('linkWindow', 'Servidores Por Ano de Admissão Com Email e CPF Por Tipo de Cargo', '../grhRelatorios/servidoresPorAnoAdmissaoComEmailCpf.php');
            $menu->add_item('linkWindow', 'Servidores Por Ano de Saída Com Email e CPF Por Tipo de Cargo', '../grhRelatorios/servidoresPorAnoDemissaoComEmailCpf.php');
            $menu->add_item('linkWindow', 'Servidores Ativo em um Determinado Ano', '../grhRelatorios/servidoresAtivosPorAno.php');
            $menu->show();
            break;

        ######################################

        case "endereco";
            $menu = new Menu();
            $menu->add_item('titulo', 'Endereço');
            $menu->add_item('linkWindow', 'Relatório de Servidores Agrupado por Cidade', '../grhRelatorios/enderecoPorCidade.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Endereço, Emails e Telefones Agrupado por Lotaçao', '../grhRelatorios/enderecoEmailLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Endereço, CPF, Emails e Telefones Agrupado por Lotaçao', '../grhRelatorios/enderecoEmailLotacaoCpf.php');
            $menu->add_item('linkWindow', 'Relatório de Ativos e Aposentados Com Endereço', '../grhRelatorios/enderecoAtivoAposentado.php');
            $menu->show();
            break;

        ######################################

        case "parentes";
            $menu = new Menu();
            $menu->add_item('titulo', 'Parentes');
            $menu->add_item('linkWindow', 'Relatório de Parentes de Servidores ', '../grhRelatorios/parentes.geral.php');
            $menu->add_item('linkWindow', 'Relatório de Parentes de Servidores Com até 24 Anos', '../grhRelatorios/parentes.ate24.php');
            $menu->add_item('linkWindow', 'Relatório de Parentes de Servidores Com até 7 Anos', '../grhRelatorios/parentes.ate7.php');
            $menu->show();
            break;

        ######################################

        case "seguro";
            $menu = new Menu();
            $menu->add_item('titulo', 'Seguro Anual');
            $menu->add_item('linkWindow', 'Relatório Geral de Ativos', '../grhRelatorios/seguro.geral.php');
            $menu->add_item('linkWindow', 'Relatório de Administrativo e Tecnicos', '../grhRelatorios/seguro.admTec.php');
            $menu->add_item('linkWindow', 'Relatório de Professores', '../grhRelatorios/seguro.professores.php');
            $menu->show();
            callout("Os relatórios desta seção se referentem ao contrato de seguro solicitado anualmente pela PROGRAD.");
            break;

        ######################################

        case "recad2018";
            $menu = new Menu();
            $menu->add_item('titulo', 'Recadastramento 2018');
            $menu->add_item('titulo1', 'por Lotaçao');
            $menu->add_item('linkWindow', 'Servidores Ativos Recadastrados', '../grhRelatorios/recadastramentoLotacao.php');
            $menu->add_item('linkWindow', 'Servidores Ativos NÃO Recadastrados', '../grhRelatorios/recadastramentoFaltamLotacao.php');
            $menu->add_item('linkWindow', 'Servidores Inativos Recadastrados', '../grhRelatorios/recadastramentoLotacaoInativos.php');
            $menu->add_item('titulo1', 'por Cargo');
            $menu->add_item('linkWindow', 'Servidores Ativos Recadastrados', '../grhRelatorios/recadastramentoCargo.php');
            $menu->add_item('linkWindow', 'Servidores Ativos NÃO Recadastrados', '../grhRelatorios/recadastramentoFaltamCargo.php');
            $menu->add_item('linkWindow', 'Servidores Inativos Recadastrados', '../grhRelatorios/recadastramentoCargoInativos.php');
            $menu->add_item('titulo1', 'por Sisgen (Docentes Ativos)');
            $menu->add_item('linkWindow', 'Realizou Sisgen', '../grhRelatorios/recadastramentoSisgen.php?sisgen=1');
            $menu->add_item('linkWindow', 'Nao Realizou Sisgem', '../grhRelatorios/recadastramentoSisgen.php?sisgen=0');
            $menu->add_item('linkWindow', 'Nao Responderam o Anexo III', '../grhRelatorios/recadastramentoSisgen.php?sisgen=2');
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

            $resumo[] = array("Servidores Ativos", $totalAdm, $totalProfessores, $totalServidores);
            $resumo[] = array("Recadastrados Ativos", $recadastradosAdmAtivos, $recadastradosProfessoresAtivos, $recadastradosAtivosTotal);
            $resumo[] = array("Recadastrados Inativos", $recadastradosAdmInativos, $recadastradosProfessoresInativos, $recadastradosInativosTotal);
            $resumo[] = array("NÃO Recadastrados Ativos", ($totalAdm - $recadastradosAdmAtivos), ($totalProfessores - $recadastradosProfessoresAtivos), ($totalServidores - $recadastradosAtivosTotal));

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Descrição", "Adm/Tec", "Professores", "Total"));
            $tabela->set_totalRegistro(false);
            $tabela->set_align(array("left", "center"));
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

            $resumo[] = array("Realizaram", $realizaram);
            $resumo[] = array("Nao Realizaram", $naoRealizaram);
            $resumo[] = array("Nao Responderam", $naoResponderam);
            $total = $realizaram + $naoRealizaram + $naoResponderam;
            $resumo[] = array("Total", $total);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Descrição", "Nº de Servidores"));
            $tabela->set_totalRegistro(false);
            $tabela->set_align(array("left", "center"));
            $tabela->set_titulo("Sisgen (Ativos)");
            #$tabela->set_rodape("Total de Servidores: ".$totalServidores3);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ######################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}