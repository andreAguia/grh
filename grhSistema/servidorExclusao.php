<?php
/**
 * Histórico de Férias de um servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado
$matriculaGrh = null;		  # Reservado para a matrícula pesquisada

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idusuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados   
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

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
    $objeto->set_rotinaExtraParametro($matriculaGrh); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Histórico de Férias');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT anoExercicio,
                                     status,
                                     dtInicial,
                                     numDias,
                                     periodo,
                                     ADDDATE(dtInicial,numDias-1),
                                     documento,
                                     folha,
                                     idFerias,
                                     idFerias
                                FROM tbferias
                               WHERE matricula='.$matriculaGrh.'
                            ORDER BY dtInicial desc');

    # select do edita
    $objeto->set_selectEdita('SELECT anoExercicio,
                                     status,
                                     dtInicial,
                                     numDias,
                                     periodo,
                                     documento,
                                     folha,
                                     obs,
                                     matricula
                                FROM tbferias
                               WHERE idFerias = '.$id);

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Exercicio","Status","Data Inicial","Dias","P","Data Final","Documento 1/3","Folha","Solicitação de Férias"));
    $objeto->set_width(array(10,10,10,5,8,10,15,12,8));	
    $objeto->set_align(array("center"));
    $objeto->set_function(array (null,null,'date_to_php',null,null,'date_to_php'));

    # Botão da solicitação de férias
    $botao1 = new BotaoGrafico();
    $botao1->set_title('Emite a segunda via da solicitação de férias');
    $botao1->set_label('');
    $botao1->set_url('?fase=solicitacaoFerias&id=');     
    $botao1->set_image(PASTA_FIGURAS_GERAIS.'relatorio.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","","","","","",$botao1));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbferias');

    # Nome do campo id
    $objeto->set_idCampo('idFerias');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'anoExercicio',
                                       'label' => 'Ano do Exercício:',
                                       'tipo' => 'numero',
                                       'size' => 7,
                                       'col' => 2,
                                       'required' => true,
                                       'autofocus' => true,
                                       'title' => 'Ano de Exercício das Férias.',
                                       'linha' => 1),
                               array ( 'nome' => 'status',
                                       'label' => 'Status:',
                                       'tipo' => 'combo',
                                       'required' => true,
                                       'array' => array('solicitada','confirmada','fruida','cancelada'),
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Status das férias',
                                       'linha' => 1),      
                               array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => true,
                                       'title' => 'Data do início das férias.',
                                       'linha' => 1),
                               array ( 'nome' => 'numDias',
                                       'label' => 'Dias:',
                                       'tipo' => 'numero',
                                       'col' => 2,
                                       'size' => 5,
                                       'required' => true,
                                       'title' => 'Dias de Férias.',
                                       'linha' => 1),
                               array ( 'nome' => 'periodo',
                                       'label' => 'Período:',
                                       'tipo' => 'combo',
                                       'array' => array(' ','1º','2º','3º','Único'),
                                       'size' => 20,
                                       'col' => 2,
                                       'title' => 'período de férias',
                                       'linha' => 1), 	         
                               array ( 'nome' => 'documento',
                                       'label' => 'Documento Solicitando 1/3:',
                                       'tipo' => 'texto',
                                       'size' => 50,                                   
                                       'title' => 'Documento solicitando 1/3.',
                                       'col' => 7,
                                       'linha' => 2),
                               array ( 'nome' => 'folha',
                                       'label' => 'Mês/Ano do pagamento da folha:',
                                       'tipo' => 'texto',
                                       'size' => 50,                                   
                                       'title' => 'mês/ano do pagamento da folha.',
                                       'col' => 5,
                                       'linha' => 2),
                               array ( 'linha' => 3,
                                       'col' => 12,
                                        'nome' => 'obs',
                                        'label' => 'Observação:',
                                        'tipo' => 'textarea',
                                        'size' => array(80,5)),
                               array ( 'nome' => 'matricula',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'hidden',
                                       'padrao' => $matriculaGrh,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 6)));

    # Matrícula para o Log
    $objeto->set_idusuario($idusuario);

    # Paginação
    $objeto->set_paginacao(true);
    $objeto->set_paginacaoInicial($paginacao);
    $objeto->set_paginacaoItens(6);


################################################################

    switch ($fase)
    {
            case "" :
            case "listar" :
            case "editar" :			
            case "excluir" :	
            case "gravar" :		
                $objeto->$fase($id);			
                break;

################################################################

            case 'solicitacaoFerias':
                $id = get('id');

                # pega os dados do servidor
                $nome = $pessoal->get_nome($matriculaGrh);
                $cargo = $pessoal->get_cargo($matriculaGrh);
                $perfil = $pessoal->get_perfil($matriculaGrh);
                $lotacao = $pessoal->get_lotacao($matriculaGrh);

                # Select das férias
                $select = "SELECT periodo,
                                  anoExercicio,
                                  DATE_FORMAT(dtInicial,'%d/%m/%Y'),
                                  numDias,
                                  DATE_FORMAT(ADDDATE(dtInicial,numDias-1),'%d/%m/%Y') as dtFinal
                             FROM tbferias
                            WHERE tbferias.idFerias = ".$id;

                # Acessa o Banco de dados
                $ferias = new Pessoal();
                $row = $ferias->select($select,false);
                $row = urlencode(serialize($row));  // Prepara para ser enviado por get

                # preenche outro array com o restante dos dados
                $servidor = array($nome,$cargo,$perfil,$lotacao,$matriculaGrh);
                $servidor = urlencode(serialize($servidor));  // Prepara para ser enviado por get        

                loadPage('../grhRelatorios/solicitacaoFerias.php?row='.$row.'&servidor='.$servidor,'_blank');  // envia um array pelo get
                loadPage('?');
                break;

################################################################

            case 'avisoFerias':
                $id = get('id');

                # muda status das férias
                $pessoal->mudaStatusFeriasSolicitadaConfirmada($id);

                # pega os dados do servidor
                $nome = $pessoal->get_nome($matriculaGrh);
                $cargo = $pessoal->get_cargo($matriculaGrh);
                $perfil = $pessoal->get_perfil($matriculaGrh);
                $lotacao = $pessoal->get_lotacao($matriculaGrh);

                # Select das férias
                $select = "SELECT periodo,
                                  anoExercicio,
                                  DATE_FORMAT(dtInicial,'%d/%m/%Y'),
                                  numDias,
                                  DATE_FORMAT(ADDDATE(dtInicial,numDias-1),'%d/%m/%Y') as dtFinal
                             FROM tbferias
                            WHERE tbferias.idFerias = ".$id;

                # Acessa o Banco de dados
                $ferias = new Pessoal();
                $row = $ferias->select($select,false);
                $row = urlencode(serialize($row));  // Prepara para ser enviado por get

                # preenche outro array com o restante dos dados
                $servidor = array($nome,$cargo,$perfil,$lotacao,$matriculaGrh);
                $servidor = urlencode(serialize($servidor));  // Prepara para ser enviado por get        

                loadPage('../relatorios/avisoFerias.php?row='.$row.'&servidor='.$servidor,'_blank');  // envia um array pelo get
                loadPage('?');
                break;
    }									 	 		

    $page->terminaPagina();
}
