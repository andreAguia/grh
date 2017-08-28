<?php
/**
 * Histórico de Férias de um servidor
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

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Verifica se veio da área de férias
    $areaFerias = get_session("areaFerias");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Verifica a paginacão
    $paginacao = get('paginacao',get_session('sessionPaginacao',0));	// Verifica se a paginação vem por get, senão pega a session
    set_session('sessionPaginacao',$paginacao);		
    
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
    $objeto->set_nome('Histórico de Férias');

    # botão de voltar da lista
    if($areaFerias){
        $objeto->set_voltarLista('areaFerias.php');
    }else{
        $objeto->set_voltarLista('servidorMenu.php');
    }
        
    # botão de voltar do formulário
    $objeto->set_linkListar('?fase=listar');

    # select da lista
    $objeto->set_selectLista('SELECT anoExercicio,
                                     status,
                                     dtInicial,
                                     numDias,
                                     idFerias,
                                     ADDDATE(dtInicial,numDias-1),
                                     folha,
                                     idFerias,
                                     idFerias
                                FROM tbferias
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY dtInicial desc');

    # select do edita
    $objeto->set_selectEdita('SELECT anoExercicio,
                                     status,
                                     dtInicial,
                                     numDias,
                                     folha,
                                     obs,
                                     idServidor
                                FROM tbferias
                               WHERE idFerias = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    

    # Parametros da tabela
    $objeto->set_label(array("Exercicio","Status","Data Inicial","Dias","P","Data Final","Folha do 1/3"));
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array (NULL,NULL,'date_to_php',NULL,NULL,'date_to_php'));
    $objeto->set_width(array (10,10,15,10,10,15,20));
    $objeto->set_classe(array(NULL,NULL,NULL,NULL,"pessoal"));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,"get_feriasPeriodo"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbferias');

    # Nome do campo id
    $objeto->set_idCampo('idFerias');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);
    
    # Pega o valor para o anoexercicio
    $exercícioDisponivel = $pessoal->get_feriasExercicioDisponivel($idServidorPesquisado);
    
    # Pega o ano atual
    if($areaFerias){
        $anoPadrao = get_session('parametroAnoExercicio');
    }else{
        $anoPadrao = $exercícioDisponivel;
    }

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'anoExercicio',
                                       'label' => 'Ano do Exercício:',
                                       'tipo' => 'numero',
                                       'size' => 7,
                                       'col' => 2,
                                       'padrao' => $anoPadrao,
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'title' => 'Ano de Exercício das Férias.',
                                       'linha' => 1),
                               array ( 'nome' => 'status',
                                       'label' => 'Status:',
                                       'tipo' => 'combo',
                                       'required' => TRUE,
                                       'array' => array('','solicitada','confirmada','fruida','cancelada'),
                                       'size' => 20,
                                       'col' => 2,
                                       'title' => 'Status das férias',
                                       'linha' => 1),      
                               array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => TRUE,
                                       'title' => 'Data do início das férias.',
                                       'linha' => 1),
                               array ( 'nome' => 'numDias',
                                       'label' => 'Dias:',
                                       'tipo' => 'combo',
                                       'array' => array(NULL,30,20,15,10),
                                       'col' => 2,
                                       'size' => 5,
                                       'required' => TRUE,
                                       'title' => 'Dias de Férias.',
                                       'linha' => 1),
                               array ( 'nome' => 'folha',
                                       'label' => 'Mês/Ano do pagamento do 1/3:',
                                       'tipo' => 'texto',
                                       'size' => 50,                                   
                                       'title' => 'mês/ano do pagamento do 1/3',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'linha' => 3,
                                       'col' => 12,
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
                                       'linha' => 2)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Férias");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorFerias.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        
    # Resumo
    $botaoResumo = new Button("Resumo");
    $botaoResumo->set_title("Resumo das Férias");
    $botaoResumo->set_url("?fase=resumo");
    $botaoResumo->set_accessKey('R');
    
    $objeto->set_botaoListarExtra(array($botaoRel,$botaoResumo));
        
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Paginação
    $objeto->set_paginacao(FALSE);
    $objeto->set_paginacaoInicial($paginacao);
    $objeto->set_paginacaoItens(6);


################################################################

    switch ($fase)
    {
            case "" :
            case "listar" :
                $objeto->listar();
                break;
            
            case "editar" :  
                $mensagem1 = "Tipos de Férias:<br/>"
                    . " Solicitada -> Férias solicitadas pelo servidor que ainda não foi fruída.<br/>"
                    . " Confirmada -> Férias que foram emitidas o aviso de férias e assim sendo confirmadas pelo sistema.<br/>"
                    . " Fruídas    -> Férias confirmadas cuja data de início ja passou.<br/>"
                    . " Canceladas -> Férias canceladas por motivos diversos.";
                #$objeto->set_rotinaExtraEditar("callout");
                #$objeto->set_rotinaExtraEditarParametro($mensagem1);
                    
                $objeto->editar($id);
                break;
            case "gravar" :		
                $objeto->gravar($id,"servidorFeriasExtra.php"); 			
                break;
            
            case "excluir" :	
                $objeto->excluir($id);
                break;
            case "resumo" :
                botaoVoltar("?");
                get_DadosServidor($idServidorPesquisado);
                
                $grid = new Grid();
                $grid->abreColuna(12);
                    titulo("Resumo das Férias");
                    br();
                $grid->fechaColuna();
                $grid->fechaGrid();    
                
                $grid = new Grid("center");
                $grid->abreColuna(4);
                
                    $lista = $pessoal->get_feriasResumo($idServidorPesquisado);
                    $tabela = new Tabela();
                    $tabela->set_conteudo($lista);
                    $tabela->set_label(array("Exercício","Dias"));
                    $tabela->set_align(array("center"));
                    $tabela->show();
                    
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;

################################################################

            case 'solicitacaoFerias':
                $id = get('id');

                # pega os dados do servidor
                $nome = $pessoal->get_nome($idServidorPesquisado);
                $cargo = $pessoal->get_cargo($idServidorPesquisado);
                $perfil = $pessoal->get_perfil($idServidorPesquisado);
                $lotacao = $pessoal->get_lotacao($idServidorPesquisado);

                # Select das férias
                $select = "SELECT anoExercicio,
                                  DATE_FORMAT(dtInicial,'%d/%m/%Y'),
                                  numDias,
                                  DATE_FORMAT(ADDDATE(dtInicial,numDias-1),'%d/%m/%Y') as dtFinal
                             FROM tbferias
                            WHERE tbferias.idFerias = ".$id;

                # Acessa o Banco de dados
                $ferias = new Pessoal();
                $row = $ferias->select($select,FALSE);
                $row = urlencode(serialize($row));  // Prepara para ser enviado por get

                # preenche outro array com o restante dos dados
                $servidor = array($nome,$cargo,$perfil,$lotacao,$idServidorPesquisado);
                $servidor = urlencode(serialize($servidor));  // Prepara para ser enviado por get        

                loadPage('../grhRelatorios/solicitacaoFerias.php?row='.$row.'&servidor='.$servidor,'_blank');  // envia um array pelo get
                
                # Log
                $atividade = "Emitiu Solicitação de Férias de ".$pessoal->get_nome($idServidorPesquisado);
                $data = date("Y-m-d H:i:s");
                $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,4,$idServidorPesquisado);
    
                loadPage('?');
                break;

################################################################

            case 'avisoFerias':
                $id = get('id');

                # muda status das férias
                $pessoal->mudaStatusFeriasSolicitadaConfirmada($id);

                # pega os dados do servidor
                $nome = $pessoal->get_nome($idServidorPesquisado);
                $cargo = $pessoal->get_cargo($idServidorPesquisado);
                $perfil = $pessoal->get_perfil($idServidorPesquisado);
                $lotacao = $pessoal->get_lotacao($idServidorPesquisado);

                # Select das férias
                $select = "SELECT anoExercicio,
                                  DATE_FORMAT(dtInicial,'%d/%m/%Y'),
                                  numDias,
                                  DATE_FORMAT(ADDDATE(dtInicial,numDias-1),'%d/%m/%Y') as dtFinal
                             FROM tbferias
                            WHERE tbferias.idFerias = ".$id;

                # Acessa o Banco de dados
                $ferias = new Pessoal();
                $row = $ferias->select($select,FALSE);
                $row = urlencode(serialize($row));  // Prepara para ser enviado por get

                # preenche outro array com o restante dos dados
                $servidor = array($nome,$cargo,$perfil,$lotacao,$idServidorPesquisado);
                $servidor = urlencode(serialize($servidor));  // Prepara para ser enviado por get        

                loadPage('../relatorios/avisoFerias.php?row='.$row.'&servidor='.$servidor,'_blank');  // envia um array pelo get
                loadPage('?');
                break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}