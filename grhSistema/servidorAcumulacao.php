<?php
/**
 * Histórico de Gratificações Especiais
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
    $objeto->set_nome('Cadastro de Acumulações de Cargos Públicos');
    
    $origem = get_session("origem");
    if(is_null($origem)){
        $caminhoVolta = 'servidorMenu.php';
    }else{
        $caminhoVolta = $origem;
    }

    # botão de voltar da lista
    $objeto->set_voltarLista($caminhoVolta);

    # select da lista
    $objeto->set_selectLista('SELECT idAcumulacao,
                                     dtProcesso,
                                     processo,
                                     instituicao,
                                     cargo,                                     
                                     matricula
                                FROM tbacumulacao
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY dtProcesso');

    # select do edita
    $objeto->set_selectEdita('SELECT processo,
                                     dtProcesso,
                                     origemProcesso,
                                     dtEnvio,
                                     instituicao,
                                     cargo,                                     
                                     matricula,
                                     resultado,
                                     dtPublicacao,
                                     pgPublicacao,
                                     resultado1,
                                     dtPublicacao1,
                                     pgPublicacao1,
                                     resultado2,
                                     dtPublicacao2,
                                     pgPublicacao2,
                                     resultado3,
                                     dtPublicacao3,
                                     pgPublicacao3,
                                     obs,
                                     idServidor
                                FROM tbacumulacao
                               WHERE idAcumulacao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    
    $objeto->set_formatacaoCondicional(array( array('coluna' => 0,
                                                    'valor' => 'Em Aberto',
                                                    'operador' => '=',
                                                    'id' => 'emAberto'),  
                                              array('coluna' => 0,
                                                    'valor' => 'Ilícito',
                                                    'operador' => '=',
                                                    'id' => 'arquivado'),
                                              array('coluna' => 0,
                                                    'valor' => 'Lícito',
                                                    'operador' => '=',
                                                    'id' => 'vigenteReducao')   
                                                    ));

    # Parametros da tabela
    $objeto->set_label(array("Resultado","Data","Processo","Instituição","Cargo","Matrícula"));
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array(NULL,"date_to_php"));
    $objeto->set_classe(array("Acumulacao"));
    $objeto->set_metodo(array("get_resultado"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbacumulacao');

    # Nome do campo id
    $objeto->set_idCampo('idAcumulacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'processo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'size' => 30,
                                       'col' => 3,
                                       'title' => 'Número do Processo',
                                       'autofocus' => TRUE,
                                       'linha' => 1),
                               array ( 'nome' => 'dtProcesso',
                                       'label' => 'Data do Processo:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data de entrada do processo.',
                                       'linha' => 1),
                               array ( 'nome' => 'origemProcesso',
                                       'label' => 'Processo aberto por:',
                                       'tipo' => 'texto',
                                       'size' => 200,
                                       'valor' => NULL,
                                       'col' => 3,
                                       'title' => 'Órgão que abriu o processo.',
                                       'linha' => 1),
                               array ( 'nome' => 'dtEnvio',
                                       'label' => 'Data do Envio à COCPP/SECCG:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data do Envio ao Rio.',
                                       'linha' => 1),
                               array ( 'nome' => 'instituicao',
                                       'fieldset' => 'Outro Vínculo:',
                                       'label' => 'Instituição:',
                                       'tipo' => 'texto',
                                       'size' => 200,
                                       'col' => 5,
                                       'title' => 'Instituição Pública.',
                                       'linha' => 2),
                               array ( 'nome' => 'cargo',
                                       'label' => 'Cargo:',
                                       'tipo' => 'texto',
                                       'size' => 200,
                                       'col' => 5,
                                       'title' => 'Cargo na outra Instituição.',
                                       'linha' => 2),
                               array ( 'nome' => 'matricula',
                                       'label' => 'Matrícula:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 2,
                                       'title' => 'Matrícula da outra instituição.',
                                       'linha' => 2),
                               array ( 'nome' => 'resultado',
                                       'fieldset' => 'fecha',
                                       'label' => 'Resultado:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,NULL),
                                                        array(1,"Lícito"),
                                                        array(2,"Ilícito")),
                                       'size' => 2,
                                       'valor' => NULL,
                                       'col' => 2,
                                       'title' => 'Resultado.',
                                       'linha' => 4),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da publicação.',
                                       'linha' => 4),
                               array ( 'nome' => 'pgPublicacao',
                                       'label' => 'Página:',
                                       'tipo' => 'numero',
                                       'min' => 1,
                                       'max' => 9999,
                                       'size' => 5,
                                       'col' => 2,
                                       'title' => 'A página da Publicação no DOERJ.',
                                       'linha' => 4),
                               array ( 'nome' => 'resultado1',
                                       'fieldset' => 'Recursos:',
                                       'label' => 'Recurso 1:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,NULL),
                                                        array(1,"Lícito"),
                                                        array(2,"Ilícito")),
                                       'size' => 2,
                                       'valor' => NULL,
                                       'col' => 2,
                                       'title' => 'Resultado.',
                                       'linha' => 5),
                               array ( 'nome' => 'dtPublicacao1',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da publicação.',
                                       'linha' => 5),
                               array ( 'nome' => 'pgPublicacao1',
                                       'label' => 'Página:',
                                       'tipo' => 'numero',
                                       'size' => 5,
                                       'col' => 2,
                                       'min' => 1,
                                       'max' => 9999,
                                       'title' => 'A página da Publicação no DOERJ.',
                                       'linha' => 5),
                               array ( 'nome' => 'resultado2',
                                       'label' => 'Recurso 2:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,NULL),
                                                        array(1,"Lícito"),
                                                        array(2,"Ilícito")),
                                       'size' => 2,
                                       'valor' => NULL,
                                       'col' => 2,
                                       'title' => 'Resultado.',
                                       'linha' => 6),
                               array ( 'nome' => 'dtPublicacao2',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da publicação.',
                                       'linha' => 6),
                               array ( 'nome' => 'pgPublicacao2',
                                       'label' => 'Página:',
                                       'tipo' => 'numero',
                                       'min' => 1,
                                       'max' => 9999,
                                       'size' => 5,
                                       'col' => 2,
                                       'title' => 'A página da Publicação no DOERJ.',
                                       'linha' => 6),
                                array ( 'nome' => 'resultado3',
                                       'label' => 'Recurso 3:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,NULL),
                                                        array(1,"Lícito"),
                                                        array(2,"Ilícito")),
                                       'size' => 2,
                                       'valor' => NULL,
                                       'col' => 2,
                                       'title' => 'Resultado.',
                                       'linha' => 7),
                               array ( 'nome' => 'dtPublicacao3',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da publicação.',
                                       'linha' => 7),
                               array ( 'nome' => 'pgPublicacao3',                                      
                                       'label' => 'Página:',
                                       'tipo' => 'numero',
                                       'min' => 1,
                                       'max' => 9999,
                                       'size' => 5,
                                       'col' => 2,
                                       'title' => 'A página da Publicação no DOERJ.',
                                       'linha' => 7),
                                array ('linha' => 8,
                                       'fieldset' => 'fecha',
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
                                       'linha' => 5)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Acumulação de Cargo");    
    $botaoRel->set_url("../grhRelatorios/servidorAcumulacao.php");
    $botaoRel->set_target("_blank");
    
    # Normas
    $botao2 = new Button("Regras","?fase=regras");
    $botao2->set_title("Exibe as regras da acumulação");    
    #$botao2->set_url("../grhRelatorios/servidorGratificacao.php");
    $botao2->set_target("_blank");
    
    $objeto->set_botaoListarExtra(array($botaoRel,$botao2));
    
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
        
        case "regras" :
            $regra = new Procedimento();
            $regra->exibeProcedimento(24,$idUsuario);
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}