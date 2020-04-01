<?php
/**
 * Histórico de Licenças Prêmio de um servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de licenças prêmio";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7,$idServidorPesquisado);
    }
    
    # Inicia a classe de licença
    $licenca = new LicencaPremio();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Verifica se veio da área de Licença Premio
    $areaPremio = get_session("areaPremio");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Verifica se o Servidor tem direito a licença
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    if ($pessoal->get_perfilLicenca($idPerfil) == "Não"){
        $mensagem = 'Esse servidor está em um perfil que não pode ter licença !!';
        $alert = new Alert($mensagem) ;
        $alert->show();
        loadPage('servidorMenu.php');
    }else{
        # Abre um novo objeto Modelo
        $objeto = new Modelo();

        ################################################################
        
        # Exibe os dados do Servidor
        $objeto->set_rotinaExtra(array("get_DadosServidor"));
        $objeto->set_rotinaExtraParametro(array($idServidorPesquisado)); 

        # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
        $objeto->set_nome($pessoal->get_licencaNome(6));
        
        # botão de voltar da lista
        if($areaPremio){
            $objeto->set_voltarLista('areaLicencaPremio.php');
        }else{
            $objeto->set_voltarLista('servidorMenu.php');
        }

        # select da lista
        $objeto->set_selectLista('SELECT tbpublicacaopremio.dtPublicacao,
                                         tbpublicacaopremio.dtInicioPeriodo,
                                         tbpublicacaopremio.dtFimPeriodo,
                                         dtInicial,
                                         tblicencapremio.numdias,
                                         ADDDATE(dtInicial,tblicencapremio.numDias-1),
                                         idLicencaPremio
                                    FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                                   WHERE tblicencapremio.idServidor = '.$idServidorPesquisado.'
                                ORDER BY dtInicial desc');        
        
        # select do edita
        $objeto->set_selectEdita('SELECT dtInicial,
                                         numDias,
                                         idPublicacaoPremio,
                                         obs,
                                         idServidor
                                    FROM tblicencapremio
                                   WHERE idLicencaPremio = '.$id);
        
        # Caminhos
        $objeto->set_linkEditar('?fase=editar');
        $objeto->set_linkExcluir('?fase=excluir');
        $objeto->set_linkGravar('?fase=gravar');
        $objeto->set_linkListar('?fase=listar');

        # Parametros da tabela
        $objeto->set_label(array("Data da Publicação","Período Aquisitivo<br/>Início","Período Aquisitivo<br/>Fim","Inicio","Dias","Término"));
        #$objeto->set_width(array(25,10,25,25));	
        $objeto->set_align(array("center"));
        $objeto->set_funcao(array('date_to_php','date_to_php','date_to_php','date_to_php',NULL,'date_to_php'));
        #$objeto->set_classe(array(NULL,NULL,NULL,'LicencaPremio'));
        #$objeto->set_metodo(array(NULL,NULL,NULL,'get_publicacao'));
        $objeto->set_numeroOrdem(TRUE);
        $objeto->set_numeroOrdemTipo("d");
        $objeto->set_exibeTempoPesquisa(FALSE);
    
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
                    WHERE idServidor = '.$idServidorPesquisado;            
        
        # Inclui as publicações de outros vinculos
        if(($numVinculos > 0) AND ($idSituacao == 1)){
            
            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $pessoal->get_vinculos($idServidorPesquisado);      
            
            # Percorre os vinculos
            foreach($vinculos as $tt){
                $select .= ' OR idServidor = '.$tt[0];
            }            
        }
        
        $select .= ' ORDER BY dtInicioPeriodo desc';
        
        $publicacao = $pessoal->select($select);
        
        array_unshift($publicacao, array(NULL,' -- Selecione uma Publicação')); # Adiciona o valor de nulo
        
        # Campos para o formulario
        $objeto->set_campos(array(array('nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data do início.',
                                       'linha' => 1),
                                array( 'nome' => 'numDias',
                                       'label' => 'Dias:',
                                       'tipo' => 'combo',
                                       'array' => $array = array(90,60,30),        
                                       'size' => 5,
                                       'required' => TRUE,
                                       'title' => 'Número de dias.',
                                       'col' => 2,
                                       'linha' => 1),
                                 array('nome' => 'idPublicacaoPremio',
                                        'label' => 'Publicação:',
                                        'tipo' => 'combo',
                                        'size' => 50,
                                        'array' => $publicacao,
                                        'title' => 'Publicação.',
                                        'col' => 5,
                                        'linha' => 1),
                                array ('linha' => 3,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,4),
                                       'linha' => 2),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 8)));
        
        # Log
        $objeto->set_idUsuario($idUsuario);
        $objeto->set_idServidorPesquisado($idServidorPesquisado);
        
        $imagem = new Imagem(PASTA_FIGURAS.'ajuda.png',NULL,15,15);
        $botaoHelp = new Button();
        $botaoHelp->set_imagem($imagem);
        $botaoHelp->set_title("Ajuda");
        $botaoHelp->set_url("../grhRelatorios/servidorLicenca.php");
        $botaoHelp->set_target("_blank");
        
        $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Relatório de Licença Prêmio");
        $botaoRel->set_url("../grhRelatorios/servidorLicencaPremio.php");
        $botaoRel->set_target("_blank");
        
        $objeto->set_botaoListarExtra(array($botaoRel));

        ################################################################

        switch ($fase){
            case "" :
            case "listar" :
                # Exibe quadro de licença prêmio
                #Grh::quadroLicencaPremio($idServidorPesquisado);

                # Pega os dados 
                $diasPublicados = $licenca->get_numDiasPublicadosTotal($idServidorPesquisado);
                $diasFruidos = $licenca->get_numDiasFruidosTotal($idServidorPesquisado);
                $diasDisponiveis = $licenca->get_numDiasDisponiveisTotal($idServidorPesquisado);
                $numProcesso = $licenca->get_numProcesso($idServidorPesquisado);
                
                $nome = $pessoal->get_licencaNome(6);
                $idSituacao = $pessoal->get_idSituacao($idServidorPesquisado);
                
                # inicia o array das rotinas extras
                $rotinaExtra = array();
                $rotinaExtraParametro = array();
                $mensagem = NULL;

                # Exibe alerta se $diasDisponíveis for negativo no geral
                if($diasDisponiveis < 0){ 
                    $mensagem .= "Servidor tem mais dias fruídos de $nome do que publicados.<br/>";
                    $objeto->set_botaoIncluir(FALSE);
                }

                # Servidor sem dias disponíveis. Precisa publicar antes de tirar nova licença
                if($diasDisponiveis < 1){
                    $mensagem .= "Servidor sem dias disponíveis. É necessário cadastrar uma publicação antes de incluir uma $nome.<br/>";
                    $objeto->set_botaoIncluir(FALSE);
                }  
                
                # Servidor sem processo cadastrado
                if(is_null($numProcesso)){
                    $mensagem .= "Servidor sem número de processo de $nome cadastrado.<br/>";
                    $objeto->set_botaoIncluir(FALSE);
                }
                
                if(!is_null($mensagem)){
                    $rotinaExtra[] = "callout";
                    $rotinaExtraParametro[] = $mensagem;
                }
                
                # Acrescenta as rotinas
                $objeto->set_rotinaExtraListar($rotinaExtra);
                $objeto->set_rotinaExtraListarParametro($rotinaExtraParametro);
                
                $objeto->listar();
                
                # Limita o tamanho da tela
                $grid = new Grid();
                $grid->abreColuna(12);
                
                # Exibe as licenças prêmio de outros vinculos com a UENF                
                $numVinculos = $licenca->get_numVinculosPremio($idServidorPesquisado);
                #p("Vinculos: $numVinculos");
                
                # Exibe o tempo de licença anterior somente de servidores ativos
                #if($idSituacao == 1){
                    
                    # Verifica se tem vinculos anteriores
                    if($numVinculos > 0){
                        
                        # Carrega um array com os idServidor de cada vinculo
                        $vinculos = $pessoal->get_vinculos($idServidorPesquisado);                    

                        # Percorre os vinculos
                        foreach($vinculos as $tt){

                            # Pega o perfil da cada vínculo
                            $idPerfilPesquisado = $pessoal->get_idPerfil($tt[0]);

                            if($idServidorPesquisado <> $tt[0]){
                                
                                # Verifica se é estatutário
                                if($idPerfilPesquisado == 1){
                                    # Cria um menu
                                    $menu = new MenuBar();

                                    # Número do processo
                                    $licenca->exibeLicencaPremio($tt[0]);
                                }
                            }
                        }
                    }
               # }
                
                # Cria um menu
                $menu = new MenuBar();
                
                # Número do processo
                $linkBotao1 = new Link("Edita Processo","servidorProcessoPremio.php");
                $linkBotao1->set_class('button');
                $linkBotao1->set_title("Edita o número do processo de licença prêmio");
                $menu->add_link($linkBotao1,"left");

                # Cadastro de Publicações
                $linkBotao3 = new Link("Publicações","servidorPublicacaoPremio.php");
                $linkBotao3->set_class('button');
                $linkBotao3->set_title("Acessa o Cadastro de Publicações");
                $menu->add_link($linkBotao3,"right");
                $menu->show();
                
                # Exibe as publicações de Licença Prêmio
                $licenca->exibePublicacoesPremio($idServidorPesquisado);
                
                # Exibe os procedimentos
                #$painel = new Callout();
                #$painel->abre();
                
                #titulo("Procedimentos");
                #br();
                #$licenca->exibeProcedimentos();
                
                #$painel->fecha();

                # Exibe o idServidor
                $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
                $texto = '';

                if(!is_null($idServidorPesquisado)){
                    $texto .= "Servidor: ".$idServidorPesquisado;
                }

                if(!is_null($idPessoa)){
                    $texto .= " - Pessoa: ".$idPessoa;
                }

                p($texto,'idServidor'); 
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
                $objeto->gravar($id,"servidorLicencaPremioExtra.php"); 
                break;	
            
            case "outroVinculo" :
                br(8);
                aguarde();

                set_session('idServidorPesquisado',$id);
                loadPage('servidorLicencaPremio.php');
                break; 
        
        ###############################
        }
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}