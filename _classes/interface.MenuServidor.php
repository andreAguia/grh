<?php

class MenuServidor {

    /**
     * Gera o Menu Principal do Sistema
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $idUsuario = null;
    private $idServidor = null;
    private $perfil = null;
    private $situacao = null;
    private $cargoComissao = null;
    private $orgaoCedido = null;
    private $tamanhoImagem = 50;

######################################################################################################################    

    public function __construct($idServidor = null, $idUsuario = null) {
        /**
         * Inicia a classe
         */
        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega o perfil do servidor pesquisado
        $perfil = $pessoal->get_idPerfil($idServidor);
        $situacao = $pessoal->get_situacao($idServidor);
        $cargoComissao = $pessoal->get_cargoComissao($idServidor);
        $orgaoCedido = $pessoal->get_orgaoCedido($idServidor);

        # Preenche variável
        $this->idUsuario = $idUsuario;
        $this->idServidor = $idServidor;
        $this->perfil = $perfil;
        $this->situacao = $situacao;
        $this->cargoComissao = $cargoComissao;
        $this->orgaoCedido = $orgaoCedido;

        ##########################################################
        # Inicia o Grid

        $grid = new Grid();

        if (!is_null($this->cargoComissao)) {
            $grid->abreColuna(12);
            $this->moduloCargoComissao();
            $grid->fechaColuna();
        }

        if (!is_null($this->orgaoCedido)) {
            $grid->abreColuna(12);
            $this->moduloOrgaoCedido();
            $grid->fechaColuna();
        }
        # --
        $grid->abreColuna(12, 6, 6);
        $this->moduloOcorrencias();
        $grid->fechaColuna();
        # --
        $grid->abreColuna(12, 6, 6);
        $this->moduloVinculos();
        $grid->fechaColuna();
        # --
        $grid->abreColuna(12, 7, 6);
        $this->moduloFuncionais();
        $grid->fechaColuna();
        # --
        $grid->abreColuna(7, 5, 4);
        $this->moduloPessoais();
        $grid->fechaColuna();
        # --
        $grid->abreColuna(5, 3, 2);
        $this->moduloFoto();
        $grid->fechaColuna();
        # --
        $grid->abreColuna(5, 3, 2);
        $this->moduloBeneficios();
        $grid->fechaColuna();
        # --
        $grid->abreColuna(7, 6, 5);
        $this->moduloFinanceiro();
        $grid->fechaColuna();
        # --
        $grid->abreColuna(12, 6, 5);
        $this->moduloAfastamentos();
        $grid->fechaColuna();
        # --
        $grid->abreColuna(12, 6, 6);
        $this->moduloRelatorios();
        $grid->fechaColuna();
        # --
        $grid->fechaGrid();
    }

######################################################################################################################

    /**
     * Método moduloFoto
     * 
     * Exibe a Foto do servidor
     */
    private function moduloFoto() {

        titulo('Foto');
        br();

        # Inicia o Grid
        $grid = new Grid();
        $grid->abreColuna(12);

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        $idPessoa = $pessoal->get_idPessoa($this->idServidor);

        $foto = new ExibeFoto();
        $foto->set_fotoLargura(140);
        $foto->set_fotoAltura(180);
        $foto->set_url('?fase=exibeFoto');
        $foto->show($idPessoa);

        $grid->fechaColuna();
        $grid->fechaGrid();

        $div = new Div("center");
        $div->abre();

        $link = new Link("Alterar Foto", "?fase=uploadFoto");
        $link->set_id("alteraFoto");
        $link->show();

        $div->fecha();
    }

######################################################################################################################

    /**
     * Método moduloFuncionais
     * 
     * Exibe o menu de Dados Funcionais
     */
    private function moduloFuncionais() {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        titulo('Funcionais');
        br();

        $menu = new MenuGrafico(5);

        # Funcionais
        $botao = new BotaoGrafico();
        $botao->set_label('Funcionais');
        $botao->set_url('servidorFuncionais.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'funcional.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Dados Funcionais do Servidor');
        $menu->add_item($botao);

        # Lotação
        $botao = new BotaoGrafico();
        $botao->set_label('Lotação');
        $botao->set_url('servidorLotacao.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'lotacao.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Histórico da Lotação do Servidor');
        $menu->add_item($botao);

        # Cargo em Comissão
        if ($pessoal->get_perfilComissao($this->perfil) == "Sim") {
            $botao = new BotaoGrafico();
            $botao->set_label('Cargo em Comissão');
            $botao->set_url('servidorComissao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'comissao.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Histórico dos Cargos em Comissão do Servidor');
            $menu->add_item($botao);
        }

        # Tempo de Serviço
        if (($this->perfil == 1) OR ($this->perfil == 4)) {   // Ser for estatutário ou clt
            $botao = new BotaoGrafico();
            $botao->set_label('Tempo de Serviço');
            $botao->set_url('servidorAverbacao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'historico.png', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Cadastro de Tempo de Serviço Averbado');
            $menu->add_item($botao);

            if ($this->situacao == "Ativo") {
                $botao = new BotaoGrafico();
                $botao->set_label('Aposentadoria');
                $botao->set_url('servidorAposentadoria.php?grh=1');
                $botao->set_imagem(PASTA_FIGURAS . 'aposentadoria.png', $this->tamanhoImagem, $this->tamanhoImagem);
                $botao->set_title('Avalia a posentadoria do Servidor');
                $menu->add_item($botao);
            }
            
            $botao = new BotaoGrafico();
            $botao->set_label('Concurso');
            $botao->set_url('servidorConcurso.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'concurso.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Dados do concurso');
            $menu->add_item($botao);
        }

        # Cessão
        if (($this->perfil == 1) OR ($this->perfil == 4)) {   // Ser for estatutário
            $botao = new BotaoGrafico();
            $botao->set_label('Cessão');
            $botao->set_url('servidorCessao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'cessao.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Histórico de Cessões do Servidor');
            $menu->add_item($botao);
        } elseif ($this->perfil == 2) { // se for cedido
            $botao = new BotaoGrafico();
            $botao->set_label('Cessão');
            $botao->set_url('servidorCessaoCedido.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'cessao.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Dados da Cessão do Servidor');
            $menu->add_item($botao);
        }

        # Acumulação
        $botao = new BotaoGrafico();
        $botao->set_label('Acumulação de Cargos Públicos');
        #$botao->set_url('servidorAcumulacao.php?grh=1');
        $botao->set_url('servidorMenu.php?fase=acumulacao');
        $botao->set_imagem(PASTA_FIGURAS . 'acumulacao.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Controle de Acumulação de Cargo Público');
        $menu->add_item($botao);

        # Declaração de Acumulação
        $botao = new BotaoGrafico();
        $botao->set_label('Declaração de Acumulação');
        $botao->set_url('servidorAcumulacaoDeclaracao.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'declaracao.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Controle da entrega da declaração de acumulação de cargo público');
        #$menu->add_item($botao);
        # Prestador de Contas
        $botao = new BotaoGrafico();
        $botao->set_label('Ordenação de Despesas');
        $botao->set_url('servidorOrdenador.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'declaracao.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Histórico de designação para ordenação de despesas');
        $menu->add_item($botao);

        # Pasta Funcional
        $botao = new BotaoGrafico();
        $botao->set_label('Pasta Funcional');
        $botao->set_url('servidorPasta.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'arquivo.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Pasta funcional do servidor');
        $menu->add_item($botao);

        # Sei
        $botao = new BotaoGrafico();
        $botao->set_label('Documentos no Sei');
        $botao->set_url('servidorSei.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'sei2.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro de documentos com os respectivos números no Sei');
        $menu->add_item($botao);

        # Elogios
        $botao = new BotaoGrafico();
        $botao->set_label('Elogios');
        $botao->set_url('servidorElogios.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'elogios.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro de Elogios e Advertências do Servidor');
        $menu->add_item($botao);

        # Advertências
        $botao = new BotaoGrafico();
        $botao->set_label('Penalidades');
        $botao->set_url('servidorPenalidades.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'penalidades.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro de Elogios e Advertências do Servidor');
        $menu->add_item($botao);

        # Obs
        $botao = new BotaoGrafico();
        $botao->set_label('Observações');
        $botao->set_url('servidorObs.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'obs.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Observações Gerais do Servidor');
        $menu->add_item($botao);

        $menu->show();
        br();
    }

######################################################################################################################

    /**
     * Método moduloOcorrencia
     * 
     * Exibe os servidores que atendem o balcão
     */
    private function moduloOcorrencias() {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Inicializa a variável das mensagens
        $mensagem = array();

        ##### Verifica Afastamentos
        $afastClass = new VerificaAfastamentos($this->idServidor);
        $afastClass->verifica();
        if (!vazio($afastClass->getAfastamento())) {
            $mensagem[] = "Servidor em {$afastClass->getAfastamento()} ({$afastClass->getDetalhe()})";
        }

        $situacao = $pessoal->get_idSituacao($this->idServidor);

        # Motivo de Saída (quanto inativo)
        if (($situacao <> 1) AND ($pessoal->get_motivo($this->idServidor) <> "Outros")) {
            $mensagem[] = $pessoal->get_motivo($this->idServidor);
        }

        # Alertas        
        $metodos = get_class_methods('Checkup');
        $checkup = new Checkup();
        $alertas = $checkup->listaPorServidor($this->idServidor);

        # Junta todas as ocorrências em um único array
        $ocorrencias = array_merge($mensagem, $alertas);

        # Pega a quantidade de ocorrências
        $qtdMensagem = count($ocorrencias);

        $painel = new Callout("warning");
        $painel->abre();

        p("Ocorrências", "palertaServidor");

        # Verifica se tem alguma ocorrência
        if ($qtdMensagem > 0) {
            foreach ($ocorrencias as $item) {
                p("- " . $item, "exibeOcorrencia");
            }
        } else {
            p("Não há ocorrências deste servidor.", "center", "f12");
        }
        $painel->fecha();
    }

######################################################################################################################

    /**
     * Método moduloVinculos
     * 
     * Exibe os vinculos desse servidor
     */
    private function moduloVinculos() {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Vinculos do servidor
        $numVinculos = $pessoal->get_numVinculos($this->idServidor);

        $painel = new Callout("primary");
        $painel->abre();

        p("Vínculos", "palertaServidor");

        # Número de Vinculos
        if ($numVinculos > 1) {

            # Conecta o banco de dados
            $pessoal = new Pessoal();

            # Monta o menu
            $menu = new Menu("menuVinculos");

            # Exibe os vinculos
            $vinculos = $pessoal->get_vinculos($this->idServidor);

            # Percorre os vínculos
            foreach ($vinculos as $rr) {

                # Descarta o vinculo em tela
                if ($rr[0] <> $this->idServidor) {
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

                    #$menu->add_item("link","$cargo - $perfil ($dtAdm - $dtSai) - $motivo",'servidor.php?fase=editar&id='.$rr[0]);
                    $menu->add_item("link", "$cargo - $perfil ($motivo)", 'servidor.php?fase=editar&id=' . $rr[0]);
                }
            }

            # Exibe o menu
            $menu->show();
        } else {
            p("Não há vinculos anteriores deste servidor na Uenf.", "center", "f12");
        }
        $painel->fecha();
    }

    ######################################################################################################################

    /**
     * Método moduloRelatorios
     * 
     * Exibe os relatórios desse servidor
     */
    private function moduloRelatorios() {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $cargo = $pessoal->get_idCargo($this->idServidor);

        titulo('Documentos');
        br();

        $menu = new Menu("menuServidor");
        $menu->add_item('titulo', 'Afastamento Eleitoral', '#');
        $menu->add_item('linkWindow', 'Declaração de Frequência', '../grhRelatorios/declaracao.Eleitoral.Frequencia.php');
        $menu->add_item('linkWindow', 'Documento Termo de Responsabilidade', '../grhRelatorios/declaracao.Eleitoral.Termo.Responsabilidade.php');
        $menu->add_item('linkWindow', 'Declaração de Responsabilização', '../grhRelatorios/declaracao.Eleitoral.Responsabilizacao.php');

        $menu->add_item('titulo', 'Outros Documentos', '#');
        $menu->add_item("linkWindow", "Despacho para Abertura de Processo", "?fase=despacho");
        $menu->add_item("linkWindow", "Despacho para Reitoria", "../grhRelatorios/despacho.Reitoria.php");
        $menu->add_item("linkWindow", "Despacho para Publicação de Ato do Reitor", "../grhRelatorios/despacho.Publicacao.php");
        $menu->add_item("linkWindow", "Despacho à Chefia/Servidor para Retirada do Ato", "?fase=despachoChefia");

        $menu->add_item("linkWindow", "Ficha Cadastral", "../grhRelatorios/fichaCadastral.php");
        $menu->add_item("linkWindow", "Folha de Presença", "../grhRelatorios/folhaPresenca.php");
        $menu->add_item("linkWindow", "Mapa do Cargo", "../grhRelatorios/mapaCargo.php?cargo=$cargo");

        $menu->add_item('linkWindow', 'Declaração de Inquérito Administrativo', '../grhRelatorios/declaracao.InqueritoAdministrativo.php');
        $menu->add_item('linkWindow', 'Declaração de Atribuições do Cargo', '../grhRelatorios/declaracao.AtribuicoesCargo.php');

        #$menu->add_item('link','Declaração para o INSS','#');
        #$menu->add_item("linkWindow","FAF","../grhRelatorios/fichaAvaliacaoFuncional.php");
        #$menu->add_item("linkWindow","Capa da Pasta","../grhRelatorios/capaPasta.php");
        $menu->show();
    }

######################################################################################################################

    /**
     * Método moduloPessoais
     * 
     * Exibe os dados pessoais desse servidor
     */
    private function moduloPessoais() {

        # Exibe o título
        titulo('Pessoais');
        br();

        $menu = new MenuGrafico(2);
        $botao = new BotaoGrafico();
        $botao->set_label('Pessoais');
        $botao->set_url('servidorPessoais.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'pessoais.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Dados Pessoais Gerais do Servidor');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Endereço & Contatos');
        $botao->set_url('servidorEnderecoContatos.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'bens.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Endereço e Contatos do Servidor');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Documentos');
        $botao->set_url('servidorDocumentos.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'documento.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro da Documentação do Servidor');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Formação');
        $botao->set_url('servidorFormacao.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'diploma.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro de Formação Escolar do Servidor');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Parentes');
        $botao->set_url('servidorDependentes.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'dependente.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro dos Parentes do Servidor');
        $menu->add_item($botao);

        $menu->show();
        br();
    }

######################################################################################################################

    /**
     * Método moduloAfastamentos
     * 
     * Exibe os dados de afastamentos desse servidor
     */
    private function moduloAfastamentos() {


        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        titulo('Afastamentos');
        br();

        $menu = new MenuGrafico(3);

        $botao = new BotaoGrafico();
        $botao->set_label('Afastamento Geral');
        $botao->set_url('servidorAfastamentos.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'afastamento.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Todos os afastamentos do servidor');
        #$botao->set_accessKey('i');
        $menu->add_item($botao);

        if ($pessoal->get_perfilFerias($this->perfil) == "Sim") {
            $botao = new BotaoGrafico();
            $botao->set_label('Férias');
            $botao->set_url('servidorFerias.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'ferias2.png', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Cadastro das Férias do Servidor');
            $botao->set_accessKey('i');
            $menu->add_item($botao);
        }

        if ($pessoal->get_perfilLicenca($this->perfil) == "Sim") {
            $botao = new BotaoGrafico();
            $botao->set_label('Licenças e Afastamentos');
            $botao->set_url('servidorLicenca.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'licenca.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Cadastro de Licenças do Servidor');
            $botao->set_accessKey('L');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label($pessoal->get_licencaNome(6));
            $botao->set_url('servidorLicencaPremio.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'premio.png', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Cadastro de Licenças Prêmio do Servidor');
            #$botao->set_accessKey('L');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Licença Sem Vencimentos');
            $botao->set_url('servidorLicencaSemVencimentos.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'semVencimento.png', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Cadastro de Licenças Sem Vencimentos do Servidor');
            $menu->add_item($botao);
        }

        $botao = new BotaoGrafico();
        $botao->set_label('Atestados (Faltas Abonadas)');
        $botao->set_url('servidorAtestado.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'atestado.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro de Atestados do Servidor');
        #$botao->set_accessKey('i');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('TRE');
        $botao->set_url('servidorTre.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'tre.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro de dias trabalhados no TRE com controle de folgas');
        #$botao->set_accessKey('i');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Afastamento Anual');
        $botao->set_url('?fase=timeline');
        $botao->set_imagem(PASTA_FIGURAS . 'timeline.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Resumo gráfico do tempo de vida funcional do servidor dentro da Universidade');
        #$botao->set_accessKey('i');
        #$menu->add_item($botao);

        $menu->show();
        br();
    }

######################################################################################################################

    /**
     * Método moduloFinanceiro
     * 
     * Exibe os dados financeiros desse servidor
     */
    private function moduloFinanceiro() {


        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        titulo('Financeiro');
        br();

        $menu = new MenuGrafico(3);
        if ($pessoal->get_perfilProgressao($this->perfil) == "Sim") {
            $botao = new BotaoGrafico();
            $botao->set_label('Progressão e Enquadramento');
            $botao->set_url('servidorProgressao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'salario.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Cadastro de Progressões e Enquadramentos do Servidor');
            $menu->add_item($botao);
        }

        if ($pessoal->get_perfilTrienio($this->perfil) == "Sim") {
            $botao = new BotaoGrafico();
            $botao->set_label('Triênio');
            $botao->set_url('servidorTrienio.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'trienio.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Histórico de Triênios do Servidor');
            $menu->add_item($botao);
        }

        if ($pessoal->get_perfilGratificacao($this->perfil) == "Sim") {
            $botao = new BotaoGrafico();
            $botao->set_label('Gratificação Especial');
            $botao->set_url('servidorGratificacao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'gratificacao.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Histórico das Gratificações Especiais do Servidor');
            $menu->add_item($botao);
        }

        # Direito Pessoal    
        $botao = new BotaoGrafico();
        $botao->set_label('Direito Pessoal');
        $botao->set_url('servidorDireitoPessoal.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'abono.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro de Abono / Direito Pessoal');
        $menu->add_item($botao);

        if ($this->perfil == 1) {   // Ser for estatutário
            # Abono Permanencia    
            $botao = new BotaoGrafico();
            $botao->set_label('Abono Permanência');
            $botao->set_url('servidorAbono.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'money.png', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Cadastro de Abono Permanencia');
            $menu->add_item($botao);
        }

        # Diarias
        $botao = new BotaoGrafico();
        $botao->set_label('Diárias');
        $botao->set_url('servidorDiaria.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'diaria.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Controle de Diárias');
        $menu->add_item($botao);

        # Dados Bancários
        $botao = new BotaoGrafico();
        $botao->set_label('Dados Bancários');
        $botao->set_url('servidorBancario.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'banco.jpg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Cadastro dos dados bancários do Servidor');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Resumo Financeiro');
        $botao->set_url('servidorFinanceiro.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'lista.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Informações sobre os valores recebidos pelo servidor');
        #$botao->set_onClick("abreFechaDiv('divResumo');");
        $menu->add_item($botao);

        $menu->show();
        br();
    }

    ######################################################################################################################

    /**
     * Método moduloBeneficios
     *  
     * Exibe os dados de benefícios desse servidor
     */
    private function moduloBeneficios() {
        titulo('Benefícios');
        br();

        $menu = new MenuGrafico(1);

        $botao = new BotaoGrafico();
        $botao->set_label('Readaptação');
        $botao->set_url('servidorReadaptacao.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'readaptacao.png', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Controle de Readaptação');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Redução da Carga Horária');
        $botao->set_url('servidorReducao.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS . 'carga-horaria.svg', $this->tamanhoImagem, $this->tamanhoImagem);
        $botao->set_title('Controle de Redução da Carga Horária');
        $menu->add_item($botao);

        if ($this->situacao <> "Ativo" AND $this->perfil == 1) {

            $botao = new BotaoGrafico();
            $botao->set_label('Auxílio Funeral');
            $botao->set_url('servidorAuxilioFuneral.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'funeral.png', $this->tamanhoImagem, $this->tamanhoImagem);
            $botao->set_title('Dados do auxílio funeral');
            $menu->add_item($botao);
        }

        $menu->show();
        br();
    }

    ######################################################################################################################

    /**
     * Método moduloCargoComissao
     *  
     * Exibe os dados de cargo em comissão
     */
    private function moduloCargoComissao() {

        $painel = new Callout("success");
        $painel->abre();

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        $descricao = $pessoal->get_cargoComissaoDescricao($this->idServidor);
        p($descricao, "center");

        $painel->fecha();
    }

    ######################################################################################################################

    /**
     * Método moduloOrgaoCedido
     *  
     * Informa o órgão onde o servidor está cedido
     */
    private function moduloOrgaoCedido() {

        $painel = new Callout("success");
        $painel->abre();

        p("Cedido para " . $this->orgaoCedido, "center");

        $painel->fecha();
    }

######################################################################################################################
}
