<?php

class MenuPrincipal{
    /**
     * Gera o Menu Principal do Sistema
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    
    private $idUsuario = NULL;
    
######################################################################################################################    
    
    public function __construct($idUsuario){
    /**
     * Inicia a Ci e preenche oas variáveis com valores padrão
     */
        
        # Inicia o Grid
        $grid = new Grid();
        
        # Preenche variável
        $this->idUsuario = $idUsuario;
        
        # Primeira Coluna
        $grid->abreColuna(12,4,3);
        
        # Módulos
        $this->moduloServidores();
        $this->moduloLegislacao();
        $this->moduloTabelasSecundarias();
                
        $grid->fechaColuna();
        
        ##########################################################
            
        # Área Central 
        $grid->abreColuna(12,8,5);
        
        # Módulos
        $this->moduloTabelaAuxiliares();
        $this->moduloAreaEspecial();
        $this->moduloLinksExternos();
        
        $grid->fechaColuna();
        
        ###############################################################################################
        
        # Terceira Coluna
        $grid->abreColuna(12,6,4);
        
        # Módulos        
        $this->moduloBalcao();
        $this->moduloAniversariantes();
        $this->moduloAlertas();
        
        $grid->fechaColuna();
        $grid->fechaGrid();        
    }

######################################################################################################################
    
    /**
     * Método moduloServidores
     * 
     * Exibe o menu do cadastro de Servidore
     */
    
    private function moduloServidores(){
        
        $painel = new Callout();
        $painel->abre();
        
        # Servidores
        titulo('Servidores');
        br();

        $tamanhoImage = 180;
        $menu = new MenuGrafico(1);

        $botao = new BotaoGrafico();
        $botao->set_label('Servidores');
        $botao->set_url('servidor.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'servidores.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Servidores');
        $botao->set_accesskey('S');
        $menu->add_item($botao);

        $menu->show();        
        $painel->fecha();
    }
        
######################################################################################################################
    
    /**
     * Método moduloLegislacao
     * 
     * Exibe o menu de Legislação
     */
    
    private function moduloLegislacao(){
        
        $painel = new Callout();
        $painel->abre();
        
        # Servidores
        titulo('Legislação');
        br();
        
        # Menu
        $menu = new Menu("menuProcedimentos");
        #$menu->add_item('titulo','Legislação','#','Área Especial');
        $menu->add_item('linkWindow','Estatuto dos Servidores',"http://alerjln1.alerj.rj.gov.br/decest.nsf/968d5212a901f75f0325654c00612d5c/2caa8a7c2265c33b0325698a0068e8fb?OpenDocument#_Section1","Decreto nº 2479 de 08 de Março de 1979");
        $menu->add_item('linkWindow','Plano de Cargos e Vencimentos',"http://alerjln1.alerj.rj.gov.br/contlei.nsf/b24a2da5a077847c032564f4005d4bf2/aa5390d4c58db774832571b60066a2ba?OpenDocument","LEI Nº 4.800 de 29 de Junho de 2006");
        $menu->add_item('linkWindow','Resoluções da Reitoria',"http://uenf.br/reitoria/legislacao/resolucoes/");
        $menu->add_item('linkWindow','Portarias',"http://uenf.br/reitoria/legislacao/portarias/");     
        $menu->add_item('linkWindow','Estatuto da UENF',"http://www.uenf.br/Uenf/Downloads/REITORIA_1360_1101117875.pdf");
        
        $menu->show();            
        $painel->fecha();
    }
        
######################################################################################################################
    
    /**
     * Método moduloBalcao
     * 
     * Exibe o menu de Legislação
     */
    
    private function moduloBalcao(){
        
        $painel = new Callout("primary");
        $painel->abre();
        
        titulo('Hoje no Balcão');
        br();
        
        $select = "SELECT manha, tarde FROM tbbalcao WHERE month(curdate()) = mes AND day(curdate()) = dia AND year(curdate()) = ano";
        $pessoal = new Pessoal();
        $sortudos = $pessoal->select($select, false);

        if(is_NULL($sortudos)){
            p("Não Haverá Atendimento Hoje.");
        }else{
            echo "<table class='tabelaPadrao'>";
            #echo "<tr><th>Turno</th><th>Servidor</th></tr>";
            echo "<tr><td>Manhã:</td><td>".trataNulo($sortudos[0])."</td></tr>";
            echo "<tr><td>Tarde:</td><td>".trataNulo($sortudos[1])."</td></tr>";
            echo "</table>";
        }
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

        if(Verifica::acesso($this->idUsuario,1)){
            $botao->set_label('Procedimentos');
            $botao->set_target('blank');
            $botao->set_url('../../areaServidor/sistema/procedimentos.php');
            $botao->set_imagem(PASTA_FIGURAS.'procedimentos.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Àrea de Procedimentos da GRH');
            $menu->add_item($botao);
        }        
        
        $botao = new BotaoGrafico();
        $botao->set_label('Férias');
        $botao->set_url('areaFeriasExercicio.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'ferias.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Férias');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Licença Prêmio');
        $botao->set_url('areaLicencaPremio.php');
        $botao->set_imagem(PASTA_FIGURAS.'premio.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Licença Prêmio');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('TRE');
        $botao->set_url('areaTre.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'tre.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Controle de Folgas do TRE');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Estatística');
        $botao->set_url('estatistica.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'pie.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Análise estatísticas');
        $menu->add_item($botao);    
        
        $botao = new BotaoGrafico();
        $botao->set_label('Benefícios');
        $botao->set_url('areaBeneficios.php');
        $botao->set_imagem(PASTA_FIGURAS.'beneficios.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Benefícios dos Servidores');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Formação');
        $botao->set_url('areaFormacao.php');
        $botao->set_imagem(PASTA_FIGURAS.'diploma.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Formação Escolar dos Servidores');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Recadastramento');
        $botao->set_url('areaRecadastramento.php');
        $botao->set_imagem(PASTA_FIGURAS.'recadastramento.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Recadastramento de Servidores');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Aposentadoria');
        $botao->set_url('areaAposentadoria.php?grh=1');
        $botao->set_imagem(PASTA_FIGURAS.'aposentadoria.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área das rotinas de aposentadoria do serviodor');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Feriados');
        $botao->set_url("cadastroFeriado.php?grh=1");
        $botao->set_imagem(PASTA_FIGURAS.'faltas.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Feriados');
        #$botao->set_accesskey('S');
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