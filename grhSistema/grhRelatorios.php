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
    $fase = get('fase', 'abono');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar, "left");

    # Gerador de Relat
    $botaoVoltar = new Link("Gerador de Relatórios", "geradorRelatorios.php?grh=true");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Gera relatórios');
    $botaoVoltar->set_accessKey('G');
    $menu1->add_link($botaoVoltar, "right");

    $menu1->show();

    # Verifica se veio da fase de ferias e se tem que mudar a data de entrega
    $dataDev = post("dtEntrega");

    # Salva o novo valor
    if (!vazio($dataDev)) {
        # Arquiva a data
        $intra->set_variavel("dataDevolucaoGrh", date_to_php($dataDev));
        loadPage('?fase=ferias');
    }

    $grid->fechaColuna();
    $grid->abreColuna(5, 4);

    # Array do menu
    $array = [
        ['Abono Permanencia', 'abono'],
        ['Aniversariantes', 'aniversariantes'],
        ['Afastamentos', 'afastamentos'],
        ['Aposentadoria e Tempo Averbado', 'aposentados'],
        ['Atestado', 'atestado'],
        ['Cargo Efetivo', 'cargoEfetivo'],
        ['Cargo em Comissão', 'cargoEmComissao'],
        ['Cedidos', 'cedidos'],
        ['Contatos & Endereços', 'contatos'],
        ['Dependentes & Auxílio Creche', 'dependentes'],
        ['Estatutários', 'estatutarios'],
        ['Etiquetas', 'etiquetas'],
        ['Férias', 'ferias'],
        ['Financeiro', 'financeiro'],
        ['Folha de Frequência', 'frequencia'],
        ['Geral - Servidores Ativos', 'geralAtivos'],
        ['Geral - Servidores Inativos', 'geralInativos'],
        ['Geral - Servidores Ativos e Inativos', 'geralGeral'],
        ['Licença Prêmio', 'licencaPremio'],
        ['Lotação', 'lotacao'],
        ['Movimentação de Pessoal', 'movimentacao'],
        ['Outros', 'outros'],
        ['Parentes', 'parentes'],
        ['Professores (Docentes)', 'professores'],
        ['Processo Eleitoral', 'eleitoral'],
        ['Seguro Anual', 'seguro'],
        ['Triênio', 'trienio'],
        ['Histórico', 'historico']
    ];

    # Menu de tipos de relatórios
    $menu = new Menu();
    $menu->add_item('titulo', 'Relatórios');

    foreach ($array as $item) {
        if ($fase == $item[1]) {
            $menu->add_item('link', "<b>| {$item[0]} |</b>", "?fase={$item[1]}");
        } else {
            $menu->add_item('link', $item[0], "?fase={$item[1]}", "?fase={$item[1]}");
        }
    }

    $menu->show();
    $grid->fechaColuna();

    ##########################################################
    # Menu dos Relatórios
    $grid->abreColuna(7, 8);

    ######################################

    switch ($fase) {

        case "abono";
            $menu = new Menu();
            $menu->add_item('titulo', 'Abono Permanência');
            #$menu->add_item('linkWindow','Relatório de Estatutários com Idade para Aposentadoria','../grhRelatorios/servIdadeAposent.php');
            #$menu->add_item('linkWindow','Relatório de Estatutários que Atingiram Idade para Aposentadoria','../grhRelatorios/servidoresComIdadeParaAposentar.php');
            #$menu->add_item('linkWindow','Relatório Geral de Servidores Aposentados - Com Email e Telefone','../grhRelatorios/geralServidoresAposentados.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Estatutarios - com Abono Permanencia Deferido', '../grhRelatorios/abonoPermanencia.geral.deferido.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Estatutarios - com Abono Permanencia Indeferido', '../grhRelatorios/abonoPermanencia.geral.indeferido.php');

            $menu->show();
            break;

        ######################################

        case "aniversariantes";
            $menu = new Menu();
            $menu->add_item('titulo', 'Aniversariantes');
            $menu->add_item('linkWindow', 'Relatório Anual de Aniversariantes por Lotação', '../grhRelatorios/aniversariantes.anual.lotacao.php');

            $menu->show();
            break;

        ######################################

        case "afastamentos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Afastamentos');

            $menu->add_item('titulo1', 'Faltas');
            $menu->add_item('linkWindow', 'Relatório de Faltas - Mensal', '../grhRelatorios/faltas.mensal.php');
            $menu->add_item('linkWindow', 'Relatório de Faltas - Anual', '../grhRelatorios/faltas.anual.php');

            $menu->add_item('titulo1', 'Geral');
            $menu->add_item('linkWindow', 'Relatório de Professores - com Afastamento Maior que 15 Dias', '../grhRelatorios/afastamento.docente.maiorque15.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores - com Afastamento Maior que 30 Dias', '../grhRelatorios/afastamento.geral.maiorque30.php');
            $menu->add_item('linkWindow', 'Relatório Mensal de Servidores com Afastamento', '../grhRelatorios/afastamento.geral.mensal.php');
            
            #$menu->add_item('linkWindow', 'Relatório de Servidores Ativos Com Afastamentos em um Mês Específico', '../grhRelatorios/geralServidoresAtivosCheck.afastamentos.php');

            $menu->show();
            break;

        ######################################

        case "aposentados";
            $menu = new Menu();
            $menu->add_item('titulo', 'Aposentadoria e Tempo Averbado');

            $menu->add_item('titulo1', 'Servidores Ativos');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutário - Sem Tempo Averbado', '../grhRelatorios/estatutarios.ativos.semTempoAverbado.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutário - Com o Tempo Averbado e de Uenf', '../grhRelatorios/estatutarios.ativos.tempoUenf.averbado.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários - Análise de Aposentadoria', '../grhRelatorios/estatutarios.ativos.analise.aposentadoria.php');

            $menu->add_item('titulo1', 'Servidores Aposentados');
            $menu->add_item('linkWindow', 'Relatório de Servidores Aposentados - Geral', '../grhRelatorios/aposentados.geral.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Aposentados - Por Cargo - Professores', '../grhRelatorios/aposentados.professor.porCargo.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Aposentados - Por Cargo - Administrativo e Técnicos', '../grhRelatorios/aposentados.admtec.porCargo.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Aposentados - Com Tempo de Serviço Publico Averbado', '../grhRelatorios/aposentados.tempo.publico.averbado.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Aposentados - Por Período', '../grhRelatorios/aposentados.porPeriodo.php');

            $menu->show();
            break;

        ######################################

        case "atestado";
            $menu = new Menu();
            $menu->add_item('titulo', 'Atestado');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Atestado - Mensal', '../grhRelatorios/atestado.mensal.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores com Atestado - Anual', '../grhRelatorios/atestado.anual.php');

            $menu->show();
            break;

        ######################################

        case "cargoEfetivo";
            $menu = new Menu();
            $menu->add_item('titulo', 'Cargo Efetivo');

            $menu->add_item('titulo1', 'Administrativos e Técnicos');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários - Administrativos e Técnicos - por Lotação', '../grhRelatorios/cargo.admTec.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários - Administrativos e Técnicos - por Sexo', '../grhRelatorios/cargo.admTec.porSexo.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários - Administrativos e Técnicos - por Escolaridade do Cargo', '../grhRelatorios/cargo.admTec.porEscolaridadeCargo.php');

            $menu->add_item('titulo1', 'Professores');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários - Professores - por Lotação', '../grhRelatorios/cargo.professor.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários - Professores - por Sexo', '../grhRelatorios/cargo.professor.porSexo.php');

            $menu->add_item('titulo1', 'Geral');
            $menu->add_item('linkWindow', 'Relatório de Cargos - Agrupados por Nível', '../grhRelatorios/cargo.nivel.php');
            $menu->add_item('linkWindow', 'Relatório de Cargos - Numero de Servidores Ativos por Diretoria / Cargo', '../grhRelatorios/cargo.nivel.lotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários - Por Cargo', '../grhRelatorios/cargo.estatutarios.php');

            $menu->show();
            break;

        ######################################

        case "cargoEmComissao";
            $menu = new Menu();
            $menu->add_item('titulo', 'Cargos Em Comissão');
            $menu->add_item('titulo1', 'Relação dos Cargos');
            $menu->add_item('linkWindow', 'Relatório dos Cargos em Comissão Ativos', '../grhRelatorios/cargoComissao.ativos.php');
            $menu->add_item('linkWindow', 'Relatório dos Cargos em Comissão Inativos', '../grhRelatorios/cargoComissao.inativos.php');

            $menu->add_item('titulo1', 'Relação dos Servidores com Cargo em Comissão');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - Vigentes', '../grhRelatorios/cargoComissao.vigentes.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - Agrupados por Cargo', '../grhRelatorios/cargoComissao.geral.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - com Vagas e Agrupados por Cargo', '../grhRelatorios/cargoComissao.vagas.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - Agrupados por Lotacao', '../grhRelatorios/cargoComissao.lotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - Agrupados por Cargo - Com CPF e RG', '../grhRelatorios/cargoComissao.cpf.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - Docentes', '../grhRelatorios/cargoComissao.docentes.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - Adm & Tec', '../grhRelatorios/cargoComissao.admTec.php');

            $menu->add_item('titulo1', 'Histórico');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - Histórico - Cargos Ativos', '../grhRelatorios/cargoComissao.historico.cargosAtivos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores - com Cargos em Comissão - Histórico - Cargos Inativos', '../grhRelatorios/cargoComissao.historico.cargosInativos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - com Cargos em Comissão - Docentes - Histórico por Ano', '../grhRelatorios/cargoComissao.historico.docentes.porAno.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - com Cargos em Comissão - Adm & Tec - Histórico por Ano', '../grhRelatorios/cargoComissao.historico.admTec.porAno.php');

            $menu->show();
            break;

        ######################################

        case "cedidos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Cedidos');
            $menu->add_item('titulo1', 'Cedidos da Uenf para Outro Órgão - Ativos');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Ativos - Cedidos', '../grhRelatorios/estatutarios.cedidos.geral.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Ativos - Cedidos - Admin e Tecnicos', '../grhRelatorios/estatutarios.cedidos.admin.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Ativos - Cedidos - Professores', '../grhRelatorios/estatutarios.cedidos.professores.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Ativos - Cedidos - Agrupados por Órgão', '../grhRelatorios/estatutarios.cedidos.porOrgao.php');
            $menu->add_item('titulo1', 'Cedidos da Uenf para Outro Órgão - Geral');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Geral - Cedidos - Por Ano', '../grhRelatorios/estatutarios.cedidos.porAno.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Geral - Cedidos - Por Ano do Início da Cessão', '../grhRelatorios/estatutarios.cedidos.porAnoCessao.php');
            $menu->add_item('titulo1', 'Cedidos de Fora');
            #$menu->add_item('linkWindow','Escala Anual de Férias - Servidores Técnicos Estatutários Cedidos','../grhRelatorios/escalaAnualFeriasTecnicosSandraCedidos.php');
            $menu->add_item('linkWindow', 'Relatório de Cedidos de Outros Órgãos - Agrupados por Órgão', '../grhRelatorios/cedidos.porOrgao.php');

            $menu->show();
            break;

        ######################################

        case "contatos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Contatos & Endereços');
            $menu->add_item('titulo1', 'Email');
            $menu->add_item('linkWindow', 'Email Institucional dos Servidores Ativos', '../grhRelatorios/contatos.ativos.email.institucional.php');
            $menu->add_item('linkWindow', 'Email Institucional dos Servidores Administrativos/Técnicos Ativos', '../grhRelatorios/contatos.ativos.admi.email.institucional.php');
            $menu->add_item('linkWindow', 'Email Pessoal dos Servidores Ativos', '../grhRelatorios/contatos.ativos.email.pessoal.php');
            $menu->add_item('linkWindow', 'Emails dos Servidores Ativos', '../grhRelatorios/contatos.ativos.emails.php');
            $menu->add_item('linkWindow', 'Emails dos Servidores Inativos', '../grhRelatorios/contatos.inativos.emails.php');
            $menu->add_item('linkWindow', 'Servidores sem E-mail Institucional Cadastrado', '../grhRelatorios/contatos.ativos.semEmail.institucional.php');

            $menu->add_item('titulo1', 'Telefone');
            $menu->add_item('linkWindow', 'Telefones dos Servidores Ativos', '../grhRelatorios/contatos.ativos.telefones.php');
            $menu->add_item('linkWindow', 'Servidores sem Celular Cadastrado', '../grhRelatorios/contatos.ativos.servidoresSemCelular.php');

            $menu->add_item('titulo1', 'Contatos');
            $menu->add_item('linkWindow', 'Contatos dos Servidores Ativos', '../grhRelatorios/contatos.ativos.php');
            $menu->add_item('linkWindow', 'Contatos dos Servidores Ativos - Formato Sispatri', '../grhRelatorios/contatos.ativos.sispatri.php');
            $menu->add_item('linkWindow', 'Servidores sem E-mail Institucional ou Celular Cadastrado', '../grhRelatorios/contatos.ativos.semEmailECelular.php');

            $menu->add_item('titulo1', 'Endereços');
            $menu->add_item('linkWindow', 'Endereço de Servidores Ativos Agrupado por Cidade', '../grhRelatorios/endereco.porCidade.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos com Endereço, Emails e Telefones Agrupado por Lotaçao', '../grhRelatorios/endereco.email.lotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos com Endereço, CPF, Emails e Telefones Agrupado por Lotaçao', '../grhRelatorios/endereco.email.lotacao.cpf.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos e Aposentados Com Endereço', '../grhRelatorios/endereco.ativos.aposentados.php');

            $menu->show();
            break;

        ######################################

        case "dependentes";
            $menu = new Menu();
            $menu->add_item('titulo', 'Dependentes & Auxílio Creche');

            $menu->add_item('titulo1', 'Dependentes');
            $menu->add_item('linkWindow', 'Relatório Servidores Ativos - com Dependente (Filhos)', '../grhRelatorios/dependentes.servidores.ativos.comFilho.php');
            $menu->add_item('linkWindow', 'Relatório de Dependentes - Filhos de Servidores Ativos', '../grhRelatorios/dependentes.filhos.ativos.php');

            $menu->add_item('titulo1', 'Auxílio Creche');
            $menu->add_item('linkWindow', 'Relatório Geral de Auxílio Creche', '../grhRelatorios/auxilioCreche.servidores.ativos.php');
            $menu->add_item('linkWindow', 'Relatório de Vencimento de Auxilio Creche - Mensal', '../grhRelatorios/auxilioCreche.vencimento.mensal.php');
            $menu->add_item('linkWindow', 'Relatório de Vencimento de Auxilio Creche - Anual', '../grhRelatorios/auxilioCreche.vencimento.anual.php');

            $menu->show();
            break;

        ######################################

        case "diarias";
//            $menu = new Menu();
//            $menu->add_item('titulo', 'Diárias');
//            $menu->add_item('linkWindow', 'Relatório Mensal pela Data do Processo', '../grhRelatorios/diariasMensalDataProcesso.php');
//            $menu->add_item('linkWindow', 'Relatório Anual pela Data do Processo', '../grhRelatorios/diariasAnualDataProcesso.php');
//            $menu->add_item('linkWindow', 'Relatório Mensal por Data de Saída', '../grhRelatorios/diariasMensal.php');
//            $menu->add_item('linkWindow', 'Relatório Anual por Data de Saída', '../grhRelatorios/diariasAnual.php');
//
//            $menu->show();
            break;

        ######################################

        case "estatutarios";
            $menu = new Menu();
            $menu->add_item('titulo', 'Estatutários');
            $menu->add_item('titulo1', 'Ativos');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com Assinatura', '../grhRelatorios/estatutarios.ativos.assinatura.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - Agrupados pela Lotação', '../grhRelatorios/estatutarios.ativos.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - Agrupados pelo Cargo', '../grhRelatorios/estatutarios.ativos.porCargo.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com CPF e Data de Nascimento', '../grhRelatorios/estatutarios.ativos.cpf.nascimento.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com Idade', '../grhRelatorios/estatutarios.ativos.porIdade.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com Cargo e E-mail - Por Ano de Admissão', '../grhRelatorios/estatutarios.ativos.email.porAnoAdmissao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - Agrupados por Nacionalidade', '../grhRelatorios/estatutarios.ativos.porNacionalidade.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com E-mails', '../grhRelatorios/estatutarios.ativos.email.php');

            $menu->add_item('titulo1', 'Inativos');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Inativos - por Lotação', '../grhRelatorios/estatutarios.inativos.porLotacao.php');

            $menu->show();
            break;

        ######################################

        case "etiquetas";
            $menu = new Menu();
            $menu->add_item('titulo', 'Etiquetas');
            $menu->add_item('titulo1', 'Etiquetas');
            $menu->add_item('linkWindow', 'Etiquetas Geral', '../grhRelatorios/etiqueta.geral.php');

            $menu->add_item('titulo1', 'Listagem de Apoio');
            $menu->add_item('linkWindow', 'Listagem Geral para Checagem', '../grhRelatorios/etiqueta.geral.check.php');

            $menu->show();
            break;

        ######################################

        case "ferias";
            $menu = new Menu();
            $menu->add_item('titulo', 'Férias');
            $menu->add_item('titulo1', 'Escala de Férias');
            $menu->add_item('linkWindow', 'Escala Anual de Férias - por Ano de Exercício', '../grhRelatorios/ferias.escala.anual.porDiretoria.php');
            $menu->add_item('linkWindow', 'Escala Anual de Férias - Cedidos da Uenf - por Ano de Exercício', '../grhRelatorios/ferias.escala.anual.cedidos.php');

            $menu->add_item('titulo1', 'Recesso de Professores');
            $menu->add_item('linkWindow', 'Relatório de Professores com Afastamento Maior que 15 Dias', '../grhRelatorios/afastamento.docente.maiorque15.php');

            $menu->add_item('titulo1', 'Férias Fruídas');
            $menu->add_item('linkWindow', 'Total de Dias Fruídos por Ano de Exercício - Professor Ativo', '../grhRelatorios/ferias.exercicio.porTotalDias.professor.php');
            $menu->add_item('linkWindow', 'Total de Dias Fruídos por Ano de Exercício - Adm & Tec Ativo', '../grhRelatorios/ferias.exercicio.porTotalDias.adm.php');
            $menu->add_item('linkWindow', 'Férias Fruídas por Ano de Exercício - Professor Ativo', '../grhRelatorios/ferias.exercicio.fruidas.professor.php');
            $menu->add_item('linkWindow', 'Férias Fruídas por Ano de Exercício - Adm & Tec Ativo', '../grhRelatorios/ferias.exercicio.fruidas.adm.php');

            $menu->show();
            break;

        ######################################

        case "financeiro";
            $menu = new Menu();
            $menu->add_item('titulo', 'Financeiro');
            $menu->add_item('titulo1', 'Geral');
            $menu->add_item('linkWindow', 'Relatório Geral Financeiro', '../grhRelatorios/financeiro.geral.php');

            $menu->add_item('titulo1', 'Estatutários');
            $menu->add_item('linkWindow', 'Relatório de Estatutarios Com Data de Nascimento, Faixa e Nivel do Plano de Cargos', '../grhRelatorios/financeiro.nascimento.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutarios Com Data de Admissão, Faixa e Nivel do Plano de Cargos', '../grhRelatorios/financeiro.admissao.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutarios Com Faixa e Nivel do Plano de Cargos', '../grhRelatorios/financeiro.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutarios Por Cargo Com Faixa e Nivel do Plano de Cargos', '../grhRelatorios/financeiro.porLotacao.porCargo.php');

            $menu->show();
            break;

        ######################################

        case "frequencia";
            $menu = new Menu();
            $menu->add_item('titulo', 'Folha de Frequência dos Servidores');
            $menu->add_item('linkWindow', 'Folha de Frequencia de uma Lotação', '../grhRelatorios/folhaFrequencia.porLotacao.php');

            $menu->show();
            break;

        ######################################

        case "outros";
            $menu = new Menu();
            $menu->add_item('titulo', 'Outros');
            $menu->add_item('linkWindow', 'Formulário de saúde de uma lotação', '../grhRelatorios/formularioSaude.porLotacao.php');

            $menu->show();
            break;

        ######################################

        case "geralAtivos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Geral - Servidores Ativos');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos', '../grhRelatorios/geral.servidores.ativos.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Professores', '../grhRelatorios/geral.servidores.ativos.professores.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Administrativos e Técnicos', '../grhRelatorios/geral.servidores.ativos.admTec.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Agrupados por Lotação', '../grhRelatorios/geral.servidores.ativos.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Com Sexo - Agrupados por Lotação', '../grhRelatorios/geral.servidores.ativos.sexo.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Com CPF - Agrupados por Lotação', '../grhRelatorios/geral.servidores.ativos.cpf.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Com CPF', '../grhRelatorios/geral.servidores.ativos.cpf.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Assinatura', '../grhRelatorios/geral.servidores.ativos.assinatura.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Assinatura e CPF', '../grhRelatorios/geral.servidores.ativos.cpf.assinatura.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Email', '../grhRelatorios/geral.servidores.ativos.email.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Email Uenf e CPF', '../grhRelatorios/geral.servidores.ativos.email.cpf.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Emails e CPF', '../grhRelatorios/geral.servidores.ativos.email.cpf2.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Check', '../grhRelatorios/geral.servidores.ativos.check.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, Cpf, Cargo e Nascimento', '../grhRelatorios/geral.servidores.ativos.nome.cpf.nascimento.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, Cpf, Cargo, Nascimento e Admissão', '../grhRelatorios/geral.servidores.ativos.nome.cpf.nascimento.admissao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, Cpf, Cargo e Idade', '../grhRelatorios/geral.servidores.ativos.nome.cpf.cargo.idade.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, Cpf, Sexo e Nascimento', '../grhRelatorios/geral.servidores.ativos.nome.cpf.nascimento.sexo.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - Nome, CPF, Identidade, Nacionalidade e Contatos', '../grhRelatorios/geral.servidores.ativos.nome.cpf.nacionalidade.contatos.php');
            $menu->show();
            break;

        ######################################

        case "geralInativos";
            $menu = new Menu();
            $menu->add_item('titulo', 'Geral - Inativos');
            $menu->add_item('titulo1', 'Ordenados pela Data de Saída');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Inativos', '../grhRelatorios/geral.servidores.inativos.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Inativos - Desde um ano', '../grhRelatorios/geral.servidores.inativos.desdeAno.php');
            $menu->add_item('linkWindow', 'Relatório de Concursados Inativos - Professores', '../grhRelatorios/geral.concursados.inativos.professores.php');
            $menu->add_item('linkWindow', 'Relatório de Concursados Inativos - Administrativos e Técnicos', '../grhRelatorios/geral.concursados.inativos.admTec2.php');
            $menu->add_item('titulo1', 'Ordenados pelo Nome');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Inativos', '../grhRelatorios/geral.servidores.inativos.porNome.php');
            $menu->add_item('linkWindow', 'Relatório de Concursados Inativos - Professores', '../grhRelatorios/geral.concursados.inativos.professores.porNome.php');
            $menu->add_item('linkWindow', 'Relatório de Concursados Inativos - Administrativos e Técnicos', '../grhRelatorios/geral.concursados.inativos.admTec.porNome.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários e Celetistas - Exonerados ou Demitidos', '../grhRelatorios/servidores.estatutarios.celetistas.inativos.porNome.php');
            $menu->show();
            break;

        ######################################

        case "geralGeral";
            $menu = new Menu();
            $menu->add_item('titulo', 'Geral - Ativos e Inativos');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos e Inativos - Agrupados por Lotação', '../grhRelatorios/geral.servidores.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos e Inativos - Com CPF', '../grhRelatorios/geral.servidores.cpf.php');
            $menu->show();
            break;

        ######################################

        case "licencaPremio";
            $menu = new Menu();
            $menu->add_item('titulo', 'Licença Prêmio');
            $menu->add_item('linkWindow', 'Relatório de Licença Prêmio Fruídas - Anual', '../grhRelatorios/licenca.premio.anual.php');
            $menu->add_item('linkWindow', 'Relatório de Licença Prêmio Fruídas - Período', '../grhRelatorios/licenca.premio.periodo.php');
            $menu->show();
            break;

        ######################################

        case "lotacao";
            $menu = new Menu();
            $menu->add_item('titulo', 'Lotação');
            $menu->add_item('titulo1', 'Cadastro de Lotações');
            $menu->add_item('linkWindow', 'Relatório de Lotações Ativas', '../grhRelatorios/lotacao.ativa.php');
            $menu->add_item('linkWindow', 'Relatório de Lotações Inativas', '../grhRelatorios/lotacao.inativa.php');

            $menu->add_item('titulo1', 'Servidores nas Lotações');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - Por Lotação', '../grhRelatorios/lotacao.servidores.ativos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - Histórico Por Lotação', '../grhRelatorios/lotacao.servidores.ativos.historico.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Inativos - Por Lotação', '../grhRelatorios/estatutarios.inativos.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Ativos - Por Lotação', '../grhRelatorios/estatutarios.ativos.porLotacao.php');

            $menu->add_item('titulo1', 'Movimentação de Lotação');
            $menu->add_item('linkWindow', 'Relatório Mensal de Movimentação de Lotação de Servidores', '../grhRelatorios/movimentacao.mensal.lotacao.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Movimentação de Lotação de Servidores', '../grhRelatorios/movimentacao.anual.lotacao.php');

            $menu->add_item('titulo1', 'Histórico de Servidores nas Lotações');
            $menu->add_item('linkWindow', 'Histórico de Servidores em uma Lotação', '../grhRelatorios/lotacao.historico.servidores.php');
            $menu->add_item('linkWindow', 'Histórico de Servidores Ativos em uma Lotação', '../grhRelatorios/lotacao.historico.servidores.ativos.php');
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
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Admitidos com Nacionalidade', '../grhRelatorios/movimentacao.anual.docentes.admitidos.nacionalidade.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Demitidos, Aposentados, Exonerados, etc', '../grhRelatorios/movimentacao.anual.docentes.demitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Demitidos, Aposentados, Exonerados, etc com Nacionalidade', '../grhRelatorios/movimentacao.anual.docentes.demitidos.nacionalidade.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Nomeados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.docentes.nomeados.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Docentes Exonerados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.docentes.exonerados.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Docentes Demitidos e Exonerados (Sem Agrupamento a partir de uma Data)', '../grhRelatorios/movimentacao.data.docentes.demitidos.php');

            $menu->add_item('titulo1', 'Somente os Técnicos & Administrativos');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Admitidos', '../grhRelatorios/movimentacao.anual.administrativos.admitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Admitidos com Nacionalidade', '../grhRelatorios/movimentacao.anual.administrativos.admitidos.nacionalidade.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Demitidos, Aposentados, Exonerados, etc', '../grhRelatorios/movimentacao.anual.administrativos.demitidos.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Nomeados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.administrativos.nomeados.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Administrativos & Técnicos Exonerados (Cargo em Comissão)', '../grhRelatorios/movimentacao.anual.administrativos.exonerados.php');
            $menu->add_item('linkWindow', 'Relatório de Administrativos & Técnicos Demitidos e Exonerados (Sem Agrupamento a partir de uma Data)', '../grhRelatorios/movimentacao.data.administrativos.demitidos.php');
            $menu->add_item('linkWindow', 'Relatório de Estatutários Administrativos & Técnicos Demitidos e Exonerados (Sem Agrupamento a partir de uma Data)', '../grhRelatorios/movimentacao.data.administrativos.estatutarios.demitidos.php');

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

        case "professores";
            $menu = new Menu();
            $menu->add_item('titulo', 'Professores');
            $menu->add_item('titulo1', 'Geral');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos', '../grhRelatorios/geral.servidores.ativos.professores.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos - Agrupados por Cargo', '../grhRelatorios/professores.ativos.porCargo.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos - Agrupados por Nacionalidade', '../grhRelatorios/professores.ativos.porNacionalidade.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos - Estrangeiros com E-mail', '../grhRelatorios/professores.ativos.estrangeiros.email.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos - Com Data de Nascimento, Idade e Sexo', '../grhRelatorios/professores.ativos.idade.sexo.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos - Agrupados por Lotaçao', '../grhRelatorios/professores.ativos.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos - Com Email A Partir do Ano de Admissão', '../grhRelatorios/professores.ativos.email.porAnoAdmissao.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos - Com CPF, Data de Nascimento e Email', '../grhRelatorios/professores.ativos.email.cpf.nascimento.php');
            $menu->add_item('linkWindow', 'Relatório de Professores Ativos - A Partir do Ano de Admissão', '../grhRelatorios/professores.ativos.porAnoAdmissao.php');

            $menu->add_item('titulo1', 'A Pedido da PROPPG');
            $menu->add_item('linkWindow', 'Relatório da Professores Ativos', '../grhRelatorios/professores.ativos.proppg.php');
            $menu->add_item('linkWindow', 'Relatório da Professores Inativos', '../grhRelatorios/professores.inativos.proppg.php');

            $menu->add_item('titulo1', 'A pedido da SECACAD - Censo Anual');
            $menu->add_item('linkWindow', 'Censo 2020 - Relatório da Professores Ativos - Em um Ano Específico - Com CPF, Nome da Mãe e E-mails', '../grhRelatorios/professores.censo.2020.php');
            $menu->add_item('linkWindow', 'Censo 2021 - Relatório da Professores Ativos - Com CPF e E-mails', '../grhRelatorios/professores.censo.2021.php');
            $menu->add_item('linkWindow', 'Censo 2021 - Relatório da Professores - Por Ano de Admissão - Com CPF e E-mails', '../grhRelatorios/professores.censo.2021.PorAnoAdmissao.php');
            $menu->add_item('linkWindow', 'Censo 2021 - Relatório da Professores - Por Ano de Admissão - Com CPF, Nome da Mãe e E-mails', '../grhRelatorios/professores.censo.2021.PorAnoAdmissao2.php');
            $menu->add_item('linkWindow', 'Censo 2021 - Relatório da Professores Inativos - Por Ano de Saída', '../grhRelatorios/professores.censo.2021.porAnoSaida.php');
            $menu->add_item('linkWindow', 'Censo 2024 - Relatório da Professores - Por Ano de Admissão', '../grhRelatorios/professores.censo.2024.porAnoAdmissao.php');
            $menu->add_item('linkWindow', 'Censo 2024 - Relatório da Professores Inativos - Por Ano de Saída', '../grhRelatorios/professores.censo.2024.porAnoSaida.php');
            $menu->add_item('linkWindow', 'Censo 2024 - Relatório da Professores - Com E-mail', '../grhRelatorios/professores.censo.2024.comEmail.php');

            $menu->show();
            break;

        ######################################

        case "eleitoral";
            $menu = new Menu();
            $menu->add_item('titulo', 'Processo Eleitoral'); ### parei aqui

            $menu->add_item('titulo1', 'Geral');
            $menu->add_item('linkWindow', 'Relatório Geral de Servidores Ativos - com CPF, RG e Cargo - por Locação - Agrupado por Diretoria', '../grhRelatorios/eleitoral.geral.porLotacao.php');

            $menu->add_item('titulo1', 'Administrativo e Técnico');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com Cargo - por Locação - Agrupado por Gerência', '../grhRelatorios/eleitoral.estatutarios.admTec.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com Cargo - por Locação - Agrupado por Nivel do Cargo', '../grhRelatorios/eleitoral.estatutarios.admTec.nivelCargo.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com CPF e Assinatura - por Lotação', '../grhRelatorios/eleitoral.estatutarios.admTec.assinatura.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com CPF e Assinatura - Polo Macaé', '../grhRelatorios/eleitoral.estatutarios.admTec.assinatura.poloMacae.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com CPF e Assinatura - Polo Campos', '../grhRelatorios/eleitoral.estatutarios.admTec.assinatura.poloCampos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - com CPF, RG e Cargo - por Locação - Agrupado por Diretoria', '../grhRelatorios/eleitoral.geral.admTec.porLotacao.php');

            $menu->add_item('titulo1', 'Professores');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com Cargo - por Locação - Agrupado por Gerência', '../grhRelatorios/eleitoral.estatutarios.professores.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com CPF e Assinatura - por Lotação', '../grhRelatorios/eleitoral.estatutarios.professores.assinatura.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com CPF e Assinatura - Polo Macaé', '../grhRelatorios/eleitoral.estatutarios.professores.assinatura.poloMacae.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Estatutários Ativos - com CPF e Assinatura - Polo Campos', '../grhRelatorios/eleitoral.estatutarios.professores.assinatura.poloCampos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - com CPF, RG e Cargo - por Locação - Agrupado por Diretoria', '../grhRelatorios/eleitoral.geral.professores.porLotacao.php');

            $menu->show();
            break;

        ######################################

        case "seguro";
            $menu = new Menu();
            $menu->add_item('titulo', 'Seguro Anual');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - Geral - por Lotação', '../grhRelatorios/seguro.geral.ativos.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - Administrativo e Tecnicos - por Lotação', '../grhRelatorios/seguro.admTec.ativos.porLotacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - Professores - por Lotação', '../grhRelatorios/seguro.professores.ativos.porLotacao.php');
            $menu->show();
            callout("Os relatórios desta seção se referentem ao contrato de seguro solicitado anualmente pela PROGRAD.");
            break;

        ######################################

        case "trienio";
            $menu = new Menu();
            $menu->add_item('titulo', 'Triênio');
            $menu->add_item('titulo1', 'Servidores com Triêmio');
            $menu->add_item('linkWindow', 'Relatório Geral de Triênio - Servidores Estatutários Ativos', '../grhRelatorios/trienio.geral.ativos.php');
            $menu->add_item('linkWindow', 'Relatório de Triênio por Lotação', '../grhRelatorios/trienio.geral.ativos.porLotacao.php');

            $menu->add_item('titulo1', 'Vencimento de Triênios');
            $menu->add_item('linkWindow', 'Relatório Mensal de Vencimento de Triênios', '../grhRelatorios/trienio.vencimento.mensal.php');
            $menu->add_item('linkWindow', 'Relatório Anual de Vencimento de Triênios', '../grhRelatorios/trienio.vencimento.anual.php');

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

        case "historico";
            $menu = new Menu();
            $menu->add_item('titulo', 'Histórico');
            $menu->add_item('titulo1', 'Servidores Ex-Fenorte');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Ativos', '../grhRelatorios/historico.exFenorte.ativos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Ativos - Agrupador por Cargo', '../grhRelatorios/historico.exFenorte.porCargo.ativos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Inativos - Agrupados por Situação', '../grhRelatorios/historico.exFenorte.inativos.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Geral - Agrupados por Situação', '../grhRelatorios/historico.exFenorte.geral.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Geral - por Concurso - Agrupados por Situação', '../grhRelatorios/historico.exFenorte.porConcurso.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Geral - com Dados de Tempo Averbado', '../grhRelatorios/historico.exFenorte.geral.averbacao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ex-Fenorte Estatutários - Com Telefones e E-mails', '../grhRelatorios/historico.exFenorte.estatutarios.telefone.php');

            $menu->add_item('titulo1', 'Histórico Anual');
            $menu->add_item('linkWindow', 'Relatório de Servidores Ativos - em um Determinado Ano', '../grhRelatorios/historico.servidores.ativos.porAno.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores -  com E-mail e CPF - por Ano de Admissão - por Tipo de Cargo', '../grhRelatorios/historico.servidores.admissao.email.cpf.porAnoAdmissao.php');
            $menu->add_item('linkWindow', 'Relatório de Servidores -  com E-mail e CPF - por Ano de Saída - por Tipo de Cargo', '../grhRelatorios/historico.servidores.admissao.email.cpf.porAnoSaida.php');
            $menu->show();
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

    # Fecha o grid
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}