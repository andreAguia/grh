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
        #$objeto->set_width(array(15,5,5,8,5,8,14,10,10,10));	
        $objeto->set_align(array("center"));
        $objeto->set_funcao(array('date_to_php',NULL,'date_to_php'));
        $objeto->set_numeroOrdem(TRUE);
        $objeto->set_numeroOrdemTipo("d");
    
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

            # pega a primeira publicação disponível dessa matrícula
            #$diaPublicacao = $pessoal->get_licencaPremioPublicacaoDisponivel($idServidorPesquisado);

            # pega quantos dias estão disponíveis
            $diasDisponiveis = $pessoal->get_NumDiasDisponiveis();

            # monta os valores
            switch ($diasDisponiveis){
                case 90 :
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

        # Publicação de Licença Prêmio
        $botaoPremio = new Button("Publicações");
        $botaoPremio->set_title("Acessa o Cadastro de Publicação para Licença Prêmio");
        $botaoPremio->set_url('servidorPublicacaoPremio.php');  
               
        $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Relatório de Licença");
        $botaoRel->set_onClick("window.open('../grhRelatorios/servidorLicenca.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        
        $objeto->set_botaoListarExtra(array($botaoRel,$botaoPremio));

        ################################################################

        switch ($fase){
            case "" :
            case "listar" :
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