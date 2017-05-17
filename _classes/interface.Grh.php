<?php

class Grh
{
    /**
     * Encapsula as rotivas de interface do sistema de pessoal
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    
    /**
     * Método cabecalho
     * 
     * Exibe o cabecalho
     */     
    public static function cabecalho($titulo = NULL)
    {        
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
    

###########################################################
    
    public static function menu($idUsuario){

    /**
     * Exibe o menu inicial do sistema
     * 
     * @var private $matriculaUsuário string NULL Informa a matrícula do servidor logado para exibir somente os links que o servidor tem permissão 
     */
        
        ##########################################################
            
        # Cadastro de Servidores 
        $grid = new Grid();
        $grid->abreColuna(12,4,3);
            
        titulo('Servidores');
        br();
        
        $tamanhoImage = 180;
        $menu = new MenuGrafico(1);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Servidores');
        $botao->set_url('servidor.php');
        $botao->set_image(PASTA_FIGURAS.'servidores.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Servidores');
        $botao->set_accesskey('S');
        $menu->add_item($botao);

        $menu->show();
        br(); 
        $grid->fechaColuna();
        
        ##########################################################
            
        # Tabelas Auxiliares 
        $grid->abreColuna(12,8,5);        
           
        titulo('Tabelas Auxiliares');           
        br();

        $tamanhoImage = 60;
        if(Verifica::acesso($idUsuario,1)){
            $menu = new MenuGrafico(5);
        }else{
            $menu = new MenuGrafico(4);
        }
        
        $botao = new BotaoGrafico();
        $botao->set_label('Perfil');
        $botao->set_url('cadastroPerfil.php');
        $botao->set_image(PASTA_FIGURAS.'usuarios.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Perfil');
        $botao->set_accesskey('P');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Lotação');
        $botao->set_url('cadastroLotacao.php');
        $botao->set_image(PASTA_FIGURAS.'lotacao.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Lotação');
        $botao->set_accesskey('L');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Cargo e Função');
        $botao->set_url('cadastroCargo.php');
        $botao->set_image(PASTA_FIGURAS.'cracha.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Funções');
        $botao->set_accesskey('C');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Cargo em Comissão');
        $botao->set_url('cadastroCargoComissao.php');
        $botao->set_image(PASTA_FIGURAS.'usuarios.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Cargos em Comissão');
        $botao->set_accesskey('g');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Concurso');
        $botao->set_url('cadastroConcurso.php');
        $botao->set_image(PASTA_FIGURAS.'concurso.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Concursos');
        $botao->set_accesskey('o');
        $menu->add_item($botao);   

        $botao = new BotaoGrafico();
        $botao->set_label('Licenças e Afastamentos');
        $botao->set_url('cadastroLicenca.php');
        $botao->set_image(PASTA_FIGURAS.'nene.gif',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Tipos de Licenças');
        #$botao->set_accesskey('T');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('PDV');
        $botao->set_url('cadastroPlanoCargos.php');
        $botao->set_image(PASTA_FIGURAS.'plano.gif',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Plano de Cargos e Vencimentos');
        $botao->set_accesskey('D');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Tabela Salarial');
        $botao->set_url('cadastroTabelaSalarial.php');
        $botao->set_image(PASTA_FIGURAS.'dinheiro.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Tipos de Licenças');
        $botao->set_accesskey('b');
        $menu->add_item($botao);
        
        $botao = new BotaoGrafico();
        $botao->set_label('Férias');
        $botao->set_url('areaferias.php');
        $botao->set_image(PASTA_FIGURAS.'ferias.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Área de Férias');
        $botao->set_accesskey('F');
        $menu->add_item($botao);

        $menu->show();
        $grid->fechaColuna();
		       
        ###############################################################################################
            
        # Alertas
        $grid->abreColuna(12,6,4);

        $divAlertas = new Div("divAlertas");
        $divAlertas->abre();            
            titulo('Alertas');
            br(2);
            aguarde();
        $divAlertas->fecha();
        
        $grid->fechaColuna();
        br();        
        ##########################################################
            
        # Legislação
        #$grid = new Grid();
        $grid->abreColuna(12,6,4);

            titulo('Legislação');
            br();
            $menu = new Menu();
            $menu->add_item('linkWindow','Estatuto dos Servidores',"http://alerjln1.alerj.rj.gov.br/decest.nsf/968d5212a901f75f0325654c00612d5c/2caa8a7c2265c33b0325698a0068e8fb?OpenDocument#_Section1","Decreto nº 2479 de 08 de Março de 1979");
            $menu->add_item('linkWindow','Plano de Cargos e Vencimentos',"http://alerjln1.alerj.rj.gov.br/contlei.nsf/b24a2da5a077847c032564f4005d4bf2/aa5390d4c58db774832571b60066a2ba?OpenDocument","LEI Nº 4.800 de 29 de Junho de 2006");
            $menu->add_item('linkWindow','Resoluções da Reitoria',"http://uenf.br/reitoria/legislacao/resolucoes/");
            $menu->add_item('linkWindow','Portarias',"http://uenf.br/reitoria/legislacao/portarias/");     
            $menu->add_item('linkWindow','Estatuto da UENF',"http://www.uenf.br/Uenf/Downloads/REITORIA_1360_1101117875.pdf");            
            $menu->show();
        
        $grid->fechaColuna();        
        
        ##########################################################
        
        # links externos
        $grid->abreColuna(12,12,8);
            titulo('Links Externos');
            br();
            
            $menu = new MenuGrafico(3);
            $largura = 120;
            $altura = 50;

            $botao = new BotaoGrafico();
            #$botao->set_label(SISTEMA_GRH);
            $botao->set_title('Portal do Sistema Integrado de Gestao de Recursos Humanos do Estado do Rio de Janeiro');
            $botao->set_image(PASTA_FIGURAS."sigrh.png",$largura,$altura);      
            $botao->set_url("http://www.entradasigrhn.rj.gov.br/");
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label("");
            $botao->set_image(PASTA_FIGURAS."do.png",$largura,$altura);  
            $botao->set_url("http://www.imprensaoficial.rj.gov.br/portal/modules/profile/user.php?xoops_redirect=/portal/modules/content/index.php?id=21");
            $botao->set_title("Imprensa Oficial do Estado do Rio de Janeiro");
            $menu->add_item($botao);
            
            $botao = new BotaoGrafico();
            #$botao->set_label(SISTEMA_GRH);
            $botao->set_title('Portal do Processo Digital');
            $botao->set_image(PASTA_FIGURAS."processoDigital.png",$largura,$altura);     
            $botao->set_url("https://www.processodigital.rj.gov.br/");
            $menu->add_item($botao);
            
            $botao = new BotaoGrafico();
            #$botao->set_label(SISTEMA_GRH);
            $botao->set_title('Site da UENF');
            $botao->set_image(PASTA_FIGURAS."uenf.jpg",$largura,$altura);       
            $botao->set_url("http://www.uenf.br/portal/index.php/br/");
            $menu->add_item($botao);
            
            $botao = new BotaoGrafico();
            #$botao->set_label(SISTEMA_GRH);
            $botao->set_title('Site da GRH');
            $botao->set_image(PASTA_FIGURAS."GRH.png",$largura,$altura);  
            $botao->set_url("http://uenf.br/dga/grh/");
            $menu->add_item($botao);

            $menu->show();
        
        $grid->fechaColuna();
        
        ##########################################################
        
        # Tabelas Secundárias
        if(Verifica::acesso($idUsuario,1)){
            $grid->abreColuna(12,12);            

                $tamanhoImage = 50;
                titulo('Tabelas Secundárias'); 
                br();

                $menu = new MenuGrafico(9);   

                $botao = new BotaoGrafico();
                $botao->set_label('Banco');
                $botao->set_url("cadastroBanco.php");
                #$botao->set_onClick("abreDivId('divMensagemAguarde'); fechaDivId('divMenu'); window.location='banco.php'");
                $botao->set_image(PASTA_FIGURAS.'banco.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Bancos');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Escolaridade');
                $botao->set_url("cadastroEscolaridade.php");
                $botao->set_image(PASTA_FIGURAS.'diploma.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Escolaridades');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Estado Civil');
                $botao->set_url("cadastroEstadoCivil.php");
                $botao->set_image(PASTA_FIGURAS.'licenca.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Estado Civil');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Parentesco');
                $botao->set_url("cadastroParentesco.php");
                $botao->set_image(PASTA_FIGURAS.'parentesco.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Parentesco');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Situação');
                $botao->set_url("cadastroSituacao.php");
                $botao->set_image(PASTA_FIGURAS.'usuarios.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Situação');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Motivos de Saída');
                $botao->set_url("cadastroMotivo.php");
                $botao->set_image(PASTA_FIGURAS.'saida.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Motivos de Saída do Servidor da Instituição');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Tipos de Progressão');
                $botao->set_url("cadastroProgressao.php");
                $botao->set_image(PASTA_FIGURAS.'dinheiro.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Tipos de Progressões');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('Nacionalidade');
                $botao->set_url("cadastroNacionalidade.php");
                $botao->set_image(PASTA_FIGURAS.'nacionalidade.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Nacionalidades');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $botao = new BotaoGrafico();
                $botao->set_label('País');
                $botao->set_url("cadastroPais.php");
                $botao->set_image(PASTA_FIGURAS.'pais.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Paises');
                #$botao->set_accesskey('S');
                $menu->add_item($botao);

                $menu->show();

            $grid->fechaColuna();
        }
        $grid->fechaGrid();
        br();
    }
        
##########################################################

    public static function menuServidor($idServidor){
            
    /**
     * método menuServidor
     * 
     * Exibe o menu do servidor - o que aparece quando se seleciona um servidor 
     */
        
        # Divide a tela        
        $grid2 = new Grid();        
        
        # Funcionais 
        $grid2->abreColuna(12,6);        
            titulo('Funcionais');
            br();     
            $tamanhoImage = 50;
            
            $menu = new MenuGrafico(3);
            $botao = new BotaoGrafico();
            $botao->set_label('Funcionais');
            $botao->set_url('servidorFuncionais.php');
            $botao->set_image(PASTA_FIGURAS.'funcional.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Dados Funcionais do Servidor');
            $botao->set_accessKey('F');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Lotação');
            $botao->set_url('servidorLotacao.php');
            $botao->set_image(PASTA_FIGURAS.'lotacao.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Histórico da Lotação do Servidor');
            $botao->set_accessKey('o');
            $menu->add_item($botao);

            $pessoa = new Pessoal();
            $perfil = $pessoa->get_idPerfil($idServidor);

            if ($perfil == 1)   // Ser for estatutário
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Cessão');
                $botao->set_url('servidorCessao.php');
                $botao->set_image(PASTA_FIGURAS.'cessao.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico de Cessões do Servidor');
                $botao->set_accessKey('C');
                $menu->add_item($botao);
            }
            elseif($perfil == 2) // se for cedido
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Cessão');
                $botao->set_url('servidorCessaoCedido.php');
                $botao->set_image(PASTA_FIGURAS.'cessao.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Dados da Cessão do Servidor');
                $botao->set_accessKey('C');
                $menu->add_item($botao);
            }

            if($pessoa->get_perfilComissao($perfil) == "Sim")
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Cargo em Comissão');
                $botao->set_url('servidorComissao.php');
                $botao->set_image(PASTA_FIGURAS.'comissao.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico dos Cargos em Comissão do Servidor');                
                $botao->set_accessKey('a');
                $menu->add_item($botao);
            }

            if ($perfil == 1)   // Ser for estatutário
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Tempo de Serviço Averbado');
                $botao->set_url('servidorAverbacao.php');
                $botao->set_image(PASTA_FIGURAS.'historico.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Tempo de Serviço Averbado');                
                $botao->set_accessKey('T');
                $menu->add_item($botao);
            }

            $botao = new BotaoGrafico();
            $botao->set_label('Elogios / Advertências');
            $botao->set_url('servidorElogiosAdvertencias.php');
            $botao->set_image(PASTA_FIGURAS.'ocorrencia.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Elogios e Advertências do Servidor');                
            $botao->set_accessKey('E');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Observações');
            $botao->set_url('servidorObs.php');
            $botao->set_image(PASTA_FIGURAS.'obs.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Observações Gerais do Servidor');                
            $botao->set_accessKey('b');
            $menu->add_item($botao);
            
            $botao = new BotaoGrafico();
            $botao->set_label('Arquivo');
            $botao->set_url('servidorArquivo.php');
            $botao->set_image(PASTA_FIGURAS.'arquivo.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Posição Física das Pastas Funcionais');                
            #$botao->set_accessKey('b');
            #$menu->add_item($botao);
            
            $menu->show();
            br();

        $grid2->fechaColuna();
        # Pessoais 
        $grid2->abreColuna(12,6);        
            titulo('Pessoais');
            br();

            $menu = new MenuGrafico(3);
            $botao = new BotaoGrafico();
            $botao->set_label('Pessoais');
            $botao->set_url('servidorPessoais.php');
            $botao->set_image(PASTA_FIGURAS.'pessoais.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Dados Pessoais do Servidor');
            $botao->set_accessKey('P');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Telefones & Emails');
            $botao->set_url('servidorTelefones.php');
            $botao->set_image(PASTA_FIGURAS.'telefone.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Dados dos Contatos do Servidor');
            $botao->set_accessKey('n');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Documentos');
            $botao->set_url('servidorDocumentos.php');
            $botao->set_image(PASTA_FIGURAS.'documento.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro da Documentação do Servidor');
            $botao->set_accessKey('D');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Formação');
            $botao->set_url('servidorFormacao.php');
            $botao->set_image(PASTA_FIGURAS.'diploma.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Formação Escolar do Servidor');
            $botao->set_accessKey('m');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Dependentes');
            $botao->set_url('servidorDependentes.php');
            $botao->set_image(PASTA_FIGURAS.'dependente.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro dos Dependentes do Servidor');
            $botao->set_accessKey('s');
            $menu->add_item($botao);

            $menu->show();
            br();
            
        $grid2->fechaColuna();
        
        # Ocorrências
        $grid2->abreColuna(12,6);
            titulo('Afastamentos');
            br();

            $menu = new MenuGrafico(3);
            if($pessoa->get_perfilFerias($perfil) == "Sim")
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Férias');
                $botao->set_url('servidorFerias.php');
                $botao->set_image(PASTA_FIGURAS.'ferias.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro das Férias do Servidor');
                $botao->set_accessKey('i');
                $menu->add_item($botao);
            }

            if($pessoa->get_perfilLicenca($perfil) == "Sim")
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Licenças e Afastamentos');
                $botao->set_url('servidorLicenca.php');
                $botao->set_image(PASTA_FIGURAS.'licenca.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Licenças do Servidor');
                $botao->set_accessKey('L');
                $menu->add_item($botao);
            }

            $botao = new BotaoGrafico();
            $botao->set_label('Atestados (Faltas Abonadas)');
            $botao->set_url('servidorAtestado.php');
            $botao->set_image(PASTA_FIGURAS.'atestado.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Atestados do Servidor');                
            #$botao->set_accessKey('i');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Faltas');
            $botao->set_url('servidorFaltas.php');
            $botao->set_image(PASTA_FIGURAS.'faltas.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de Faltas do Servidor');                
            #$botao->set_accessKey('i');
            #$menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('TRE - Afastamentos');
            $botao->set_url('servidorAfastamentoTre.php');
            $botao->set_image(PASTA_FIGURAS.'tre.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de dias afastados do trabalho para prestar serviços ao TRE');                
            #$botao->set_accessKey('i');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('TRE - Folgas Recebidas');
            $botao->set_url('servidorFolga.php');
            $botao->set_image(PASTA_FIGURAS.'tre.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro de folgas recebidas por ter trabalhado no TRE');                
            #$botao->set_accessKey('i');
            $menu->add_item($botao);
            
            $menu->show();
        
        $grid2->fechaColuna();

        # Financeiro                                    
        $grid2->abreColuna(12,6);
            titulo('Financeiro');
            br();

            $menu = new MenuGrafico(3);
            if($pessoa->get_perfilProgressao($perfil) == "Sim")
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Progressão e Enquadramento');
                $botao->set_url('servidorProgressao.php');
                $botao->set_image(PASTA_FIGURAS.'salario.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Progressões e Enquadramentos do Servidor');                
                $botao->set_accessKey('q');
                $menu->add_item($botao);
            }

            if($pessoa->get_perfilTrienio($perfil) == "Sim")
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Triênio');
                $botao->set_url('servidorTrienio.php');
                $botao->set_image(PASTA_FIGURAS.'trienio.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico de Triênios do Servidor');                
                #$botao->set_accessKey('i');
                $menu->add_item($botao);
            }

            if($pessoa->get_perfilGratificacao($perfil) == "Sim")
            {
                $botao = new BotaoGrafico();
                $botao->set_label('Gratificação Especial');
                $botao->set_url('servidorGratificacao.php');
                $botao->set_image(PASTA_FIGURAS.'gratificacao.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico das Gratificações Especiais do Servidor');                
                $botao->set_accessKey('G');
                $menu->add_item($botao);
            }
            
            # Diarias
            $botao = new BotaoGrafico();
            $botao->set_label('Diárias');
            $botao->set_url('servidorDiaria.php');
            $botao->set_image(PASTA_FIGURAS.'diaria.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Controle de Diárias');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Dados Bancários');
            $botao->set_url('servidorBancario.php');
            $botao->set_image(PASTA_FIGURAS.'banco.jpg',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Cadastro dos dados bancários do Servidor');                
            #$botao->set_accessKey('i');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Resumo Financeiro');
            $botao->set_url('servidorFinanceiro.php');
            $botao->set_image(PASTA_FIGURAS.'money.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Informações sobre os valores recebidos pelo servidor');                
            #$botao->set_onClick("abreFechaDiv('divResumo');");
            $menu->add_item($botao);

            # Declaração de Bens e Valores
            #$botao = new BotaoGrafico();
            #$botao->set_label('DBV - Declaração de Bens e Valores');
            #$botao->set_url('servidorDbvControle.php');
            #$botao->set_image(PASTA_FIGURAS.'bens.png',$tamanhoImage,$tamanhoImage);
            #$botao->set_title('DBV - Declaração de Bens e Valores');
            #$menu->add_item($botao);

            $menu->show();
            br();
        $grid2->fechaColuna();
        $grid2->fechaGrid();
     }     
    
    ###########################################################
     
    /**
     * método quadroLicencaPremio
     * Exibe um quadro informativo da licença Prêmio de um servidor
     */
    
     public static function quadroLicencaPremio($idServidor)
    {
        $servidor = new Pessoal();
        $diasPublicados = $servidor->get_licencaPremioNumDiasPublicadaPorMatricula($idServidor);
        $diasFruidos = $servidor->get_licencaPremioNumDiasFruidos($idServidor);
        $diasDisponiveis = $diasPublicados - $diasFruidos;        
        
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
        
    ###########################################################
     
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
        if(count($result) == 0)
        {        
            $p = new P('Nenhum item encontrado !!','center');
            $p->show();
        }
        else
        {
            # Monta a tabela
            $tabela = new tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(array("Cargo","Simbolo","Valor (R$)","Vagas","Vagas Ocupadas","Vagas Disponíveis"));
            #$tabela->set_width(array(30,20,15,15,10,10));
            $tabela->set_align(array("left"));
            $tabela->set_funcao(array(NULL,NULL,"formataMoeda"));
            $tabela->set_classe(array(NULL,NULL,NULL,'pessoal','pessoal'));
            $tabela->set_metodo(array(NULL,NULL,NULL,'get_servidoresCargoComissao','get_cargoComissaoVagasDisponiveis'));
            $tabela->show();
        }
    }	
        
    ###########################################################
     
    /**
     * método exibeOcorênciaServidor
     * Div que ressalta situação do servidor (licença, férias, etc)
     */
    
    public static function exibeOcorênciaServidor($idservidor)
    {
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Div que ressalta situação do servidor (licença, férias, etc)
        $ferias = $pessoal->emFerias($idservidor);
        $licenca = $pessoal->emLicenca($idservidor);
        $situacao = $pessoal->get_idSituacao($idservidor);
        $folgaTre = $pessoal->emFolgaTre($idservidor);
        $afastadoTre = $pessoal->emAfastamentoTre($idservidor);
        
        if(($ferias) OR ($licenca) OR ($afastadoTre) OR ($folgaTre) OR ($situacao <> 1)){
            
            # Férias
            if($ferias){
                $mensagem = 'Servidor em férias';
            }

            # Licenca
            if($licenca){
                $mensagem = 'Servidor em licença '.$pessoal->get_licenca($idservidor);
            }
            
            # Situação
            if($situacao <> 1){
                $mensagem = $pessoal->get_motivo($idservidor);
            }
            
            # Folga TRE
            if($folgaTre){
                $mensagem = 'Folga TRE';
            }
            
            # Afastamento TRE
            if($afastadoTre){
                $mensagem = 'Prestando serviço ao TRE';
            }
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Exibe a mensagem
            $callout = new Callout('warning');
            $callout->abre();
                p($mensagem);
            $callout->fecha();
            
            $grid->fechaColuna();
            $grid->fechaGrid();  
        } 

        # Verifica pendencia de motorista com carteira vencida no sistema grh
        $perfil = $pessoal->get_perfil($idservidor);
        $cargo = $pessoal->get_cargo($idservidor);
        $idPessoa = $pessoal->get_idPessoa($idservidor);
        $dataCarteira = $pessoal->get_dataVencimentoCarteiraMotorista($idPessoa);

        # Se é motorista estatutário
        if($perfil == 'Estatutário'){
            if($cargo == 'Motorista'){
                if(jaPassou($dataCarteira)){
                    $mensagem = 'Motorista com Carteira de Habilitação Vencida !! ('.$dataCarteira.')';
                    
                    $grid = new Grid();
                    $grid->abreColuna(12);

                    # Exibe a mensagem
                    $callout = new Callout('warning');
                    $callout->abre();
                        p($mensagem);
                    $callout->fecha();

                    $grid->fechaColuna();
                    $grid->fechaGrid();  
                }
            }
        }
    }
    
    ###########################################################
    
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
                         tbperfil.nome,
                         tbservidor.idServidor,
                         tbservidor.dtAdmissao,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.dtDemissao
                    FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                       LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                                       LEFT JOIN tbperfil ON tbservidor.idPerfil = tbperfil.idPerfil
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
        
        $classe = array(NULL,NULL,NULL,NULL,"pessoal",NULL,"pessoal","pessoal");
        $metodo = array(NULL,NULL,NULL,NULL,"get_Cargo",NULL,"get_Lotacao","get_Situacao");
        
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

    ###########################################################
    
    /**
    * método listaFolgasTre
    * Exibe os dados de Folgas do TRE
    * 
    * @param    string $matricula -> matricula do servidor
    */
    public static function listaFolgasTre($matricula)
    {       
        # Conecta com o banco de dados
        $servidor = new Pessoal();
        
        $folgasConcedidas = $servidor->get_folgasConcedidas($matricula);
        $folgasFruidas = $servidor->get_folgasFruidas($matricula);
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
    
    ###########################################################
    
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
        
    ##########################################################
    
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
            if(HTML5){
                p('Versão: '.VERSAO.' (HTML5)','versao');
            }else{
                p('Versão: '.VERSAO,'versao');
            }
        $grid->fechaColuna();
        $grid->abreColuna(4);
            p(BROWSER_NAME." - ".IP,'ip');
        $grid->fechaColuna();
        $grid->fechaGrid();
    }
}