<?php
/**
 * Cadastro de Publicação de Licenças Prêmios
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

    # Abre um novo objeto Modelo
    $objeto = new Modelo();
		
    ################################################################
    
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Publicação de Licença Prêmio no DOERJ');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('servidorLicencaPremio.php');

    # controle de pesquisa
    #$objeto->set_parametroLabel('Pesquisar');
    #$objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista ('SELECT dtPublicacao,
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
                             ORDER BY dtPublicacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtPublicacao,
                                     pgPublicacao,
                                     processo,
                                     dtInicioPeriodo,
                                     dtFimPeriodo,
                                     numDias,
                                     obs,
                                     idServidor
                                FROM tbpublicacaopremio
                               WHERE idPublicacaoPremio = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Data da Publicação","Pag.","Período Aquisitivo - Início","Período Aquisitivo - Fim","Processo","Dias Publicados","Dias Fruídos","Disponíveis"));
    $objeto->set_width(array(15,5,15,15,15,10,10,10));
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array('date_to_php',NULL,'date_to_php','date_to_php'));
    $objeto->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,'LicencaPremio','LicencaPremio'));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,'get_NumDiasFruidosPorPublicacao','get_NumDiasDisponiveisPorPublicacao'));
    $objeto->set_exibeTempoPesquisa(FALSE);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpublicacaopremio');

    # Nome do campo id
    $objeto->set_idCampo('idPublicacaoPremio');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('dtPublicacao');

    # Campos para o formulario
    $objeto->set_campos(array(
                        array ( 'nome' => 'dtPublicacao',
                                'label' => 'Data da Pub. no DOERJ:',
                                'tipo' => 'data',
                                'size' => 20,
                                'col' => 3,
                                'required' => TRUE,
                                'title' => 'Data da Publicação no DOERJ.',
                                'linha' => 1),
                        array ( 'nome' => 'pgPublicacao',
                                'label' => 'Pág:',
                                'tipo' => 'texto',
                                'col' => 2,
                                'size' => 5,                         
                                'title' => 'A Página do DOERJ',
                                'linha' => 1),
                        array ( 'nome' => 'processo',
                                'label' => 'Processo:',
                                'tipo' => 'texto',
                                'padrao' => $pessoal->get_licencaPremioNumProcesso($idServidorPesquisado),
                                'size' => 30,
                                'col' => 4,
                                'required' => TRUE,
                                'title' => 'Número do Processo',
                                'linha' => 1),
                        array ( 'nome' => 'dtInicioPeriodo',
                                'label' => 'Período Aquisitivo Início:',
                                'tipo' => 'data',
                                'col' => 3,
                                'size' => 20,
                                'required' => TRUE,                 
                                'title' => 'Data de início do período aquisitivo',
                                'linha' => 2),
                        array ( 'nome' => 'dtFimPeriodo',
                                'label' => 'Período Aquisitivo Término:',
                                'tipo' => 'data',
                                'size' => 20,
                                'col' => 3,
                                'required' => TRUE,                 
                                'title' => 'Data de término do período aquisitivo',
                                'linha' => 2),
                        array ( 'nome' => 'numDias',
                                'label' => 'Dias:',
                                'tipo' => 'numero',
                                'padrao' => 90,
                                'readOnly' => TRUE,
                                'size' => 5,
                                'col' => 3,
                                'required' => TRUE,
                                'title' => 'Dias de Férias.',
                                'linha' => 2),
                         array ('linha' => 5,
                                'nome' => 'obs',
                                'label' => 'Observação:',
                                'tipo' => 'textarea',
                                'linha' => 3,
                                'col' => 12,
                                'size' => array(80,5)),
                        array ( 'nome' => 'idServidor',
                                'label' => 'idServidor:',
                                'tipo' => 'hidden',
                                'padrao' => $idServidorPesquisado,
                                'size' => 5,
                                'title' => 'Matrícula',
                                'linha' => 6)));
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Licença prêmio");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorLicencaPremio.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    
    $objeto->set_botaoListarExtra(array($botaoRel));  

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase)
    {
        case "" :
        case "listar" : 
            
            # Pega os dados para o alerta
            $licenca = new LicencaPremio();
            $diasPublicados = $licenca->get_NumDiasPublicados($idServidorPesquisado);
            $diasFruidos = $licenca->get_NumDiasFruidos($idServidorPesquisado);
            $diasDisponiveis = $licenca->get_NumDiasDisponiveis($idServidorPesquisado);

            # Exibe alerta se $diasDisponíveis for negativo
            if($diasDisponiveis < 0){                    
                $mensagem1 = "Este Servidor tem mais dias fruídos de Licença prêmio do que publicados.";
                $objeto->set_rotinaExtraListar("callout");
                $objeto->set_rotinaExtraListarParametro($mensagem1);
            }

            $objeto->listar();

            # Exibe as licenças prêmio
            $select = 'SELECT dtInicial,
                              numdias,
                              ADDDATE(dtInicial,numDias-1),
                              idLicencaPremio,
                              idLicencaPremio
                         FROM tblicencaPremio 
                        WHERE idServidor='.$idServidorPesquisado.'
                     ORDER BY dtInicial desc';

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Cabeçalho da tabela
            $titulo = 'Licenças Prêmio';
            $label = array("Inicio","Dias","Término","Publicação");
            #$width = array(13,10,6,10,6,10,15,15,15);
            $funcao = array('date_to_php',NULL,'date_to_php');
            $align = array('center');

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_align($align);
            $tabela->set_label($label);
            $tabela->set_titulo($titulo);
            $tabela->set_funcao($funcao);
            
            hr();
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(3);

                # Tabela de Serviços
                $mesServico = date('m');
                $array = array(array('Dias Publicados',$diasPublicados),
                               array('Dias Fruídos',$diasFruidos),
                               array('Disponíveis',$diasDisponiveis));
                
                $estatistica = new Tabela();
                $estatistica->set_titulo("Resumo");
                $estatistica->set_conteudo($array);
                $estatistica->set_label(array("Dias","Valor"));
                $estatistica->set_align(array("center"));
                $estatistica->set_width(array(60,40));
                $estatistica->set_totalRegistro(FALSE);
                $estatistica->show();
            
            $grid->fechaColuna();
            $grid->abreColuna(9);

            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();   
            break;

        case "editar" :	
        case "gravar" :
        case "excluir" :
            $objeto->$fase($id);
            break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}