<?php
/**
 * Controle do Abono de Permanencia
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
    $intra = new Intra();
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
    $objeto->set_nome('Controle de Redução da Carga Horária');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT dtSolicitacao,
                                     dtPericia,
                                     CASE
                                     WHEN resultado = 1 THEN "Deferido"
                                     WHEN resultado = 2 THEN "Indeferido"
                                     ELSE "---"
                                     END,
                                     dtPublicacao,
                                     dtInicio,
                                     periodo,
                                     idReducao
                                FROM tbreducao
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY dtSolicitacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtSolicitacao,
                                     dtPericia,
                                     resultado,
                                     dtPublicacao,
                                     dtInicio,
                                     periodo,
                                     numCiInicio,
                                     numCiTermino,
                                     idServidor
                                FROM tbreducao
                               WHERE idReducao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Solicitado em:","Pericia","Resultado","Publicação","Início","Período"));
    #$objeto->set_width(array(10,10,10,20,20,10,10));	
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array ("date_to_php","date_to_php",NULL,"date_to_php","date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbreducao');

    # Nome do campo id
    $objeto->set_idCampo('idReducao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtSolicitacao',
                                       'label' => 'Solicitado em:',
                                       'tipo' => 'data',
                                       'size' => 30,
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'title' => 'A data da Solicitação.',
                                       'col' => 3,
                                       'fieldset' => 'Da Solicitação',
                                       'linha' => 1),
                               array ( 'nome' => 'dtPericia',
                                       'label' => 'Data do envio a perícia:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'required' => TRUE,
                                       'title' => 'A data do envio do processo à perícia.',
                                       'linha' => 1),
                               array ( 'nome' => 'resultado',
                                       'label' => 'Resultado:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,""),array(1,"Deferido"),array(2,"Indeferido")),
                                       'size' => 20,                               
                                       'title' => 'Se o processo foi deferido ou indeferido',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A Data da Publicação no DOERJ.',
                                       'fieldset' => 'Quando Aprovado',
                                       'linha' => 2),
                               array ( 'nome' => 'dtInicio',
                                       'label' => 'Data do Inicio do Benefício:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data em que o servidor passou a receber o benefício.',
                                       'linha' => 2),
                               array ( 'nome' => 'periodo',
                                       'label' => 'Período em Meses:',
                                       'tipo' => 'texto',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'O período em meses do benefício.',
                                       'linha' => 2),
                               array ( 'nome' => 'numCiInicio',
                                       'label' => 'CI informando Início:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Número da Ci informando a chefia imediata do servidor da data de início do benefício.',
                                       'linha' => 3),
                               array ( 'nome' => 'numCiTermino',
                                       'label' => 'CI informando Término:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Número da Ci informando a chefia imediata do servidor da data de término do benefício.',
                                       'linha' => 3),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'linha' => 6)));
    
    # Alterar Senha
    $botaoSite = new Button("Site da GRH");
    $botaoSite->set_target('_blank');
    $botaoSite->set_title("Pagina no site da GRH sobre Abono Permanencia");
    $botaoSite->set_url("http://uenf.br/dga/grh/gerencia-de-recursos-humanos/reducao-de-carga-horaria/");

    # Legislação
    $botaoLegis = new Button("Legislação");
    $botaoLegis->set_disabled(TRUE);
    $botaoLegis->set_title('Exibe as Legislação pertinente');
    #$botaoLegis->set_onClick("window.open('https://docs.google.com/document/d/e/2PACX-1vRfb7P06MCBHAwd15hKm6KWV4-y0I8yBzlac58uAA-xCHeaL9aCbtSGCgGguZzaPQafvXYvGqWhwG0r/pub','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Processo de redução da carga horária");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorGratificacao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    
    # Fluxograma
    $imagem = new Imagem(PASTA_FIGURAS.'fluxograma.png',NULL,15,15);
    $botaoFluxo = new Button();
    $botaoFluxo->set_imagem($imagem);
    $botaoFluxo->set_title("Exibe o Fluxograma de todo o processo de readaptação");
    $botaoFluxo->set_onClick("window.open('../_diagramas/reducao.png','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=1300,height=700');");
    
    $objeto->set_botaoListarExtra(array($botaoSite,$botaoLegis,$botaoFluxo,$botaoRel));
    
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

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

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}