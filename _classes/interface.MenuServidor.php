<?php

class MenuServidor{
    /**
     * Gera o Menu Principal do Sistema
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    
    private $idUsuario = NULL;
    private $idServidor = NULL;
    private $perfil = NULL;
    private $situacao = NULL;
    
######################################################################################################################    
    
    public function __construct($idServidor = NULL, $idUsuario = NULL){
    /**
     * Inicia a classe
     */
        
        # Conecta o banco de dados
        $pessoal = new Pessoal();
        
        # Pega o perfil do servidor pesquisado
        $perfil = $pessoal->get_idPerfil($idServidor);
        $situacao = $pessoal->get_situacao($idServidor);
        
        # Preenche variável
        $this->idUsuario = $idUsuario;
        $this->idServidor = $idServidor;
        $this->perfil = $perfil;
        $this->situacao = $situacao;
                
        # Inicia o Grid
        $grid = new Grid();
        
        # Primeira Coluna
        $grid->abreColuna(12,4,3);
        
        # Módulos
        if($this->perfil <> 10){          // Se não for bolsista
            $this->moduloFoto();
        }
                
        $grid->fechaColuna();
        
        ##########################################################
            
        # Área Central 
        $grid->abreColuna(12,8,5);
        
        # Módulos
        $this->moduloFuncionais();
        $this->moduloGrh();
        $this->moduloAreaEspecial();
        $this->moduloLinksExternos();
        
        $grid->fechaColuna();
        
        ###############################################################################################
        
        # Terceira Coluna
        $grid->abreColuna(12,6,4);
        
        # Módulos        
        $this->moduloOcorrencia();
        $this->moduloAniversariantes();
        $this->moduloAlertas();
        
        $grid->fechaColuna();
        $grid->fechaGrid();        
    }

######################################################################################################################
    
    /**
     * Método moduloFoto
     * 
     * Exibe a Foto do servidor
     */
    
    private function moduloFoto(){
        
        $painel = new Callout();
        $painel->abre();
        
        titulo('Foto do Servidor');
        
        # Conecta o banco de dados
        $pessoal = new Pessoal();

        $idPessoa = $pessoal->get_idPessoa($this->idServidor);

        # Define a pasta
        $arquivo = "../../_fotos/$idPessoa.jpg";

        # Verifica se tem pasta desse servidor
        if(file_exists($arquivo)){
            br();

            $botao = new BotaoGrafico();
            $botao->set_url('?fase=exibeFoto');
            $botao->set_imagem($arquivo,'Foto do Servidor',200,150);
            $botao->set_title('Foto do Servidor');
            $botao->show();
        }else{                
            $foto = new Imagem(PASTA_FIGURAS.'foto.png','Foto do Servidor',150,100);
            $foto->set_id('foto');
            $foto->show();
            br();
        }

        $div = new Div("center");
        $div->abre();

        $link = new Link("Alterar Foto","?fase=uploadFoto");
        $link->set_id("alteraFoto");
        $link->show();

        $div->fecha();
        $painel->fecha();
    }
        
######################################################################################################################
    
    /**
     * Método moduloFuncionais
     * 
     * Exibe o menu de Dados Funcionais
     */
    
    private function moduloFuncionais(){
        
        $painel = new Callout();
        $painel->abre();
        
        # Conecta o banco de dados
        $pessoal = new Pessoal();
        
        titulo('Funcionais');
        br();     
        $tamanhoImage = 50;

        $menu = new MenuGrafico(4);

        # Funcionais
        $botao = new BotaoGrafico();
        $botao->set_label('Funcionais');
        $botao->set_url('servidorFuncionais.php');
        $botao->set_imagem(PASTA_FIGURAS.'funcional.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Dados Funcionais do Servidor');
        $menu->add_item($botao);

        # Lotação
        $botao = new BotaoGrafico();
        $botao->set_label('Lotação');
        $botao->set_url('servidorLotacao.php');
        $botao->set_imagem(PASTA_FIGURAS.'lotacao.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Histórico da Lotação do Servidor');
        $menu->add_item($botao);            

        # Cargo em Comissão
        if($pessoal->get_perfilComissao($this->perfil) == "Sim"){
            $botao = new BotaoGrafico();
            $botao->set_label('Cargo em Comissão');
            $botao->set_url('servidorComissao.php');
            $botao->set_imagem(PASTA_FIGURAS.'comissao.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Histórico dos Cargos em Comissão do Servidor');
            $menu->add_item($botao);
        }

        # Tempo de Serviço
        if(($this->perfil == 1) OR ($this->perfil == 4)){   // Ser for estatutário
            $botao = new BotaoGrafico();
            $botao->set_label('Tempo de Serviço');
            $botao->set_url('servidorAverbacao.php');
            $botao->set_imagem(PASTA_FIGURAS.'historico.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Tempo de Serviço Averbado');
            $menu->add_item($botao);

            if($this->situacao == "Ativo"){
                $botao = new BotaoGrafico();
                $botao->set_label('Aposentadoria');
                $botao->set_url('servidorAposentadoria.php');
                $botao->set_imagem(PASTA_FIGURAS.'aposentadoria.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Avalia a posentadoria do Servidor');
                $menu->add_item($botao);
            }
        }

        # Cessão
        if(($this->perfil == 1) OR ($this->perfil == 4)){   // Ser for estatutário
            $botao = new BotaoGrafico();
            $botao->set_label('Cessão');
            $botao->set_url('servidorCessao.php');
            $botao->set_imagem(PASTA_FIGURAS.'cessao.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Histórico de Cessões do Servidor');
            $menu->add_item($botao);
        }elseif($this->perfil == 2){ // se for cedido
            $botao = new BotaoGrafico();
            $botao->set_label('Cessão');
            $botao->set_url('servidorCessaoCedido.php');
            $botao->set_imagem(PASTA_FIGURAS.'cessao.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Dados da Cessão do Servidor');
            $menu->add_item($botao);
        }

        # Obs
        $botao = new BotaoGrafico();
        $botao->set_label('Observações');
        $botao->set_url('servidorObs.php');
        $botao->set_imagem(PASTA_FIGURAS.'obs.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Observações Gerais do Servidor');
        $menu->add_item($botao);

        # Pasta Funcional
        $botao = new BotaoGrafico();
        $botao->set_label('Pasta Funcional');
        $botao->set_url('?fase=pasta');
        $botao->set_imagem(PASTA_FIGURAS.'arquivo.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Pasta funcional do servidor');
        $menu->add_item($botao);

        # Elogios e Advertências
        if($this->perfil <> 10){          // Se não for bolsista
            $botao = new BotaoGrafico();
            $botao->set_label('Elogios & Advertências');
            $botao->set_url('servidorElogiosAdvertencias.php');
            $botao->set_imagem(PASTA_FIGURAS.'ocorrencia.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Elogios e Advertências do Servidor');
            $menu->add_item($botao);
        }

        $menu->show();
        $painel->fecha();
    }
        
######################################################################################################################
    
    /**
     * Método moduloOcorrencia
     * 
     * Exibe os servidores que atendem o balcão
     */
    
    private function moduloOcorrencia(){
        
        $painel = new Callout("primary");
        $painel->abre();
        
        titulo('Ocorrencias');
       
        Grh::exibeOcorênciaServidor($this->idServidor);
        
        $painel->fecha();
    }
        
######################################################################################################################
    
    /**
     * Método moduloGrh
     * 
     * Exibe o menu de assuntos pertinentes aos servidores da grh
     */
    
    private function moduloGrh(){
        
        $painel = new Callout();
        $painel->abre();
        
        titulo('GRH');           
        br();

        $tamanhoImage = 60;
        $menu = new MenuGrafico(4);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Afastamentos');
        #$botao->set_target('blank');
        $botao->set_url('grhAfastamentos.php');
        $botao->set_imagem(PASTA_FIGURAS.'afastamento.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Afastamentos dos Servidores da GRH');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Atribuições');
        $botao->set_url("cadastroAtribuicoes.php?grh=1");
        $botao->set_imagem(PASTA_FIGURAS.'atribuicoes.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Atribuições de tarefas');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Balcão');
        $botao->set_url("balcao.php?grh=1");
        $botao->set_imagem(PASTA_FIGURAS.'balcao.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Controle de Atendimento do Balcão');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Feriados');
        $botao->set_url("cadastroFeriado.php?grh=1");
        $botao->set_imagem(PASTA_FIGURAS.'faltas.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Feriados');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Procedimentos');
        #$botao->set_target('blank');
        $botao->set_url('../../areaServidor/sistema/procedimentos.php');
        $botao->set_imagem(PASTA_FIGURAS.'procedimentos.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Procedimentos da GRH');
        #$menu->add_item($botao);
        
        $menu->show();
        $painel->fecha();
    }
        
######################################################################################################################
    
    /**
     * Método moduloTabelaAuxiliares
     * 
     * Exibe o menu de Legislação
     */
    
    private function moduloTabelaAuxiliares(){
        
        $painel = new Callout();
        $painel->abre();

        titulo('Tabelas Auxiliares');           
        br();

        $tamanhoImage = 60;
        $menu = new MenuGrafico(4);

        $botao = new BotaoGrafico();
        $botao->set_label('Perfil');
        $botao->set_url('cadastroPerfil.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'usuarios.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Perfil');
        $botao->set_accesskey('P');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Lotação');
        $botao->set_url('cadastroLotacao.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'lotacao.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Lotação');
        $botao->set_accesskey('L');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Cargo Efetivo');
        $botao->set_url('cadastroCargo.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'cracha.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Funções');
        $botao->set_accesskey('C');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Cargo em Comissão');
        $botao->set_url('areaCargoComissao.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'usuarios.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Cargos em Comissão');
        $botao->set_accesskey('g');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Concurso');
        $botao->set_url('cadastroConcurso.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'concurso.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Concursos');
        $botao->set_accesskey('o');
        $menu->add_item($botao);   

        $botao = new BotaoGrafico();
        $botao->set_label('Licenças e Afastamentos');
        $botao->set_url('cadastroLicenca.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'nene.gif',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Tipos de Licenças');
        #$botao->set_accesskey('T');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Plano de Cargos & Vencimentos');
        $botao->set_url('cadastroPlanoCargos.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'plano.gif',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Plano de Cargos & Vencimentos');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Tabela Salarial');
        $botao->set_url('cadastroTabelaSalarial.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'dinheiro.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Tipos de Licenças');
        $botao->set_accesskey('b');
        #$menu->add_item($botao);        

        $menu->show();
        $painel->fecha();
    }
        
######################################################################################################################
    
    /**
     * Método moduloAreaEspecial
     * 
     * Exibe o menu de Legislação
     */
    
    private function moduloAreaEspecial(){
        
        $painel = new Callout();
        $painel->abre();

        titulo('Área Especial');
        br();

        $tamanhoImage = 60;
        $menu = new MenuGrafico(4);
        $botao = new BotaoGrafico();            
        
        $botao = new BotaoGrafico();
        $botao->set_label('Férias');
        $botao->set_url('areaFeriasExercicio.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'ferias2.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Férias');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Licença Prêmio');
        $botao->set_url('areaLicencaPremio.php');
        $botao->set_imagem(PASTA_FIGURAS.'premio.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Licença Prêmio');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Benefícios');
        $botao->set_url('areaBeneficios.php');
        $botao->set_imagem(PASTA_FIGURAS.'beneficios.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Benefícios dos Servidores');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Afastamentos');
        $botao->set_url('areaFrequencia.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'afastamento.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Informa todo o tipo de Afastamento de Servidor');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Formação');
        $botao->set_url('areaFormacao.php');
        $botao->set_imagem(PASTA_FIGURAS.'diploma.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Formação Escolar dos Servidores');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Parentes');
        $botao->set_url('areaParente.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'parente.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área do Cadastro de Parentes dos Servidores');
        $menu->add_item($botao);        

        $botao = new BotaoGrafico();
        $botao->set_label('TRE');
        $botao->set_url('areaTre.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'tre.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Controle de Folgas do TRE');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Sispatri');
        $botao->set_url("areaSispatri.php");
        $botao->set_imagem(PASTA_FIGURAS.'sispatri.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Controle de Sispatri');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Estatística');
        $botao->set_url('estatistica.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'pie.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Análise estatísticas');
        $menu->add_item($botao); 
        
        $botao = new BotaoGrafico();
        $botao->set_label('Aposentadoria');
        $botao->set_url('areaAposentadoria.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'aposentadoria.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área das rotinas de aposentadoria do serviodor');
        $menu->add_item($botao);
        
        if(Verifica::acesso($this->idUsuario,1)){
            $botao = new BotaoGrafico();
            $botao->set_label('Recadastramento');
            $botao->set_url('areaRecadastramento.php');
            $botao->set_imagem(PASTA_FIGURAS.'recadastramento.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Recadastramento de Servidores');
            $menu->add_item($botao);
        }
        
        $menu->show();
        $painel->fecha();
    }
        
        
######################################################################################################################
    
    /**
     * Método moduloAlertas
     * 
     * Exibe os Alertas
     */
    
    private function moduloAlertas(){
        
        $painel = new Callout("warning");
        $painel->abre();

        $divAlertas = new Div("divAlertas");
        $divAlertas->abre();            
            titulo('Alertas');
            br(5);
            aguarde();
            br(5);
        $divAlertas->fecha();
        $painel->fecha(); 
    }
        
######################################################################################################################
    
    /**
     * Método moduloAniversariantes
     * 
     * Exibe os Aniversariantes
     */
    
    private function moduloAniversariantes(){
        
        $painel = new Callout("success");
        $painel->abre();
        titulo("Aniversariantes de ".get_nomeMes());
        br();

        # Pega os valores
        $pessoal = new Pessoal();
        $numServidores = $pessoal->get_numAniversariantes();
        $numHoje = $pessoal->get_numAniversariantesHoje();

        # Exibe os valores
        p("Aniversariantes do mês: ".$numServidores,"aniversariante");
        p("Aniversariantes de hoje: ".$numHoje,"aniversariante");

        $div = new Div("divAniversariante");
        $div->abre();
            $link = new Link("Saiba mais","?fase=aniversariantes");
            #$link->set_class('small button');
            $link->set_id('linkAniversariante');
            $link->set_title('Aniversarintes do mês');
            $link->show();
        $div->fecha();
        $painel->fecha(); 
    }
        
######################################################################################################################
    
    /**
     * Método moduloLinksExternos
     * 
     * Exibe os Links Externos
     */
    
    private function moduloLinksExternos(){
        
        $painel = new Callout("secondary");
        $painel->abre();
        titulo('Links Externos');
        br();

        $largura = 120;
        $altura = 50;

        $menu = new MenuGrafico(2);
        $botao = new BotaoGrafico();
        $botao->set_label("Sistema do Almoxarifado");
        $botao->set_title('Sistema do Almoxarifado da Uenf');
        $botao->set_imagem(PASTA_FIGURAS."almoxarifado.png",50,50);      
        $botao->set_url("https://almoxarifado.uenf.br/usuarios/sign_in/");
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        #$botao->set_label(SISTEMA_GRH);
        $botao->set_title('Portal do Sistema Integrado de Gestao de Recursos Humanos do Estado do Rio de Janeiro');
        $botao->set_imagem(PASTA_FIGURAS."sigrh.png",$largura,$altura);      
        $botao->set_url("entradasigrh.fazenda.rj.gov.br/");
        $menu->add_item($botao);
        $menu->show();

        br();

        $menu = new MenuGrafico(2);
        $botao = new BotaoGrafico();
        $botao->set_label("");
        $botao->set_imagem(PASTA_FIGURAS."do.png",$largura,$altura);  
        $botao->set_url("http://www.imprensaoficial.rj.gov.br/portal/modules/profile/user.php?xoops_redirect=/portal/modules/content/index.php?id=21");
        $botao->set_title("Imprensa Oficial do Estado do Rio de Janeiro");
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        #$botao->set_label(SISTEMA_GRH);
        $botao->set_title('Portal do Processo Digital');
        $botao->set_imagem(PASTA_FIGURAS."processoDigital.png",$largura,$altura);     
        $botao->set_url("https://www.processodigital.rj.gov.br/");
        $menu->add_item($botao);
        $menu->show();

        br();

        $menu = new MenuGrafico(2);            
        $botao = new BotaoGrafico();
        #$botao->set_label(SISTEMA_GRH);
        $botao->set_title('Site da UENF');
        $botao->set_imagem(PASTA_FIGURAS."uenf.png",$largura,$altura);       
        $botao->set_url("http://www.uenf.br/portal/index.php/br/");
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        #$botao->set_label(SISTEMA_GRH);
        $botao->set_title('Site da GRH');
        $botao->set_imagem(PASTA_FIGURAS."GRH.png",$largura,$altura);  
        $botao->set_url("http://uenf.br/dga/grh/");
        $menu->add_item($botao);
        $menu->show();

        $painel->fecha(); 
    }
        
######################################################################################################################
    
    /**
     * Método moduloTabelasSecundarias
     * 
     * Exibe as Tabelas Secundária
     */
    
    private function moduloTabelasSecundarias(){
        
        $painel = new Callout();
        $painel->abre();
        
        # Servidores
        titulo('Tabelas Secundárias');
        br();
        
        # Menu
        $menu = new Menu("menuProcedimentos");
        #$menu->add_item('titulo','Tabelas Secundárias','#','Tabelas Secundárias');
        #$menu->add_item('link','Descrrição dos Cargos em Comissão','cadastroDescricaoComissao.php?grh=1','Acessa o Cadastro de Descrição dos Cargos em Comissão');
        $menu->add_item('link','Tabela Salarial','cadastroTabelaSalarial.php?grh=1','Acessa o Cadastro de Tabela Salarial');
        $menu->add_item('link','Banco','cadastroBanco.php?grh=1','Acessa o Cadastro de Bancos');
        $menu->add_item('link','Escolaridade','cadastroEscolaridade.php?grh=1','Acessa o Cadastro de Escolaridade');
        $menu->add_item('link','Estado Civil','cadastroEstadoCivil.php?grh=1','Acessa o Cadastro de Estado Civil');
        $menu->add_item('link','Parentesco','cadastroParentesco.php?grh=1','Acessa o Cadastro de Parentesco');
        $menu->add_item('link','Situação','cadastroSituacao.php?grh=1','Acessa o Cadastro de Situação');
        $menu->add_item('link','Motivos de Saída','cadastroMotivo.php?grh=1','Acessa o Cadastro de Motivo de Saída');
        $menu->add_item('link','Tipos de Progressão','cadastroProgressao.php?grh=1','Acessa o Cadastro de Progressão & Enquadramento');
        $menu->add_item('link','Nacionalidade','cadastroNacionalidade.php?grh=1','Acessa o Cadastro de Nacionalidade');
        $menu->add_item('link','País','cadastroPais.php?grh=1','Acessa o Cadastro de Pais');
        $menu->add_item('link','Estado','cadastroEstado.php?grh=1','Acessa o Cadastro de Estados');
        $menu->add_item('link','Cidades','cadastroCidade.php?grh=1','Acessa o Cadastro de Cidades');        
        
        $menu->show();            
        $painel->fecha();
    }
        
######################################################################################################################
    
}