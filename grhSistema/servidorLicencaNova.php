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

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');
    
    # Cria dinamicamente uma rotina em jquery para exibir ou não 
    # o input de acordo com as exgências na tbtipolicenca
    
        # Início do código 
        $script = '<script type="text/javascript" language="javascript">
            
            $(document).ready(function(){
                
                $("#tipo").hide();
                $("#labeltipo").hide();
                $("#alta").hide();
                $("#labelalta").hide();
                $("#dtInicioPeriodo").hide();
                $("#labeldtInicioPeriodo").hide();
                $("#dtFimPeriodo").hide();
                $("#labeldtFimPeriodo").hide();
                $("#processo").hide();
                $("#labelprocesso").hide();
                $("#dtPublicacao").hide();
                $("#labeldtPublicacao").hide();
                $("#pgPublicacao").hide();
                $("#labelpgPublicacao").hide();
                $("#dtPericia").hide();
                $("#labeldtPericia").hide();
                $("#num_Bim").hide();
                $("#labelnum_Bim").hide();
                    
                // Executa rotina sempre que o valor do select mudar
                $("#idTpLicenca").change(function(){
                
                // Guarda na variável id o valor alterado
                var id = $("#idTpLicenca option:selected").val();
                ';
    
        # Pega o id de cada tipo de licençca
        $selectTipo = $pessoal->select('SELECT idTpLicenca FROM tbtipolicenca ORDER BY idTpLicenca');
        
        # Licença Médica
        $script .= '
                        if(id == 1){
                            $("#tipo").show();
                            $("#labeltipo").show();
                            $("#alta").show();
                            $("#labelalta").show();
                        }else{
                            $("#tipo").hide();
                            $("#labeltipo").hide();
                            $("#alta").hide();
                            $("#labelalta").hide();
                        }
                        ';
        
        # Percorre o resultado
        foreach($selectTipo as $tipo) {
            $script .= '
                       if(id == '.$tipo[0].'){';
            
                # Exibe o período aquisitivo
                if($pessoal->get_licencaPeriodo($tipo[0]) == "Sim"){
                    $script .= ' $("#dtInicioPeriodo").show();
                                 $("#labeldtInicioPeriodo").show();
                                 $("#dtFimPeriodo").show();
                                 $("#labeldtFimPeriodo").show();';
                }else{
                    $script .= ' $("#dtInicioPeriodo").hide();
                                 $("#labeldtInicioPeriodo").hide();
                                 $("#dtFimPeriodo").hide();
                                 $("#labeldtFimPeriodo").hide();';
                }
                
                # Exibe o processo
                if($pessoal->get_licencaProcesso($tipo[0]) == "Sim"){
                    $script .= ' $("#processo").show();
                                 $("#labelprocesso").show();';
                }else{
                    $script .= ' $("#processo").hide();
                                 $("#labelprocesso").hide();';
                }
                
                # Exibe a publicação
                if($pessoal->get_licencaPublicacao($tipo[0]) == "Sim"){
                    $script .= ' $("#dtPublicacao").show();
                                 $("#labeldtPublicacao").show();
                                 $("#pgPublicacao").show();
                                 $("#labelpgPublicacao").show();';
                }else{
                    $script .= ' $("#dtPublicacao").hide();
                                 $("#labeldtPublicacao").hide();
                                 $("#pgPublicacao").hide();
                                 $("#labelpgPublicacao").hide();'; 
                }
                
                 # Verifica se essa licença necessita de perícia
                if($pessoal->get_licencaPericia($tipo[0]) == "Sim"){
                    $script .= ' $("#dtPericia").show();
                                 $("#labeldtPericia").show();
                                 $("#num_Bim").show();
                                 $("#labelnum_Bim").show();';
                }else{
                    $script .= ' $("#dtPericia").hide();
                                 $("#labeldtPericia").hide();
                                 $("#num_Bim").hide();
                                 $("#labelnum_Bim").hide();';
                }
            
            
            $script .= '  
                       }
                       ';
        }
    
         $script .= '});
                     });
                    </script>';

    # Começa uma nova página
    $page = new Page();
    $page->set_jscript($script);
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
        $objeto->set_rotinaExtra("get_DadosServidor");
        $objeto->set_rotinaExtraParametro($idServidorPesquisado); 

        # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
        $objeto->set_nome('Afastamentos e Licenças');

        # botão de voltar da lista
        $objeto->set_voltarLista('servidorMenu.php');

        # ordenação
        if(is_null($orderCampo)){
            $orderCampo = "1";
        }

        if(is_null($orderTipo)){
            $orderTipo = 'desc';
        }

        # select da lista
        $objeto->set_selectLista('SELECT CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")),
                                     CASE tipo
                                        WHEN 1 THEN "Inicial"
                                        WHEN 2 THEN "Prorrogação"
                                        end,
                                     IF(alta = 1,"Alta",NULL),
                                     dtInicial,
                                     numdias,
                                     ADDDATE(dtInicial,numDias-1),
                                     tblicenca.processo,
                                     dtInicioPeriodo,
                                     dtFimPeriodo,
                                     dtPublicacao,
                                     idLicenca
                                FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
                               WHERE idServidor='.$idServidorPesquisado.'
                            ORDER BY tblicenca.dtInicial desc');

        # select do edita
        $objeto->set_selectEdita('SELECT idTpLicenca,
                                   tipo,
                                   alta,
                                   dtInicioPeriodo,
                                   dtFimPeriodo,
                                   dtInicial,
                                   numDias,
                                   processo,
                                   dtPublicacao,
                                   pgPublicacao,
                                   dtPericia,
                                   num_Bim,
                                   obs,
                                   idServidor
                              FROM tblicenca WHERE idLicenca = '.$id);
    # ordem da lista
        $objeto->set_orderCampo($orderCampo);
        $objeto->set_orderTipo($orderTipo);
        $objeto->set_orderChamador('?fase=listar');

        # Caminhos
        $objeto->set_linkEditar('?fase=editar');
        $objeto->set_linkExcluir('?fase=excluir');
        $objeto->set_linkGravar('?fase=gravar');
        $objeto->set_linkListar('?fase=listar');

        # Parametros da tabela
        $objeto->set_label(array("Licença ou Afastamento","Tipo","Alta","Inicio","Dias","Término","Processo","P.Aq. Início","P.Aq. Término","Publicação"));
        #$objeto->set_width(array(15,5,5,8,5,8,14,10,10,10));	
        $objeto->set_align(array("left"));
        $objeto->set_funcao(array(NULL,NULL,NULL,'date_to_php',NULL,'date_to_php',NULL,'date_to_php','date_to_php','date_to_php'));
        $objeto->set_numeroOrdem(TRUE);
        $objeto->set_numeroOrdemTipo("d");

        # Classe do banco de dados
        $objeto->set_classBd('pessoal');

        # Nome da tabela
        $objeto->set_tabela('tblicenca');

        # Nome do campo id
        $objeto->set_idCampo('idLicenca');

        # Tipo de label do formulário
        $objeto->set_formLabelTipo(1);
        
        # Pega os dados da combo licenca
        $result = $pessoal->select('SELECT idTpLicenca,CONCAT(nome,IFNULL(concat(" (",lei,")"),""))
                                      FROM tbtipolicenca
                                  ORDER BY nome');
        array_unshift($result, array('Inicial',' -- Selecione o Tipo de Licença --')); # Adiciona o valor de nulo
        
        # Habilita ou não os controles de acordo com a licença
        

        # Campos para o formulario
        $objeto->set_campos(array(array('nome' => 'idTpLicenca',
                                        'label' => 'Tipo de Afastamento ou Licença:',
                                        'tipo' => 'combo',
                                        'size' => 50,
                                        'array' => $result,                      
                                        'readonly' => TRUE,
                                        'autofocus' => TRUE,
                                        'title' => 'Tipo do Adastamento/Licença.',
                                        'col' => 12,
                                        'linha' => 1),
                                array ( 'nome' => 'tipo',
                                        'label' => 'Tipo:',
                                        'tipo' => 'combo',
                                        'size' => 20,
                                        'required' => TRUE,
                                        'array' => array(array(NULL,""),
                                                         array(1,"Inicial"),
                                                         array(2,"Prorrogação")),
                                         'col' => 2,
                                         'linha' => 2),
                                 array ( 'nome' => 'alta',
                                         'label' => 'Alta:',
                                         'tipo' => 'combo',
                                         'required' => TRUE,
                                         'size' => 20,
                                         'array' => array(array(2,"Não"),
                                                          array(1,"Sim")),
                                         'col' => 2,
                                         'linha' => 2),
                                 array ( 'nome' => 'dtInicioPeriodo',
                                         'label' => 'Período Aquisitivo Início:',
                                         'tipo' => 'data',
                                         'size' => 20,               
                                         'title' => 'Data de início do período aquisitivo',
                                         'col' => 3,
                                         'linha' => 3),
                                 array ( 'nome' => 'dtFimPeriodo',
                                         'label' => 'Período Aquisitivo Término:',
                                         'tipo' => 'data',
                                         'size' => 20,
                                         'col' => 3,              
                                         'title' => 'Data de término do período aquisitivo',
                                         'linha' => 3),
                                 array ( 'nome' => 'dtInicial',
                                         'label' => 'Data Inicial:',
                                         'tipo' => 'data',
                                         'required' => TRUE,
                                         'size' => 20,
                                         'col' => 3,
                                         'title' => 'Data do início.',
                                         'linha' => 4),
                                 array ( 'nome' => 'numDias',
                                         'label' => 'Dias:',
                                         'tipo' => 'numero',
                                         'size' => 5,
                                         'required' => TRUE,
                                         'title' => 'Número de dias.',
                                         'col' => 2,
                                         'linha' => 4),
                                 array ( 'nome' => 'processo',
                                         'label' => 'Processo:',
                                         'tipo' => 'processo',
                                         'size' => 30,
                                         'col' => 5,
                                         'title' => 'Número do Processo',
                                         'linha' => 5),
                                 array ( 'nome' => 'dtPublicacao',
                                         'label' => 'Data da Pub. no DOERJ:',
                                         'tipo' => 'data',
                                         'size' => 20,
                                         'title' => 'Data da Publicação no DOERJ.',
                                         'col' => 3,
                                         'linha' => 6),
                                 array ( 'nome' => 'pgPublicacao',
                                         'label' => 'Pág:',
                                         'tipo' => 'texto',
                                         'size' => 5,                         
                                         'title' => 'A Página do DOERJ',
                                         'col' => 2,
                                         'linha' => 6),
                                 array ( 'nome' => 'dtPericia',
                                         'label' => 'Data da Perícia:',
                                         'tipo' => 'data',
                                         'size' => 20,
                                         'title' => 'Data da Perícia.',
                                         'col' => 3,
                                         'linha' => 7),
                                 array ( 'nome' => 'num_Bim',
                                         'label' => 'Número da Bim:',
                                         'tipo' => 'texto',
                                         'size' => 30,
                                         'col' => 2,
                                         'title' => 'Número da Bim',
                                         'linha' => 7),
                                 array ( 'linha' => 8,
                                         'nome' => 'obs',
                                         'label' => 'Observação:',
                                         'tipo' => 'textarea',
                                         'size' => array(80,3)),
                                 array ( 'nome' => 'idServidor',
                                         'label' => 'idServidor:',
                                         'tipo' => 'hidden',
                                         'padrao' => $idServidorPesquisado,
                                         'size' => 5,
                                         'linha' => 9)));

        # Log
        $objeto->set_idUsuario($idUsuario);
        $objeto->set_idServidorPesquisado($idServidorPesquisado);

        $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Relatório de Licença");
        $botaoRel->set_onClick("window.open('../grhRelatorios/servidorLicenca.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");

        $objeto->set_botaoListarExtra(array($botaoRel));

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
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}