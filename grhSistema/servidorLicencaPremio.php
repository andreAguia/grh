<?php

/**
 * Histórico de Licenças Prêmio de um servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de licenças prêmio";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Inicia a classe de licença
    $licenca = new LicencaPremio();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio da área de Licença Premio
    $areaPremio = get_session("areaPremio");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Rotina em Jscript
    $script = '<script type="text/javascript" language="javascript">
        
            $(document).ready(function(){
                
                // Faz o cáuculo quando entra na tela
                var dt1 = $("#dtInicial").val();
                var numDias = $("#numDias").val();
                    
                data1 = new Date(dt1);
                data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));
                    
                formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                $("#dtTermino").val(formatado);

            
                // Quando muda a data de término
                 $("#dtTermino").change(function(){
                    var dt1 = $("#dtInicial").val();
                    var dt2 = $("#dtTermino").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(dt2);
                    
                    dias = (data2 - data1)/(1000*3600*24)+1;

                    $("#numDias").val(dias);
                  });                  

                 // Quando muda o período 
                 $("#numDias").change(function(){
                   
                    var dt1 = $("#dtInicial").val();
                    var numDias = $("#numDias").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#dtTermino").val(formatado);
                  });
                  
                // Quando muda a data Inicial
                $("#dtInicial").change(function(){
                   
                    var dt1 = $("#dtInicial").val();
                    var numDias = $("#numDias").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#dtTermino").val(formatado);
                  });
                  
                });
             </script>';

    # Começa uma nova página
    $page = new Page();
    if ($fase == "editar") {
        $page->set_jscript($script);
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Salta uma linha para afastar do menu
    if ($fase == "listar") {
        br();
    }

    # Verifica se o Servidor tem direito a licença
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    if ($pessoal->get_perfilLicenca($idPerfil) == "Não") {
        $mensagem = 'Esse servidor está em um perfil que não pode ter licença !!';
        $alert = new Alert($mensagem);
        $alert->show();
        loadPage('servidorMenu.php');
    } else {
        # Abre um novo objeto Modelo
        $objeto = new Modelo();

        ################################################################
        # Exibe os dados do Servidor
        $objeto->set_rotinaExtra(array("get_DadosServidor"));
        $objeto->set_rotinaExtraParametro(array($idServidorPesquisado));

        # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
        $objeto->set_nome("Licenças Fruídas");

        # botão de voltar da lista
        if ($areaPremio) {
            $objeto->set_voltarLista('areaLicencaPremio.php');
        } else {
            $objeto->set_voltarLista('servidorMenu.php');
        }

        # select da lista
        $objeto->set_selectLista('SELECT tbpublicacaopremio.dtPublicacao,
                                         IFNULL(CONCAT(DATE_FORMAT(dtInicioPeriodo, "%d/%m/%Y")," - ",DATE_FORMAT(dtFimPeriodo, "%d/%m/%Y")),"---"),
                                         dtInicial,
                                         tblicencapremio.numdias,
                                         ADDDATE(dtInicial,tblicencapremio.numDias-1),
                                         processo,
                                         idLicencaPremio,
                                         idLicencaPremio
                                    FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                                   WHERE tblicencapremio.idServidor = ' . $idServidorPesquisado . '
                                ORDER BY dtInicial desc');

        # select do edita
        $objeto->set_selectEdita('SELECT dtInicial,
                                         numDias,
                                         dtTermino,
                                         processo,
                                         idPublicacaoPremio,
                                         obs,
                                         idServidor
                                    FROM tblicencapremio
                                   WHERE idLicencaPremio = ' . $id);

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
        $objeto->set_label(["Data da Publicação", "Período Aquisitivo", "Inicio", "Dias", "Término", "Processo de Solicitação", "Obs"]);
        #$objeto->set_width([17, 22, 17, 10, 17, 12]);
        $objeto->set_funcao(['date_to_php', null, 'date_to_php', null, 'date_to_php']);
        $objeto->set_classe([null, null, null, null, null, null, 'LicencaPremio']);
        $objeto->set_metodo([null, null, null, null, null, null, 'exibeObs']);
        $objeto->set_colunaSomatorio(3);
        $objeto->set_totalRegistro(false);

        $objeto->set_exibeTempoPesquisa(false);
        $objeto->set_numeroOrdem(true);
        $objeto->set_numeroOrdemTipo("d");

        # Classe do banco de dados
        $objeto->set_classBd('pessoal');

        # Nome da tabela
        $objeto->set_tabela('tblicencapremio');

        # Nome do campo id
        $objeto->set_idCampo('idLicencaPremio');

        # Tipo de label do formulário
        $objeto->set_formLabelTipo(1);

        # Pega os Dados para exibir as publicações de todos os vinculos
        $numVinculos = $pessoal->get_numVinculosNaoAtivos($idServidorPesquisado);
        $idSituacao = $pessoal->get_idSituacao($idServidorPesquisado);

        # Pega os dados da combo licenca
        $select = 'SELECT idPublicacaoPremio, 
                          CONCAT(date_format(dtPublicacao,"%d/%m/%Y")," (",date_format(dtInicioPeriodo,"%d/%m/%Y")," - ",date_format(dtFimPeriodo,"%d/%m/%Y"),")")
                     FROM tbpublicacaopremio
                    WHERE idServidor = ' . $idServidorPesquisado;

        # Inclui as publicações de outros vinculos
        if (($numVinculos > 0) AND ($idSituacao == 1)) {

            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $pessoal->get_vinculos($idServidorPesquisado);

            # Percorre os vinculos
            foreach ($vinculos as $tt) {
                $select .= ' OR idServidor = ' . $tt[0];
            }
        }

        $select .= ' ORDER BY dtInicioPeriodo desc';

        $publicacao = $pessoal->select($select);
        array_unshift($publicacao, array(null, ' -- Selecione uma Publicação'));

        # Campos para o formulario
        $objeto->set_campos(array(
            array('nome' => 'dtInicial',
                'label' => 'Data Inicial:',
                'tipo' => 'data',
                'required' => true,
                'autofocus' => true,
                'size' => 20,
                'col' => 3,
                'title' => 'Data do início.',
                'linha' => 1),
            array('nome' => 'numDias',
                'label' => 'Dias:',
                'tipo' => 'numero',
                'min' => 1,
                'size' => 5,
                'required' => true,
                'title' => 'Número de dias.',
                'col' => 2,
                'linha' => 1),
            array('nome' => 'dtTermino',
                'label' => 'Data de Termino (opcional):',
                'tipo' => 'data',
                'size' => 20,
                'col' => 3,
                'title' => 'Data de término da licença.',
                'linha' => 1),
            array('nome' => 'processo',
                'label' => 'Processo:',
                'tipo' => 'texto',
                'size' => 30,
                'col' => 3,
                'title' => 'Número do Processo',
                'linha' => 2),
            array('nome' => 'idPublicacaoPremio',
                'label' => 'Publicação:',
                'tipo' => 'combo',
                'size' => 50,
                'array' => $publicacao,
                'title' => 'Publicação.',
                'required' => true,
                'col' => 4,
                'linha' => 2),
            array('linha' => 3,
                'nome' => 'obs',
                'label' => 'Observação:',
                'tipo' => 'textarea',
                'size' => array(80, 4)),
            array('nome' => 'idServidor',
                'label' => 'idServidor:',
                'tipo' => 'hidden',
                'padrao' => $idServidorPesquisado,
                'size' => 5,
                'title' => 'Matrícula',
                'linha' => 8)));

        # Log
        $objeto->set_idUsuario($idUsuario);
        $objeto->set_idServidorPesquisado($idServidorPesquisado);

        $botaoAfast = new Button('Todos os Afastamentos', 'servidorAfastamentos.php?volta=0');
        $botaoAfast->set_title("Verifica todos os afastamentos deste servidor");
        $botaoAfast->set_target("_blank");

        $botaoAfastPremio = new Button('Afastamentos Específicos', 'servidorAfastamentosPremio.php?volta=0');
        $botaoAfastPremio->set_title("Verifica os afastamentos que interferem no período aquisitido da licença prêmio deste servidor");
        $botaoAfastPremio->set_target("_blank");

        # Procedimentos
        $botaoProcedimentos = new Link("Procedimentos", "?fase=procedimentos");
        $botaoProcedimentos->set_class('button');
        $botaoProcedimentos->set_title('Exibe os procedimentos');
        $botaoProcedimentos->set_target("_blank");

        $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Relatório de Licença Prêmio");
        $botaoRel->set_url("../grhRelatorios/servidorLicencaPremio.php");
        $botaoRel->set_target("_blank");

        # Edita Obs
        if (Verifica::acesso($idUsuario, [1, 2])) {
            $botaoObs = new Button("Obs Geral", "servidorInformacaoAdicionalPremio.php");
            $botaoObs->set_title("Insere / edita as observações gerais.");
            $objeto->set_botaoListarExtra([$botaoObs, $botaoRel, $botaoAfastPremio, $botaoAfast, $botaoProcedimentos]);
        }

        if (Verifica::acesso($idUsuario, 12)) {
            $objeto->set_botaoListarExtra([$botaoRel, $botaoAfastPremio, $botaoAfast]);
        }


        ################################################################

        switch ($fase) {
            case "" :
            case "listar" :
                # Exibe quadro de licença prêmio
                /* Grh::quadroLicencaPremio($idServidorPesquisado); */

                # Pega os dados 
                $diasPublicados = $licenca->get_numDiasPublicadosTotal($idServidorPesquisado);
                $diasFruidos = $licenca->get_numDiasFruidosTotal($idServidorPesquisado);
                $diasDisponiveis = $licenca->get_numDiasDisponiveisTotal($idServidorPesquisado);
                $numProcesso = $licenca->get_numProcessoContagem($idServidorPesquisado);

                $nome = $pessoal->get_licencaNome(6);
                $idSituacao = $pessoal->get_idSituacao($idServidorPesquisado);

                # Exibe alerta se $diasDisponíveis for negativo no geral
                if ($diasDisponiveis < 0) {
                    $objeto->set_botaoIncluir(false);
                }

                # Servidor sem dias disponíveis. Precisa publicar antes de tirar nova licença
                if ($diasDisponiveis < 1) {
                    $objeto->set_botaoIncluir(false);
                }

                # Servidor sem processo de contagem cadastrado
                if (is_null($numProcesso)) {
                    $objeto->set_botaoIncluir(false);
                }

                # Função para acrescentar a rotina extra

                function exibeObs($idServidor) {
                    $licencPremio = new LicencaPremio();
                    $licencPremio->exibeObsGeral($idServidor);
                }

                # Acrescenta as rotinas
                $objeto->set_rotinaExtraListar("exibeObs");
                $objeto->set_rotinaExtraListarParametro($idServidorPesquisado);

                $objeto->listar();

                # Limita o tamanho da tela
                $grid = new Grid();
                $grid->abreColuna(12);

                # Exibe as licenças prêmio de outros vinculos com a UENF                
                $numVinculos = $licenca->get_numVinculosPremio($idServidorPesquisado);

                # Exibe o tempo de licença anterior
                # Verifica se tem vinculos anteriores
                if ($numVinculos > 0) {

                    # Carrega um array com os idServidor de cada vinculo
                    $vinculos = $pessoal->get_vinculos($idServidorPesquisado);

                    # Percorre os vinculos
                    foreach ($vinculos as $tt) {

                        # Pega o perfil da cada vínculo
                        $idPerfilPesquisado = $pessoal->get_idPerfil($tt[0]);

                        if ($idServidorPesquisado <> $tt[0]) {

                            # Verifica se é estatutário
                            if ($idPerfilPesquisado == 1) {
                                # Cria um menu
                                $menu = new MenuBar();

                                # Número do processo
                                $licenca->exibeLicencaPremio($tt[0]);
                            }
                        }
                    }
                }

                # Cria um menu
                if (Verifica::acesso($idUsuario, [1, 2])) {
                    $menu = new MenuBar();

                    # Editar Processo
                    $linkBotao1 = new Link("Editar Processo", "servidorProcessoPremio.php");
                    $linkBotao1->set_class('button');
                    $linkBotao1->set_title("Edita o número do processo de contagem de licença prêmio");
                    $menu->add_link($linkBotao1, "left");

                    # Cadastro de Publicações
                    $linkBotao3 = new Link("Publicações", "servidorPublicacaoPremio.php");
                    $linkBotao3->set_class('button');
                    $linkBotao3->set_title("Acessa o Cadastro de Publicações");
                    $menu->add_link($linkBotao3, "right");
                    $menu->show();
                }

                # Exibe as publicações de Licença Prêmio
                $licenca->exibePublicacoesPremio($idServidorPesquisado);

                # Exibe o idServidor e idPessoa
                p("S {$idServidorPesquisado} / P {$pessoal->get_idPessoa($idServidorPesquisado)}", 'idServidor');

                $grid->fechaColuna();
                $grid->fechaGrid();
                break;

            case "editar" :
                $objeto->$fase($id);
                br();

                # Exibe as publicações de Licença Prêmio
                $licenca->exibePublicacoesPremio($idServidorPesquisado);
                break;

            case "excluir" :
                $objeto->$fase($id);
                break;

            case "gravar" :
                $objeto->gravar($id, "servidorLicencaPremioExtra.php");
                break;

            case "outroVinculo" :
                br(8);
                aguarde();

                set_session('idServidorPesquisado', $id);
                loadPage('servidorLicencaPremio.php');
                break;

            ############################################################################

            case "procedimentos" :

                br();
                $procedimento = new Procedimento();
                $procedimento->exibeProcedimentoSubCategoria("Licença Prêmio");
                break;

            ############################################################################    
        }
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}