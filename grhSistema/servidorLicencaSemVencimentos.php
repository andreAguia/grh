<?php
/**
 * Histórico de Licença Sem Vencimentos de um Servidor
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
    
    # Verifica de onde veio
    $origem = get_session("origem");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    
    # Rotina em Jscript
    $script = '<script type="text/javascript" language="javascript">
        
            $(document).ready(function(){
            
                // Quando muda a data de término
                 $("#dtTermino").change(function(){
                    var dt1 = $("#dtInicial").val();
                    var dt2 = $("#dtTermino").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(dt2);
                    
                    dias = (data2 - data1)/(1000*3600*24)+1;

                    $("#periodo").val(dias);
                  });
                  

                 // Quando muda o período 
                 $("#periodo").change(function(){
                   
                    var dt1 = $("#dtInicial").val();
                    var periodo = $("#periodo").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (periodo * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#dtTermino").val(formatado);
                  });
                  
                // Quando muda a data Inicial
                $("#dtInicial").change(function(){
                   
                    var dt1 = $("#dtInicial").val();
                    var periodo = $("#periodo").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (periodo * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#dtTermino").val(formatado);
                  });
                  
                });
             </script>';
    # Começa uma nova página
    $page = new Page();
    if($fase == "editar"){
        $page->set_jscript($script);
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);
    
    $objeto->set_rotinaExtraListar("exibeRegraStatusLSV");

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Hstórico de Licença Sem Vencimentos');

    # botão de voltar da lista
    if(vazio($origem)){
        $objeto->set_voltarLista('servidorMenu.php');
    }else{
        $objeto->set_voltarLista($origem);
    }

    # select da lista
    $objeto->set_selectLista('SELECT idLicencaSemVencimentos,
                                     CASE tipo
                                         WHEN 1 THEN "Inicial"
                                         WHEN 2 THEN "Renovação"
                                         ELSE "--"
                                     END,
                                     idTpLicenca,
                                     idLicencaSemVencimentos,
                                     idLicencaSemVencimentos, 
                                     idLicencaSemVencimentos
                                FROM tblicencasemvencimentos
                          WHERE idServidor='.$idServidorPesquisado.'
                       ORDER BY dtSolicitacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT idTpLicenca,
                                     tipo,
                                     dtSolicitacao,
                                     processo,
                                     dtPublicacao,
                                     dtInicial,
                                     periodo,
                                     dtTermino,
                                     dtRetorno,
                                     crp,
                                     obs,
                                     idServidor
                                FROM tblicencasemvencimentos
                               WHERE idLicencaSemVencimentos = '.$id);
    
    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Status","Tipo","Licença Sem Vencimentos","Dados","Período","Entregou CRP?"));
    #$objeto->set_width(array(10,10,30,10,20,15));	
    $objeto->set_align(array("center","center","left","left","left"));
    #$objeto->set_funcao(array(NULL,NULL,NULL,"date_to_php"));
    
    $objeto->set_classe(array("LicencaSemVencimentos",NULL,"LicencaSemVencimentos","LicencaSemVencimentos","LicencaSemVencimentos","LicencaSemVencimentos"));
    $objeto->set_metodo(array("exibeStatus",NULL,"get_nomeLicenca","exibeProcessoPublicacao","exibePeriodo","exibeCrp"));
    
    $objeto->set_formatacaoCondicional(array( array('coluna' => 0,
                                                    'valor' => 'Em Aberto',
                                                    'operador' => '=',
                                                    'id' => 'emAberto'),  
                                              array('coluna' => 0,
                                                    'valor' => 'Arquivado',
                                                    'operador' => '=',
                                                    'id' => 'arquivado'),
                                              array('coluna' => 0,
                                                    'valor' => 'Aguardando CRP',
                                                    'operador' => '=',
                                                    'id' => 'agurdando'),
                                              array('coluna' => 0,
                                                    'valor' => 'INCOMPLETO',
                                                    'operador' => '=',
                                                    'id' => 'incompleto'),
                                              array('coluna' => 0,
                                                    'valor' => 'Vigente',
                                                    'operador' => '=',
                                                    'id' => 'vigenteReducao')   
                                                    ));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tblicencasemvencimentos');

    # Nome do campo id
    $objeto->set_idCampo('idLicencaSemVencimentos');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);
    
    # Pega os dados da combo licenca
    $result = $pessoal->select('SELECT idTpLicenca, tbtipolicenca.nome
                                  FROM tbtipolicenca
                                 WHERE (idTpLicenca = 5) OR (idTpLicenca = 8) OR (idTpLicenca = 16)
                              ORDER BY 2');
    array_unshift($result, array(NULL,' -- Selecione o Tipo de Afastamento ou Licença --')); # Adiciona o valor de nulo

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'idTpLicenca',
                                    'label' => 'Tipo de Afastamento ou Licença:',
                                    'tipo' => 'combo',
                                    'size' => 50,
                                    'array' => $result,
                                    'required' => TRUE,
                                    'autofocus' => TRUE,
                                    'title' => 'Tipo do Adastamento/Licença.',
                                    'col' => 12,
                                    'linha' => 1),
                            array ( 'nome' => 'tipo',
                                    'label' => 'Tipo:',
                                    'tipo' => 'combo',
                                    'array' => array(array(NULL,NULL),
                                                     array(1,"Inicial"),
                                                     array(2,"Renovação")),
                                    'required' => TRUE,
                                    'size' => 2,
                                    'valor' => 0,
                                    'col' => 2,
                                   'title' => 'Se é inicial ou renovação.',
                                   'linha' => 2),
                           array ( 'nome' => 'dtSolicitacao',
                                   'label' => 'Solicitado em:',
                                   'tipo' => 'data',
                                   'size' => 30,                                  
                                   'title' => 'A data da Solicitação.',
                                   'col' => 3,                                    
                                  'linha' => 2),
                          array ( 'nome' => 'processo',
                                  'label' => 'Processo:',
                                  'tipo' => 'processo',
                                  'size' => 30,
                                  'col' => 3,
                                  'title' => 'Número do Processo',
                                  'linha' => 2),
                          array ( 'nome' => 'dtPublicacao',
                                  'label' => 'Data da Publicação:',
                                  'tipo' => 'data',
                                  'size' => 10,
                                  'col' => 3,
                                  'title' => 'A Data da Publicação.',
                                  'linha' => 2),
                          array ( 'nome' => 'dtInicial',
                                  'label' => 'Data Inicial:',
                                  'tipo' => 'data',
                                  'size' => 20,
                                  'col' => 3,
                                  'title' => 'Data do início.',
                                  'linha' => 3),
                          array ( 'nome' => 'periodo',
                                  'label' => 'Dias:',
                                  'tipo' => 'numero',
                                  'min' => 1,
                                  'size' => 5,
                                  'title' => 'Número de dias.',
                                  'col' => 2,
                                  'linha' => 3),
                          array ( 'nome' => 'dtTermino',
                                  'label' => 'Data de Termino (previsto):',
                                  'tipo' => 'data',
                                  'size' => 20,
                                  'col' => 3,
                                  'title' => 'Data de Termino.',
                                  'linha' => 3),
                           array ( 'nome' => 'dtRetorno',
                                  'label' => 'Data de Retorno (de fato):',
                                  'tipo' => 'data',
                                  'size' => 10,
                                  'col' => 3,
                                  'title' => 'Data do início.',
                                  'linha' => 3),
                           array ('linha' => 4,
                                  'col' => 2,
                                  'nome' => 'crp',
                                  'title' => 'informa se entregou CRP',
                                  'label' => 'entregou CRP',
                                  'tipo' => 'combo',
                                  'array' => array(array(FALSE,"Não"),
                                                   array(TRUE,"Sim")),
                                  'size' => 10),
                          array ( 'linha' => 5,
                                  'nome' => 'obs',
                                  'label' => 'Observação:',
                                  'tipo' => 'textarea',
                                  'size' => array(80,3)),
                          array ( 'nome' => 'idServidor',
                                  'label' => 'idServidor',
                                  'tipo' => 'hidden',
                                  'padrao' => $idServidorPesquisado,
                                  'size' => 5,
                                  'linha' => 11)));
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Formação");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorLicencaSemVencimentos.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        
    # Status
    $botao2 = new Button("Status");
    $botao2->set_title("Exibe as regras de mudança automática do status");
    $botao2->set_onClick("abreFechaDivId('divRegrasLsv');");
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
        case "gravar" :
            $objeto->$fase($id);
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}