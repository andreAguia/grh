<?php

class Grh {
    /**
     * Encapsula as rotivas de interface do sistema de pessoal
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
######################################################################################################################    

    /**
     * Método cabecalho
     * 
     * Exibe o cabecalho
     */
    public static function cabecalho($titulo = null) {
        # tag do cabeçalho
        echo '<header>';

        $cabec = new Div('center');
        $cabec->abre();
        $imagem = new Imagem(PASTA_FIGURAS . 'uenf.jpg', 'Área do Servidor da Uenf', 190, 60);
        $imagem->show();
        $cabec->fecha();

        if (!(is_null($titulo))) {
            br();
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Topbar        
            $top = new TopBar($titulo);
            $top->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
        }
        echo '</header>';
    }

######################################################################################################################

    public static function menuServidor($idServidor, $idUsuario) {

        /**
         * método menuServidor
         * 
         * Exibe o menu do servidor - o que aparece quando se seleciona um servidor 
         */
        # Pega o perfil do servidor pesquisado
        $pessoal = new Pessoal();
        $perfil = $pessoal->get_idPerfil($idServidor);
        $situacao = $pessoal->get_situacao($idServidor);

        # Divide a tela        
        $grid2 = new Grid();

        #######################################################################
        # Funcionais
        if ($perfil == 10) {          // Se for bolsista
            $grid2->abreColuna(12, 6);
            $itensMenu = 4;
        } else {
            $grid2->abreColuna(12, 4);
            $itensMenu = 3;
        }

        titulo('Funcionais');
        br();
        $tamanhoImage = 50;

        $menu = new MenuGrafico($itensMenu);

        # Funcionais
        $botao = new BotaoGrafico();
        $botao->set_label('Funcionais');
        $botao->set_url('servidorFuncionais.php');
        $botao->set_imagem(PASTA_FIGURAS . 'funcional.jpg', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Dados Funcionais do Servidor');
        $menu->add_item($botao);

        # Lotação
        $botao = new BotaoGrafico();
        $botao->set_label('Lotação');
        $botao->set_url('servidorLotacao.php');
        $botao->set_imagem(PASTA_FIGURAS . 'lotacao.png', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Histórico da Lotação do Servidor');
        $menu->add_item($botao);

        # Cargo em Comissão
        if ($pessoal->get_perfilComissao($perfil) == "Sim") {
            $botao = new BotaoGrafico();
            $botao->set_label('Cargo em Comissão');
            $botao->set_url('servidorComissao.php');
            $botao->set_imagem(PASTA_FIGURAS . 'comissao.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Histórico dos Cargos em Comissão do Servidor');
            $menu->add_item($botao);
        }

        # Tempo de Serviço
        if (($perfil == 1) OR ($perfil == 4)) {   // Ser for estatutário
            $botao = new BotaoGrafico();
            $botao->set_label('Tempo de Serviço');
            $botao->set_url('servidorAverbacao.php');
            $botao->set_imagem(PASTA_FIGURAS . 'historico.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Cadastro de Tempo de Serviço Averbado');
            $menu->add_item($botao);

            if ($situacao == "Ativo") {
                $botao = new BotaoGrafico();
                $botao->set_label('Aposentadoria');
                $botao->set_url('servidorAposentadoria.php');
                $botao->set_imagem(PASTA_FIGURAS . 'aposentadoria.png', $tamanhoImage, $tamanhoImage);
                $botao->set_title('Avalia a posentadoria do Servidor');
                $menu->add_item($botao);
            }
        }

        # Cessão
        if (($perfil == 1) OR ($perfil == 4)) {   // Ser for estatutário
            $botao = new BotaoGrafico();
            $botao->set_label('Cessão');
            $botao->set_url('servidorCessao.php');
            $botao->set_imagem(PASTA_FIGURAS . 'cessao.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Histórico de Cessões do Servidor');
            $menu->add_item($botao);
        } elseif ($perfil == 2) { // se for cedido
            $botao = new BotaoGrafico();
            $botao->set_label('Cessão');
            $botao->set_url('servidorCessaoCedido.php');
            $botao->set_imagem(PASTA_FIGURAS . 'cessao.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Dados da Cessão do Servidor');
            $menu->add_item($botao);
        }

        # Obs
        $botao = new BotaoGrafico();
        $botao->set_label('Observações');
        $botao->set_url('servidorObs.php');
        $botao->set_imagem(PASTA_FIGURAS . 'obs.jpg', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Observações Gerais do Servidor');
        $menu->add_item($botao);

        # Pasta Funcional
        $botao = new BotaoGrafico();
        $botao->set_label('Pasta Funcional');
        $botao->set_url('?fase=pasta');
        $botao->set_imagem(PASTA_FIGURAS . 'arquivo.png', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Pasta funcional do servidor');
        $menu->add_item($botao);

        # Elogios e Advertências
        if ($perfil <> 10) {          // Se não for bolsista
            $botao = new BotaoGrafico();
            $botao->set_label('Elogios & Advertências');
            $botao->set_url('servidorElogiosAdvertencias.php');
            $botao->set_imagem(PASTA_FIGURAS . 'ocorrencia.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Cadastro de Elogios e Advertências do Servidor');
            $menu->add_item($botao);
        }

        $menu->show();
        br();

        $grid2->fechaColuna();

        #######################################################################
        # Pessoais 

        if ($perfil == 10) {          // Se for bolsista
            $grid2->abreColuna(12, 6);
            $itensMenu = 4;
        } else {
            $grid2->abreColuna(12, 4);
            $itensMenu = 3;
        }

        titulo('Pessoais');
        br();

        $menu = new MenuGrafico($itensMenu);
        $botao = new BotaoGrafico();
        $botao->set_label('Pessoais');
        $botao->set_url('servidorPessoais.php');
        $botao->set_imagem(PASTA_FIGURAS . 'pessoais.jpg', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Dados Pessoais Gerais do Servidor');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Endereço & Contatos');
        $botao->set_url('servidorEnderecoContatos.php');
        $botao->set_imagem(PASTA_FIGURAS . 'bens.png', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Endereço e Contatos do Servidor');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Contatos');
        $botao->set_url('servidorContatos.php');
        $botao->set_imagem(PASTA_FIGURAS . 'telefone.jpg', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Dados dos Contatos do Servidor');
        #$menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Documentos');
        $botao->set_url('servidorDocumentos.php');
        $botao->set_imagem(PASTA_FIGURAS . 'documento.jpg', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Cadastro da Documentação do Servidor');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Formação');
        $botao->set_url('servidorFormacao.php');
        $botao->set_imagem(PASTA_FIGURAS . 'diploma.jpg', $tamanhoImage, $tamanhoImage);
        $botao->set_title('Cadastro de Formação Escolar do Servidor');
        $menu->add_item($botao);

        if ($perfil <> 10) {          // Se não for bolsista
            $botao = new BotaoGrafico();
            $botao->set_label('Parentes');
            $botao->set_url('servidorDependentes.php');
            $botao->set_imagem(PASTA_FIGURAS . 'dependente.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Cadastro dos Parentes do Servidor');
            $menu->add_item($botao);
        }

        $menu->show();

        $grid2->fechaColuna();

        #######################################################################
        # Afastamentos
        if ($perfil <> 10) {          // Se não for bolsista
            $grid2->abreColuna(12, 4);
            titulo('Afastamentos');
            br();

            $menu = new MenuGrafico(3);
            if ($pessoal->get_perfilFerias($perfil) == "Sim") {
                $botao = new BotaoGrafico();
                $botao->set_label('Férias');
                $botao->set_url('servidorFerias.php');
                $botao->set_imagem(PASTA_FIGURAS . 'ferias.jpg', $tamanhoImage, $tamanhoImage);
                $botao->set_title('Cadastro das Férias do Servidor');
                $botao->set_accessKey('i');
                $menu->add_item($botao);
            }

            if ($pessoal->get_perfilLicenca($perfil) == "Sim") {
                $botao = new BotaoGrafico();
                $botao->set_label('Licenças e Afastamentos');
                $botao->set_url('servidorLicenca.php');
                $botao->set_imagem(PASTA_FIGURAS . 'licenca.jpg', $tamanhoImage, $tamanhoImage);
                $botao->set_title('Cadastro de Licenças do Servidor');
                $botao->set_accessKey('L');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label($pessoal->get_licencaNome(6));
                $botao->set_url('servidorLicencaPremio.php');
                $botao->set_imagem(PASTA_FIGURAS . 'premio.png', $tamanhoImage, $tamanhoImage);
                $botao->set_title('Cadastro de Licenças Prêmio do Servidor');
                #$botao->set_accessKey('L');
                $menu->add_item($botao);
            }

            $botao = new BotaoGrafico();
            $botao->set_label('Atestados (Faltas Abonadas)');
            $botao->set_url('servidorAtestado.php');
            $botao->set_imagem(PASTA_FIGURAS . 'atestado.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Cadastro de Atestados do Servidor');
            #$botao->set_accessKey('i');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Faltas');
            $botao->set_url('servidorFaltas.php');
            $botao->set_imagem(PASTA_FIGURAS . 'faltas.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Cadastro de Faltas do Servidor');
            #$botao->set_accessKey('i');
            #$menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('TRE');
            $botao->set_url('servidorTre.php');
            $botao->set_imagem(PASTA_FIGURAS . 'tre.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Cadastro de dias trabalhados no TRE com controle de folgas');
            #$botao->set_accessKey('i');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Afastamento Anual');
            $botao->set_url('?fase=timeline');
            $botao->set_imagem(PASTA_FIGURAS . 'timeline.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Resumo gráfico do tempo de vida funcional do servidor dentro da Universidade');
            #$botao->set_accessKey('i');
            #$menu->add_item($botao);

            $menu->show();

            $grid2->fechaColuna();
        }

        #######################################################################
        # Financeiro                                    
        if ($perfil <> 10) {          // Se não for bolsista
            $grid2->abreColuna(12, 4);
            titulo('Financeiro');
            br();

            $menu = new MenuGrafico(3);
            if ($pessoal->get_perfilProgressao($perfil) == "Sim") {
                $botao = new BotaoGrafico();
                $botao->set_label('Progressão e Enquadramento');
                $botao->set_url('servidorProgressao.php');
                $botao->set_imagem(PASTA_FIGURAS . 'salario.jpg', $tamanhoImage, $tamanhoImage);
                $botao->set_title('Cadastro de Progressões e Enquadramentos do Servidor');
                $menu->add_item($botao);
            }

            if ($pessoal->get_perfilTrienio($perfil) == "Sim") {
                $botao = new BotaoGrafico();
                $botao->set_label('Triênio');
                $botao->set_url('servidorTrienio.php');
                $botao->set_imagem(PASTA_FIGURAS . 'trienio.jpg', $tamanhoImage, $tamanhoImage);
                $botao->set_title('Histórico de Triênios do Servidor');
                $menu->add_item($botao);
            }

            if ($pessoal->get_perfilGratificacao($perfil) == "Sim") {
                $botao = new BotaoGrafico();
                $botao->set_label('Gratificação Especial');
                $botao->set_url('servidorGratificacao.php');
                $botao->set_imagem(PASTA_FIGURAS . 'gratificacao.jpg', $tamanhoImage, $tamanhoImage);
                $botao->set_title('Histórico das Gratificações Especiais do Servidor');
                $menu->add_item($botao);
            }

            # Direito Pessoal    
            $botao = new BotaoGrafico();
            $botao->set_label('Direito Pessoal');
            $botao->set_url('servidorDireitoPessoal.php');
            $botao->set_imagem(PASTA_FIGURAS . 'abono.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Cadastro de Abono / Direito Pessoal');
            $menu->add_item($botao);

            if ($perfil == 1) {   // Ser for estatutário
                # Abono Permanencia    
                $botao = new BotaoGrafico();
                $botao->set_label('Abono Permanencia');
                $botao->set_url('servidorAbono.php');
                $botao->set_imagem(PASTA_FIGURAS . 'money.png', $tamanhoImage, $tamanhoImage);
                $botao->set_title('Cadastro de Abono Permanencia');
                $menu->add_item($botao);
            }

            # Diarias
            $botao = new BotaoGrafico();
            $botao->set_label('Diárias');
            $botao->set_url('servidorDiaria.php');
            $botao->set_imagem(PASTA_FIGURAS . 'diaria.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Controle de Diárias');
            $menu->add_item($botao);

            # Dados Bancários
            $botao = new BotaoGrafico();
            $botao->set_label('Dados Bancários');
            $botao->set_url('servidorBancario.php');
            $botao->set_imagem(PASTA_FIGURAS . 'banco.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Cadastro dos dados bancários do Servidor');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Resumo Financeiro');
            $botao->set_url('servidorFinanceiro.php');
            $botao->set_imagem(PASTA_FIGURAS . 'lista.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Informações sobre os valores recebidos pelo servidor');
            #$botao->set_onClick("abreFechaDiv('divResumo');");
            $menu->add_item($botao);

            # Declaração de Bens e Valores
            #$botao = new BotaoGrafico();
            #$botao->set_label('DBV - Declaração de Bens e Valores');
            #$botao->set_url('servidorDbvControle.php');
            #$botao->set_imagem(PASTA_FIGURAS.'bens.png',$tamanhoImage,$tamanhoImage);
            #$botao->set_title('DBV - Declaração de Bens e Valores');
            #$menu->add_item($botao);

            $menu->show();

            $grid2->fechaColuna();
        }

        #######################################################################   
        # Benefício
        if ($perfil <> 10) {          // Se não for bolsista
            $grid2->abreColuna(12, 3);
            titulo('Benefícios');
            br();

            $menu = new MenuGrafico(2);

            $botao = new BotaoGrafico();
            $botao->set_label('Redução da Carga Horária');
            $botao->set_url('servidorReducao.php');
            $botao->set_imagem(PASTA_FIGURAS . 'carga-horaria.svg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Controle de Redução da Carga Horária');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Readaptação');
            $botao->set_url('servidorReadaptacao.php');
            $botao->set_imagem(PASTA_FIGURAS . 'readaptacao.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Controle de Readaptação');
            $menu->add_item($botao);

            $menu->show();

            $grid2->fechaColuna();
        }

        #######################################################################
        # Relatórios
        if ($perfil <> 10) {          // Se não for bolsista
            $grid2->abreColuna(12, 3);
            titulo('Relatórios & Declarações');
            br();

            $cargo = $pessoal->get_idCargo($idServidor);

            $menu = new Menu();
            $menu->add_item('titulo1', 'Gerais');
            $menu->add_item("linkWindow", "Ficha Cadastral", "../grhRelatorios/fichaCadastral.php");
            $menu->add_item("linkWindow", "Folha de Presença", "../grhRelatorios/folhaPresenca.php");
            #$menu->add_item("linkWindow","Mapa do Cargo","../grhRelatorios/mapaCargo.php?cargo=$cargo");
            $menu->add_item('titulo1', 'Declarações');
            $menu->add_item('linkWindow', 'Declaração de Inquérito Administrativo', '../grhRelatorios/declaracao.InqueritoAdministrativo.php');
            $menu->add_item('linkWindow', 'Declaração de Atribuições do Cargo', '../grhRelatorios/declaracao.AtribuicoesCargo.php');
            $menu->add_item('link', 'Declaração para o INSS', '#');

            #$menu->add_item("linkWindow","FAF","../grhRelatorios/fichaAvaliacaoFuncional.php");
            #$menu->add_item("linkWindow","Capa da Pasta","../grhRelatorios/capaPasta.php");
            $menu->show();

            $grid2->fechaColuna();
        }

        #######################################################################
        # Foto 
        if ($perfil <> 10) {          // Se não for bolsista
            $grid2->abreColuna(12, 2);
            titulo('Foto do Servidor');

            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Define a pasta
            $arquivo = PASTA_FOTOS . "$idPessoa.jpg";

            # Verifica se tem pasta desse servidor
            if (file_exists($arquivo)) {
                br();

                $botao = new BotaoGrafico();
                $botao->set_url('?fase=exibeFoto');
                $botao->set_imagem($arquivo, 'Foto do Servidor', 200, 150);
                $botao->set_title('Foto do Servidor');
                $botao->show();
            } else {
                $foto = new Imagem(PASTA_FIGURAS . 'foto.png', 'Foto do Servidor', 150, 100);
                $foto->set_id('foto');
                $foto->show();
                br();
            }

            $div = new Div("center");
            $div->abre();

            $link = new Link("Alterar Foto", "?fase=uploadFoto");
            $link->set_id("alteraFoto");
            $link->show();

            $div->fecha();

            $grid2->fechaColuna();
        }
        #######################################################################

        $grid2->fechaGrid();
    }

######################################################################################################################

    /**
     * método quadroLicencaPremio
     * Exibe um quadro informativo da licença Prêmio de um servidor
     */
    public static function quadroLicencaPremio($idServidor) {

        # Pega os dados para o alerta
        $licenca = new LicencaPremio();
        $diasPublicados = $licenca->get_numDiasPublicados($idServidor);
        $diasFruidos = $licenca->get_numDiasFruidos($idServidor);
        $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidor);

        # Div do numero de serviços
        $div = new Div('divQuadroLicenca');
        $div->set_title('Quadro de Licenças Prêmio e Publicações');
        $div->abre();

        # Tabela de Serviços
        $mesServico = date('m');
        $tabela = array(array('Dias Publicados', $diasPublicados),
            array('Dias Fruídos', $diasFruidos),
            array('Disponíveis', $diasDisponiveis));
        $estatistica = new Tabela();
        $estatistica->set_conteudo($tabela);
        $estatistica->set_label(array("", ""));
        $estatistica->set_align(array("center"));
        $estatistica->set_width(array(60, 40));
        $estatistica->set_totalRegistro(false);
        $estatistica->show();

        $div->fecha();
    }

######################################################################################################################

    /**
     * método quadroVagasCargoComissao
     * Exibe um quadro informativo das vagas dos Cargos em Comissão
     */
    public static function quadroVagasCargoComissao() {
        $select = 'SELECT descricao,
                          simbolo,
                          valsal,
                          vagas,                               
                          idTipoComissao,
                          idTipoComissao,
                          idTipoComissao,
                          idTipoComissao
                     FROM tbtipocomissao
                    WHERE ativo
                    ORDER BY 2 asc';

        # Conecta com o banco de dados
        $servidor = new Pessoal();
        $result = $servidor->select($select);

        # Verifica se tem registros a serem exibidos
        if (count($result) == 0) {
            $p = new P('Nenhum item encontrado !!', 'center');
            $p->show();
        } else {
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(array("Cargo", "Simbolo", "Valor (R$)", "Vagas", "Nomeados", "ProTempore", "Designados", "Vagas Disponíveis"));
            #$tabela->set_width(array(25,15,15,15,15,15));
            $tabela->set_align(array("left"));
            $tabela->set_funcao(array(null, null, "formataMoeda"));
            $tabela->set_classe(array(null, null, null, null, 'CargoComissao', 'CargoComissao', 'CargoComissao', 'CargoComissao'));
            $tabela->set_metodo(array(null, null, null, null, 'get_numServidoresNomeados', 'get_numServidoresProTempore', 'get_numServidoresDesignados', 'get_vagasDisponiveis'));
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 7,
                    'valor' => 0,
                    'operador' => '<',
                    'id' => "comissaoVagasNegativas"),
                array('coluna' => 7,
                    'valor' => 0,
                    'operador' => '=',
                    'id' => "comissaoSemVagas"),
                array('coluna' => 7,
                    'valor' => 0,
                    'operador' => '>',
                    'id' => "comissaoComVagas")));

            $tabela->show();
        }
    }

######################################################################################################################

    /**
     * método exibeOcorênciaServidor
     * Div que ressalta situação do servidor (licença, férias, etc)
     */
    public static function exibeOcorênciaServidor($idServidor) {
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Inicializa a variável das mensagens
        $mensagem = array();

        ##### Situação do servidor
        # Pega as situações
        $ferias = $pessoal->emFerias($idServidor);
        $licenca = $pessoal->emLicenca($idServidor);
        $licencaPremio = $pessoal->emLicencaPremio($idServidor);
        $situacao = $pessoal->get_idSituacao($idServidor);
        $folgaTre = $pessoal->emFolgaTre($idServidor);
        $afastadoTre = $pessoal->emAfastamentoTre($idServidor);
        #$cedido = $pessoal->emCessao($idServidor);
        $orgaoCedido = null;

        # Férias
        if ($ferias) {
            $exercicio = $pessoal->emFeriasExercicio($idServidor);
            $mensagem[] = 'Servidor em férias (Exercicio ' . $exercicio . ')';
        }

        # Licenca
        if ($licenca) {
            $mensagem[] = 'Servidor em ' . $pessoal->get_licenca($idServidor);
        }

        # Licenca Prêmio
        if ($licencaPremio) {
            $mensagem[] = 'Servidor em ' . $pessoal->get_licencaNome(6);
        }

        # Motivo de Saída
        if (($situacao <> 1) AND ($pessoal->get_motivo($idServidor) <> "Outros")) {
            $mensagem[] = $pessoal->get_motivo($idServidor);
        }

        # Folga TRE
        if ($folgaTre) {
            $mensagem[] = 'Servidor em Folga TRE';
        }

        # Afastamento TRE
        if ($afastadoTre) {
            $mensagem[] = 'Prestando serviço ao TRE';
        }

        # Cedido
        #if($cedido){
        #    $orgaoCedido = $pessoal->get_orgaoCedido($idServidor);
        #    $mensagem[] = 'Servidor Cedido a(o) '.$orgaoCedido;
        #}
        ##### Ocorrências

        $metodos = get_class_methods('Checkup');
        $ocorrencia = new Checkup(false);

        foreach ($metodos as $nomeMetodo) {
            if (($nomeMetodo <> 'get_all') AND ($nomeMetodo <> '__construct')) {
                $texto = $ocorrencia->$nomeMetodo($idServidor);

                if (!is_null($texto)) {
                    $mensagem[] = $texto;
                }
            }
        }

        # Chefia Imediata
        #$idChefe = $pessoal->get_chefiaImediata($idServidor);
        #if(!is_null($idChefe)){
        #    $mensagem[] = "Chefia Imediata: ".$pessoal->get_nome($idChefe). " (".$pessoal->get_chefiaImediataDescricao($idServidor).")";
        #}

        $qtdMensagem = count($mensagem);
        $contador = 1;

        # Vinculos do servidor
        $numVinculos = $pessoal->get_numVinculos($idServidor);

        # Verifica se tem algo a ser exibido
        if (($qtdMensagem > 0) OR ($numVinculos > 1)) {

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Exibe a mensagem
            $callout = new Callout("warning");
            $callout->abre();

            # Verifica se tem os dois ou se tem um
            if (($qtdMensagem > 0) AND ($numVinculos > 1)) {
                $coluna = 6;
            } else {
                $coluna = 12;
            }

            # Coluna Interna
            $grid2 = new Grid();

            ##### Mensagens
            if ($qtdMensagem > 0) {

                $grid2->abreColuna($coluna);

                # Percorre o array 
                foreach ($mensagem as $mm) {
                    p("- " . $mm, "exibeOcorrencia");
                    if ($contador < $qtdMensagem) {
                        $contador++;
                    }
                }

                $grid2->fechaColuna();
            }

            ##### Vinculos
            # Número de Vinculos
            if ($numVinculos > 1) {

                $grid2->abreColuna($coluna);

                p("- Outros Vínculos na UENF", "exibeOcorrencia");

                # Monta o menu
                $menu = new Menu("menuVinculos");

                # Exibe os vinculos
                $vinculos = $pessoal->get_vinculos($idServidor);

                # Percorre os vínculos
                foreach ($vinculos as $rr) {

                    # Descarta o vinculo em tela
                    if ($rr[0] <> $idServidor) {
                        $dtAdm = $pessoal->get_dtAdmissao($rr[0]);
                        $dtSai = $pessoal->get_dtSaida($rr[0]);
                        $perfil = $pessoal->get_perfilSimples($rr[0]);
                        $cargo = $pessoal->get_cargoSimples($rr[0]);
                        $motivo = $pessoal->get_motivo($rr[0]);
                        $idSituacao = $pessoal->get_idSituacao($rr[0]);


                        # Quando o cargo for null
                        if (!vazio($cargo)) {
                            $cargo = "- " . $cargo;
                        }

                        # Cria um motivo Ativo
                        if ($idSituacao == 1) {
                            $motivo = "Ativo";
                        }

                        $menu->add_item("link", "$cargo - $perfil ($dtAdm - $dtSai) - $motivo", 'servidor.php?fase=editar&id=' . $rr[0]);
                    }
                }

                # Exibe o menu
                $menu->show();
                $grid2->fechaColuna();
            }

            $grid2->fechaGrid();

            $callout->fecha();
            $grid->fechaColuna();
            $grid->fechaGrid();
        }
    }

######################################################################################################################

    /**
     * método listaDadosServidor
     * Exibe os dados principais do servidor logado
     * 
     * @param    string $idServidor -> idServidor do servidor
     */
    public static function listaDadosServidor($idServidor) {

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Telas maiores
        $div = new Div(null, "hide-for-small-only");
        $div->abre();

        $select = 'SELECT tbservidor.idFuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.dtAdmissao,
                          tbservidor.idServidor,
                          tbservidor.dtDemissao
                     FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                     LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                    WHERE idServidor = ' . $idServidor;

        $conteudo = $servidor->select($select, true);

        # Pega a situação
        $situacao = $servidor->get_situacao($idServidor);

        if ($situacao == "Ativo") {
            $label = array("Id Funcional", "Servidor", "Cargo", "Lotação", "Perfil", "Admissão", "Situação");
            $function = array(null, null, null, null, null, "date_to_php");
        } else {
            $label = array("Id Funcional", "Servidor", "Cargo", "Lotação", "Perfil", "Admissão", "Situação", "Saída");
            $function = array(null, null, null, null, null, "date_to_php", null, "date_to_php");
        }
        #$align = array("center");

        $classe = array(null, null, "pessoal", "pessoal", "pessoal", null, "pessoal");
        $metodo = array(null, null, "get_cargoComSalto", "get_Lotacao", "get_Perfil", null, "get_Situacao");

        $formatacaoCondicional = array(array('coluna' => 0,
                'valor' => $servidor->get_idFuncional($idServidor),
                'operador' => '=',
                'id' => 'listaDados'));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_funcao($function);
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);

        $tabela->show();

        $div->fecha();

        ######################################3
        # Telas menores
        $div = new Div(null, "show-for-small-only");
        $div->abre();

        $select = 'SELECT tbservidor.idFuncional,
                             tbpessoa.nome,
                             tbservidor.idServidor
                        FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                        LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                       WHERE idServidor = ' . $idServidor;

        $conteudo = $servidor->select($select, true);

        # Pega a situação
        $situacao = $servidor->get_situacao($idServidor);
        $label = array("Id Funcional", "Servidor", "Perfil");
        $function = array(null, null, null);
        $classe = array(null, null, "pessoal");
        $metodo = array(null, null, "get_Perfil");

        $formatacaoCondicional = array(array('coluna' => 0,
                'valor' => $servidor->get_idFuncional($idServidor),
                'operador' => '=',
                'id' => 'listaDados'));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_funcao($function);
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);

        $tabela->show();

        $select = 'SELECT tbservidor.idServidor,
                             tbservidor.dtAdmissao,
                             tbservidor.idServidor,
                             tbservidor.idServidor,
                             tbservidor.dtDemissao
                        FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                           LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                       WHERE idServidor = ' . $idServidor;

        $conteudo = $servidor->select($select, true);

        # Pega a situação
        $situacao = $servidor->get_situacao($idServidor);

        if ($situacao == "Ativo") {
            $label = array("Cargo", "Admissão", "Lotação", "Situação");
            $function = array(null, "date_to_php");
        } else {
            $label = array("Cargo", "Admissão", "Lotação", "Situação", "Saída");
            $function = array(null, "date_to_php", null, null, "date_to_php");
        }

        $classe = array("pessoal", null, "pessoal", "pessoal");
        $metodo = array("get_Cargo", null, "get_Lotacao", "get_Situacao");

        $formatacaoCondicional = array(array('coluna' => 3,
                'valor' => $situacao,
                'operador' => '=',
                'id' => 'listaDados'));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_funcao($function);
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);

        $tabela->show();

        $div->fecha();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

######################################################################################################################

    /**
     * método listaFolgasTre
     * Exibe os dados de Folgas do TRE
     * 
     * @param    string $idServidor -> idServidor do servidor
     */
    public static function listaFolgasTre($idServidor) {
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $folgasConcedidas = $servidor->get_treFolgasConcedidas($idServidor);
        $folgasFruidas = $servidor->get_treFolgasFruidas($idServidor);
        $folgasPendentes = $folgasConcedidas - $folgasFruidas;

        # Div do numero de folgas
        $div = new Div('divAfastamentoTre');
        $div->abre();

        # Tabela
        $folgas = Array(Array('Folgas Concedidas', $folgasConcedidas),
            Array('Folgas Fruídas', $folgasFruidas),
            Array('Folgas Pendentes', $folgasPendentes));
        #$label = array("Folgas","Dias");
        $label = array("", "");
        $width = array(70, 30);
        $align = array("left");


        $tabela = new Tabela("tabelaTre");
        #$estatistica->set_titulo('Legenda'); 
        $tabela->set_conteudo($folgas);
        $tabela->set_cabecalho($label, $width, $align);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => 'Folgas Pendentes',
                'operador' => '=',
                'id' => 'trePendente')));

        $tabela->show();

        $div->fecha();
    }

######################################################################################################################

    /**
     * método listaDadosServidorRelatório
     * Exibe os dados principais do servidor para relatório
     * 
     * @param string $idServidor null idServidor do servidor
     * @param string $titulo     null O título do relatório 
     * @param string $cabecalho  true Se exibirá o início do relatório (menu, cabecalho, etc) 
     */
    public static function listaDadosServidorRelatorio($idServidor, $titulo = null, $cabecalho = true) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        # Dados do Servidor
        $select = 'SELECT tbservidor.idFuncional,
                         tbpessoa.nome,
                         tbperfil.nome,
                         tbservidor.idServidor,
                         tbservidor.dtAdmissao,
                         tbservidor.idServidor,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                       LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                                       LEFT JOIN tbperfil ON tbservidor.idPerfil = tbperfil.idPerfil
                   WHERE idServidor = ' . $idServidor;

        $result = $pessoal->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_titulo($titulo);
        $relatorio->set_label(array("Id", "Servidor", "Perfil", "Cargo", "Admissão", "Lotação", "Situação"));
        #$relatorio->set_width(array(8,20,10,20,10,20,5));
        $relatorio->set_funcao(array(null, null, null, null, "date_to_php"));
        $relatorio->set_classe(array(null, null, null, "pessoal", null, "pessoal", "pessoal"));
        $relatorio->set_metodo(array(null, null, null, "get_cargoComSalto", null, "get_Lotacao", "get_Situacao"));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_linhaNomeColuna(false);
        $relatorio->set_brHr(0);
        $relatorio->set_linhaFinal(true);
        $relatorio->set_log(false);

        # Verifica se exibe ou não o início do cabeçalho
        # Utilizado para quando os dados doservidor é a primeira coisa a ser
        # exibida no relatório. Se não for esconde o cabeçalho, menu etc
        if (!$cabecalho) {
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
        }

        $relatorio->show();
    }

######################################################################################################################

    /**
     * método rodape
     * Exibe oo rodapé
     * 
     * @param    string $idUsuario -> Usuário logado
     */
    public static function rodape($idUsuario, $idServidor = null, $idPessoa = null) { {
            # Exibe faixa azul
            $grid = new Grid();
            $grid->abreColuna(12);
            titulo();
            $grid->fechaColuna();
            $grid->fechaGrid();

            # Exibe a versão do sistema
            $intra = new Intra();
            $grid = new Grid();
            $grid->abreColuna(6);
            p('Usuário : ' . $intra->get_usuario($idUsuario), 'usuarioLogado');
            $grid->fechaColuna();
            $grid->abreColuna(6);
            #p("Desenvolvido por André Águia", 'pauthor');
            p("UENF - Universidade Estadual do Norte Fluminense Darcy Ribeiro", 'pauthor');
            $grid->fechaColuna();
            $grid->fechaGrid();
        }
    }

######################################################################################################################

    /**
     * Método exibe get_numServidoresAtivosTipoCargo
     * 
     * Exibe o número de servidores ativos por tipo de cargo e o link para exibí-los
     * Usado na tabela da rotina de cadastro de cargo efetivo
     */
    public function get_numServidoresAtivosTipoCargo($id) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresAtivosTipoCargo($id);
        echo "&nbsp&nbsp&nbsp";

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=exibeServidoresAtivos&id=' . $id);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresAtivosTipoCargo
     * 
     * Exibe o número de servidores ativos por tipo de cargo e o link para exibí-los
     * Usado na tabela da rotina de cadastro de cargo efetivo
     */
    public function get_numServidoresInativosTipoCargo($id) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresInativosTipoCargo($id);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=exibeServidoresInativos&id=' . $id);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresCargoComissao
     * 
     * Exibe o número de servidores ativos por de cargo em comissão e o link para exibí-los
     * Usado na tabela da rotina de cadastro de cargo em comissão
     */
    public function get_numServidoresCargoComissao($id) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresCargoComissao($id);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=vigente&id=' . $id);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresAtivosPerfil
     * 
     * Exibe o número de servidores ativos por perfil e o link para exibí-los
     * Usado na tabela da rotina de cadastro de perfil
     */
    public function get_numServidoresAtivosPerfil($id) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresAtivosPerfil($id);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=listaServidoresAtivos&id=' . $id);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresInativosPerfil
     * 
     * Exibe o número de servidores inativos por perfil e o link para exibí-los
     * Usado na tabela da rotina de cadastro de perfil
     */
    public function get_numServidoresInativosPerfil($id) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresInativosPerfil($id);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=listaServidoresInativos&id=' . $id);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresAtivosLotacao
     * 
     * Exibe o número de servidores ativos por lotação e o link para exibí-los
     * Usado na tabela da rotina de cadastro de lotação
     */
    public function get_numServidoresAtivosLotacao($idLotacao) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresAtivosLotacao($idLotacao);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=listaServidoresAtivos&id=' . $idLotacao);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresInativosLotacao
     * 
     * Exibe o número de servidores inativos por lotação e o link para exibí-los
     * Usado na tabela da rotina de cadastro de lotação
     */
    public function get_numServidoresInativosLotacao($idLotacao) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresInativosLotacao($idLotacao);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=listaServidoresInativos&id=' . $idLotacao);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresAtivosConcurso
     * 
     * Exibe o número de servidores ativos por concurso e o link para exibí-los
     * Usado na tabela da rotina de cadastro de concurso
     */
    public function get_numServidoresAtivosConcurso($idConcurso) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresAtivosConcurso($idConcurso);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=listaServidoresAtivos&id=' . $idConcurso);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresInativosConcurso
     * 
     * Exibe o número de servidores inativos por concurso e o link para exibí-los
     * Usado na tabela da rotina de cadastro de concurso
     */
    public function get_numServidoresInativosConcurso($idConcurso) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresInativosConcurso($idConcurso);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=listaServidoresInativos&id=' . $idConcurso);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'ver.png', 20, 20);
        $botao->show();
    }

    ###########################################################
}
