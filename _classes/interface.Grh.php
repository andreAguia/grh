<?php

class Grh{
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
    public static function cabecalho($titulo = NULL){        
        # tag do cabeçalho
        echo '<header>';
        
        $cabec = new Div('center');
        $cabec->abre();
            $imagem = new Imagem(PASTA_FIGURAS.'uenf.jpg','Área do Servidor da Uenf',190,60);
            $imagem->show();
        $cabec->fecha();       
        
        if(!(is_null($titulo))){
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
    
    public static function menu($idUsuario){

    /**
     * Exibe o menu inicial do sistema
     * 
     * @var private $matriculaUsuário string NULL Informa a matrícula do servidor logado para exibir somente os links que o servidor tem permissão 
     */
        
        $gridFora = new Grid();
        $gridFora->abreColuna(12,8,8);
        
       ##########################################################
        
        # Cadastro de Servidores
        $grid = new Grid();
        $grid->abreColuna(12,12,4);

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
        br(); 
        $grid->fechaColuna();
        
        ##########################################################
            
        # Tabelas Auxiliares 
        $grid->abreColuna(12,12,8);        

        titulo('Tabelas Auxiliares');           
        br();

        $tamanhoImage = 60;
        $menu = new MenuGrafico(4);

        $botao = new BotaoGrafico();
        $botao->set_label('Perfil');
        $botao->set_url('cadastroPerfil.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'usuarios.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Perfil');
        $botao->set_accesskey('P');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Lotação');
        $botao->set_url('cadastroLotacao.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'lotacao.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Lotação');
        $botao->set_accesskey('L');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Cargo Efetivo');
        $botao->set_url('cadastroCargo.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'cracha.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Funções');
        $botao->set_accesskey('C');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Cargo em Comissão');
        $botao->set_url('cadastroCargoComissao.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'usuarios.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Cargos em Comissão');
        $botao->set_accesskey('g');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Concurso');
        $botao->set_url('cadastroConcurso.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'concurso.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Concursos');
        $botao->set_accesskey('o');
        $menu->add_item($botao);   

        $botao = new BotaoGrafico();
        $botao->set_label('Licenças e Afastamentos');
        $botao->set_url('cadastroLicenca.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'nene.gif',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Tipos de Licenças');
        #$botao->set_accesskey('T');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Plano de Cargos & Vencimentos');
        $botao->set_url('cadastroPlanoCargos.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'plano.gif',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Plano de Cargos & Vencimentos');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Tabela Salarial');
        $botao->set_url('cadastroTabelaSalarial.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'dinheiro.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Tipos de Licenças');
        $botao->set_accesskey('b');
        #$menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Feriados');
        $botao->set_url("cadastroFeriado.php?origem=1");
        $botao->set_imagem(PASTA_FIGURAS.'faltas.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Feriados');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Atribuições');
        $botao->set_url("cadastroAtribuicoes.php?origem=1");
        $botao->set_imagem(PASTA_FIGURAS.'atribuicoes.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Atribuições de tarefas');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);            

        $botao = new BotaoGrafico();
        $botao->set_label('Balcão');
        $botao->set_url("balcao.php?origem=1");
        $botao->set_imagem(PASTA_FIGURAS.'balcao.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Controle de Atendimento do Balcão');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);

        $menu->show();
        br();

        $grid->fechaColuna();
		       
        ###############################################################################################
                        
        # Área Especial        
        $grid->abreColuna(12);

        titulo('Área Especial');
        br();
        
        if(Verifica::acesso($idUsuario,1)){
            $menuItem = 6;
        }else{
            $menuItem = 5;
        }

        $tamanhoImage = 60;
        $menu = new MenuGrafico($menuItem);
        $botao = new BotaoGrafico();
        
        if(Verifica::acesso($idUsuario,1)){
            $botao->set_label('Procedimentos');
            $botao->set_url('../../areaServidor/sistema/procedimentos.php');
            $botao->set_imagem(PASTA_FIGURAS.'procedimentos.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Àrea de Procedimentos da GRH');
            $menu->add_item($botao);
        }
        
        $botao = new BotaoGrafico();
        $botao->set_label('Férias');
        $botao->set_url('areaFeriasExercicio.php?origem=1');
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
        $botao->set_url('areaTre.php?origem=1');
        $botao->set_imagem(PASTA_FIGURAS.'tre.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Controle de Folgas do TRE');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Estatística');
        $botao->set_url('estatistica.php?origem=1');
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
        $botao->set_label('Recadastramento');
        $botao->set_url('areaRecadastramento.php');
        $botao->set_imagem(PASTA_FIGURAS.'recadastramento.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Recadastramento de Servidores');
        #$menu->add_item($botao);
        
        $menu->show();
        br();

        $grid->fechaColuna();
        $grid->fechaGrid();
        
        $gridFora->fechaColuna();
        
        ###############################################################################################
        
         # Alertas
        $gridFora->abreColuna(12,4,4);
            
            $divAlertas = new Div("divAlertas");
            $divAlertas->abre();            
                titulo('Alertas');
                br(5);
                aguarde();
            $divAlertas->fecha();
            
        $gridFora->fechaColuna();     
        
        ##########################################################  
        
         # Legislação
        $gridFora->abreColuna(12,6,4);

        titulo('Legislação');
        br();

        $menu = new Menu();
        $menu->add_item('linkWindow','Estatuto dos Servidores',"http://alerjln1.alerj.rj.gov.br/decest.nsf/968d5212a901f75f0325654c00612d5c/2caa8a7c2265c33b0325698a0068e8fb?OpenDocument#_Section1","Decreto nº 2479 de 08 de Março de 1979");
        $menu->add_item('linkWindow','Plano de Cargos e Vencimentos',"http://alerjln1.alerj.rj.gov.br/contlei.nsf/b24a2da5a077847c032564f4005d4bf2/aa5390d4c58db774832571b60066a2ba?OpenDocument","LEI Nº 4.800 de 29 de Junho de 2006");
        $menu->add_item('linkWindow','Resoluções da Reitoria',"http://uenf.br/reitoria/legislacao/resolucoes/");
        $menu->add_item('linkWindow','Portarias',"http://uenf.br/reitoria/legislacao/portarias/");     
        $menu->add_item('linkWindow','Estatuto da UENF',"http://www.uenf.br/Uenf/Downloads/REITORIA_1360_1101117875.pdf");            
        $menu->show();

        $gridFora->fechaColuna();
        br();
        
        ##########################################################
        
        # Aniversariantes
        $grid->abreColuna(12,6,4);

            titulo("Aniversariantes de ".get_nomeMes());
            br();

            # Pega os valores
            $pessoal = new Pessoal();
            $numServidores = $pessoal->get_numAniversariantes();
            $numHoje = $pessoal->get_numAniversariantesHoje();

            # Exibe os valores
            p("Aniversariantes do mês: ".$numServidores,"aniversariante");
            p("Aniversariantes de hoje: ".$numHoje,"aniversariante");
            br();

            # Voltar
            $div = new Div("divAniversariante");
            $div->abre();
                $link = new Link("Saiba mais","?fase=aniversariantes");
                #$link->set_class('small button');
                $link->set_id('linkAniversariante');
                $link->set_title('Aniversarintes do mês');
                $link->show();
            $div->fecha();

        $grid->fechaColuna();
        
        ##########################################################
        
        # Balcão
        $gridFora->abreColuna(12,12,4);
            titulo('Hoje no Balcão');
            br();
            
            $select = "SELECT manha, tarde FROM tbbalcao WHERE month(curdate()) = mes AND day(curdate()) = dia AND year(curdate()) = ano";
            $pessoal = new Pessoal();
            $sortudos = $pessoal->select($select, false);
            
            #$painel = new Callout("warning");
            #$painel->abre();
            
            $grid = new Grid("center");
            $grid->abreColuna(8);
            
            if(is_NULL($sortudos)){
                p("Não Haverá Atendimento Hoje.");
            }else{
                echo "<table class='tabelaPadrao'>";
                #echo "<tr><th>Turno</th><th>Servidor</th></tr>";
                echo "<tr><td>Manhã:</td><td>".trataNulo($sortudos[0])."</td></tr>";
                echo "<tr><td>Tarde:</td><td>".trataNulo($sortudos[1])."</td></tr>";
                echo "</table>";
            }
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            #$painel->fecha();
        
        $gridFora->fechaColuna();
        
        ##########################################################                
        
        # links externos
        $gridFora->abreColuna(12);
            titulo('Links Externos');
            br();
            
            $menu = new MenuGrafico(3);
            $largura = 120;
            $altura = 50;
            
            $botao = new BotaoGrafico();
            $botao->set_label("Sistema do Almoxarifado");
            $botao->set_title('Sistema DO Almoxarifado da Uenf');
            $botao->set_imagem(PASTA_FIGURAS."almoxarifado.png",50,50);      
            $botao->set_url("https://almoxarifado.uenf.br/usuarios/sign_in/");
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            #$botao->set_label(SISTEMA_GRH);
            $botao->set_title('Portal do Sistema Integrado de Gestao de Recursos Humanos do Estado do Rio de Janeiro');
            $botao->set_imagem(PASTA_FIGURAS."sigrh.png",$largura,$altura);      
            $botao->set_url("entradasigrh.fazenda.rj.gov.br/");
            $menu->add_item($botao);

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
            
        $gridFora->fechaColuna();
            
        ##########################################################
            
        # Tabelas Secundárias
        if(Verifica::acesso($idUsuario,1)){
            $gridFora->abreColuna(12);            

                $tamanhoImage = 50;
                br();
                titulo('Tabelas Secundárias'); 
                br();

                $menu = new MenuGrafico(6);

                $botao = new BotaoGrafico();
                $botao->set_label('Banco');
                $botao->set_url("cadastroBanco.php?origem=1");
                #$botao->set_onClick("abreDivId('divMensagemAguarde'); fechaDivId('divMenu'); window.location='banco.php'");
                $botao->set_imagem(PASTA_FIGURAS.'banco.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Bancos');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Escolaridade');
                $botao->set_url("cadastroEscolaridade.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'diploma.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Escolaridades');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Estado Civil');
                $botao->set_url("cadastroEstadoCivil.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'licenca.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Estado Civil');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Parentesco');
                $botao->set_url("cadastroParentesco.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'parentesco.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Parentesco');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Situação');
                $botao->set_url("cadastroSituacao.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'usuarios.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Situação');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Motivos de Saída');
                $botao->set_url("cadastroMotivo.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'saida.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Motivos de Saída do Servidor da Instituição');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Tipos de Progressão');
                $botao->set_url("cadastroProgressao.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'dinheiro.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Tipos de Progressões');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Nacionalidade');
                $botao->set_url("cadastroNacionalidade.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'pais.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Nacionalidades');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('País');
                $botao->set_url("cadastroPais.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'nacionalidade.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Paises');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);
                
                $botao = new BotaoGrafico();
                $botao->set_label('Estado');
                $botao->set_url("cadastroEstado.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'estado.jpeg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Estados');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);
                
                $botao = new BotaoGrafico();
                $botao->set_label('Cidades');
                $botao->set_url("cadastroCidade.php?origem=1");
                $botao->set_imagem(PASTA_FIGURAS.'city.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Cidades');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $menu->show();

            $gridFora->fechaColuna();
            
        }
        $gridFora->fechaGrid();
    }
        
######################################################################################################################

    public static function menuServidor($idServidor,$idUsuario){
            
    /**
     * método menuServidor
     * 
     * Exibe o menu do servidor - o que aparece quando se seleciona um servidor 
     */
        
        # Divide a tela        
        $grid2 = new Grid();   
        
        #######################################################################
        
        # Funcionais 
        $grid2->abreColuna(12,5);        
            titulo('Funcionais');
            br();     
            $tamanhoImage = 50;
            
            $menu = new MenuGrafico(4);
            $botao = new BotaoGrafico();
            $botao->set_label('Funcionais');
            $botao->set_url('servidorFuncionais.php');
            $botao->set_imagem(PASTA_FIGURAS.'funcional.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Dados Funcionais do Servidor');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Lotação');
            $botao->set_url('servidorLotacao.php');
            $botao->set_imagem(PASTA_FIGURAS.'lotacao.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Histórico da Lotação do Servidor');
            $menu->add_item($botao);

            $pessoal = new Pessoal();
            $perfil = $pessoal->get_idPerfil($idServidor);

            if(($perfil == 1) OR ($perfil == 4)){   // Ser for estatutário
                $botao = new BotaoGrafico();
                $botao->set_label('Cessão');
                $botao->set_url('servidorCessao.php');
                $botao->set_imagem(PASTA_FIGURAS.'cessao.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico de Cessões do Servidor');
                $menu->add_item($botao);
            }elseif($perfil == 2){ // se for cedido
                $botao = new BotaoGrafico();
                $botao->set_label('Cessão');
                $botao->set_url('servidorCessaoCedido.php');
                $botao->set_imagem(PASTA_FIGURAS.'cessao.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Dados da Cessão do Servidor');
                $menu->add_item($botao);
            }

            if($pessoal->get_perfilComissao($perfil) == "Sim"){
                $botao = new BotaoGrafico();
                $botao->set_label('Cargo em Comissão');
                $botao->set_url('servidorComissao.php');
                $botao->set_imagem(PASTA_FIGURAS.'comissao.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico dos Cargos em Comissão do Servidor');
                $menu->add_item($botao);
            }

            if(($perfil == 1) OR ($perfil == 4)){   // Ser for estatutário
                $botao = new BotaoGrafico();
                $botao->set_label('Tempo de Serviço');
                $botao->set_url('servidorAverbacao.php');
                $botao->set_imagem(PASTA_FIGURAS.'historico.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Tempo de Serviço Averbado');
                $menu->add_item($botao);
            }
            
            $botao = new BotaoGrafico();
            $botao->set_label('Readaptação');
            $botao->set_url('servidorReadaptacao.php');
            $botao->set_imagem(PASTA_FIGURAS.'readaptacao.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Controle de Readaptaçao do Servidor');
            #$menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Elogios / Advertências');
            $botao->set_url('servidorElogiosAdvertencias.php');
            $botao->set_imagem(PASTA_FIGURAS.'ocorrencia.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Elogios e Advertências do Servidor');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Observações');
            $botao->set_url('servidorObs.php');
            $botao->set_imagem(PASTA_FIGURAS.'obs.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Observações Gerais do Servidor');
            $menu->add_item($botao);
            
            $botao = new BotaoGrafico();
            $botao->set_label('Pasta Funcional');
            $botao->set_url('?fase=pasta');
            $botao->set_imagem(PASTA_FIGURAS.'arquivo.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Pasta funcional do servidor');
            $menu->add_item($botao);
            
            $menu->show();
            br();

        $grid2->fechaColuna();
        
        #######################################################################
                
        # Pessoais 
        
        $grid2->abreColuna(12,5);        
            titulo('Pessoais');
            br();

            $menu = new MenuGrafico(4);
            $botao = new BotaoGrafico();
            $botao->set_label('Pessoais');
            $botao->set_url('servidorPessoais.php');
            $botao->set_imagem(PASTA_FIGURAS.'pessoais.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Dados Pessoais Gerais do Servidor');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Endereço & Contatos');
            $botao->set_url('servidorEnderecoContatos.php');
            $botao->set_imagem(PASTA_FIGURAS.'bens.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Endereço e Contatos do Servidor');            
            $menu->add_item($botao);
            
            $botao = new BotaoGrafico();
            $botao->set_label('Contatos');
            $botao->set_url('servidorContatos.php');
            $botao->set_imagem(PASTA_FIGURAS.'telefone.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Dados dos Contatos do Servidor');
            #$menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Documentos');
            $botao->set_url('servidorDocumentos.php');
            $botao->set_imagem(PASTA_FIGURAS.'documento.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro da Documentação do Servidor');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Formação');
            $botao->set_url('servidorFormacao.php');
            $botao->set_imagem(PASTA_FIGURAS.'diploma.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Formação Escolar do Servidor');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Dependentes');
            $botao->set_url('servidorDependentes.php');
            $botao->set_imagem(PASTA_FIGURAS.'dependente.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro dos Dependentes do Servidor');
            $menu->add_item($botao);

            $menu->show();
            br();
            
        $grid2->fechaColuna();   
        
        #######################################################################
                
        # Foto 
        
        $grid2->abreColuna(12,2); 
            titulo('Foto do Servidor');
            
            $idPessoa = $pessoal->get_idPessoa($idServidor);

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
           
        $grid2->fechaColuna();
        
        #######################################################################
        
        # Financeiro                                    
        
        $grid2->abreColuna(12,5); 
            titulo('Financeiro');
            br();

            $menu = new MenuGrafico(4);
            if($pessoal->get_perfilProgressao($perfil) == "Sim"){
                $botao = new BotaoGrafico();
                $botao->set_label('Progressão e Enquadramento');
                $botao->set_url('servidorProgressao.php');
                $botao->set_imagem(PASTA_FIGURAS.'salario.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Progressões e Enquadramentos do Servidor');
                $menu->add_item($botao);
            }

            if($pessoal->get_perfilTrienio($perfil) == "Sim"){
                $botao = new BotaoGrafico();
                $botao->set_label('Triênio');
                $botao->set_url('servidorTrienio.php');
                $botao->set_imagem(PASTA_FIGURAS.'trienio.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico de Triênios do Servidor');
                $menu->add_item($botao);
            }

            if($pessoal->get_perfilGratificacao($perfil) == "Sim"){
                $botao = new BotaoGrafico();
                $botao->set_label('Gratificação Especial');
                $botao->set_url('servidorGratificacao.php');
                $botao->set_imagem(PASTA_FIGURAS.'gratificacao.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico das Gratificações Especiais do Servidor'); 
                $menu->add_item($botao);
            }
            
            # Direito Pessoal    
            $botao = new BotaoGrafico();
            $botao->set_label('Direito Pessoal');
            $botao->set_url('servidorDireitoPessoal.php');
            $botao->set_imagem(PASTA_FIGURAS.'abono.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Abono / Direito Pessoal');                
            $menu->add_item($botao);
            
            if ($perfil == 1){   // Ser for estatutário
                # Abono Permanencia    
                $botao = new BotaoGrafico();
                $botao->set_label('Abono Permanencia');
                $botao->set_url('servidorAbono.php');
                $botao->set_imagem(PASTA_FIGURAS.'money.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Abono Permanencia');                
                $menu->add_item($botao);
            }
            
            # Diarias
            $botao = new BotaoGrafico();
            $botao->set_label('Diárias');
            $botao->set_url('servidorDiaria.php');
            $botao->set_imagem(PASTA_FIGURAS.'diaria.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Controle de Diárias');
            $menu->add_item($botao);
            
            # Dados Bancários
            $botao = new BotaoGrafico();
            $botao->set_label('Dados Bancários');
            $botao->set_url('servidorBancario.php');
            $botao->set_imagem(PASTA_FIGURAS.'banco.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro dos dados bancários do Servidor');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Resumo Financeiro');
            $botao->set_url('servidorFinanceiro.php');
            $botao->set_imagem(PASTA_FIGURAS.'lista.png',$tamanhoImage,$tamanhoImage);
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
            br();
        $grid2->fechaColuna();
        
        #######################################################################
                
        # Afastamentos
        
        $grid2->abreColuna(12,5);
        titulo('Afastamentos');
        br();

        $menu = new MenuGrafico(4);
        if($pessoal->get_perfilFerias($perfil) == "Sim"){
            $botao = new BotaoGrafico();
            $botao->set_label('Férias');
            $botao->set_url('servidorFerias.php');
            $botao->set_imagem(PASTA_FIGURAS.'ferias.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro das Férias do Servidor');
            $botao->set_accessKey('i');
            $menu->add_item($botao);
        }

        if($pessoal->get_perfilLicenca($perfil) == "Sim"){
            $botao = new BotaoGrafico();
            $botao->set_label('Licenças e Afastamentos');
            $botao->set_url('servidorLicenca.php');
            $botao->set_imagem(PASTA_FIGURAS.'licenca.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Licenças do Servidor');
            $botao->set_accessKey('L');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label($pessoal->get_licencaNome(6));
            $botao->set_url('servidorLicencaPremio.php');
            $botao->set_imagem(PASTA_FIGURAS.'premio.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Licenças Prêmio do Servidor');
            #$botao->set_accessKey('L');
            $menu->add_item($botao);
        }

        $botao = new BotaoGrafico();
        $botao->set_label('Atestados (Faltas Abonadas)');
        $botao->set_url('servidorAtestado.php');
        $botao->set_imagem(PASTA_FIGURAS.'atestado.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Atestados do Servidor');                
        #$botao->set_accessKey('i');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Faltas');
        $botao->set_url('servidorFaltas.php');
        $botao->set_imagem(PASTA_FIGURAS.'faltas.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Faltas do Servidor');                
        #$botao->set_accessKey('i');
        #$menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('TRE');
        $botao->set_url('servidorTre.php');
        $botao->set_imagem(PASTA_FIGURAS.'tre.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de dias trabalhados no TRE com controle de folgas');                
        #$botao->set_accessKey('i');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Afastamento Anual');
        $botao->set_url('?fase=timeline');
        $botao->set_imagem(PASTA_FIGURAS.'timeline.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Resumo gráfico do tempo de vida funcional do servidor dentro da Universidade');                
        #$botao->set_accessKey('i');
        #$menu->add_item($botao);

        $menu->show();
        
        $grid2->fechaColuna();
        
        #######################################################################
                
        # Relatórios
        
        $grid2->abreColuna(12,2); 
            titulo('Relatórios');
            br();
            
            $cargo = $pessoal->get_idCargo($idServidor);
            
            $menu = new Menu();
            $menu->add_item("linkWindow","Ficha Cadastral","../grhRelatorios/fichaCadastral.php");
            $menu->add_item("linkWindow","Atribuições do Cargo (Mapa)","../grhRelatorios/mapaCargo.php?cargo=$cargo");
            $menu->add_item("linkWindow","FAF","../grhRelatorios/fichaAvaliacaoFuncional.php");
            #$menu->add_item("linkWindow","Capa da Pasta","../grhRelatorios/capaPasta.php");
            $menu->add_item("linkWindow","Folha de Presença","../grhRelatorios/folhaPresenca.php");
            $menu->show();
            
        $grid2->fechaColuna(); 
        
        #######################################################################
                
        # Benefício 
        
        $grid2->abreColuna(12,5);        
            titulo('Benefícios');
            br();

            $menu = new MenuGrafico(4);
            
            $botao = new BotaoGrafico();
            $botao->set_label('Redução da Carga Horária');
            $botao->set_url('servidorReducao.php');
            $botao->set_imagem(PASTA_FIGURAS.'carga-horaria.svg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Controle de Redução da Carga Horária');
            $menu->add_item($botao);
            
            $botao = new BotaoGrafico();
            $botao->set_label('Readaptação');
            #$botao->set_url('servidorReadaptacao.php');
            $botao->set_imagem(PASTA_FIGURAS.'readaptacao.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Controle de Readaptação');
            $menu->add_item($botao);

            $menu->show();
            br();
            
        $grid2->fechaColuna();   
        
        #######################################################################
                
        # Outros 
        
        $grid2->abreColuna(12,5);        
            titulo('Outros');
            br();

            $menu = new MenuGrafico(4);
            
            $botao = new BotaoGrafico();
            $botao->set_label('Recadastramento');
            $botao->set_url('areaRecadastramento.php?fase=editar&origem=menuServidor&id='.$idServidor);
            $botao->set_imagem(PASTA_FIGURAS.'recadastramento.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Recadastramento do Servidor');
            $menu->add_item($botao);

            $menu->show();
            br();
            
        $grid2->fechaColuna();   
        
        #######################################################################
        
        $grid2->fechaGrid();
    }     
    
######################################################################################################################
     
    /**
     * método quadroLicencaPremio
     * Exibe um quadro informativo da licença Prêmio de um servidor
     */
    
     public static function quadroLicencaPremio($idServidor){
         
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
            $tabela = array(array('Dias Publicados',$diasPublicados),
                            array('Dias Fruídos',$diasFruidos),
                            array('Disponíveis',$diasDisponiveis));
            $estatistica = new Tabela();
            $estatistica->set_conteudo($tabela);
            $estatistica->set_label(array("",""));
            $estatistica->set_align(array("center"));
            $estatistica->set_width(array(60,40));
            $estatistica->set_totalRegistro(FALSE);
            $estatistica->show();

        $div->fecha();		

    }	
        
######################################################################################################################
     
    /**
     * método quadroVagasCargoComissao
     * Exibe um quadro informativo das vagas dos Cargos em Comissão
     */
    
     public static function quadroVagasCargoComissao()
    {        
        $select = 'SELECT descricao,
                          simbolo,
                          valsal,
                          vagas,                               
                          idTipoComissao,
                          idTipoComissao
                     FROM tbtipocomissao
                    WHERE ativo
                    ORDER BY 2 asc';        
        
        # Conecta com o banco de dados
        $servidor = new Pessoal();        
        $result = $servidor->select($select);

        # Verifica se tem registros a serem exibidos
        if(count($result) == 0){        
            $p = new P('Nenhum item encontrado !!','center');
            $p->show();
        }else{
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(array("Cargo","Simbolo","Valor (R$)","Vagas","Vagas Ocupadas","Vagas Disponíveis"));
            $tabela->set_width(array(25,15,15,15,15,15));
            $tabela->set_align(array("left"));
            $tabela->set_funcao(array(NULL,NULL,"formataMoeda"));
            $tabela->set_classe(array(NULL,NULL,NULL,NULL,'pessoal','pessoal'));
            $tabela->set_metodo(array(NULL,NULL,NULL,NULL,'get_servidoresCargoComissao','get_cargoComissaoVagasDisponiveis'));
            $tabela->show();
        }
    }	
        
######################################################################################################################
     
    /**
     * método exibeOcorênciaServidor
     * Div que ressalta situação do servidor (licença, férias, etc)
     */
    
    public static function exibeOcorênciaServidor($idServidor){
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
        $cedido = $pessoal->emCessao($idServidor);
        $orgaoCedido = NULL;
            
        # Férias
        if($ferias){
            $exercicio = $pessoal->emFeriasExercicio($idServidor);
            $mensagem[] = 'Servidor em férias (Exercicio '.$exercicio.')';
        }

        # Licenca
        if($licenca){
            $mensagem[] = 'Servidor em '.$pessoal->get_licenca($idServidor);
        }
        
        # Licenca Prêmio
        if($licencaPremio){
            $mensagem[] = 'Servidor em '.$pessoal->get_licencaNome(6);
        }

        # Motivo de Saída
        if(($situacao <> 1) AND ($pessoal->get_motivo($idServidor) <> "Outros")){
            $mensagem[] = $pessoal->get_motivo($idServidor);
        }

        # Folga TRE
        if($folgaTre){
            $mensagem[] = 'Servidor em Folga TRE';
        }

        # Afastamento TRE
        if($afastadoTre){
            $mensagem[] = 'Prestando serviço ao TRE';
        }

        # Cedido
        if($cedido){
            $orgaoCedido = $pessoal->get_orgaoCedido($idServidor);
            $mensagem[] = 'Servidor Cedido a(o) '.$orgaoCedido;
        }
        
        ##### Ocorrências
        
        $metodos = get_class_methods('Checkup');
        $ocorrencia = new Checkup(FALSE);
                
        foreach ($metodos as $nomeMetodo) {
            if (($nomeMetodo <> 'get_all') AND ($nomeMetodo <> '__construct')){
                $texto = $ocorrencia->$nomeMetodo($idServidor);
                
                if(!is_null($texto)){
                    $mensagem[] = $texto;
                }
            }
        }
        
        $qtdMensagem = count($mensagem);
        $contador = 1;
        
        ##### Exibe a mensagem
        if($qtdMensagem > 0){
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Exibe a mensagem
            $callout = new Callout("warning");
            $callout->abre();
            
            # Percorre o array 
            foreach ($mensagem as $mm) {
                p("- ".$mm,"exibeOcorrencia");
                if($contador < $qtdMensagem){
                    $contador++;
                }
            }
            
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
    public static function listaDadosServidor($idServidor)
    {       
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select ='SELECT tbservidor.idFuncional,
                         tbservidor.matricula,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.dtAdmissao,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.dtDemissao
                    FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                       LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                   WHERE idServidor = '.$idServidor;

        $conteudo = $servidor->select($select,TRUE);
        
        # Pega a situação
        $situacao = $servidor->get_situacao($idServidor);
        
        if ($situacao == "Ativo"){
            $label = array("Id","Matrícula","Servidor","Perfil","Cargo","Admissão","Lotação","Situação");
            $function = array(NULL,"dv",NULL,NULL,NULL,"date_to_php");
        }else{
            $label = array("Id","Matrícula","Servidor","Perfil","Cargo","Admissão","Lotação","Situação","Saída");
            $function = array(NULL,"dv",NULL,NULL,NULL,"date_to_php",NULL,NULL,"date_to_php");
        }
        #$align = array("center");
        
        $classe = array(NULL,NULL,NULL,"pessoal","pessoal",NULL,"pessoal","pessoal");
        $metodo = array(NULL,NULL,NULL,"get_Perfil","get_Cargo",NULL,"get_Lotacao","get_Situacao");
        
        $formatacaoCondicional = array( array('coluna' => 0,
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
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);
        
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);
              
        $tabela->show();

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
    public static function listaFolgasTre($idServidor){       
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        $folgasConcedidas = $servidor->get_treFolgasConcedidas($idServidor);
        $folgasFruidas = $servidor->get_treFolgasFruidas($idServidor);
        $folgasPendentes = $folgasConcedidas - $folgasFruidas;

        # Div do numero de folgas
        $div = new Div('divAfastamentoTre');
        $div->abre();

            # Tabela
            $folgas = Array(Array('Folgas Concedidas',$folgasConcedidas),
                            Array('Folgas Fruídas',$folgasFruidas),
                            Array('Folgas Pendentes',$folgasPendentes));						
            #$label = array("Folgas","Dias");
            $label = array("","");
            $width = array(70,30);
            $align = array("left");
            
            
            $tabela = new Tabela("tabelaTre");
            #$estatistica->set_titulo('Legenda'); 
            $tabela->set_conteudo($folgas);
            $tabela->set_cabecalho($label,$width,$align);
            $tabela->set_totalRegistro(FALSE);
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
     * @param string $idServidor NULL idServidor do servidor
     * @param string $titulo     NULL O título do relatório 
     * @param string $cabecalho  TRUE Se exibirá o início do relatório (menu, cabecalho, etc) 
    */
    
    public static function listaDadosServidorRelatorio($idServidor,$titulo = NULL,$cabecalho = TRUE){ 
        
        # Conecta com o banco de dados
        $pessoal = new Pessoal();
        
        # Dados do Servidor
        $select ='SELECT tbservidor.idFuncional,
                         tbpessoa.nome,
                         tbperfil.nome,
                         tbservidor.idServidor,
                         tbservidor.dtAdmissao,
                         tbservidor.idServidor,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                       LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                                       LEFT JOIN tbperfil ON tbservidor.idPerfil = tbperfil.idPerfil
                   WHERE idServidor = '.$idServidor;

        $result = $pessoal->select($select);   

        $relatorio = new Relatorio();
        $relatorio->set_titulo($titulo);
        $relatorio->set_label(array("Id","Servidor","Perfil","Cargo","Admissão","Lotação","Situação"));
        #$relatorio->set_width(array(8,20,10,20,10,20,5));
        $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));        
        $relatorio->set_classe(array(NULL,NULL,NULL,"pessoal",NULL,"pessoal","pessoal"));
        $relatorio->set_metodo(array(NULL,NULL,NULL,"get_Cargo",NULL,"get_Lotacao","get_Situacao"));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(FALSE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_brHr(0);
        $relatorio->set_linhaFinal(TRUE);
        $relatorio->set_log(FALSE);
        
        # Verifica se exibe ou não o início do cabeçalho
        # Utilizado para quando os dados doservidor é a primeira coisa a ser
        # exibida no relatório. Se não for esconde o cabeçalho, menu etc
        if(!$cabecalho){
            $relatorio->set_cabecalhoRelatorio(FALSE);
            $relatorio->set_menuRelatorio(FALSE);
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
    public static function rodape($idUsuario,$idServidor = NULL,$idPessoa = NULL) {
       
        # Exibe faixa azul
        $grid = new Grid();
        $grid->abreColuna(12);        
            titulo();        
        $grid->fechaColuna();
        $grid->fechaGrid();

        # Exibe a versão do sistema
        $intra = new Intra();
        $grid = new Grid();
        $grid->abreColuna(4);
            $texto = 'Usuário: '.$intra->get_usuario($idUsuario);
            
            if(!is_null($idServidor)){
                $texto .= " - Servidor: ".$idServidor;
            }
            
            if(!is_null($idPessoa)){
                $texto .= " - Pessoa: ".$idPessoa;
            }
            
            p($texto,'usuarioLogado');
        $grid->fechaColuna();
        $grid->abreColuna(4);
            p('Versão: '.VERSAO,'versao');
        $grid->fechaColuna();
        $grid->abreColuna(4);
            p(BROWSER_NAME." - ".IP,'ip');
        $grid->fechaColuna();
        $grid->fechaGrid();
    }
    
######################################################################################################################
    
    public static function exibePublicacoesPremio($idServidor){
        
     /**
     * Exibe uma tabela com as publicações de Licença Prêmio de um servidor
     */
        
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(3);
        
        # Pega os dados para o alerta
        $licenca = new LicencaPremio();
        $diasPublicados = $licenca->get_numDiasPublicados($idServidor);
        $diasFruidos = $licenca->get_numDiasFruidos($idServidor);
        $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidor);
        $numProcesso = $licenca->get_numProcesso($idServidor);

            # Tabela de Serviços
            $mesServico = date('m');
            $tabela = array(array('Processo',$numProcesso),
                            array('Dias Publicados',$diasPublicados),
                            array('Dias Fruídos',$diasFruidos),
                            array('Disponíveis',$diasDisponiveis));
            
            $estatistica = new Tabela();
            $estatistica->set_conteudo($tabela);
            $estatistica->set_label(array("Descrição","Valor"));
            $estatistica->set_align(array("center"));
            #$estatistica->set_width(array(60,40));
            $estatistica->set_totalRegistro(FALSE);
            $estatistica->set_titulo("Dados");
            $estatistica->show();
        
        $grid->fechaColuna();
        $grid->abreColuna(9);
                
        # Conecta com o banco de dados
        $pessoal = new Pessoal();
    
        # Exibe as Publicações
        $select = 'SELECT dtPublicacao,
                        dtInicioPeriodo,
                        dtFimPeriodo,
                        numDias,
                        idPublicacaoPremio,
                        idPublicacaoPremio,
                        idPublicacaoPremio
                   FROM tbpublicacaopremio
                   WHERE idServidor = '.$idServidor.'
               ORDER BY dtInicioPeriodo desc';

        $result = $pessoal->select($select);
        $count = $pessoal->count($select);

        # Cabeçalho da tabela
        $titulo = 'Publicações';
        $label = array("Data da Publicação","Período Aquisitivo <br/> Início","Período Aquisitivo <br/> Fim","Dias <br/> Publicados","Dias <br/> Fruídos","Dias <br/> Disponíveis");
        $width = array(15,10,15,15,15,10,10,10);
        $funcao = array('date_to_php','date_to_php','date_to_php');
        $classe = array(NULL,NULL,NULL,NULL,'LicencaPremio','LicencaPremio');
        $metodo = array(NULL,NULL,NULL,NULL,'get_numDiasFruidosPorPublicacao','get_numDiasDisponiveisPorPublicacao');
        $align = array('center');            

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_align($align);
        $tabela->set_label($label);
        #$tabela->set_width($width);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_numeroOrdem(TRUE);
        $tabela->set_numeroOrdemTipo("d");
        
        $tabela->set_formatacaoCondicional(array(array('coluna' => 5,
                                                       'valor' => 0,
                                                       'operador' => '<',
                                                       'id' => 'alerta')));

        $tabela->show();
        
        $grid->fechaColuna();
        $grid->fechaGrid();   
    }

######################################################################################################################
    
    public static function exibeVinculos($idServidor){
        
     /**
     * Exibe Um menu com os vinculos do servidor na uenf
     */
        
        # Conecta com o banco de dados
        $pessoal = new Pessoal();
        
        # Vinculos do servidor
        $numVinculos = $pessoal->get_numVinculos($idServidor);

        # Número de Vinculos
        if($numVinculos > 1){               // So entra se tiver mais de um vinculo
            $grid = new Grid();
            $grid->abreColuna(12);        
             
            titulo("Outros Registros Desse Servidor no Sistema");
            
            $callout = new Callout();
            $callout->abre();

            # Monta o menu
            $menu = new Menu();

            # Exibe os vinculos
            $vinculos = $pessoal->get_vinculos($idServidor);
            
            # Percorre os vínculos
            foreach($vinculos as $rr){
                
                # Descarta o vinculo em tela
                if($rr[0] <> $idServidor){
                    $dtAdm = $pessoal->get_dtAdmissao($rr[0]);
                    $dtSai = $pessoal->get_dtSaida($rr[0]);
                    $perfil = $pessoal->get_perfilSimples($rr[0]);
                    $menu->add_item("link",$perfil."&nbsp;(".$dtAdm." - ".$dtSai.")",'servidor.php?fase=editar&id='.$rr[0]);
                }
            }

            # Exibe o menu
            $menu->show();
            
            $callout->fecha();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
        }
    }

    
    ###########################################################
	
	/**
	 * Método exibe get_numServidoresAtivosTipoCargo
	 * 
	 * Exibe o número de servidores ativos por tipo de cargo e o link para exibí-los
         * Usado na tabela da rotina de cadastro de cargo efetivo
	 */
	
	public function get_numServidoresAtivosTipoCargo($id){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresAtivosTipoCargo($id);
            
            echo $numero;
            echo "&nbsp&nbsp&nbsp";
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=exibeServidoresAtivos&id='.$id);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	###########################################################
	
	/**
	 * Método exibe get_numServidoresAtivosTipoCargo
	 * 
	 * Exibe o número de servidores ativos por tipo de cargo e o link para exibí-los
         * Usado na tabela da rotina de cadastro de cargo efetivo
	 */
	
	public function get_numServidoresInativosTipoCargo($id){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresInativosTipoCargo($id);
            
            echo $numero;
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=exibeServidoresInativos&id='.$id);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	###########################################################
	
	/**
	 * Método exibe get_numServidoresCargoComissao
	 * 
	 * Exibe o número de servidores ativos por de cargo em comissão e o link para exibí-los
         * Usado na tabela da rotina de cadastro de cargo em comissão
	 */
	
	public function get_numServidoresCargoComissao($id){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresCargoComissao($id);
            
            echo $numero;
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=listaServidores&id='.$id);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	###########################################################
	
	/**
	 * Método exibe get_numServidoresAtivosPerfil
	 * 
	 * Exibe o número de servidores ativos por perfil e o link para exibí-los
         * Usado na tabela da rotina de cadastro de perfil
	 */
	
	public function get_numServidoresAtivosPerfil($id){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresAtivosPerfil($id);
            
            echo $numero;
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=listaServidoresAtivos&id='.$id);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	###########################################################
	
	/**
	 * Método exibe get_numServidoresInativosPerfil
	 * 
	 * Exibe o número de servidores inativos por perfil e o link para exibí-los
         * Usado na tabela da rotina de cadastro de perfil
	 */
	
	public function get_numServidoresInativosPerfil($id){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresInativosPerfil($id);
            
            echo $numero;
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=listaServidoresInativos&id='.$id);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	###########################################################
	
	/**
	 * Método exibe get_numServidoresAtivosLotacao
	 * 
	 * Exibe o número de servidores ativos por lotação e o link para exibí-los
         * Usado na tabela da rotina de cadastro de lotação
	 */
	
	public function get_numServidoresAtivosLotacao($idLotacao){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresAtivosLotacao($idLotacao);
            
            echo $numero;
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=listaServidoresAtivos&id='.$idLotacao);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	###########################################################
	
	/**
	 * Método exibe get_numServidoresInativosLotacao
	 * 
	 * Exibe o número de servidores inativos por lotação e o link para exibí-los
         * Usado na tabela da rotina de cadastro de lotação
	 */
	
	public function get_numServidoresInativosLotacao($idLotacao){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresInativosLotacao($idLotacao);
            
            echo $numero;
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=listaServidoresInativos&id='.$idLotacao);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	###########################################################
	
	/**
	 * Método exibe get_numServidoresAtivosConcurso
	 * 
	 * Exibe o número de servidores ativos por concurso e o link para exibí-los
         * Usado na tabela da rotina de cadastro de concurso
	 */
	
	public function get_numServidoresAtivosConcurso($idConcurso){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresAtivosConcurso($idConcurso);
            
            echo $numero;
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=listaServidoresAtivos&id='.$idConcurso);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	###########################################################
	
	/**
	 * Método exibe get_numServidoresInativosConcurso
	 * 
	 * Exibe o número de servidores inativos por concurso e o link para exibí-los
         * Usado na tabela da rotina de cadastro de concurso
	 */
	
	public function get_numServidoresInativosConcurso($idConcurso){
            
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
            
            $numero = $pessoal->get_servidoresInativosConcurso($idConcurso);
            
            echo $numero;
            
            # Botão de exibição dos servidores
            $botao = new Link('','?fase=listaServidoresInativos&id='.$idConcurso);
            $botao->set_id('aServidorTipoCargo');
            $botao->set_imagem(PASTA_FIGURAS_GERAIS.'ver.png',20,20);
            $botao->show();
            
	}

	######################################################################################################################

    public static function menuServidor2($idServidor,$idUsuario){
            
    /**
     * método menuServidor
     * 
     * Exibe o menu do servidor - o que aparece quando se seleciona um servidor 
     */
        
        # Conecta com o banco de dados
        $pessoal = new Pessoal();
        
        # Pega o perfil desse servidor
        $perfil = $pessoal->get_idPerfil($idServidor); 
        
        $grid1 = new Grid();
        $grid1->abreColuna(10);
        
        ###################################
        
        $grid = new Grid();
        $grid->abreColuna(12,6,4);

        # Define o tipo do callout
        $tipo = "secondary";

        # Funcionais
        $painel = new Callout($tipo);
        $painel->abre();

        $div = new Div("ajustaMenuServidor");
        $div->abre();

            $divFigura = new Div("figuraMenuServidor");
            $divFigura->abre();

            $figura = new Imagem(PASTA_FIGURAS.'funcional.jpg','Dados Funcionais',40,40);
            $figura->show();

            $divFigura->fecha();

            $menu = new Menu("menuServidor");
            $menu->add_item("titulo","Funcionais");
            $menu->add_item("link","Funcionais","servidorFuncionais.php",'Dados Funcionais do Servidor');
            #$menu->add_item("link","Admissão","servidorFuncionais.php");
            #$menu->add_item("link","Saída","servidorFuncionais.php");
            $menu->add_item("link","Lotação","servidorLotacao.php",'Histórico da Lotação do Servidor');

            if(($perfil == 1) OR ($perfil == 4)){   // Ser for estatutário
                $menu->add_item("link","Cessão","servidorCessao.php",'Histórico de Cessões do Servidor');
            }elseif($perfil == 2){ // se for cedido
                $menu->add_item("link","Cessão","servidorCessaoCedido.php",'Dados da Cessão do Servidor');
            }

            if($pessoal->get_perfilComissao($perfil) == "Sim"){                
                $menu->add_item("link","Cargo em Comissão","servidorComissao.php",'Histórico dos Cargos em Comissão do Servidor');
            }

            if(($perfil == 1) OR ($perfil == 4)){   // Ser for estatutário
                $menu->add_item("link","Tempo de Serviço","servidorAverbacao.php",'Cadastro de Tempo de Serviço Averbado');
            }

            $menu->add_item("link","Elogios & Advertências","servidorElogiosAdvertencias.php",'Cadastro de Elogios e Advertências do Servidor');
            $menu->add_item("link","Observações","servidorObs.php",'Observações Gerais do Servidor');
            $menu->add_item("link","Pasta Funcional","?fase=pasta",'Pasta funcional do servidor');
            $menu->add_item("link","Recadastramento",'areaRecadastramento.php?fase=editar&origem=menuServidor&id='.$idServidor,'Recadastramento do servidor');

            $menu->show();                

        $div->fecha();

        $painel->fecha();
        $grid->fechaColuna();

        ###################################

        $grid->abreColuna(12,6,4);

        # Pessoais
        $painel = new Callout($tipo);
        $painel->abre();

        $div = new Div("ajustaMenuServidor");
        $div->abre();

            $divFigura = new Div("figuraMenuServidor");
            $divFigura->abre();

            $figura = new Imagem(PASTA_FIGURAS.'pessoais.jpg','Dados Pessoais',40,40);
            $figura->show();

            $divFigura->fecha();

            $menu = new Menu("menuServidor");
            $menu->add_item("titulo","Pessoais");
            $menu->add_item("link","Pessoais","servidorPessoais.php","Dados Pessoais Gerais do Servidor");
            $menu->add_item("link","Endereço & Contatos","servidorEnderecoContatos.php","Endereço e Contatos do Servidor");
            #$menu->add_item("link","Contatos","servidorContatos.php","Dados dos Contatos do Servidor");
            $menu->add_item("link","Documentos","servidorDocumentos.php","Cadastro da Documentação do Servidor");
            $menu->add_item("link","Formação","servidorFormacao.php",'Cadastro de Formação Escolar do Servidor');
            $menu->add_item("link","Dependentes","servidorDependentes.php",'Cadastro dos Dependentes do Servidor');
            $menu->show();                

        $div->fecha();    

        $painel->fecha();
        $grid->fechaColuna();

        ###################################

        # Afastamentos
        $grid->abreColuna(12,6,4);
        $painel = new Callout($tipo);
        $painel->abre();

        $div = new Div("ajustaMenuServidor");
        $div->abre();

            $divFigura = new Div("figuraMenuServidor");
            $divFigura->abre();

            $figura = new Imagem(PASTA_FIGURAS.'ferias.jpg','Dados dos Afastamentos',40,40);
            $figura->show();

            $divFigura->fecha();

            $menu = new Menu("menuServidor");
            $menu->add_item("titulo","Afastamentos");

            if($pessoal->get_perfilFerias($perfil) == "Sim"){
                $menu->add_item("link","Férias","servidorFerias.php","Cadastro das Férias do Servidor");
            }

            if($pessoal->get_perfilLicenca($perfil) == "Sim"){
                $menu->add_item("link","Licenças e Afastamentos","servidorLicenca.php","Cadastro de Licenças do Servidor");
                $menu->add_item("link",$pessoal->get_licencaNome(6),"servidorLicencaPremio.php","Cadastro de Licenças Prêmio do Servidor");
            }

            $menu->add_item("link","Atestados (Faltas Abonadas)","servidorAtestado.php","Cadastro de Atestados do Servidor");
            $menu->add_item("link","Faltas","servidorFaltas.php","Cadastro de Faltas do Servidor");
            $menu->add_item("link","TRE","servidorTre.php","Cadastro de dias trabalhados no TRE com controle de folgas");
            $menu->add_item("link","Férias","servidorFerias.php","Cadastro das Férias do Servidor");
            $menu->show();                

        $div->fecha();

        $painel->fecha();
        $grid->fechaColuna();

        ###################################

        # Financeiro
        $grid->abreColuna(12,6,4);
        $painel = new Callout($tipo);
        $painel->abre();

        $div = new Div("ajustaMenuServidor");
        $div->abre();

            $divFigura = new Div("figuraMenuServidor");
            $divFigura->abre();

            $figura = new Imagem(PASTA_FIGURAS.'salario.jpg','Dados Financeiros',40,40);
            $figura->show();

            $divFigura->fecha();

            $menu = new Menu("menuServidor");
            $menu->add_item("titulo","Financeiro");
            if($pessoal->get_perfilProgressao($perfil) == "Sim"){
                $menu->add_item("link","Progressão e Enquadramento","servidorProgressao.php","Cadastro de Progressões e Enquadramentos do Servidor");
            }

            if($pessoal->get_perfilTrienio($perfil) == "Sim"){
                $menu->add_item("link","Triênio","servidorTrienio.php","Histórico das Gratificações Especiais do Servidor");
            }

            if($pessoal->get_perfilGratificacao($perfil) == "Sim"){
                $menu->add_item("link","Gratificação Especial","servidorGratificacao.php","Histórico das Gratificações Especiais do Servidor");
            }

            $menu->add_item("link","Direito Pessoal","servidorDireitoPessoal.php","Cadastro de Abono / Direito Pessoal");

            if ($perfil == 1){   // Ser for estatutário
                $menu->add_item("link","Abono Permanencia","servidorAbono.php","Cadastro de Abono Permanencia");
            }


            $menu->add_item("link","Diárias","servidorDiaria.php","Controle de Diárias");
            $menu->add_item("link","Dados Bancários","servidorBancario.php","Cadastro dos dados bancários do Servidor");
            $menu->add_item("link","Resumo Financeiro","servidorFinanceiro.php","Informações sobre os valores recebidos pelo servidor");
            $menu->show(); 

        $div->fecha();    

        $painel->fecha();
        $grid->fechaColuna();

        ###################################

        # Benefícios
        $grid->abreColuna(12,6,4);
        $painel = new Callout($tipo);
        $painel->abre();

        $div = new Div("ajustaMenuServidor");
        $div->abre();

            $divFigura = new Div("figuraMenuServidor");
            $divFigura->abre();

            $figura = new Imagem(PASTA_FIGURAS.'readaptacao.png','Dados dos Benefícios',40,40);
            $figura->show();

            $divFigura->fecha();

            $menu = new Menu("menuServidor");
            $menu->add_item("titulo","Benefícios");                
            $menu->add_item("link","Redução da Carga Horária","servidorReducao.php","Controle de Redução da Carga Horária");
            $menu->add_item("link","Readaptação","#","Controle de Readaptação");
            $menu->show();                

        $div->fecha();    
        $painel->fecha();
        $grid->fechaColuna();

        ###################################

        # Relatórios
        $grid->abreColuna(12,6,4);
        $painel = new Callout($tipo);
        $painel->abre();

        $div = new Div("ajustaMenuServidor");
        $div->abre();

            $divFigura = new Div("figuraMenuServidor");
            $divFigura->abre();

            $figura = new Imagem(PASTA_FIGURAS.'print.png','Relatórios',40,40);
            $figura->show();

            $divFigura->fecha();

            $cargo = $pessoal->get_idCargo($idServidor);
            
            $menu = new Menu("menuServidor");
            $menu->add_item("titulo","Relatórios");    
            $menu->add_item("linkWindow","Ficha Cadastral","../grhRelatorios/fichaCadastral.php");
            $menu->add_item("linkWindow","Atribuições do Cargo (Mapa)","../grhRelatorios/mapaCargo.php?cargo=$cargo");
            $menu->add_item("linkWindow","FAF","../grhRelatorios/fichaAvaliacaoFuncional.php");
            #$menu->add_item("linkWindow","Capa da Pasta","../grhRelatorios/capaPasta.php");
            $menu->add_item("linkWindow","Folha de Presença","../grhRelatorios/folhaPresenca.php");
            $menu->show();                

        $div->fecha();    
        $painel->fecha();
        $grid->fechaColuna();
        $grid->fechaGrid();

        ###################################
        
        $grid1->fechaColuna();        
        
        # Foto 
        $grid1->abreColuna(2); 
        
        $painel = new Callout($tipo);
        $painel->abre("center");

            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Define a pasta
            $arquivo = "../../_fotos/$idPessoa.jpg";

            # Verifica se tem pasta desse servidor
            if(file_exists($arquivo)){
                br();
                
                $botao = new BotaoGrafico();
                $botao->set_url('?fase=exibeFoto');
                $botao->set_imagem($arquivo,'Foto do Servidor',130,85);
                $botao->set_title('Foto do Servidor');
                $botao->show();
            }else{                
                $foto = new Imagem(PASTA_FIGURAS.'foto.png','Foto do Servidor',150,100);
                $foto->set_id('foto2');
                $foto->show();
            }
            
            $div = new Div("center");
            $div->abre();
            
            $link = new Link("Alterar Foto","?fase=uploadFoto");
            $link->set_id("alteraFoto");
            $link->show();

        $painel->fecha();
        
        $grid1->fechaColuna();
        $grid1->fechaGrid();
        
    }     
    
######################################################################################################################
    
}