<?php
/**
 * Controle de Readaptaçao do Servidor
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
    $objeto->set_nome('Cadastro de Readaptação do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT case tipo
                                        when 1 then "Ex-Ofício"
                                        when 2 then "Solicitada"
                                      end,  
                                     idReadaptacao
                                FROM tbreadaptacao
                               WHERE idServidor = '.$idServidorPesquisado);

    # select do edita
    $objeto->set_selectEdita('SELECT tipo,
                                     idServidor
                                FROM tbreadaptacao
                               WHERE idReadaptacao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Tipo"));
    #$objeto->set_width(array(20,20,20,30));	
    $objeto->set_align(array("center"));
    #$objeto->set_funcao(array(NULL,NULL,"date_to_php","date_to_php","date_to_php","date_to_php"));
    
    # Número de Ordem
    $objeto->set_numeroOrdem(TRUE);
    $objeto->set_numeroOrdemTipo("d");

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbreadaptacao');

    # Nome do campo id
    $objeto->set_idCampo('idReadaptacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'tipo',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'array' => array(array(1,"Ex-Ofício"),array(2,"Solicitado")),
                                       'col' => 3,
                                       'size' => 12,
                                       'required' => TRUE,
                                       'title' => 'O Tipo da solicitaçao de readaptaçao.',
                                       'linha' => 1),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'idServidor',
                                       'linha' => 5)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Processo de readaptação");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorGratificacao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");

    # Legislação
    $botaoLegis = new Link("Legislação");
    $botaoLegis->set_class('button');
    $botaoLegis->set_title('Exibe as Legislação pertinente');
    $botaoLegis->set_onClick("window.open('https://docs.google.com/document/d/e/2PACX-1vRfb7P06MCBHAwd15hKm6KWV4-y0I8yBzlac58uAA-xCHeaL9aCbtSGCgGguZzaPQafvXYvGqWhwG0r/pub','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    
    # Fluxograma
    $imagem = new Imagem(PASTA_FIGURAS.'fluxograma.png',NULL,15,15);
    $botaoFluxo = new Button();
    $botaoFluxo->set_imagem($imagem);
    $botaoFluxo->set_title("Exibe o Fluxograma de todo o processo de readaptação");
    $botaoFluxo->set_onClick("window.open('../_diagramas/readaptacao.png','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=900,height=800');");
    
    #$objeto->set_botaoListarExtra(array($botaoRel,$botaoFluxo,$botaoLegis));
    
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
            $objeto->$fase($id); 
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}