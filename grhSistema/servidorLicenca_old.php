<?php
/**
 * Histórico de Licenças de um servidor
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
    Grh::cabecalho();

    # pega o tipo de licença
    $idTpLicenca = post('idTpLicenca');

    # Pega o idTpLicenca
    if($fase == 'editar') {
        if(is_null($id)){
            $idTpLicenca = get_session('sessionLicenca');
        }else{
            $idTpLicenca = $pessoal->get_tipoLicenca($id);
        }
    }

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
        $objeto->set_nome('Afastamentos e Licenças');

        # botão de voltar da lista
        $objeto->set_voltarLista('servidorMenu.php');

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
        
        # link para editar
        $botao1 = new BotaoGrafico();
        $botao1->set_title('Edita');
        $botao1->set_label('');
        $botao1->set_url('?fase=editar&id=');     
        $botao1->set_image(PASTA_FIGURAS_GERAIS.'bullet_edit.png',20,20);
    
        ### select do edita
        if(($fase == 'editar') or ($fase == 'gravar')){            
            $selectEdita = 'SELECT idTpLicenca,';
            
            # campos tipo e alta
            if($idTpLicenca == 1){
                $selectEdita .= 'tipo,alta,';
            }

            # período aquisitivo
            if($pessoal->get_licencaPeriodo($idTpLicenca) == "Sim"){
                $selectEdita .= 'dtInicioPeriodo,dtFimPeriodo,';
            }

            # data inicial e numero de dias
            $selectEdita .= 'dtInicial,numDias,';

            # processo
            if($pessoal->get_licencaProcesso($idTpLicenca) == "Sim"){
                $selectEdita .= 'processo,';
            }

            # publicação no DOERJ
            if($pessoal->get_licencaPublicacao($idTpLicenca) == "Sim"){
               $selectEdita .= 'dtPublicacao,pgPublicacao,';
            }
            
            # perícia
            if($pessoal->get_licencaPericia($idTpLicenca) == "Sim"){
                $selectEdita .= 'dtPericia,num_Bim,';
            }
            
            # o resto do select
            $selectEdita .= 'obs,idServidor FROM tblicenca WHERE idLicenca = '.$id;

            $objeto->set_selectEdita($selectEdita);
        }
        
        # Caminhos
        $objeto->set_linkEditar('?fase=editar');    // Comentar caso não queira edição de licença prêmio
        $objeto->set_linkExcluir('?fase=excluir');
        $objeto->set_linkGravar('?fase=gravar');
        $objeto->set_linkListar('?fase=listar');
        $objeto->set_linkIncluir('?fase=incluir');

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
        
        echo $idTpLicenca;

        if(($fase == 'editar') or ($fase == 'gravar')){
            # preenche a combo idTpLicenca
            $result = array(array($idTpLicenca,$pessoal->get_licencaNome($idTpLicenca)));

            # Campos para o formulario
            $campos = array(array( 'nome' => 'idTpLicenca',
                                'label' => 'Tipo de Afastamento ou Licença:',
                                'tipo' => 'combo',
                                'size' => 20,
                                'array' => $result,                      
                                'readonly' => TRUE,
                                'autofocus' => TRUE,
                                'col' => 6,
                                'title' => 'Tipo do Adastamento/Licença.',
                                'linha' => 1));

            # Verifica se é licença Médica e exibe os campos de tipo(Inicial/Prorrogação) e (Com ou Sem Alta)
            if($idTpLicenca == 1){
             array_push($campos,array ( 'nome' => 'tipo',
                                        'label' => 'Tipo:',
                                        'tipo' => 'combo',
                                        'size' => 20,
                                        'required' => TRUE,
                                        'array' => array(array(NULL,""),
                                                         array(1,"Inicial"),
                                                         array(2,"Prorrogação")),
                                        'col' => 2,
                                        'linha' => 1),
                                array ( 'nome' => 'alta',
                                        'label' => 'Alta:',
                                        'tipo' => 'combo',
                                        'required' => TRUE,
                                        'size' => 20,
                                        'array' => array(array(2,"Não"),
                                                         array(1,"Sim")),
                                        'col' => 2,
                                        'linha' => 1));   
            }
            
            # Período (se esse tipo de licença tiver período aquisitivo)
            if($pessoal->get_licencaPeriodo($idTpLicenca) == "Sim"){
                # oculta controle se for licença premio para pegar os dados da publicaçao
                if($idTpLicenca == 6){
                    $tipo = 'hidden';
                    $notNULL = FALSE;
                }else{
                    $tipo = 'data';
                    $notNULL = TRUE;
                }    
            
                array_push($campos,array ( 'nome' => 'dtInicioPeriodo',
                                        'label' => 'Período Aquisitivo Início:',
                                        'tipo' => $tipo,
                                        'size' => 20,
                                        'required' => $notNULL,                 
                                        'title' => 'Data de início do período aquisitivo',
                                        'col' => 4,
                                        'linha' => 2),
                                    array ( 'nome' => 'dtFimPeriodo',
                                        'label' => 'Período Aquisitivo Término:',
                                        'tipo' => $tipo,
                                        'size' => 20,
                                        'col' => 4,
                                        'required' => $notNULL,                 
                                        'title' => 'Data de término do período aquisitivo',
                                        'linha' => 2));   
            }

            # A data Inicial 
            array_push($campos,array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'required' => TRUE,
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data do início.',
                                       'linha' => 3));
            
            # Número de dias
            $dias = $pessoal->get_licencaDias($idTpLicenca);    // verifica se tem valor fixo de dias
            
            if(!is_null($dias)){
                $valor = $dias;
            }else{
                $valor = NULL;
            }
                        
            # monta o controle
            array_push($campos,array ( 'nome' => 'numDias',
                                       'label' => 'Dias:',
                                       'tipo' => 'numero',
                                       'padrao' => $valor,
                                       'size' => 5,
                                       'required' => TRUE,
                                       'title' => 'Número de dias.',
                                       'col' => 2,
                                       'linha' => 3));

            # Verifica se essa licença necessita processo
            if(($pessoal->get_licencaProcesso($idTpLicenca) == "Sim")){
                
                array_push($campos,array ( 'nome' => 'processo',
                                           'label' => 'Processo:',
                                           'tipo' => 'processo',
                                           'size' => 30,
                                           'col' => 6,
                                           'padrao' => $valor,
                                           'title' => 'Número do Processo',
                                           'linha' => 4));
            }

            # Verifica la se essa licença necessita Publicação
            if($pessoal->get_licencaPublicacao($idTpLicenca) == "Sim"){

                array_push($campos,array ( 'nome' => 'dtPublicacao',
                                            'label' => 'Data da Pub. no DOERJ:',
                                            'tipo' => 'data',
                                            'size' => 20,
                                            'title' => 'Data da Publicação no DOERJ.',
                                            'linha' => 5),
                                    array ( 'nome' => 'pgPublicacao',
                                            'label' => 'Pág:',
                                            'tipo' => 'texto',
                                            'size' => 5,                         
                                            'title' => 'A Página do DOERJ',
                                            'linha' => 5));

            }
            
            # Verifica se essa licença necessita de perícia
            if($pessoal->get_licencaPericia($idTpLicenca) == "Sim"){
                array_push($campos,array ( 'nome' => 'dtPericia',
                                        'label' => 'Data da Perícia:',
                                        'tipo' => 'data',
                                        'size' => 20,
                                        'title' => 'Data da Perícia.',
                                        'col' => 3,
                                        'linha' => 6),
                                array ( 'nome' => 'num_Bim',
                                        'label' => 'Número da Bim:',
                                        'tipo' => 'texto',
                                        'size' => 30,
                                        'col' => 2,
                                        'title' => 'Número da Bim',
                                        'linha' => 6));   
            }
            
            # Observação e matrícula
            array_push($campos,array ('linha' => 7,
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
                                    'linha' => 8));

            $objeto->set_campos($campos);
        }

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
            case "gravar" :
                $objeto->$fase($id);
                break;

            case "incluir" :
                # Botão voltar
                botaoVoltar('?');
                
                # Limita o tamanho da tela
                $grid = new Grid();
                $grid->abreColuna(12);
                
                titulo("Inclusão de novo afastamento");

                # Pega os dados da combo licenca
                $result = $pessoal->select('SELECT idTpLicenca,CONCAT(nome,IFNULL(concat(" (",lei,")"),""))
                                            FROM tbtipolicenca
                                        ORDER BY nome');
                array_unshift($result, array('Inicial',' -- Selecione o Tipo de Licença --')); # Adiciona o valor de nulo

                $form = new Form('?fase=validaLicenca','inclusao');
                #$form->set_withTable(FALSE);
                $form->onSubmit("return enviardados();");        // insere rotina extra em jscript

                    br();

                    # Tipo de Licença
                    $controle = new Input('idTpLicenca','combo','Tipo de Afastamento ou Licença',1);
                    $controle->set_size(20);
                    $controle->set_required(TRUE);
                    $controle->set_autofocus(TRUE);
                    $controle->set_title('O tipo do Afastamento ou Licença');
                    $controle->set_array($result);
                    $controle->set_linha(1);
                    $form->add_item($controle);

                    # submit
                    $controle = new Input('submit','submit');
                    $controle->set_valor(' Cadastrar ');
                    $controle->set_size(20);
                    $controle->set_linha(2);
                    $form->add_item($controle);

                $form->show();
                
                $grid->fechaColuna();
                $grid->fechaGrid();
                break; 

            case "validaLicenca" :
                $erro = 0;
                $msgErro = '';

                # Verifica se foi digitado o tipo de licença
                if($idTpLicenca == 'Inicial'){
                    $msgErro.='Você deve informar o tipo de licença!!';
                    $erro = 1;
                }else{
                    # Verifica se a licença tem limitação por genero (sexo)
                    $sexo = $pessoal->get_sexo($idServidorPesquisado);
                    $limite = $pessoal->get_licencaSexo($idTpLicenca);

                    if(($limite <> 'Todos') AND ($limite <> $sexo)){
                        $msgErro.='Esse tipo de licença não é permitido para servidores desse sexo!!';
                        $erro = 1;
                    }
                }

                if ($erro == 0){
                    set_session('sessionLicenca',$idTpLicenca);
                    loadPage('?fase=editar');
                }else{
                    alert($msgErro);
                    back(1);
                }		
                break;

            case "emiteAim" :
                set_session('sessionAim',$id);
                abreDiv('divAguarde');
                loadPage('..\relatorios\relatorioAim.php');
                break;
        }			 	 		
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}