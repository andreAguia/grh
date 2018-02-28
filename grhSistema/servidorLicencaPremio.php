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
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

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
        $objeto->set_nome('Licença Prêmio');

        # botão de voltar da lista
        $objeto->set_voltarLista('servidorMenu.php');

        # select da lista
        $objeto->set_selectLista('SELECT dtInicial,
                                         numdias,
                                         ADDDATE(dtInicial,numDias-1),
                                         idLicencaPremio,
                                         idLicencaPremio
                                    FROM tblicencaPremio 
                                   WHERE idServidor='.$idServidorPesquisado.'
                                ORDER BY dtInicial desc');        
        
        # select do edita
        $objeto->set_selectEdita('SELECT dtInicial,
                                         numdias,
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
        $objeto->set_label(array("Inicio","Dias","Término","Publicação"));
        $objeto->set_width(array(25,10,25,25));	
        $objeto->set_align(array("center"));
        $objeto->set_funcao(array('date_to_php',NULL,'date_to_php'));
        $objeto->set_classe(array(NULL,NULL,NULL,'LicencaPremio'));
        $objeto->set_metodo(array(NULL,NULL,NULL,'get_publicacao'));
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
        
        # verifica se é inclusão
        if(is_null($id)){
            # variáveis
            $diasDisponiveis = NULL;
            $array = NULL;
            $diaPublicacao = NULL;                    

            # Pega os dados para o alerta
            $licenca = new LicencaPremio($idServidorPesquisado);
            $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidorPesquisado);
            
            # monta os valores
            switch ($diasDisponiveis){
                case ($diasDisponiveis >=90) :
                    $array = array(90,60,30);
                    break;
                case 60 :
                    $array = array(60,30);
                    break;
                case 30 :
                    $array = array(30);
                    break;                        
            }                  
        }else{
            $array = array(90,60,30);                   
        }        

        # Campos para o formulario
        $objeto->set_campos(array(array('nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'required' => TRUE,
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data do início.',
                                       'linha' => 3),
                                array( 'nome' => 'numDias',
                                       'label' => 'Dias:',
                                       'tipo' => 'combo',
                                       'array' => $array,
                                       'size' => 5,
                                       'required' => TRUE,
                                       'title' => 'Número de dias.',
                                       'col' => 2,
                                       'linha' => 3),
                                array ('linha' => 7,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,5)),
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
               
        $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Relatório de Licença Prêmio");
        $botaoRel->set_onClick("window.open('../grhRelatorios/servidorLicencaPremio.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        
        $objeto->set_botaoListarExtra(array($botaoRel));

        ################################################################

        switch ($fase){
            case "" :
            case "listar" :
                # Exibe quadro de licença prêmio
                Grh::quadroLicencaPremio($idServidorPesquisado);

                # Pega os dados para o alerta
                $licenca = new LicencaPremio();
                $diasPublicados = $licenca->get_numDiasPublicados($idServidorPesquisado);
                $diasFruidos = $licenca->get_numDiasFruidos($idServidorPesquisado);
                $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidorPesquisado);

                # Exibe alerta se $diasDisponíveis for negativo
                if($diasDisponiveis < 0){                    
                    $mensagem1 = "Servidor tem mais dias fruídos de Licença prêmio do que publicados.";
                    $objeto->set_rotinaExtraListar("callout");
                    $objeto->set_rotinaExtraListarParametro($mensagem1);
                    $objeto->set_botaoIncluir(FALSE);
                }

                if($diasDisponiveis == 0){
                    $mensagem1 = "Servidor sem dias disponíveis. É necessário cadastrar uma publicação antes de incluir uma licença prêmio.";
                    $objeto->set_rotinaExtraListar("callout");
                    $objeto->set_rotinaExtraListarParametro($mensagem1);
                    $objeto->set_botaoIncluir(FALSE);
                }  

                $objeto->listar();

                # Limita o tamanho da tela
                $grid = new Grid();
                $grid->abreColuna(12);

                # Cria um menu
                $menu = new MenuBar();

                # Relatórios
                $linkBotao3 = new Link("Publicações","servidorPublicacaoPremio.php");
                $linkBotao3->set_class('button');
                $linkBotao3->set_title("Acessa o Cadastro de Publicações");
                $menu->add_link($linkBotao3,"right");

                $menu->show();

                # Exibe as Publicações
                $select = 'SELECT dtPublicacao,
                                pgPublicacao,
                                dtInicioPeriodo,
                                dtFimPeriodo,                                  
                                processo,
                                numDias,
                                idPublicacaoPremio,
                                idPublicacaoPremio,
                                idPublicacaoPremio
                           FROM tbpublicacaopremio
                           WHERE idServidor = '.$idServidorPesquisado.'
                       ORDER BY dtPublicacao desc';

                $result = $pessoal->select($select);
                $count = $pessoal->count($select);

                # Cabeçalho da tabela
                $titulo = 'Publicações';
                $label = array("Data da Publicação","Pag.","Período Aquisitivo <br/> Início","Período Aquisitivo <br/> Fim","Processo","Dias <br/> Publicados","Dias <br/> Fruídos","Dias <br/> Disponíveis");
                $width = array(15,10,15,15,15,10,10,10);
                $funcao = array('date_to_php',NULL,'date_to_php','date_to_php');
                $classe = array(NULL,NULL,NULL,NULL,NULL,NULL,'LicencaPremio','LicencaPremio');
                $metodo = array(NULL,NULL,NULL,NULL,NULL,NULL,'get_numDiasFruidosPorPublicacao','get_numDiasDisponiveisPorPublicacao');
                $align = array('center');            

                # Exibe a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_align($align);
                $tabela->set_label($label);
                $tabela->set_width($width);
                $tabela->set_titulo($titulo);
                $tabela->set_funcao($funcao);
                $tabela->set_classe($classe);
                $tabela->set_metodo($metodo);

                $tabela->show();

                $grid->fechaColuna();
                $grid->fechaGrid();   
                break;
            
            case "editar" :            
            case "excluir" :       
                $objeto->$fase($id);  
                break;

            case "gravar" :
                $objeto->gravar($id); 	
                break;	
        }
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}