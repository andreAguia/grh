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
    
    public static function menu($matriculaUsuário){

    /**
     * Exibe o menu inicial do sistema
     * 
     * @var private $matriculaUsuário string NULL Informa a matrícula do servidor logado para exibir somente os links que o servidor tem permissão 
     */
        
        ##########################################################
            
        # Cadastro de Servidores 
        $grid = new Grid();
        $grid->abreColuna(3);
            
        titulo('Servidores');
        br(2);

        $menu = new MenuGrafico(1);
        $botao = new BotaoGrafico();
        $botao->set_label('Servidores');
        $botao->set_url('servidor.php');
        $botao->set_image(PASTA_FIGURAS.'servidores.png',180,180);
        $botao->set_title('Cadastro de Servidores');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);

        $menu->show();
        $grid->fechaColuna();
        
        ##########################################################
            
        # Tabelas Auxiliares 
        $grid->abreColuna(5);        
           
        titulo('Tabelas Auxiliares');           
        br(2);

        $tamanhoImage = 70;
        $menu = new MenuGrafico(4);

        $botao = new BotaoGrafico();
        $botao->set_label('Cargos');
        $botao->set_url('cadastroCargo.php');
        $botao->set_image(PASTA_FIGURAS.'cracha.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Cargos');
        #$botao->set_accesskey('P');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Perfil');
        $botao->set_url('cadastroPerfil.php');
        $botao->set_image(PASTA_FIGURAS.'usuarios.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Perfil');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Lotação');
        $botao->set_url('cadastroLotacao.php');
        $botao->set_image(PASTA_FIGURAS.'lotacao.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Lotação');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Cargo em Comissão');
        $botao->set_url('cadastroCargoComissao.php');
        $botao->set_image(PASTA_FIGURAS.'usuarios.png',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Cargos em Comissão');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Concurso');
        $botao->set_url('cadastroConcurso.php');
        $botao->set_image(PASTA_FIGURAS.'concurso.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Concursos');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);   

        $botao = new BotaoGrafico();
        $botao->set_label('Tipos de Licenças');
        $botao->set_url('cadastroLicenca.php');
        $botao->set_image(PASTA_FIGURAS.'nene.gif',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Tipos de Licenças');
        #$botao->set_accesskey('W');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Plano de Cargos');
        $botao->set_url('cadastroPlanoCargos.php');
        $botao->set_image(PASTA_FIGURAS.'plano.gif',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Plano de Cargos');
        #$botao->set_accesskey('S');
        $menu->add_item($botao);

        $botao = new BotaoGrafico();
        $botao->set_label('Tabela Salarial (Classe & Padrão)');
        $botao->set_url('cadastroTabelaSalarial.php');
        $botao->set_image(PASTA_FIGURAS.'dinheiro.jpg',$tamanhoImage,$tamanhoImage);
        $botao->set_title('Cadastro de Tipos de Licenças');
        #$botao->set_accesskey('W');
        $menu->add_item($botao);

        $menu->show();
        $grid->fechaColuna();
        
        ##########################################################
            
        # Alertas
        $grid->abreColuna(4);

        $divAlertas = new Div("divAlertas");
        $divAlertas->abre();            
            titulo('Alertas');
            br(2);
            mensagemAguarde();
        $divAlertas->fecha();
        
        $grid->fechaColuna();
        $grid->fechaGrid();
        
        br();
        
        # Exibe faixa azul
        $grid = new Grid();
        $grid->abreColuna(12);        
            titulo();        
        $grid->fechaColuna();
        $grid->fechaGrid();
        
        # Exibe a versão do sistema
        $pessoal = new Pessoal();
        $grid = new Grid();
        $grid->abreColuna(4);
            p('Usuário : '.$pessoal->get_nome($matriculaUsuário),'grhUsuarioLogado');
        $grid->fechaColuna();
        $grid->abreColuna(4);
            p(BROWSER_NAME." - ".IP,'grhIp');
        $grid->fechaColuna();
        $grid->abreColuna(4);
            p('Versão: '.VERSAO,'grhVersao');
        $grid->fechaColuna();
        $grid->fechaGrid();
    }
        
##########################################################

    public static function menuServidor($matricula){
            
    /**
     * método menuServidor
     * 
     * Exibe o menu do servidor - o que aparece quando se seleciona um servidor 
     */
        
        # Menu Servidor
        $grid = new Grid();
        $grid->abreColuna(12);
        
        # Ocorrencias do servidor
        Grh::exibeOcorênciaServidor($matricula);
        
        $grid = new Grid();        
        
        # Funcionais 
        $grid->abreColuna(6);
            titulo('Funcionais');
            br();     
                $tamanhoImage = 50;
                $menu = new MenuGrafico(3);

                $botao = new BotaoGrafico();
                $botao->set_label('Funcionais');
                $botao->set_url('servidorFuncionais.php');
                $botao->set_image(PASTA_FIGURAS.'funcional.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Dados Funcionais do Servidor');
                #$botao->set_accessKey('F');
                $menu->add_item($botao);
                
                $botao = new BotaoGrafico();
                $botao->set_label('Lotação');
                $botao->set_url('servidorLotacao.php');
                $botao->set_image(PASTA_FIGURAS.'lotacao.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Histórico da Lotação do Servidor');
                #$botao->set_accessKey('L');
                $menu->add_item($botao);
                
                $pessoa = new Pessoal();
                $perfil = $pessoa->get_idPerfil($matricula);
                
                if ($perfil == 1)   // Ser for estatutário
                {
                    $botao = new BotaoGrafico();
                    $botao->set_label('Cessão');
                    $botao->set_url('servidorCessao.php');
                    $botao->set_image(PASTA_FIGURAS.'cessao.jpg',$tamanhoImage,$tamanhoImage);
                    $botao->set_title('Histórico de Cessões do Servidor');
                    #$botao->set_accessKey('C');
                    $menu->add_item($botao);
                }
                elseif($perfil == 2) // se for cedido
                {
                    $botao = new BotaoGrafico();
                    $botao->set_label('Cessão');
                    $botao->set_url('servidorCessaoCedido.php');
                    $botao->set_image(PASTA_FIGURAS.'cessao.jpg',$tamanhoImage,$tamanhoImage);
                    $botao->set_title('Dados da Cessão do Servidor');
                    #$botao->set_accessKey('C');
                    $menu->add_item($botao);
                }
                
                if($pessoa->get_perfilComissao($perfil) == "Sim")
                {
                    $botao = new BotaoGrafico();
                    $botao->set_label('Cargo em Comissão');
                    $botao->set_url('servidorComissao.php');
                    $botao->set_image(PASTA_FIGURAS.'comissao.jpg',$tamanhoImage,$tamanhoImage);
                    $botao->set_title('Histórico dos Cargos em Comissão do Servidor');                
                    #$botao->set_accessKey('i');
                    $menu->add_item($botao);
                }
                
                if ($perfil == 1)   // Ser for estatutário
                {
                    $botao = new BotaoGrafico();
                    $botao->set_label('Tempo de Serviço Averbado');
                    $botao->set_url('servidorAverbacao.php');
                    $botao->set_image(PASTA_FIGURAS.'historico.png',$tamanhoImage,$tamanhoImage);
                    $botao->set_title('Cadastro de Tempo de Serviço Averbado');                
                    #$botao->set_accessKey('i');
                    $menu->add_item($botao);
                }
                
                $botao = new BotaoGrafico();
                $botao->set_label('Elogios / Advertências');
                $botao->set_url('servidorElogiosAdvertencias.php');
                $botao->set_image(PASTA_FIGURAS.'ocorrencia.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Elogios e Advertências do Servidor');                
                #$botao->set_accessKey('i');
                $menu->add_item($botao);
                
                $botao = new BotaoGrafico();
                $botao->set_label('Observações');
                $botao->set_url('servidorObs.php');
                $botao->set_image(PASTA_FIGURAS.'obs.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Observações Gerais do Servidor');                
                #$botao->set_accessKey('i');
                $menu->add_item($botao);
                
                $menu->show();
            
            $grid->fechaColuna();
            
            # Ocorrências 
            $grid->abreColuna(6);
            
            titulo('Ocorrências');
            br();
            
            $menu = new MenuGrafico(3); 
                
                if($pessoa->get_perfilFerias($perfil) == "Sim")
                {
                    $botao = new BotaoGrafico();
                    $botao->set_label('Férias');
                    $botao->set_url('servidorFerias.php');
                    $botao->set_image(PASTA_FIGURAS.'ferias.jpg',$tamanhoImage,$tamanhoImage);
                    $botao->set_title('Cadastro das Férias do Servidor');
                    #$botao->set_accessKey('r');
                    $menu->add_item($botao);
                }
                
                if($pessoa->get_perfilLicenca($perfil) == "Sim")
                {
                    $botao = new BotaoGrafico();
                    $botao->set_label('Licença');
                    $botao->set_url('servidorLicenca.php');
                    $botao->set_image(PASTA_FIGURAS.'licenca.jpg',$tamanhoImage,$tamanhoImage);
                    $botao->set_title('Cadastro de Licenças do Servidor');
                    #$botao->set_accessKey('i');
                    #$botao->set_beta(true);
                    $menu->add_item($botao);
                }
                
                $botao = new BotaoGrafico();
                $botao->set_label('Atestados');
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
                $menu->add_item($botao);
                
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
                
                $botao = new BotaoGrafico();
                $botao->set_label('Suspensão');
                $botao->set_url('servidorSuspensao.php');
                $botao->set_image(PASTA_FIGURAS.'suspensao.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Suspensões de um servidor');                
                #$botao->set_accessKey('i');
                $menu->add_item($botao);
                
                $menu->show();
                
            $grid->fechaColuna();
            $grid->fechaGrid();        
            
            br();
                                              
            $grid = new Grid();
            
            # Pessoais
            $grid->abreColuna(6);
            titulo('Pessoais');
            br();
            
            $menu = new MenuGrafico(3); 

                $botao = new BotaoGrafico();
                $botao->set_label('Pessoais');
                $botao->set_url('servidorPessoais.php');
                $botao->set_image(PASTA_FIGURAS.'pessoais.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Dados Pessoais do Servidor');
                #$botao->set_accessKey('P');
                $menu->add_item($botao);
                
                $botao = new BotaoGrafico();
                $botao->set_label('Telefones & Emails');
                $botao->set_url('servidorTelefones.php');
                $botao->set_image(PASTA_FIGURAS.'telefone.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Dados dos Contatos do Servidor');
                #$botao->set_accessKey('T');
                $menu->add_item($botao);
                
                $botao = new BotaoGrafico();
                $botao->set_label('Documentos');
                $botao->set_url('servidorDocumentos.php');
                $botao->set_image(PASTA_FIGURAS.'documento.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro da Documentação do Servidor');
                #$botao->set_accessKey('D');
                $menu->add_item($botao);
                
                $botao = new BotaoGrafico();
                $botao->set_label('Formação');
                $botao->set_url('servidorFormacao.php');
                $botao->set_image(PASTA_FIGURAS.'diploma.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro de Formação Escolar do Servidor');
                #$botao->set_accessKey('o');
                $menu->add_item($botao);
                
                $botao = new BotaoGrafico();
                $botao->set_label('Dependentes');
                $botao->set_url('servidorDependentes.php');
                $botao->set_image(PASTA_FIGURAS.'dependente.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Cadastro dos Dependentes do Servidor');
                #$botao->set_accessKey('e');
                $menu->add_item($botao);
                
                $menu->show();
                br();
            $grid->fechaColuna();
            
            # Financeiro                                    
            $grid->abreColuna(6);
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
                    #$botao->set_accessKey('i');
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
                    #$botao->set_accessKey('i');
                    $menu->add_item($botao);
                }

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

                # Diarias
                $botao = new BotaoGrafico();
                $botao->set_label('Diárias');
                $botao->set_url('servidorDiaria.php');
                $botao->set_image(PASTA_FIGURAS.'diaria.jpg',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Controle de Diárias');
                $menu->add_item($botao);

                $menu->show();
                br();
            $grid->fechaColuna();            
            $grid->fechaGrid();
	
        $grid->fechaColuna();
        $grid->fechaGrid();
     }     
    
    ###########################################################
     
    /**
     * método quadroLicencaPremio
     * Exibe um quadro informativo da licença Prêmio de um servidor
     */
    
     public static function quadroLicencaPremio($matricula)
    {
        $servidor = new Pessoal();
        $diasPublicados = $servidor->get_licencaPremioNumDiasPublicadaPorMatricula($matricula);
        $diasFruidos = $servidor->get_licencaPremioNumDiasFruidos($matricula);
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
            $estatistica->set_label(array("Licença Prêmio","Dias"));
            $estatistica->set_align(array("center"));
            $estatistica->set_width(array(60,40));
            $estatistica->set_totalRegistro(false);
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
                          vagas,                                  
                          idTipoComissao,
                          idTipoComissao
                     FROM tbtipocomissao
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
            $tabela->set_label(array("Cargo","Simbolo","Vagas","Vagas Ocupadas","Vagas Disponíveis"));
            $tabela->set_width(array(30,20,15,15,15));
            $tabela->set_align(array("left"));
            $tabela->set_classe(array(null,null,null,'pessoal','pessoal'));
            $tabela->set_metodo(array(null,null,null,'get_servidoresCargoComissao','get_cargoComissaoVagasDisponiveis'));
            $tabela->set_formatacaoCondicional(array( array('coluna' => 4,
                                                            'valor' => 0,
                                                            'operador' => '=',
                                                            'id' => 'comissaoSemVagas'),
                                                      array('coluna' => 4,
                                                            'valor' => 0,
                                                            'operador' => '>',
                                                            'id' => 'comissaoComVagas'),
                                                      array('coluna' => 4,
                                                            'valor' => 0,
                                                            'operador' => '<',
                                                            'id' => 'comissaoVagasNegativas')));
            $tabela->show();
        }
    }	
        
    ###########################################################
     
    /**
     * método exibeOcorênciaServidor
     * Div que ressalta situação do servidor (licença, férias, etc)
     */
    
    public static function exibeOcorênciaServidor($matriculaServidor)
    {
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Div que ressalta situação do servidor (licença, férias, etc)
        $ferias = $pessoal->emFerias($matriculaServidor);
        $licenca = $pessoal->emLicenca($matriculaServidor);
        $situacao = $pessoal->get_situacao($matriculaServidor);
        $folgaTre = $pessoal->emFolgaTre($matriculaServidor);
        $afastadoTre = $pessoal->emAfastamentoTre($matriculaServidor);
        
        if(($ferias) OR ($licenca) OR ($afastadoTre) OR ($folgaTre) OR ($situacao == 'Inativo')){
            
            # Férias
            if($ferias)
                $mensagem = 'Servidor em férias';

            # Licenca
            if($licenca)
                $mensagem = 'Servidor em licença '.$pessoal->get_licenca($matriculaServidor);

            # Situação
            if($situacao == "Inativo")
                $mensagem = 'Servidor Inativo';
            
            # Folga TRE
            if($folgaTre)
                $mensagem = 'Folga TRE';
            
            # Afastamento TRE
            if($afastadoTre)
                $mensagem = 'Prestando serviço ao TRE';

            $callout = new Callout('warning');
            $callout->abre();
                p($mensagem);
            $callout->fecha();
        } 

        # Verifica pendencia de motorista com carteira vencida no sistema grh
        $perfil = $pessoal->get_perfil($matriculaServidor);
        $cargo = $pessoal->get_cargo($matriculaServidor);
        $idPessoa = $pessoal->get_idPessoa($matriculaServidor);
        $dataCarteira = $pessoal->get_dataVencimentoCarteiraMotorista($idPessoa);

        # Se é motorista estatutário
        if($perfil == 'Estatutário'){
            if($cargo == 'Motorista'){
                if(jaPassou($dataCarteira)){
                    $mensagem = 'Motorista com Carteira de Habilitação Vencida !! ('.$dataCarteira.')';
                    $callout = new Callout('warning');
                    $callout->abre();
                        p($mensagem);
                    $callout->fecha();
                }
            }
        }
    }
    
    ###########################################################
    
    /**
    * método listaDadosServidor
    * Exibe os dados principais do servidor logado
    * 
    * @param    string $matricula -> matricula do servidor
    */
    public static function listaDadosServidor($matricula)
    {       
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select ='SELECT tbfuncionario.matricula,
                         tbfuncionario.idFuncional,
                         tbpessoa.nome,
                         tbperfil.nome,
                         tbfuncionario.matricula,
                         tbfuncionario.dtAdmissao,
                         tbfuncionario.matricula,
                         tbfuncionario.matricula
                    FROM tbfuncionario LEFT JOIN tbpessoa ON tbfuncionario.idPessoa = tbpessoa.idPessoa
                                       LEFT JOIN tbsituacao ON tbfuncionario.sit = tbsituacao.idsit
                                       LEFT JOIN tbperfil ON tbfuncionario.idPerfil = tbperfil.idPerfil
                   WHERE matricula = '.$matricula;

        $conteudo = $servidor->select($select,true);

        $label = array("Matrícula","Id","Servidor","Perfil","Cargo","Admissão","Lotação","Situação");
        #$width = array(8,10,20,10,25,10,25,5);
        #$align = array("center");
        $function = array("dv",null,null,null,null,"date_to_php");
        $classe = array(null,null,null,null,"pessoal",null,"pessoal","pessoal");
        $metodo = array(null,null,null,null,"get_Cargo",null,"get_Lotacao","get_Situacao");
        
        $formatacaoCondicional = array( array('coluna' => 0,
                                              'valor' => dv($matricula),
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
        $tabela->set_zebrado(true);
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
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                                            array('coluna' => 0,
                                                  'valor' => 'Folgas Pendentes',
                                                  'operador' => '=',
                                                  'id' => 'trePendente')));

            $tabela->show();

        $div->fecha();
    }

}